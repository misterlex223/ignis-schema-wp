<?php
/**
 * WP-CLI Schema Commands
 *
 * Command-line interface for managing WordPress schemas.
 *
 * @package WordPress_Schema_System
 * @version 1.0.0
 */

namespace WordPressSchemaSystem\CLI;

use WP_CLI;
use WP_CLI_Command;
use WordPressSchemaSystem\SchemaParser;
use WordPressSchemaSystem\ACFFieldGenerator;
use WordPressSchemaSystem\TaxonomyFieldGenerator;
use WordPressSchemaSystem\Generators\TypeScriptGenerator;

class SchemaCommand extends WP_CLI_Command {

    /**
     * Schema directory paths
     */
    private $post_types_dir;
    private $taxonomies_dir;

    public function __construct() {
        $this->post_types_dir = WP_CONTENT_DIR . '/schemas/post-types';
        $this->taxonomies_dir = WP_CONTENT_DIR . '/schemas/taxonomies';

        // Maintain backward compatibility
        $this->schema_dir = $this->post_types_dir;
    }

    /**
     * List all registered schemas
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Type of schema to list (post-type or taxonomy)
     * ---
     * default: post-type
     * options:
     *   - post-type
     *   - taxonomy
     *   - all
     * ---
     *
     * ## EXAMPLES
     *
     *     wp schema list
     *     wp schema list --type=taxonomy
     *     wp schema list --type=all
     *
     * @when after_wp_load
     */
    public function list($args, $assoc_args) {
        $type = $assoc_args['type'] ?? 'post-type';

        try {
            if ($type === 'all') {
                $this->listPostTypes();
                WP_CLI::line("");
                $this->listTaxonomies();
            } elseif ($type === 'taxonomy') {
                $this->listTaxonomies();
            } else {
                $this->listPostTypes();
            }

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * List post types
     */
    private function listPostTypes() {
        $schemas = SchemaParser::loadDirectory($this->post_types_dir);

        if (empty($schemas)) {
            WP_CLI::warning("No post type schemas found in {$this->post_types_dir}");
            return;
        }

        WP_CLI::line("Post Types:");
        $items = [];
        foreach ($schemas as $post_type => $schema) {
            $field_count = count($schema['fields'] ?? []);
            $items[] = [
                'post_type' => $post_type,
                'label' => $schema['label'] ?? 'N/A',
                'fields' => $field_count,
                'rest_api' => ($schema['show_in_rest'] ?? false) ? 'Yes' : 'No',
            ];
        }

        WP_CLI\Utils\format_items('table', $items, ['post_type', 'label', 'fields', 'rest_api']);
    }

    /**
     * List taxonomies
     */
    private function listTaxonomies() {
        $schemas = SchemaParser::loadTaxonomyDirectory($this->taxonomies_dir);

        if (empty($schemas)) {
            WP_CLI::warning("No taxonomy schemas found in {$this->taxonomies_dir}");
            return;
        }

        WP_CLI::line("Taxonomies:");
        $items = [];
        foreach ($schemas as $taxonomy => $schema) {
            $field_count = count($schema['fields'] ?? []);
            $post_types = implode(', ', $schema['post_types'] ?? []);
            $items[] = [
                'taxonomy' => $taxonomy,
                'label' => $schema['label'] ?? 'N/A',
                'hierarchical' => ($schema['hierarchical'] ?? false) ? 'Yes' : 'No',
                'post_types' => $post_types ?: 'N/A',
                'fields' => $field_count,
            ];
        }

        WP_CLI\Utils\format_items('table', $items, ['taxonomy', 'label', 'hierarchical', 'post_types', 'fields']);
    }

    /**
     * Show information about a specific schema
     *
     * ## OPTIONS
     *
     * <slug>
     * : The post type or taxonomy slug
     *
     * [--type=<type>]
     * : Type of schema (post-type or taxonomy)
     * ---
     * default: post-type
     * options:
     *   - post-type
     *   - taxonomy
     * ---
     *
     * [--format=<format>]
     * : Output format (table, json, yaml)
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     wp schema info contact
     *     wp schema info product --format=json
     *     wp schema info product-category --type=taxonomy
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args) {
        list($slug) = $args;
        $type = $assoc_args['type'] ?? 'post-type';
        $format = $assoc_args['format'] ?? 'table';

        try {
            $file = $this->findSchemaFile($slug, $type);
            $schema = SchemaParser::parse($file);

            if ($format === 'json') {
                WP_CLI::line(json_encode($schema, JSON_PRETTY_PRINT));
                return;
            }

            if ($format === 'yaml') {
                WP_CLI::line(yaml_emit($schema));
                return;
            }

            // Table format
            WP_CLI::line("Schema: {$schema['label']}");

            if ($type === 'taxonomy') {
                WP_CLI::line("Taxonomy: {$slug}");
                WP_CLI::line("Hierarchical: " . (($schema['hierarchical'] ?? false) ? 'Yes' : 'No'));
                if (!empty($schema['post_types'])) {
                    WP_CLI::line("Post Types: " . implode(', ', $schema['post_types']));
                }
            } else {
                WP_CLI::line("Post Type: {$slug}");
            }

            WP_CLI::line("Description: " . ($schema['description'] ?? 'N/A'));
            WP_CLI::line("REST API: " . (($schema['show_in_rest'] ?? false) ? 'Enabled' : 'Disabled'));
            WP_CLI::line("");

            if (!empty($schema['fields'])) {
                WP_CLI::line("Fields:");
                $fields = [];
                foreach ($schema['fields'] as $key => $field) {
                    $fields[] = [
                        'key' => $key,
                        'label' => $field['label'] ?? $key,
                        'type' => $field['type'] ?? 'N/A',
                        'required' => ($field['required'] ?? false) ? 'Yes' : 'No',
                    ];
                }
                WP_CLI\Utils\format_items('table', $fields, ['key', 'label', 'type', 'required']);
            }

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Validate a schema file
     *
     * ## OPTIONS
     *
     * <slug>
     * : The post type or taxonomy slug
     *
     * [--type=<type>]
     * : Type of schema (post-type or taxonomy)
     * ---
     * default: post-type
     * options:
     *   - post-type
     *   - taxonomy
     * ---
     *
     * ## EXAMPLES
     *
     *     wp schema validate contact
     *     wp schema validate product-category --type=taxonomy
     *
     * @when after_wp_load
     */
    public function validate($args, $assoc_args) {
        list($slug) = $args;
        $type = $assoc_args['type'] ?? 'post-type';

        try {
            $file = $this->findSchemaFile($slug, $type);
            $schema = SchemaParser::parse($file);

            WP_CLI::line("Validating {$type} schema: {$slug}");

            if ($type === 'taxonomy') {
                $errors = SchemaParser::validateTaxonomySchema($schema);
            } else {
                $errors = SchemaParser::validate($schema);
            }

            if (empty($errors)) {
                WP_CLI::success("Schema is valid!");
            } else {
                WP_CLI::warning("Schema has validation errors:");
                foreach ($errors as $error) {
                    WP_CLI::line("  - {$error}");
                }
            }

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Create a new schema from a natural language prompt
     *
     * ## OPTIONS
     *
     * <slug>
     * : The post type or taxonomy slug
     *
     * --prompt=<prompt>
     * : Natural language description
     *
     * [--type=<type>]
     * : Type of schema (post-type or taxonomy)
     * ---
     * default: post-type
     * options:
     *   - post-type
     *   - taxonomy
     * ---
     *
     * [--overwrite]
     * : Overwrite existing schema
     *
     * ## EXAMPLES
     *
     *     wp schema create event --prompt="Event management with date, time, location, and attendees"
     *     wp schema create product-category --type=taxonomy --prompt="Product categories with icon and color"
     *
     * @when after_wp_load
     */
    public function create($args, $assoc_args) {
        list($slug) = $args;
        $type = $assoc_args['type'] ?? 'post-type';

        if (empty($assoc_args['prompt'])) {
            WP_CLI::error("Please provide a --prompt with the schema description");
        }

        $prompt = $assoc_args['prompt'];
        $overwrite = isset($assoc_args['overwrite']);

        try {
            $dir = $type === 'taxonomy' ? $this->taxonomies_dir : $this->post_types_dir;
            $file = $dir . '/' . $slug . '.yaml';

            if (file_exists($file) && !$overwrite) {
                WP_CLI::error("Schema already exists. Use --overwrite to replace it.");
            }

            // Create directory if it doesn't exist
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            WP_CLI::line("Generating {$type} schema from prompt: {$prompt}");
            WP_CLI::line("");

            // TODO: Integrate with AI to generate schema
            // For now, create a basic template
            if ($type === 'taxonomy') {
                $schema = $this->generateTaxonomySchemaFromPrompt($slug, $prompt);
            } else {
                $schema = $this->generateSchemaFromPrompt($slug, $prompt);
            }

            $yaml = yaml_emit($schema);
            file_put_contents($file, $yaml);

            WP_CLI::success("Schema created: {$file}");
            WP_CLI::line("Review and edit the schema as needed, then run:");
            WP_CLI::line("  wp schema validate {$slug} --type={$type}");

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Export schema to TypeScript types
     *
     * ## OPTIONS
     *
     * <slug>
     * : The post type or taxonomy slug
     *
     * [--type=<type>]
     * : Type of schema (post-type or taxonomy)
     * ---
     * default: post-type
     * options:
     *   - post-type
     *   - taxonomy
     * ---
     *
     * [--output=<path>]
     * : Output directory path
     * ---
     * default: wp-content/typescript
     * ---
     *
     * ## EXAMPLES
     *
     *     wp schema export contact
     *     wp schema export product --output=/path/to/frontend/types
     *     wp schema export product-category --type=taxonomy
     *
     * @when after_wp_load
     */
    public function export($args, $assoc_args) {
        list($slug) = $args;
        $type = $assoc_args['type'] ?? 'post-type';
        $output_dir = $assoc_args['output'] ?? WP_CONTENT_DIR . '/typescript';

        try {
            $file = $this->findSchemaFile($slug, $type);
            $schema = SchemaParser::parse($file);

            if (!is_dir($output_dir)) {
                mkdir($output_dir, 0755, true);
            }

            if ($type === 'taxonomy') {
                $ts_code = TypeScriptGenerator::generateTaxonomy($schema);
            } else {
                $ts_code = TypeScriptGenerator::generate($schema);
            }

            $output_file = $output_dir . '/' . $slug . '.ts';
            file_put_contents($output_file, $ts_code);

            WP_CLI::success("TypeScript types exported to: {$output_file}");

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Export all schemas to TypeScript types
     *
     * ## OPTIONS
     *
     * [--output=<path>]
     * : Output directory path
     * ---
     * default: wp-content/typescript
     * ---
     *
     * [--type=<type>]
     * : Type of schemas to export (post-type, taxonomy, or all)
     * ---
     * default: all
     * options:
     *   - post-type
     *   - taxonomy
     *   - all
     * ---
     *
     * ## EXAMPLES
     *
     *     wp schema export-all
     *     wp schema export-all --output=/path/to/frontend/types
     *     wp schema export-all --type=taxonomy
     *
     * @when after_wp_load
     */
    public function export_all($args, $assoc_args) {
        $output_dir = $assoc_args['output'] ?? WP_CONTENT_DIR . '/typescript';
        $type = $assoc_args['type'] ?? 'all';

        try {
            WP_CLI::line("Exporting schemas to TypeScript...");

            $generated = [];

            if ($type === 'all' || $type === 'post-type') {
                if (is_dir($this->post_types_dir)) {
                    $pt_output = $output_dir . '/post-types';
                    $files = TypeScriptGenerator::generateFromDirectory($this->post_types_dir, $pt_output, 'post-type');
                    $generated['post-types'] = $files;
                    WP_CLI::line("  Post Types: " . count($files) . " types generated");
                }
            }

            if ($type === 'all' || $type === 'taxonomy') {
                if (is_dir($this->taxonomies_dir)) {
                    $tax_output = $output_dir . '/taxonomies';
                    $files = TypeScriptGenerator::generateFromDirectory($this->taxonomies_dir, $tax_output, 'taxonomy');
                    $generated['taxonomies'] = $files;
                    WP_CLI::line("  Taxonomies: " . count($files) . " types generated");
                }
            }

            // Generate main index if both types were exported
            if ($type === 'all' && !empty($generated)) {
                TypeScriptGenerator::generateAll($this->post_types_dir, $this->taxonomies_dir, $output_dir);
            }

            $total = array_sum(array_map('count', $generated));
            WP_CLI::success("Exported {$total} type definitions to: {$output_dir}");

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Register post types and taxonomies from schemas
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Type to register (post-type, taxonomy, or all)
     * ---
     * default: all
     * options:
     *   - post-type
     *   - taxonomy
     *   - all
     * ---
     *
     * [--slug=<slug>]
     * : Specific slug to register (optional, defaults to all)
     *
     * ## EXAMPLES
     *
     *     wp schema register
     *     wp schema register --type=post-type --slug=contact
     *     wp schema register --type=taxonomy
     *
     * @when after_wp_load
     */
    public function register($args, $assoc_args) {
        $type = $assoc_args['type'] ?? 'all';
        $specific_slug = $assoc_args['slug'] ?? null;

        try {
            $pt_count = 0;
            $tax_count = 0;

            // Register post types
            if ($type === 'all' || $type === 'post-type') {
                $schemas = SchemaParser::loadDirectory($this->post_types_dir);

                if ($specific_slug && !isset($schemas[$specific_slug])) {
                    WP_CLI::error("Post type schema not found: {$specific_slug}");
                }

                foreach ($schemas as $post_type => $schema) {
                    if ($specific_slug && $post_type !== $specific_slug) {
                        continue;
                    }

                    $post_type_args = SchemaParser::toPostTypeArgs($schema);
                    register_post_type($post_type, $post_type_args);

                    if (!empty($schema['fields'])) {
                        $field_group = ACFFieldGenerator::generateFieldGroup($schema);
                        ACFFieldGenerator::register($field_group);
                    }

                    WP_CLI::line("Registered post type: {$post_type}");
                    $pt_count++;
                }
            }

            // Register taxonomies
            if ($type === 'all' || $type === 'taxonomy') {
                $tax_schemas = SchemaParser::loadTaxonomyDirectory($this->taxonomies_dir);

                if ($specific_slug && !isset($tax_schemas[$specific_slug])) {
                    WP_CLI::error("Taxonomy schema not found: {$specific_slug}");
                }

                // Resolve post type relationships
                $post_type_schemas = $type === 'all' ? SchemaParser::loadDirectory($this->post_types_dir) : [];
                $relations = SchemaParser::resolvePostTypeTaxonomyRelations($post_type_schemas, $tax_schemas);

                foreach ($tax_schemas as $taxonomy => $schema) {
                    if ($specific_slug && $taxonomy !== $specific_slug) {
                        continue;
                    }

                    $taxonomy_args = SchemaParser::toTaxonomyArgs($schema);
                    $post_types = $relations[$taxonomy] ?? [];
                    register_taxonomy($taxonomy, $post_types, $taxonomy_args);

                    if (!empty($schema['fields'])) {
                        $field_group = TaxonomyFieldGenerator::generateFieldGroup($schema);
                        TaxonomyFieldGenerator::register($field_group);
                    }

                    WP_CLI::line("Registered taxonomy: {$taxonomy}" . (!empty($post_types) ? " for post types: " . implode(', ', $post_types) : ""));
                    $tax_count++;
                }
            }

            $message = [];
            if ($pt_count > 0) $message[] = "{$pt_count} post type(s)";
            if ($tax_count > 0) $message[] = "{$tax_count} taxonomy(ies)";

            WP_CLI::success("Registered " . implode(' and ', $message));

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Refresh/flush rewrite rules after schema changes
     *
     * ## EXAMPLES
     *
     *     wp schema flush
     *
     * @when after_wp_load
     */
    public function flush($args, $assoc_args) {
        flush_rewrite_rules(true);
        WP_CLI::success("Rewrite rules flushed");
    }

    /**
     * Find schema file by slug and type
     *
     * @param string $slug Post type or taxonomy slug
     * @param string $type Schema type ('post-type' or 'taxonomy')
     * @return string File path
     * @throws \Exception If file not found
     */
    private function findSchemaFile($slug, $type = 'post-type') {
        $dir = $type === 'taxonomy' ? $this->taxonomies_dir : $this->post_types_dir;
        $extensions = ['yaml', 'yml', 'json'];

        foreach ($extensions as $ext) {
            $file = $dir . '/' . $slug . '.' . $ext;
            if (file_exists($file)) {
                return $file;
            }
        }

        throw new \Exception("Schema not found: {$slug} (type: {$type})");
    }

    /**
     * Generate basic schema from prompt (placeholder for AI integration)
     *
     * @param string $post_type Post type slug
     * @param string $prompt Natural language prompt
     * @return array Schema array
     */
    private function generateSchemaFromPrompt($post_type, $prompt) {
        // This is a placeholder. In a real implementation, this would:
        // 1. Call an AI API (Claude, GPT, etc.) with the prompt
        // 2. Parse the AI response to extract fields
        // 3. Generate a complete schema

        $label = ucwords(str_replace('_', ' ', $post_type));
        $singular_label = rtrim($label, 's');

        return [
            'post_type' => $post_type,
            'label' => $label,
            'singular_label' => $singular_label,
            'description' => $prompt,
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-generic',
            'supports' => ['title', 'editor', 'thumbnail'],
            'has_archive' => true,
            'rewrite' => [
                'slug' => $post_type,
                'with_front' => false,
            ],
            'fields' => [
                'name' => [
                    'type' => 'text',
                    'label' => 'Name',
                    'required' => true,
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => 'Description',
                    'rows' => 4,
                ],
            ],
            'rest_api' => [
                'enabled' => true,
            ],
        ];
    }

    /**
     * Generate basic taxonomy schema from prompt (placeholder for AI integration)
     *
     * @param string $taxonomy Taxonomy slug
     * @param string $prompt Natural language prompt
     * @return array Schema array
     */
    private function generateTaxonomySchemaFromPrompt($taxonomy, $prompt) {
        // This is a placeholder. In a real implementation, this would:
        // 1. Call an AI API (Claude, GPT, etc.) with the prompt
        // 2. Parse the AI response to extract fields
        // 3. Generate a complete schema

        $label = ucwords(str_replace(['_', '-'], ' ', $taxonomy));
        $singular_label = rtrim($label, 's');

        // Determine if hierarchical based on keywords
        $hierarchical = (
            stripos($prompt, 'categor') !== false ||
            stripos($prompt, 'hierarchical') !== false ||
            stripos($prompt, 'parent') !== false ||
            stripos($prompt, 'nested') !== false
        );

        return [
            'taxonomy' => $taxonomy,
            'label' => $label,
            'singular_label' => $singular_label,
            'description' => $prompt,
            'public' => true,
            'show_in_rest' => true,
            'hierarchical' => $hierarchical,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => $taxonomy,
                'with_front' => false,
            ],
            'post_types' => [], // Will be configured later
            'fields' => [
                'icon' => [
                    'type' => 'image',
                    'label' => 'Icon',
                    'instructions' => 'Optional icon for this term',
                    'return_format' => 'url',
                    'preview_size' => 'thumbnail',
                ],
                'color' => [
                    'type' => 'color_picker',
                    'label' => 'Color',
                    'instructions' => 'Optional color for this term',
                ],
            ],
            'rest_api' => [
                'enabled' => true,
            ],
        ];
    }
}

// Register command
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('schema', SchemaCommand::class);
}
