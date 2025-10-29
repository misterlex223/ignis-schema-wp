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
use WordPressSchemaSystem\Generators\TypeScriptGenerator;

class SchemaCommand extends WP_CLI_Command {

    /**
     * Schema directory path
     */
    private $schema_dir;

    public function __construct() {
        $this->schema_dir = WP_CONTENT_DIR . '/schemas/post-types';
    }

    /**
     * List all registered schemas
     *
     * ## EXAMPLES
     *
     *     wp schema list
     *
     * @when after_wp_load
     */
    public function list($args, $assoc_args) {
        try {
            $schemas = SchemaParser::loadDirectory($this->schema_dir);

            if (empty($schemas)) {
                WP_CLI::warning("No schemas found in {$this->schema_dir}");
                return;
            }

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

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Show information about a specific schema
     *
     * ## OPTIONS
     *
     * <post_type>
     * : The post type slug
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
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args) {
        list($post_type) = $args;
        $format = $assoc_args['format'] ?? 'table';

        try {
            $file = $this->findSchemaFile($post_type);
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
            WP_CLI::line("Post Type: {$post_type}");
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
     * <post_type>
     * : The post type slug
     *
     * ## EXAMPLES
     *
     *     wp schema validate contact
     *
     * @when after_wp_load
     */
    public function validate($args, $assoc_args) {
        list($post_type) = $args;

        try {
            $file = $this->findSchemaFile($post_type);
            $schema = SchemaParser::parse($file);

            WP_CLI::line("Validating schema: {$post_type}");

            $errors = SchemaParser::validate($schema);

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
     * <post_type>
     * : The post type slug
     *
     * --prompt=<prompt>
     * : Natural language description of the post type
     *
     * [--overwrite]
     * : Overwrite existing schema
     *
     * ## EXAMPLES
     *
     *     wp schema create event --prompt="Event management with date, time, location, and attendees"
     *
     * @when after_wp_load
     */
    public function create($args, $assoc_args) {
        list($post_type) = $args;

        if (empty($assoc_args['prompt'])) {
            WP_CLI::error("Please provide a --prompt with the schema description");
        }

        $prompt = $assoc_args['prompt'];
        $overwrite = isset($assoc_args['overwrite']);

        try {
            $file = $this->schema_dir . '/' . $post_type . '.yaml';

            if (file_exists($file) && !$overwrite) {
                WP_CLI::error("Schema already exists. Use --overwrite to replace it.");
            }

            WP_CLI::line("Generating schema from prompt: {$prompt}");
            WP_CLI::line("");

            // TODO: Integrate with AI to generate schema
            // For now, create a basic template
            $schema = $this->generateSchemaFromPrompt($post_type, $prompt);

            $yaml = yaml_emit($schema);
            file_put_contents($file, $yaml);

            WP_CLI::success("Schema created: {$file}");
            WP_CLI::line("Review and edit the schema as needed, then run:");
            WP_CLI::line("  wp schema validate {$post_type}");

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Export schema to TypeScript types
     *
     * ## OPTIONS
     *
     * <post_type>
     * : The post type slug
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
     *
     * @when after_wp_load
     */
    public function export($args, $assoc_args) {
        list($post_type) = $args;
        $output_dir = $assoc_args['output'] ?? WP_CONTENT_DIR . '/typescript';

        try {
            $file = $this->findSchemaFile($post_type);
            $schema = SchemaParser::parse($file);

            if (!is_dir($output_dir)) {
                mkdir($output_dir, 0755, true);
            }

            $ts_code = TypeScriptGenerator::generate($schema);
            $output_file = $output_dir . '/' . $post_type . '.ts';

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
     * ## EXAMPLES
     *
     *     wp schema export-all
     *     wp schema export-all --output=/path/to/frontend/types
     *
     * @when after_wp_load
     */
    public function export_all($args, $assoc_args) {
        $output_dir = $assoc_args['output'] ?? WP_CONTENT_DIR . '/typescript';

        try {
            WP_CLI::line("Exporting all schemas to TypeScript...");

            $files = TypeScriptGenerator::generateFromDirectory($this->schema_dir, $output_dir);

            WP_CLI::success("Exported " . count($files) . " type definitions to: {$output_dir}");

            foreach ($files as $file) {
                WP_CLI::line("  - " . basename($file));
            }

        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }

    /**
     * Register post types and fields from schemas
     *
     * ## OPTIONS
     *
     * [--post_type=<post_type>]
     * : Specific post type to register (optional, defaults to all)
     *
     * ## EXAMPLES
     *
     *     wp schema register
     *     wp schema register --post_type=contact
     *
     * @when after_wp_load
     */
    public function register($args, $assoc_args) {
        $specific_post_type = $assoc_args['post_type'] ?? null;

        try {
            $schemas = SchemaParser::loadDirectory($this->schema_dir);

            if ($specific_post_type && !isset($schemas[$specific_post_type])) {
                WP_CLI::error("Schema not found: {$specific_post_type}");
            }

            $count = 0;
            foreach ($schemas as $post_type => $schema) {
                if ($specific_post_type && $post_type !== $specific_post_type) {
                    continue;
                }

                // Register post type
                $post_type_args = SchemaParser::toPostTypeArgs($schema);
                register_post_type($post_type, $post_type_args);

                // Register ACF fields
                if (!empty($schema['fields'])) {
                    $field_group = ACFFieldGenerator::generateFieldGroup($schema);
                    ACFFieldGenerator::register($field_group);
                }

                WP_CLI::line("Registered: {$post_type}");
                $count++;
            }

            WP_CLI::success("Registered {$count} post type(s) with ACF fields");

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
     * Find schema file by post type
     *
     * @param string $post_type Post type slug
     * @return string File path
     * @throws \Exception If file not found
     */
    private function findSchemaFile($post_type) {
        $extensions = ['yaml', 'yml', 'json'];

        foreach ($extensions as $ext) {
            $file = $this->schema_dir . '/' . $post_type . '.' . $ext;
            if (file_exists($file)) {
                return $file;
            }
        }

        throw new \Exception("Schema not found: {$post_type}");
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
}

// Register command
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('schema', SchemaCommand::class);
}
