<?php
/**
 * Admin Dashboard
 *
 * @package WordPress_Schema_System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$schema_system = wp_schema_system();
$schemas = $schema_system->getLoadedSchemas();
?>

<div class="wrap">
    <h1>
        WordPress Schema System
        <span style="font-size: 14px; color: #666; font-weight: normal; margin-left: 10px;">
            v<?php echo WP_SCHEMA_SYSTEM_VERSION; ?>
        </span>
    </h1>

    <p class="description">
        AI-friendly schema-based custom post type and ACF field management system.
        Define your post types and fields in YAML/JSON, and they're automatically registered with full REST API support.
    </p>

    <div class="notice notice-info" style="margin-top: 20px;">
        <p>
            <strong>Quick Start:</strong>
            Schemas are located in <code><?php echo WP_CONTENT_DIR; ?>/schemas/post-types/</code>
        </p>
        <p>
            <strong>CLI:</strong> Use <code>wp schema --help</code> for command-line management
        </p>
    </div>

    <?php if (empty($schemas)): ?>
        <div class="notice notice-warning">
            <p><strong>No schemas found!</strong> Create a schema file in the schemas directory to get started.</p>
        </div>
    <?php else: ?>

    <h2>Registered Schemas (<?php echo count($schemas); ?>)</h2>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Post Type</th>
                <th>Label</th>
                <th>Fields</th>
                <th>REST API</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schemas as $post_type => $schema): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($post_type); ?></strong>
                    </td>
                    <td>
                        <?php echo esc_html($schema['label']); ?>
                        <?php if (!empty($schema['description'])): ?>
                            <br>
                            <small style="color: #666;"><?php echo esc_html($schema['description']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo count($schema['fields'] ?? []); ?> fields
                    </td>
                    <td>
                        <?php
                        $rest_enabled = $schema['show_in_rest'] ?? $schema['rest_api']['enabled'] ?? true;
                        if ($rest_enabled):
                            $rest_base = $schema['rest_api']['base'] ?? $post_type;
                            $rest_url = rest_url("wp/v2/{$rest_base}");
                        ?>
                            <span style="color: #46b450;">✓ Enabled</span>
                            <br>
                            <small>
                                <a href="<?php echo esc_url($rest_url); ?>" target="_blank" style="text-decoration: none;">
                                    /wp-json/wp/v2/<?php echo esc_html($rest_base); ?>
                                </a>
                            </small>
                        <?php else: ?>
                            <span style="color: #dc3232;">✗ Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo admin_url("edit.php?post_type={$post_type}"); ?>" class="button button-small">
                            View Posts
                        </a>
                        <a href="<?php echo admin_url("post-new.php?post_type={$post_type}"); ?>" class="button button-small">
                            Add New
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

    <hr style="margin: 40px 0;">

    <h2>WP-CLI Commands</h2>

    <table class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th style="width: 30%;">Command</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>wp schema list</code></td>
                <td>List all registered schemas</td>
            </tr>
            <tr>
                <td><code>wp schema info &lt;post_type&gt;</code></td>
                <td>Show detailed information about a schema</td>
            </tr>
            <tr>
                <td><code>wp schema validate &lt;post_type&gt;</code></td>
                <td>Validate a schema file for errors</td>
            </tr>
            <tr>
                <td><code>wp schema create &lt;post_type&gt; --prompt="..."</code></td>
                <td>Create a new schema from a natural language prompt</td>
            </tr>
            <tr>
                <td><code>wp schema export &lt;post_type&gt;</code></td>
                <td>Export schema to TypeScript types</td>
            </tr>
            <tr>
                <td><code>wp schema export-all</code></td>
                <td>Export all schemas to TypeScript types</td>
            </tr>
            <tr>
                <td><code>wp schema register</code></td>
                <td>Manually register all post types and fields</td>
            </tr>
            <tr>
                <td><code>wp schema flush</code></td>
                <td>Flush WordPress rewrite rules</td>
            </tr>
        </tbody>
    </table>

    <hr style="margin: 40px 0;">

    <h2>Quick Links</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div class="card" style="padding: 20px;">
            <h3 style="margin-top: 0;">📚 Documentation</h3>
            <p>Learn about the schema format and field types.</p>
            <a href="<?php echo esc_url(WP_SCHEMA_SYSTEM_URL . '/SCHEMA-FORMAT.md'); ?>" class="button" target="_blank">
                View Schema Format
            </a>
        </div>

        <div class="card" style="padding: 20px;">
            <h3 style="margin-top: 0;">📂 Schema Directory</h3>
            <p>Edit schema files directly on the server.</p>
            <code style="display: block; padding: 10px; background: #f5f5f5; margin: 10px 0;">
                <?php echo WP_CONTENT_DIR; ?>/schemas/post-types/
            </code>
        </div>

        <div class="card" style="padding: 20px;">
            <h3 style="margin-top: 0;">🔌 REST API</h3>
            <p>Access your custom post types via REST API.</p>
            <a href="<?php echo esc_url(rest_url()); ?>" class="button" target="_blank">
                API Root
            </a>
        </div>

        <div class="card" style="padding: 20px;">
            <h3 style="margin-top: 0;">⚙️ ACF Integration</h3>
            <p>Schemas automatically generate ACF field groups.</p>
            <?php if (function_exists('acf')): ?>
                <a href="<?php echo admin_url('edit.php?post_type=acf-field-group'); ?>" class="button">
                    View ACF Fields
                </a>
            <?php else: ?>
                <p style="color: #dc3232;"><strong>ACF not detected!</strong></p>
            <?php endif; ?>
        </div>
    </div>

    <hr style="margin: 40px 0;">

    <h2>System Information</h2>

    <table class="widefat">
        <tbody>
            <tr>
                <td style="width: 200px;"><strong>Version</strong></td>
                <td><?php echo WP_SCHEMA_SYSTEM_VERSION; ?></td>
            </tr>
            <tr>
                <td><strong>Schema Directory</strong></td>
                <td>
                    <code><?php echo WP_CONTENT_DIR; ?>/schemas/post-types/</code>
                    <?php
                    $is_writable = is_writable(WP_CONTENT_DIR . '/schemas/post-types/');
                    ?>
                    <span style="color: <?php echo $is_writable ? '#46b450' : '#dc3232'; ?>;">
                        <?php echo $is_writable ? '✓ Writable' : '✗ Not writable'; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>ACF</strong></td>
                <td>
                    <?php if (function_exists('acf')): ?>
                        <span style="color: #46b450;">✓ Active</span>
                        (Version: <?php echo defined('ACF_VERSION') ? ACF_VERSION : 'Unknown'; ?>)
                    <?php else: ?>
                        <span style="color: #dc3232;">✗ Not active</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>WP-CLI</strong></td>
                <td>
                    <?php if (defined('WP_CLI') && WP_CLI): ?>
                        <span style="color: #46b450;">✓ Available</span>
                    <?php else: ?>
                        <span style="color: #999;">Not detected (normal in web interface)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>YAML Parser</strong></td>
                <td>
                    <?php if (function_exists('yaml_parse_file')): ?>
                        <span style="color: #46b450;">✓ php-yaml extension</span>
                    <?php elseif (class_exists('\Symfony\Component\Yaml\Yaml')): ?>
                        <span style="color: #46b450;">✓ Symfony YAML component</span>
                    <?php else: ?>
                        <span style="color: #dc3232;">✗ Not available</span>
                        <br>
                        <small>Install php-yaml extension or symfony/yaml package</small>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>REST API</strong></td>
                <td>
                    <span style="color: #46b450;">✓ Enabled</span>
                    <br>
                    <small><a href="<?php echo esc_url(rest_url('schema-system/v1/schemas')); ?>" target="_blank">View Schema API</a></small>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<style>
    .card {
        background: white;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,0.04);
    }

    .card h3 {
        color: #1d2327;
        font-size: 14px;
        font-weight: 600;
    }

    .card p {
        margin: 10px 0;
        color: #50575e;
    }

    .wp-list-table code {
        background: #f0f0f1;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
    }
</style>
