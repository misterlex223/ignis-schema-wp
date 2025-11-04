<?php
/**
 * Schema Parser
 *
 * Parses YAML/JSON schema files and converts them to PHP arrays
 * for WordPress and ACF registration.
 *
 * @package WordPress_Schema_System
 * @version 1.0.0
 */

namespace WordPressSchemaSystem;

class SchemaParser {

    /**
     * Parse a schema file (YAML or JSON)
     *
     * @param string $file_path Path to schema file
     * @return array Parsed schema
     * @throws \Exception If file not found or invalid format
     */
    public static function parse($file_path) {
        if (!file_exists($file_path)) {
            throw new \Exception("Schema file not found: {$file_path}");
        }

        $extension = pathinfo($file_path, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'yaml':
            case 'yml':
                return self::parseYaml($file_path);
            case 'json':
                return self::parseJson($file_path);
            default:
                throw new \Exception("Unsupported schema format: {$extension}");
        }
    }

    /**
     * Parse YAML file
     *
     * @param string $file_path Path to YAML file
     * @return array Parsed data
     */
    private static function parseYaml($file_path) {
        if (!function_exists('yaml_parse_file')) {
            // Fallback to Symfony YAML component if available
            if (class_exists('\Symfony\Component\Yaml\Yaml')) {
                return \Symfony\Component\Yaml\Yaml::parseFile($file_path);
            }
            throw new \Exception("YAML parser not available. Install php-yaml extension or symfony/yaml package.");
        }

        $data = yaml_parse_file($file_path);

        if ($data === false) {
            throw new \Exception("Failed to parse YAML file: {$file_path}");
        }

        return $data;
    }

    /**
     * Parse JSON file
     *
     * @param string $file_path Path to JSON file
     * @return array Parsed data
     */
    private static function parseJson($file_path) {
        $content = file_get_contents($file_path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse JSON file: " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Validate schema structure
     *
     * @param array $schema Schema data
     * @return array Validation errors (empty if valid)
     */
    public static function validate($schema) {
        $errors = [];

        // Check required fields
        if (empty($schema['post_type'])) {
            $errors[] = "Missing required field: post_type";
        } elseif (strlen($schema['post_type']) > 20) {
            $errors[] = "post_type must be 20 characters or less";
        } elseif (!preg_match('/^[a-z0-9_]+$/', $schema['post_type'])) {
            $errors[] = "post_type must contain only lowercase letters, numbers, and underscores";
        }

        if (empty($schema['label'])) {
            $errors[] = "Missing required field: label";
        }

        // Validate fields
        if (isset($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as $field_key => $field) {
                $field_errors = self::validateField($field_key, $field);
                $errors = array_merge($errors, $field_errors);
            }
        }

        return $errors;
    }

    /**
     * Validate individual field
     *
     * @param string $field_key Field key
     * @param array $field Field configuration
     * @return array Validation errors
     */
    private static function validateField($field_key, $field) {
        $errors = [];

        // Validate field key naming
        if (!preg_match('/^[a-z0-9_]+$/', $field_key)) {
            $errors[] = "Field key '{$field_key}' must contain only lowercase letters, numbers, and underscores";
        }

        // Check required field properties
        if (empty($field['type'])) {
            $errors[] = "Field '{$field_key}' missing required property: type";
        }

        if (empty($field['label'])) {
            $errors[] = "Field '{$field_key}' missing required property: label";
        }

        // Validate field type
        $valid_types = [
            'text', 'textarea', 'number', 'email', 'url', 'password',
            'select', 'checkbox', 'radio', 'true_false',
            'wysiwyg', 'oembed', 'image', 'file', 'gallery',
            'post_object', 'relationship', 'taxonomy', 'user',
            'repeater', 'group', 'flexible_content',
            'date_picker', 'time_picker', 'date_time_picker',
            'color_picker', 'google_map', 'link'
        ];

        if (!empty($field['type']) && !in_array($field['type'], $valid_types)) {
            $errors[] = "Field '{$field_key}' has invalid type: {$field['type']}";
        }

        // Validate sub_fields for repeater/group/flexible_content
        if (in_array($field['type'] ?? '', ['repeater', 'group']) && !empty($field['sub_fields'])) {
            foreach ($field['sub_fields'] as $sub_key => $sub_field) {
                $sub_errors = self::validateField($sub_key, $sub_field);
                $errors = array_merge($errors, $sub_errors);
            }
        }

        // Validate conditional logic
        if (!empty($field['conditional_logic'])) {
            if (!is_array($field['conditional_logic'])) {
                $errors[] = "Field '{$field_key}' conditional_logic must be an array";
            }
        }

        return $errors;
    }

    /**
     * Get schema defaults
     *
     * @return array Default values
     */
    public static function getDefaults() {
        return [
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'hierarchical' => false,
            'has_archive' => true,
            'fields' => [],
            'rest_api' => [
                'enabled' => true,
            ],
        ];
    }

    /**
     * Merge schema with defaults
     *
     * @param array $schema Schema data
     * @return array Merged schema
     */
    public static function mergeDefaults($schema) {
        $defaults = self::getDefaults();
        return array_merge($defaults, $schema);
    }

    /**
     * Load all schemas from a directory
     *
     * @param string $directory Directory path
     * @return array Array of schemas indexed by post_type
     */
    public static function loadDirectory($directory) {
        if (!is_dir($directory)) {
            throw new \Exception("Directory not found: {$directory}");
        }

        $schemas = [];
        $files = glob($directory . '/*.{yaml,yml,json}', GLOB_BRACE);

        foreach ($files as $file) {
            try {
                $schema = self::parse($file);
                $post_type = $schema['post_type'] ?? basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
                $schemas[$post_type] = $schema;
            } catch (\Exception $e) {
                error_log("Failed to load schema from {$file}: " . $e->getMessage());
            }
        }

        return $schemas;
    }

    /**
     * Convert schema to WordPress post type args
     *
     * @param array $schema Schema data
     * @return array WordPress register_post_type() arguments
     */
    public static function toPostTypeArgs($schema) {
        $singular_label = $schema['singular_label'] ?? $schema['label'];

        $labels = [
            'name' => $schema['label'],
            'singular_name' => $singular_label,
            'add_new' => 'Add New',
            'add_new_item' => 'Add New ' . $singular_label,
            'edit_item' => 'Edit ' . $singular_label,
            'new_item' => 'New ' . $singular_label,
            'view_item' => 'View ' . $singular_label,
            'view_items' => 'View ' . $schema['label'],
            'search_items' => 'Search ' . $schema['label'],
            'not_found' => 'No ' . strtolower($schema['label']) . ' found',
            'not_found_in_trash' => 'No ' . strtolower($schema['label']) . ' found in Trash',
            'all_items' => 'All ' . $schema['label'],
            'archives' => $singular_label . ' Archives',
            'attributes' => $singular_label . ' Attributes',
            'insert_into_item' => 'Insert into ' . strtolower($singular_label),
            'uploaded_to_this_item' => 'Uploaded to this ' . strtolower($singular_label),
        ];

        $args = [
            'labels' => $labels,
            'public' => $schema['public'] ?? true,
            'show_in_rest' => $schema['show_in_rest'] ?? true,
            'supports' => $schema['supports'] ?? ['title', 'editor', 'thumbnail'],
            'hierarchical' => $schema['hierarchical'] ?? false,
            'has_archive' => $schema['has_archive'] ?? true,
        ];

        // Add optional fields
        if (!empty($schema['description'])) {
            $args['description'] = $schema['description'];
        }

        if (!empty($schema['menu_icon'])) {
            $args['menu_icon'] = $schema['menu_icon'];
        }

        if (!empty($schema['rewrite'])) {
            $args['rewrite'] = $schema['rewrite'];
        }

        // REST API configuration
        if (!empty($schema['rest_api']['base'])) {
            $args['rest_base'] = $schema['rest_api']['base'];
        }

        if (!empty($schema['rest_api']['controller'])) {
            $args['rest_controller_class'] = $schema['rest_api']['controller'];
        }

        return $args;
    }

    /**
     * Validate taxonomy schema structure
     *
     * @param array $schema Taxonomy schema data
     * @return array Validation errors (empty if valid)
     */
    public static function validateTaxonomySchema($schema) {
        $errors = [];

        // Check required fields
        if (empty($schema['taxonomy'])) {
            $errors[] = "Missing required field: taxonomy";
        } elseif (strlen($schema['taxonomy']) > 32) {
            $errors[] = "taxonomy must be 32 characters or less";
        } elseif (!preg_match('/^[a-z0-9_-]+$/', $schema['taxonomy'])) {
            $errors[] = "taxonomy must contain only lowercase letters, numbers, underscores, and hyphens";
        }

        if (empty($schema['label'])) {
            $errors[] = "Missing required field: label";
        }

        // Validate post_types if specified
        if (isset($schema['post_types'])) {
            if (!is_array($schema['post_types'])) {
                $errors[] = "post_types must be an array";
            }
        }

        // Validate fields
        if (isset($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as $field_key => $field) {
                $field_errors = self::validateField($field_key, $field);
                $errors = array_merge($errors, $field_errors);
            }
        }

        return $errors;
    }

    /**
     * Get taxonomy schema defaults
     *
     * @return array Default values
     */
    public static function getTaxonomyDefaults() {
        return [
            'public' => true,
            'show_in_rest' => true,
            'hierarchical' => false,
            'show_admin_column' => true,
            'query_var' => true,
            'fields' => [],
            'rest_api' => [
                'enabled' => true,
            ],
        ];
    }

    /**
     * Merge taxonomy schema with defaults
     *
     * @param array $schema Taxonomy schema data
     * @return array Merged schema
     */
    public static function mergeTaxonomyDefaults($schema) {
        $defaults = self::getTaxonomyDefaults();
        return array_merge($defaults, $schema);
    }

    /**
     * Load all taxonomy schemas from a directory
     *
     * @param string $directory Directory path
     * @return array Array of schemas indexed by taxonomy
     */
    public static function loadTaxonomyDirectory($directory) {
        if (!is_dir($directory)) {
            return []; // Return empty array if directory doesn't exist yet
        }

        $schemas = [];
        $files = glob($directory . '/*.{yaml,yml,json}', GLOB_BRACE);

        foreach ($files as $file) {
            try {
                $schema = self::parse($file);
                $taxonomy = $schema['taxonomy'] ?? basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
                $schemas[$taxonomy] = $schema;
            } catch (\Exception $e) {
                error_log("Failed to load taxonomy schema from {$file}: " . $e->getMessage());
            }
        }

        return $schemas;
    }

    /**
     * Convert schema to WordPress taxonomy args
     *
     * @param array $schema Taxonomy schema data
     * @return array WordPress register_taxonomy() arguments
     */
    public static function toTaxonomyArgs($schema) {
        $singular_label = $schema['singular_label'] ?? $schema['label'];
        $hierarchical = $schema['hierarchical'] ?? false;

        // Generate labels
        $labels = [
            'name' => $schema['label'],
            'singular_name' => $singular_label,
            'search_items' => 'Search ' . $schema['label'],
            'all_items' => 'All ' . $schema['label'],
            'edit_item' => 'Edit ' . $singular_label,
            'update_item' => 'Update ' . $singular_label,
            'add_new_item' => 'Add New ' . $singular_label,
            'new_item_name' => 'New ' . $singular_label . ' Name',
            'menu_name' => $schema['label'],
        ];

        // Add hierarchical-specific labels
        if ($hierarchical) {
            $labels['parent_item'] = 'Parent ' . $singular_label;
            $labels['parent_item_colon'] = 'Parent ' . $singular_label . ':';
        } else {
            $labels['popular_items'] = 'Popular ' . $schema['label'];
            $labels['separate_items_with_commas'] = 'Separate ' . strtolower($schema['label']) . ' with commas';
            $labels['add_or_remove_items'] = 'Add or remove ' . strtolower($schema['label']);
            $labels['choose_from_most_used'] = 'Choose from the most used ' . strtolower($schema['label']);
            $labels['not_found'] = 'No ' . strtolower($schema['label']) . ' found';
        }

        $args = [
            'labels' => $labels,
            'public' => $schema['public'] ?? true,
            'show_in_rest' => $schema['show_in_rest'] ?? true,
            'hierarchical' => $hierarchical,
            'show_admin_column' => $schema['show_admin_column'] ?? true,
            'query_var' => $schema['query_var'] ?? true,
        ];

        // Add optional fields
        if (!empty($schema['description'])) {
            $args['description'] = $schema['description'];
        }

        if (isset($schema['show_ui'])) {
            $args['show_ui'] = $schema['show_ui'];
        }

        if (isset($schema['show_in_menu'])) {
            $args['show_in_menu'] = $schema['show_in_menu'];
        }

        if (isset($schema['show_in_nav_menus'])) {
            $args['show_in_nav_menus'] = $schema['show_in_nav_menus'];
        }

        if (isset($schema['show_tagcloud'])) {
            $args['show_tagcloud'] = $schema['show_tagcloud'];
        }

        if (!empty($schema['rewrite'])) {
            $args['rewrite'] = $schema['rewrite'];
        }

        if (isset($schema['capabilities'])) {
            $args['capabilities'] = $schema['capabilities'];
        }

        // REST API configuration
        if (!empty($schema['rest_api']['base'])) {
            $args['rest_base'] = $schema['rest_api']['base'];
        }

        if (!empty($schema['rest_api']['controller'])) {
            $args['rest_controller_class'] = $schema['rest_api']['controller'];
        }

        return $args;
    }

    /**
     * Resolve bidirectional post type <-> taxonomy relationships
     *
     * This method analyzes both post type and taxonomy schemas to build
     * a complete mapping of which taxonomies should be registered to which post types.
     * It handles both ways of defining the relationship:
     * - In post type schema: taxonomies: [cat1, cat2]
     * - In taxonomy schema: post_types: [post1, post2]
     *
     * @param array $post_type_schemas Post type schemas indexed by post_type
     * @param array $taxonomy_schemas Taxonomy schemas indexed by taxonomy
     * @return array Mapping of [taxonomy => [post_types]]
     */
    public static function resolvePostTypeTaxonomyRelations($post_type_schemas, $taxonomy_schemas) {
        $relations = [];

        // Initialize with empty arrays for all taxonomies
        foreach ($taxonomy_schemas as $taxonomy => $schema) {
            $relations[$taxonomy] = [];
        }

        // First pass: taxonomies defining their post types
        foreach ($taxonomy_schemas as $taxonomy => $schema) {
            if (!empty($schema['post_types']) && is_array($schema['post_types'])) {
                $relations[$taxonomy] = array_merge(
                    $relations[$taxonomy] ?? [],
                    $schema['post_types']
                );
            }
        }

        // Second pass: post types defining their taxonomies
        foreach ($post_type_schemas as $post_type => $schema) {
            if (!empty($schema['taxonomies']) && is_array($schema['taxonomies'])) {
                foreach ($schema['taxonomies'] as $taxonomy) {
                    if (!isset($relations[$taxonomy])) {
                        $relations[$taxonomy] = [];
                    }
                    if (!in_array($post_type, $relations[$taxonomy])) {
                        $relations[$taxonomy][] = $post_type;
                    }
                }
            }
        }

        // Remove duplicates and empty arrays
        foreach ($relations as $taxonomy => $post_types) {
            $relations[$taxonomy] = array_unique($post_types);
        }

        return $relations;
    }
}
