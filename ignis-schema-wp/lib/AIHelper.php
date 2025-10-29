<?php
/**
 * AI Helper Functions
 *
 * Functions to assist AI models in generating and working with schemas.
 *
 * @package WordPress_Schema_System
 * @version 1.0.0
 */

namespace WordPressSchemaSystem;

class AIHelper {

    /**
     * Convert natural language prompt to schema
     *
     * This is a helper function that provides guidance to AI models
     * on how to convert natural language descriptions into schemas.
     *
     * @param string $prompt Natural language description
     * @param string $post_type Suggested post type slug
     * @return array Schema structure
     */
    public static function promptToSchema($prompt, $post_type) {
        // This function provides a template for AI to follow
        // AI should analyze the prompt and fill in the schema accordingly

        $schema = [
            'post_type' => $post_type,
            'label' => self::inferLabel($post_type),
            'singular_label' => self::inferSingularLabel($post_type),
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
            'fields' => [],
            'rest_api' => [
                'enabled' => true,
            ],
        ];

        return $schema;
    }

    /**
     * Infer field type from field name
     *
     * Helps AI determine appropriate field types based on field names.
     *
     * @param string $field_name Field name
     * @return string Suggested field type
     */
    public static function inferFieldType($field_name) {
        $field_name_lower = strtolower($field_name);

        // Email detection
        if (strpos($field_name_lower, 'email') !== false) {
            return 'email';
        }

        // URL detection
        if (strpos($field_name_lower, 'url') !== false ||
            strpos($field_name_lower, 'link') !== false ||
            strpos($field_name_lower, 'website') !== false) {
            return 'url';
        }

        // Number detection
        if (strpos($field_name_lower, 'price') !== false ||
            strpos($field_name_lower, 'amount') !== false ||
            strpos($field_name_lower, 'quantity') !== false ||
            strpos($field_name_lower, 'count') !== false ||
            strpos($field_name_lower, 'number') !== false ||
            strpos($field_name_lower, 'age') !== false ||
            strpos($field_name_lower, 'weight') !== false ||
            strpos($field_name_lower, 'height') !== false ||
            strpos($field_name_lower, 'width') !== false) {
            return 'number';
        }

        // Boolean detection
        if (strpos($field_name_lower, 'is_') === 0 ||
            strpos($field_name_lower, 'has_') === 0 ||
            strpos($field_name_lower, 'enable_') === 0 ||
            strpos($field_name_lower, 'featured') !== false ||
            strpos($field_name_lower, 'active') !== false) {
            return 'true_false';
        }

        // Date detection
        if (strpos($field_name_lower, 'date') !== false ||
            strpos($field_name_lower, 'birthday') !== false ||
            strpos($field_name_lower, 'dob') !== false) {
            return 'date_picker';
        }

        // Time detection
        if (strpos($field_name_lower, 'time') !== false) {
            return 'time_picker';
        }

        // Image detection
        if (strpos($field_name_lower, 'image') !== false ||
            strpos($field_name_lower, 'photo') !== false ||
            strpos($field_name_lower, 'picture') !== false ||
            strpos($field_name_lower, 'avatar') !== false ||
            strpos($field_name_lower, 'thumbnail') !== false) {
            return 'image';
        }

        // Gallery detection
        if (strpos($field_name_lower, 'images') !== false ||
            strpos($field_name_lower, 'photos') !== false ||
            strpos($field_name_lower, 'gallery') !== false) {
            return 'gallery';
        }

        // File detection
        if (strpos($field_name_lower, 'file') !== false ||
            strpos($field_name_lower, 'attachment') !== false ||
            strpos($field_name_lower, 'document') !== false ||
            strpos($field_name_lower, 'pdf') !== false) {
            return 'file';
        }

        // Rich text detection
        if (strpos($field_name_lower, 'description') !== false ||
            strpos($field_name_lower, 'content') !== false ||
            strpos($field_name_lower, 'bio') !== false ||
            strpos($field_name_lower, 'about') !== false) {
            return strlen($field_name_lower) > 100 ? 'wysiwyg' : 'textarea';
        }

        // Category/select detection
        if (strpos($field_name_lower, 'category') !== false ||
            strpos($field_name_lower, 'categories') !== false ||
            strpos($field_name_lower, 'type') !== false ||
            strpos($field_name_lower, 'status') !== false ||
            strpos($field_name_lower, 'department') !== false) {
            return 'select';
        }

        // Default to text
        return 'text';
    }

    /**
     * Infer label from post type slug
     *
     * @param string $slug Post type slug
     * @return string Human-readable label
     */
    private static function inferLabel($slug) {
        return ucwords(str_replace(['_', '-'], ' ', $slug));
    }

    /**
     * Infer singular label from post type slug
     *
     * @param string $slug Post type slug
     * @return string Singular label
     */
    private static function inferSingularLabel($slug) {
        $label = self::inferLabel($slug);
        return rtrim($label, 's');
    }

    /**
     * Generate field configuration suggestions
     *
     * Provides AI with field configuration recommendations based on field type.
     *
     * @param string $field_type Field type
     * @return array Suggested configuration
     */
    public static function suggestFieldConfig($field_type) {
        $suggestions = [
            'text' => [
                'placeholder' => 'Enter text...',
                'maxlength' => 200,
            ],
            'textarea' => [
                'rows' => 4,
                'placeholder' => 'Enter description...',
                'maxlength' => 500,
                'new_lines' => 'br',
            ],
            'number' => [
                'min' => 0,
                'step' => 1,
            ],
            'email' => [
                'placeholder' => 'email@example.com',
            ],
            'url' => [
                'placeholder' => 'https://example.com',
            ],
            'wysiwyg' => [
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => true,
            ],
            'image' => [
                'return_format' => 'array',
                'preview_size' => 'medium',
            ],
            'gallery' => [
                'min' => 1,
                'max' => 10,
            ],
            'date_picker' => [
                'display_format' => 'm/d/Y',
                'return_format' => 'Y-m-d',
                'first_day' => 1,
            ],
            'true_false' => [
                'ui' => true,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ],
            'select' => [
                'allow_null' => false,
                'multiple' => false,
            ],
        ];

        return $suggestions[$field_type] ?? [];
    }

    /**
     * Validate and sanitize post type slug
     *
     * @param string $slug Post type slug
     * @return string Sanitized slug
     */
    public static function sanitizePostTypeSlug($slug) {
        // Convert to lowercase
        $slug = strtolower($slug);

        // Replace spaces and special characters with underscores
        $slug = preg_replace('/[^a-z0-9_]/', '_', $slug);

        // Remove consecutive underscores
        $slug = preg_replace('/_+/', '_', $slug);

        // Trim underscores from ends
        $slug = trim($slug, '_');

        // Limit to 20 characters
        $slug = substr($slug, 0, 20);

        return $slug;
    }

    /**
     * Validate and sanitize field key
     *
     * @param string $key Field key
     * @return string Sanitized key
     */
    public static function sanitizeFieldKey($key) {
        // Convert to lowercase
        $key = strtolower($key);

        // Replace spaces with underscores
        $key = str_replace(' ', '_', $key);

        // Remove special characters except underscores
        $key = preg_replace('/[^a-z0-9_]/', '', $key);

        // Remove consecutive underscores
        $key = preg_replace('/_+/', '_', $key);

        // Trim underscores from ends
        $key = trim($key, '_');

        return $key;
    }

    /**
     * Generate documentation for schema
     *
     * Creates human-readable documentation from schema.
     *
     * @param array $schema Schema data
     * @return string Markdown documentation
     */
    public static function generateDocumentation($schema) {
        $doc = [];

        $doc[] = "# {$schema['label']}";
        $doc[] = "";
        $doc[] = "**Post Type:** `{$schema['post_type']}`";
        $doc[] = "";

        if (!empty($schema['description'])) {
            $doc[] = $schema['description'];
            $doc[] = "";
        }

        $doc[] = "## REST API";
        $doc[] = "";
        $rest_enabled = $schema['show_in_rest'] ?? $schema['rest_api']['enabled'] ?? true;
        $doc[] = "- **Enabled:** " . ($rest_enabled ? 'Yes' : 'No');

        if ($rest_enabled) {
            $rest_base = $schema['rest_api']['base'] ?? $schema['post_type'];
            $doc[] = "- **Endpoint:** `/wp-json/wp/v2/{$rest_base}`";
        }
        $doc[] = "";

        if (!empty($schema['fields'])) {
            $doc[] = "## Fields";
            $doc[] = "";

            foreach ($schema['fields'] as $field_key => $field_config) {
                $required = ($field_config['required'] ?? false) ? ' **[Required]**' : '';
                $doc[] = "### `{$field_key}`{$required}";
                $doc[] = "";
                $doc[] = "- **Label:** {$field_config['label']}";
                $doc[] = "- **Type:** `{$field_config['type']}`";

                if (!empty($field_config['instructions'])) {
                    $doc[] = "- **Instructions:** {$field_config['instructions']}";
                }

                if (!empty($field_config['default_value'])) {
                    $doc[] = "- **Default:** `{$field_config['default_value']}`";
                }

                // Type-specific details
                if ($field_config['type'] === 'select' && !empty($field_config['choices'])) {
                    $doc[] = "- **Choices:**";
                    foreach ($field_config['choices'] as $value => $label) {
                        $doc[] = "  - `{$value}`: {$label}";
                    }
                }

                $doc[] = "";
            }
        }

        return implode("\n", $doc);
    }

    /**
     * Schema examples for AI reference
     *
     * Provides example schemas for different use cases.
     *
     * @return array Example schemas
     */
    public static function getExamples() {
        return [
            'simple' => [
                'description' => 'Simple content type with basic fields',
                'schema' => [
                    'post_type' => 'article',
                    'label' => 'Articles',
                    'fields' => [
                        'author_name' => [
                            'type' => 'text',
                            'label' => 'Author Name',
                            'required' => true,
                        ],
                        'summary' => [
                            'type' => 'textarea',
                            'label' => 'Summary',
                            'rows' => 3,
                        ],
                    ],
                ],
            ],
            'ecommerce' => [
                'description' => 'E-commerce product with pricing and inventory',
                'schema' => [
                    'post_type' => 'product',
                    'label' => 'Products',
                    'fields' => [
                        'sku' => [
                            'type' => 'text',
                            'label' => 'SKU',
                            'required' => true,
                        ],
                        'price' => [
                            'type' => 'number',
                            'label' => 'Price',
                            'min' => 0,
                            'step' => 0.01,
                            'prepend' => '$',
                        ],
                        'in_stock' => [
                            'type' => 'true_false',
                            'label' => 'In Stock',
                            'ui' => true,
                        ],
                        'product_images' => [
                            'type' => 'gallery',
                            'label' => 'Images',
                            'min' => 1,
                            'max' => 10,
                        ],
                    ],
                ],
            ],
            'relationship' => [
                'description' => 'Content with relationships to other posts',
                'schema' => [
                    'post_type' => 'project',
                    'label' => 'Projects',
                    'fields' => [
                        'team_members' => [
                            'type' => 'relationship',
                            'label' => 'Team Members',
                            'post_type' => ['employee'],
                            'max' => 10,
                        ],
                        'project_category' => [
                            'type' => 'taxonomy',
                            'label' => 'Category',
                            'taxonomy' => 'project_category',
                            'field_type' => 'select',
                        ],
                    ],
                ],
            ],
        ];
    }
}
