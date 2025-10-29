<?php
/**
 * WordPress Schema System
 *
 * AI-friendly schema-based custom post type and custom fields management system.
 *
 * @package WordPress_Schema_System
 * @version 1.0.0
 * @author IgniStack
 *
 * Plugin Name: WordPress Schema System
 * Plugin URI: https://github.com/your-repo/wordpress-schema-system
 * Description: Schema-first custom post type and ACF field management with AI integration
 * Version: 1.0.0
 * Author: IgniStack
 * License: MIT
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('WP_SCHEMA_SYSTEM_VERSION', '1.0.0');
define('WP_SCHEMA_SYSTEM_PATH', __DIR__);
define('WP_SCHEMA_SYSTEM_URL', plugins_url('', __FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'WordPressSchemaSystem\\';
    $base_dir = WP_SCHEMA_SYSTEM_PATH . '/lib/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Handle generators namespace
    if (strpos($relative_class, 'Generators\\') === 0) {
        $file = WP_SCHEMA_SYSTEM_PATH . '/generators/' . substr($relative_class, strlen('Generators/')) . '.php';
    }

    // Handle CLI namespace
    if (strpos($relative_class, 'CLI\\') === 0) {
        $file = WP_SCHEMA_SYSTEM_PATH . '/cli/' . substr($relative_class, strlen('CLI/')) . '.php';
    }

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Main WordPress Schema System Class
 */
class WordPress_Schema_System {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Schema directory
     */
    private $schema_dir;

    /**
     * Loaded schemas
     */
    private $schemas = [];

    /**
     * Get instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->schema_dir = WP_CONTENT_DIR . '/schemas/post-types';

        // Create schema directory if it doesn't exist
        if (!is_dir($this->schema_dir)) {
            mkdir($this->schema_dir, 0755, true);
        }

        // Copy example schemas if directory is empty
        $this->maybeInstallExamples();

        // Load schemas
        add_action('init', [$this, 'loadSchemas'], 5);

        // Register post types and fields
        add_action('init', [$this, 'registerPostTypes'], 10);
        add_action('acf/init', [$this, 'registerACFFields'], 10);

        // Load WP-CLI commands
        if (defined('WP_CLI') && WP_CLI) {
            $this->loadCLI();
        }

        // Admin features
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addAdminMenu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        }

        // REST API extensions
        add_action('rest_api_init', [$this, 'registerRESTRoutes']);
    }

    /**
     * Load schemas from directory
     */
    public function loadSchemas() {
        try {
            $this->schemas = \WordPressSchemaSystem\SchemaParser::loadDirectory($this->schema_dir);
        } catch (Exception $e) {
            error_log('WordPress Schema System: Failed to load schemas - ' . $e->getMessage());
        }
    }

    /**
     * Register post types from schemas
     */
    public function registerPostTypes() {
        foreach ($this->schemas as $post_type => $schema) {
            $args = \WordPressSchemaSystem\SchemaParser::toPostTypeArgs($schema);
            register_post_type($post_type, $args);
        }
    }

    /**
     * Register ACF fields from schemas
     */
    public function registerACFFields() {
        foreach ($this->schemas as $post_type => $schema) {
            if (!empty($schema['fields'])) {
                $field_group = \WordPressSchemaSystem\ACFFieldGenerator::generateFieldGroup($schema);
                \WordPressSchemaSystem\ACFFieldGenerator::register($field_group);
            }
        }
    }

    /**
     * Load WP-CLI commands
     */
    private function loadCLI() {
        require_once WP_SCHEMA_SYSTEM_PATH . '/cli/SchemaCommand.php';
    }

    /**
     * Add admin menu
     */
    public function addAdminMenu() {
        add_menu_page(
            'Schema System',
            'Schema System',
            'manage_options',
            'wp-schema-system',
            [$this, 'renderAdminPage'],
            'dashicons-schedule',
            30
        );
    }

    /**
     * Render admin page
     */
    public function renderAdminPage() {
        require WP_SCHEMA_SYSTEM_PATH . '/admin/dashboard.php';
    }

    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets($hook) {
        if ($hook !== 'toplevel_page_wp-schema-system') {
            return;
        }

        // Enqueue styles and scripts if needed
    }

    /**
     * Register REST API routes
     */
    public function registerRESTRoutes() {
        // Schema management endpoint
        register_rest_route('schema-system/v1', '/schemas', [
            'methods' => 'GET',
            'callback' => [$this, 'getSchemas'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);

        register_rest_route('schema-system/v1', '/schemas/(?P<post_type>[a-z0-9_]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getSchema'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);
    }

    /**
     * REST API: Get all schemas
     */
    public function getSchemas($request) {
        return rest_ensure_response($this->schemas);
    }

    /**
     * REST API: Get specific schema
     */
    public function getSchema($request) {
        $post_type = $request['post_type'];

        if (!isset($this->schemas[$post_type])) {
            return new WP_Error('not_found', 'Schema not found', ['status' => 404]);
        }

        return rest_ensure_response($this->schemas[$post_type]);
    }

    /**
     * Install example schemas if directory is empty
     */
    private function maybeInstallExamples() {
        $files = glob($this->schema_dir . '/*.{yaml,yml,json}', GLOB_BRACE);

        if (empty($files)) {
            $examples_dir = WP_SCHEMA_SYSTEM_PATH . '/schemas/post-types';

            if (is_dir($examples_dir)) {
                $examples = glob($examples_dir . '/*.{yaml,yml,json}', GLOB_BRACE);

                foreach ($examples as $example) {
                    $dest = $this->schema_dir . '/' . basename($example);
                    copy($example, $dest);
                }
            }
        }
    }

    /**
     * Get loaded schemas
     */
    public function getLoadedSchemas() {
        return $this->schemas;
    }
}

// Initialize the system
add_action('plugins_loaded', function() {
    WordPress_Schema_System::getInstance();
});

/**
 * Helper function to get schema system instance
 */
function wp_schema_system() {
    return WordPress_Schema_System::getInstance();
}

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    // Flush rewrite rules on activation
    flush_rewrite_rules();
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
});
