<?php
/**
 * Taxonomy Field Generator
 *
 * Converts taxonomy schema field definitions to ACF term meta field group arrays
 * for programmatic registration.
 *
 * @package WordPress_Schema_System
 * @version 1.0.0
 */

namespace WordPressSchemaSystem;

class TaxonomyFieldGenerator {

    /**
     * Generate ACF field group for taxonomy term meta
     *
     * @param array $schema Taxonomy schema data
     * @return array ACF field group array
     */
    public static function generateFieldGroup($schema) {
        $taxonomy = $schema['taxonomy'];
        $group_key = 'group_taxonomy_' . $taxonomy;

        $field_group = [
            'key' => $group_key,
            'title' => $schema['label'] . ' Term Fields',
            'fields' => [],
            'location' => [
                [
                    [
                        'param' => 'taxonomy',
                        'operator' => '==',
                        'value' => $taxonomy,
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => $schema['description'] ?? '',
        ];

        // Enable REST API if configured
        if (!empty($schema['show_in_rest']) || !empty($schema['rest_api']['enabled'])) {
            $field_group['show_in_rest'] = 1;
        }

        // Generate fields
        if (!empty($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as $field_key => $field_config) {
                $acf_field = self::generateField($field_key, $field_config, $taxonomy);
                if ($acf_field) {
                    $field_group['fields'][] = $acf_field;
                }
            }
        }

        return $field_group;
    }

    /**
     * Generate individual ACF field for taxonomy term
     *
     * @param string $field_key Field key
     * @param array $field_config Field configuration from schema
     * @param string $parent_key Parent key for namespacing
     * @return array ACF field array
     */
    public static function generateField($field_key, $field_config, $parent_key = '') {
        $acf_key = 'field_' . $parent_key . '_' . $field_key;

        $field = [
            'key' => $acf_key,
            'label' => $field_config['label'] ?? ucwords(str_replace('_', ' ', $field_key)),
            'name' => $field_key,
            'type' => $field_config['type'],
            'instructions' => $field_config['instructions'] ?? '',
            'required' => $field_config['required'] ?? 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
        ];

        // Add default value if specified
        if (isset($field_config['default_value'])) {
            $field['default_value'] = $field_config['default_value'];
        }

        // Add placeholder if specified
        if (!empty($field_config['placeholder'])) {
            $field['placeholder'] = $field_config['placeholder'];
        }

        // Add wrapper settings
        if (!empty($field_config['wrapper'])) {
            if (isset($field_config['wrapper']['width'])) {
                $field['wrapper']['width'] = $field_config['wrapper']['width'];
            }
            if (isset($field_config['wrapper']['class'])) {
                $field['wrapper']['class'] = $field_config['wrapper']['class'];
            }
        }

        // Add conditional logic
        if (!empty($field_config['conditional_logic'])) {
            $field['conditional_logic'] = self::convertConditionalLogic($field_config['conditional_logic'], $parent_key);
        }

        // Type-specific configuration
        $field = self::addTypeSpecificConfig($field, $field_config, $parent_key);

        return $field;
    }

    /**
     * Add type-specific ACF configuration
     *
     * @param array $field Base field array
     * @param array $config Field configuration
     * @param string $parent_key Parent key
     * @return array Updated field array
     */
    private static function addTypeSpecificConfig($field, $config, $parent_key) {
        $type = $config['type'];

        switch ($type) {
            case 'text':
                $field['maxlength'] = $config['maxlength'] ?? '';
                $field['readonly'] = $config['readonly'] ?? 0;
                $field['disabled'] = $config['disabled'] ?? 0;
                break;

            case 'textarea':
                $field['rows'] = $config['rows'] ?? 4;
                $field['maxlength'] = $config['maxlength'] ?? '';
                $field['new_lines'] = $config['new_lines'] ?? '';
                $field['readonly'] = $config['readonly'] ?? 0;
                $field['disabled'] = $config['disabled'] ?? 0;
                break;

            case 'number':
                $field['min'] = $config['min'] ?? '';
                $field['max'] = $config['max'] ?? '';
                $field['step'] = $config['step'] ?? '';
                $field['prepend'] = $config['prepend'] ?? '';
                $field['append'] = $config['append'] ?? '';
                break;

            case 'email':
            case 'url':
            case 'password':
                $field['prepend'] = $config['prepend'] ?? '';
                $field['append'] = $config['append'] ?? '';
                break;

            case 'select':
                $field['choices'] = $config['choices'] ?? [];
                $field['allow_null'] = $config['allow_null'] ?? 0;
                $field['multiple'] = $config['multiple'] ?? 0;
                $field['ui'] = $config['ui'] ?? 0;
                $field['ajax'] = $config['ajax'] ?? 0;
                $field['return_format'] = $config['return_format'] ?? 'value';
                break;

            case 'checkbox':
                $field['choices'] = $config['choices'] ?? [];
                $field['layout'] = $config['layout'] ?? 'vertical';
                $field['toggle'] = $config['toggle'] ?? 0;
                $field['return_format'] = $config['return_format'] ?? 'value';
                $field['allow_custom'] = $config['allow_custom'] ?? 0;
                break;

            case 'radio':
                $field['choices'] = $config['choices'] ?? [];
                $field['layout'] = $config['layout'] ?? 'vertical';
                $field['return_format'] = $config['return_format'] ?? 'value';
                $field['allow_null'] = $config['allow_null'] ?? 0;
                $field['other_choice'] = $config['other_choice'] ?? 0;
                break;

            case 'true_false':
                $field['message'] = $config['message'] ?? '';
                $field['ui'] = $config['ui'] ?? 0;
                $field['ui_on_text'] = $config['ui_on_text'] ?? '';
                $field['ui_off_text'] = $config['ui_off_text'] ?? '';
                break;

            case 'wysiwyg':
                $field['tabs'] = $config['tabs'] ?? 'all';
                $field['toolbar'] = $config['toolbar'] ?? 'full';
                $field['media_upload'] = $config['media_upload'] ?? 1;
                $field['delay'] = $config['delay'] ?? 0;
                break;

            case 'oembed':
                $field['width'] = $config['width'] ?? '';
                $field['height'] = $config['height'] ?? '';
                break;

            case 'image':
                $field['return_format'] = $config['return_format'] ?? 'array';
                $field['preview_size'] = $config['preview_size'] ?? 'medium';
                $field['library'] = $config['library'] ?? 'all';
                $field['min_width'] = $config['min_width'] ?? '';
                $field['min_height'] = $config['min_height'] ?? '';
                $field['max_width'] = $config['max_width'] ?? '';
                $field['max_height'] = $config['max_height'] ?? '';
                $field['min_size'] = $config['min_size'] ?? '';
                $field['max_size'] = $config['max_size'] ?? '';
                $field['mime_types'] = $config['mime_types'] ?? '';
                break;

            case 'file':
                $field['return_format'] = $config['return_format'] ?? 'array';
                $field['library'] = $config['library'] ?? 'all';
                $field['min_size'] = $config['min_size'] ?? '';
                $field['max_size'] = $config['max_size'] ?? '';
                $field['mime_types'] = $config['mime_types'] ?? '';
                break;

            case 'gallery':
                $field['return_format'] = $config['return_format'] ?? 'array';
                $field['preview_size'] = $config['preview_size'] ?? 'medium';
                $field['insert'] = $config['insert'] ?? 'append';
                $field['library'] = $config['library'] ?? 'all';
                $field['min'] = $config['min'] ?? '';
                $field['max'] = $config['max'] ?? '';
                $field['min_width'] = $config['min_width'] ?? '';
                $field['min_height'] = $config['min_height'] ?? '';
                $field['max_width'] = $config['max_width'] ?? '';
                $field['max_height'] = $config['max_height'] ?? '';
                $field['min_size'] = $config['min_size'] ?? '';
                $field['max_size'] = $config['max_size'] ?? '';
                $field['mime_types'] = $config['mime_types'] ?? '';
                break;

            case 'post_object':
                $field['post_type'] = $config['post_type'] ?? [];
                $field['taxonomy'] = $config['taxonomy'] ?? [];
                $field['allow_null'] = $config['allow_null'] ?? 0;
                $field['multiple'] = $config['multiple'] ?? 0;
                $field['return_format'] = $config['return_format'] ?? 'object';
                $field['ui'] = $config['ui'] ?? 1;
                break;

            case 'relationship':
                $field['post_type'] = $config['post_type'] ?? [];
                $field['taxonomy'] = $config['taxonomy'] ?? [];
                $field['filters'] = $config['filters'] ?? ['search', 'post_type', 'taxonomy'];
                $field['elements'] = $config['elements'] ?? [];
                $field['min'] = $config['min'] ?? '';
                $field['max'] = $config['max'] ?? '';
                $field['return_format'] = $config['return_format'] ?? 'object';
                break;

            case 'taxonomy':
                $field['taxonomy'] = $config['taxonomy'] ?? 'category';
                $field['field_type'] = $config['field_type'] ?? 'checkbox';
                $field['add_term'] = $config['add_term'] ?? 1;
                $field['save_terms'] = $config['save_terms'] ?? 1;
                $field['load_terms'] = $config['load_terms'] ?? 1;
                $field['return_format'] = $config['return_format'] ?? 'id';
                $field['multiple'] = $config['multiple'] ?? 0;
                $field['allow_null'] = $config['allow_null'] ?? 0;
                break;

            case 'user':
                $field['role'] = $config['role'] ?? [];
                $field['allow_null'] = $config['allow_null'] ?? 0;
                $field['multiple'] = $config['multiple'] ?? 0;
                $field['return_format'] = $config['return_format'] ?? 'array';
                break;

            case 'repeater':
                $field['sub_fields'] = [];
                if (!empty($config['sub_fields'])) {
                    foreach ($config['sub_fields'] as $sub_key => $sub_config) {
                        $field['sub_fields'][] = self::generateField($sub_key, $sub_config, $field['key']);
                    }
                }
                $field['min'] = $config['min'] ?? 0;
                $field['max'] = $config['max'] ?? 0;
                $field['layout'] = $config['layout'] ?? 'table';
                $field['button_label'] = $config['button_label'] ?? 'Add Row';
                $field['collapsed'] = $config['collapsed'] ?? '';
                break;

            case 'group':
                $field['sub_fields'] = [];
                if (!empty($config['sub_fields'])) {
                    foreach ($config['sub_fields'] as $sub_key => $sub_config) {
                        $field['sub_fields'][] = self::generateField($sub_key, $sub_config, $field['key']);
                    }
                }
                $field['layout'] = $config['layout'] ?? 'block';
                break;

            case 'flexible_content':
                $field['layouts'] = [];
                $field['button_label'] = $config['button_label'] ?? 'Add Row';
                $field['min'] = $config['min'] ?? '';
                $field['max'] = $config['max'] ?? '';

                if (!empty($config['layouts'])) {
                    foreach ($config['layouts'] as $layout_key => $layout_config) {
                        $layout = [
                            'key' => 'layout_' . $field['key'] . '_' . $layout_key,
                            'name' => $layout_key,
                            'label' => $layout_config['label'] ?? ucwords(str_replace('_', ' ', $layout_key)),
                            'display' => $layout_config['display'] ?? 'block',
                            'sub_fields' => [],
                            'min' => $layout_config['min'] ?? '',
                            'max' => $layout_config['max'] ?? '',
                        ];

                        if (!empty($layout_config['sub_fields'])) {
                            foreach ($layout_config['sub_fields'] as $sub_key => $sub_config) {
                                $layout['sub_fields'][] = self::generateField($sub_key, $sub_config, $layout['key']);
                            }
                        }

                        $field['layouts'][] = $layout;
                    }
                }
                break;

            case 'date_picker':
                $field['display_format'] = $config['display_format'] ?? 'm/d/Y';
                $field['return_format'] = $config['return_format'] ?? 'Y-m-d';
                $field['first_day'] = $config['first_day'] ?? 1;
                break;

            case 'time_picker':
                $field['display_format'] = $config['display_format'] ?? 'g:i a';
                $field['return_format'] = $config['return_format'] ?? 'H:i:s';
                break;

            case 'date_time_picker':
                $field['display_format'] = $config['display_format'] ?? 'm/d/Y g:i a';
                $field['return_format'] = $config['return_format'] ?? 'Y-m-d H:i:s';
                $field['first_day'] = $config['first_day'] ?? 1;
                break;

            case 'color_picker':
                $field['enable_opacity'] = $config['enable_opacity'] ?? 0;
                $field['return_format'] = $config['return_format'] ?? 'string';
                break;

            case 'google_map':
                $field['center_lat'] = $config['center_lat'] ?? '';
                $field['center_lng'] = $config['center_lng'] ?? '';
                $field['zoom'] = $config['zoom'] ?? '';
                $field['height'] = $config['height'] ?? '';
                break;

            case 'link':
                $field['return_format'] = $config['return_format'] ?? 'array';
                break;
        }

        // Add show_in_rest for REST API exposure
        if (!empty($config['show_in_rest'])) {
            $field['show_in_rest'] = 1;
        }

        return $field;
    }

    /**
     * Convert conditional logic from schema to ACF format
     *
     * @param array $conditions Conditions from schema
     * @param string $parent_key Parent key for field reference
     * @return array ACF conditional logic array
     */
    private static function convertConditionalLogic($conditions, $parent_key) {
        $acf_logic = [];

        foreach ($conditions as $condition_group) {
            $acf_group = [];

            // Handle both single condition and array of conditions
            if (!isset($condition_group[0])) {
                $condition_group = [$condition_group];
            }

            foreach ($condition_group as $condition) {
                $acf_condition = [
                    'field' => 'field_' . $parent_key . '_' . $condition['field'],
                    'operator' => $condition['operator'] ?? '==',
                    'value' => $condition['value'] ?? '',
                ];
                $acf_group[] = $acf_condition;
            }

            $acf_logic[] = $acf_group;
        }

        return $acf_logic;
    }

    /**
     * Register ACF field group for taxonomy term meta
     *
     * @param array $field_group ACF field group array
     * @return void
     */
    public static function register($field_group) {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group($field_group);
        }
    }
}
