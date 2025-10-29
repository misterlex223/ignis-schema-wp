<?php
/**
 * TypeScript Type Generator
 *
 * Generates TypeScript interfaces and types from schema definitions
 * for use in frontend applications.
 *
 * @package WordPress_Schema_System
 * @version 1.0.0
 */

namespace WordPressSchemaSystem\Generators;

class TypeScriptGenerator {

    /**
     * Generate TypeScript types from schema
     *
     * @param array $schema Schema data
     * @return string TypeScript code
     */
    public static function generate($schema) {
        $post_type = $schema['post_type'];
        $type_name = self::toTypeName($post_type);

        $output = [];
        $output[] = "/**";
        $output[] = " * Generated TypeScript types for {$schema['label']}";
        $output[] = " * @generated from schema: {$post_type}.yaml";
        $output[] = " */";
        $output[] = "";

        // Generate field types
        $output[] = "// ACF Fields Interface";
        $output[] = "export interface {$type_name}ACF {";

        if (!empty($schema['fields'])) {
            foreach ($schema['fields'] as $field_key => $field_config) {
                $field_lines = self::generateFieldType($field_key, $field_config, '  ');
                $output = array_merge($output, $field_lines);
            }
        }

        $output[] = "}";
        $output[] = "";

        // Generate WordPress post interface
        $output[] = "// WordPress Post Interface";
        $output[] = "export interface {$type_name} {";
        $output[] = "  id: number;";
        $output[] = "  date: string;";
        $output[] = "  date_gmt: string;";
        $output[] = "  modified: string;";
        $output[] = "  modified_gmt: string;";
        $output[] = "  slug: string;";
        $output[] = "  status: 'publish' | 'future' | 'draft' | 'pending' | 'private';";
        $output[] = "  type: '{$post_type}';";
        $output[] = "  link: string;";
        $output[] = "  title: {";
        $output[] = "    rendered: string;";
        $output[] = "  };";

        if (in_array('editor', $schema['supports'] ?? [])) {
            $output[] = "  content: {";
            $output[] = "    rendered: string;";
            $output[] = "    protected: boolean;";
            $output[] = "  };";
        }

        if (in_array('excerpt', $schema['supports'] ?? [])) {
            $output[] = "  excerpt: {";
            $output[] = "    rendered: string;";
            $output[] = "    protected: boolean;";
            $output[] = "  };";
        }

        if (in_array('thumbnail', $schema['supports'] ?? [])) {
            $output[] = "  featured_media: number;";
        }

        $output[] = "  acf: {$type_name}ACF;";
        $output[] = "  _links: {";
        $output[] = "    self: Array<{ href: string }>;";
        $output[] = "    collection: Array<{ href: string }>;";
        $output[] = "  };";
        $output[] = "}";
        $output[] = "";

        // Generate API response types
        $output[] = "// API Response Types";
        $output[] = "export type {$type_name}Response = {$type_name};";
        $output[] = "export type {$type_name}ListResponse = {$type_name}[];";
        $output[] = "";

        // Generate create/update request types
        $output[] = "// Create/Update Request Type";
        $output[] = "export interface {$type_name}CreateRequest {";
        $output[] = "  title: string;";
        $output[] = "  status?: 'publish' | 'future' | 'draft' | 'pending' | 'private';";

        if (in_array('editor', $schema['supports'] ?? [])) {
            $output[] = "  content?: string;";
        }

        if (in_array('excerpt', $schema['supports'] ?? [])) {
            $output[] = "  excerpt?: string;";
        }

        $output[] = "  acf?: Partial<{$type_name}ACF>;";
        $output[] = "}";
        $output[] = "";
        $output[] = "export type {$type_name}UpdateRequest = Partial<{$type_name}CreateRequest>;";
        $output[] = "";

        // Generate helper types
        $output[] = self::generateHelperTypes();

        return implode("\n", $output);
    }

    /**
     * Generate TypeScript field type
     *
     * @param string $field_key Field key
     * @param array $field_config Field configuration
     * @param string $indent Indentation
     * @return array Lines of TypeScript code
     */
    private static function generateFieldType($field_key, $field_config, $indent = '') {
        $lines = [];
        $ts_type = self::mapFieldTypeToTS($field_config);
        $optional = ($field_config['required'] ?? false) ? '' : '?';

        // Add comment with label and instructions
        if (!empty($field_config['label'])) {
            $lines[] = $indent . "/** {$field_config['label']}";
            if (!empty($field_config['instructions'])) {
                $lines[] = $indent . " * " . str_replace("\n", "\n{$indent} * ", $field_config['instructions']);
            }
            $lines[] = $indent . " */";
        }

        $lines[] = $indent . "{$field_key}{$optional}: {$ts_type};";

        return $lines;
    }

    /**
     * Map schema field type to TypeScript type
     *
     * @param array $field_config Field configuration
     * @return string TypeScript type
     */
    private static function mapFieldTypeToTS($field_config) {
        $type = $field_config['type'] ?? 'text';

        switch ($type) {
            case 'text':
            case 'textarea':
            case 'email':
            case 'url':
            case 'password':
            case 'wysiwyg':
            case 'oembed':
            case 'color_picker':
                return 'string';

            case 'number':
                return 'number';

            case 'true_false':
                return 'boolean';

            case 'select':
                if (!empty($field_config['choices'])) {
                    $choices = array_keys($field_config['choices']);
                    $quoted = array_map(function($choice) {
                        return "'{$choice}'";
                    }, $choices);

                    if ($field_config['multiple'] ?? false) {
                        return 'Array<' . implode(' | ', $quoted) . '>';
                    }

                    return implode(' | ', $quoted);
                }
                return $field_config['multiple'] ?? false ? 'string[]' : 'string';

            case 'checkbox':
                if (!empty($field_config['choices'])) {
                    $choices = array_keys($field_config['choices']);
                    $quoted = array_map(function($choice) {
                        return "'{$choice}'";
                    }, $choices);
                    return 'Array<' . implode(' | ', $quoted) . '>';
                }
                return 'string[]';

            case 'radio':
                if (!empty($field_config['choices'])) {
                    $choices = array_keys($field_config['choices']);
                    $quoted = array_map(function($choice) {
                        return "'{$choice}'";
                    }, $choices);
                    return implode(' | ', $quoted);
                }
                return 'string';

            case 'date_picker':
            case 'time_picker':
            case 'date_time_picker':
                return 'string'; // ISO 8601 format

            case 'image':
                $return_format = $field_config['return_format'] ?? 'array';
                if ($return_format === 'url') {
                    return 'string';
                } elseif ($return_format === 'id') {
                    return 'number';
                }
                return 'WPImage';

            case 'file':
                $return_format = $field_config['return_format'] ?? 'array';
                if ($return_format === 'url') {
                    return 'string';
                } elseif ($return_format === 'id') {
                    return 'number';
                }
                return 'WPFile';

            case 'gallery':
                return 'WPImage[]';

            case 'post_object':
            case 'relationship':
                $return_format = $field_config['return_format'] ?? 'object';
                $multiple = $field_config['multiple'] ?? false;

                if ($return_format === 'id') {
                    return $multiple ? 'number[]' : 'number';
                }

                $post_types = $field_config['post_type'] ?? ['post'];
                if (count($post_types) === 1) {
                    $type_name = self::toTypeName($post_types[0]);
                    return $multiple ? "{$type_name}[]" : $type_name;
                }

                return $multiple ? 'WPPost[]' : 'WPPost';

            case 'taxonomy':
                $return_format = $field_config['return_format'] ?? 'id';
                $field_type = $field_config['field_type'] ?? 'checkbox';
                $multiple = in_array($field_type, ['checkbox', 'multi_select']);

                if ($return_format === 'id') {
                    return $multiple ? 'number[]' : 'number';
                }

                return $multiple ? 'WPTerm[]' : 'WPTerm';

            case 'user':
                $return_format = $field_config['return_format'] ?? 'array';
                $multiple = $field_config['multiple'] ?? false;

                if ($return_format === 'id') {
                    return $multiple ? 'number[]' : 'number';
                }

                return $multiple ? 'WPUser[]' : 'WPUser';

            case 'repeater':
                if (!empty($field_config['sub_fields'])) {
                    $sub_type = self::generateSubFieldsInterface($field_config['sub_fields']);
                    return $sub_type . '[]';
                }
                return 'any[]';

            case 'group':
                if (!empty($field_config['sub_fields'])) {
                    return self::generateSubFieldsInterface($field_config['sub_fields']);
                }
                return 'Record<string, any>';

            case 'flexible_content':
                if (!empty($field_config['layouts'])) {
                    $layout_types = [];
                    foreach ($field_config['layouts'] as $layout_key => $layout_config) {
                        $layout_name = self::toTypeName($layout_key);
                        $layout_types[] = $layout_name . 'Layout';
                    }
                    return 'Array<' . implode(' | ', $layout_types) . '>';
                }
                return 'any[]';

            case 'google_map':
                return '{lat: number; lng: number; address?: string}';

            case 'link':
                $return_format = $field_config['return_format'] ?? 'array';
                if ($return_format === 'url') {
                    return 'string';
                }
                return '{url: string; title: string; target: string}';

            default:
                return 'any';
        }
    }

    /**
     * Generate TypeScript interface for sub-fields
     *
     * @param array $sub_fields Sub-fields configuration
     * @return string TypeScript interface
     */
    private static function generateSubFieldsInterface($sub_fields) {
        $props = [];

        foreach ($sub_fields as $sub_key => $sub_config) {
            $ts_type = self::mapFieldTypeToTS($sub_config);
            $optional = ($sub_config['required'] ?? false) ? '' : '?';
            $props[] = "{$sub_key}{$optional}: {$ts_type}";
        }

        return '{' . implode('; ', $props) . '}';
    }

    /**
     * Convert post type slug to TypeScript type name
     *
     * @param string $slug Post type slug
     * @return string TypeScript type name (PascalCase)
     */
    private static function toTypeName($slug) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $slug)));
    }

    /**
     * Generate helper types
     *
     * @return string TypeScript helper types
     */
    private static function generateHelperTypes() {
        return <<<'TS'
// WordPress Helper Types
export interface WPImage {
  ID: number;
  id: number;
  title: string;
  filename: string;
  filesize: number;
  url: string;
  link: string;
  alt: string;
  author: string;
  description: string;
  caption: string;
  name: string;
  status: string;
  uploaded_to: number;
  date: string;
  modified: string;
  menu_order: number;
  mime_type: string;
  type: string;
  subtype: string;
  icon: string;
  width: number;
  height: number;
  sizes: {
    thumbnail?: string;
    'thumbnail-width'?: number;
    'thumbnail-height'?: number;
    medium?: string;
    'medium-width'?: number;
    'medium-height'?: number;
    large?: string;
    'large-width'?: number;
    'large-height'?: number;
    full?: string;
    'full-width'?: number;
    'full-height'?: number;
    [key: string]: string | number | undefined;
  };
}

export interface WPFile {
  ID: number;
  id: number;
  title: string;
  filename: string;
  filesize: number;
  url: string;
  link: string;
  author: string;
  description: string;
  caption: string;
  name: string;
  status: string;
  uploaded_to: number;
  date: string;
  modified: string;
  mime_type: string;
  type: string;
  subtype: string;
  icon: string;
}

export interface WPPost {
  ID: number;
  id: number;
  post_title: string;
  post_type: string;
  post_status: string;
  post_date: string;
  post_modified: string;
}

export interface WPTerm {
  term_id: number;
  name: string;
  slug: string;
  term_group: number;
  term_taxonomy_id: number;
  taxonomy: string;
  description: string;
  parent: number;
  count: number;
}

export interface WPUser {
  ID: number;
  user_firstname: string;
  user_lastname: string;
  user_email: string;
  user_login: string;
  user_nicename: string;
  display_name: string;
}
TS;
    }

    /**
     * Generate types for all schemas in a directory
     *
     * @param string $schema_dir Schema directory path
     * @param string $output_dir Output directory for TypeScript files
     * @return array Generated file paths
     */
    public static function generateFromDirectory($schema_dir, $output_dir) {
        if (!is_dir($output_dir)) {
            mkdir($output_dir, 0755, true);
        }

        $generated = [];
        $schemas = \WordPressSchemaSystem\SchemaParser::loadDirectory($schema_dir);

        foreach ($schemas as $post_type => $schema) {
            $ts_code = self::generate($schema);
            $output_file = $output_dir . '/' . $post_type . '.ts';

            file_put_contents($output_file, $ts_code);
            $generated[] = $output_file;
        }

        // Generate index file
        self::generateIndexFile($schemas, $output_dir);

        return $generated;
    }

    /**
     * Generate index file that exports all types
     *
     * @param array $schemas All schemas
     * @param string $output_dir Output directory
     * @return void
     */
    private static function generateIndexFile($schemas, $output_dir) {
        $lines = [];
        $lines[] = "/**";
        $lines[] = " * Generated TypeScript types index";
        $lines[] = " * @generated";
        $lines[] = " */";
        $lines[] = "";

        foreach ($schemas as $post_type => $schema) {
            $lines[] = "export * from './{$post_type}';";
        }

        file_put_contents($output_dir . '/index.ts', implode("\n", $lines) . "\n");
    }
}
TS;
    }
}
