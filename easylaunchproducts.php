<?php
/**
 * Plugin Name: EasyLaunchProducts
 * Plugin URI: https://easylaunch.eu/easyproducts/
 * Description: Custom post type and filter system for adding a catalogue of products to a WordPress website.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * Author: easylaunch
 * Author URI: https://easylaunch.eu
 * License: GPLv2 or later
 * Text Domain: easylaunchproducts
 * Domain Path: /lang
 *
 * @package EasyLaunchProducts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- CORE CONSTANTS ---

/**
 * Define the plugin version.
 *
 * @since 1.0.0
 */
if ( ! defined( 'EASYPRODUCTS_VERSION' ) ) {
    define( 'EASYPRODUCTS_VERSION', '1.0.0' );
}

/**
 * The main file path to the plugin. Used for hooks and registration.
 *
 * @since 1.0.0
 */
if ( ! defined( 'EASYPRODUCTS_PLUGIN_FILE' ) ) {
    define( 'EASYPRODUCTS_PLUGIN_FILE', __FILE__ );
}

/**
 * Define the main plugin directory path.
 *
 * @since 1.0.0
 */
if ( ! defined( 'EASYPRODUCTS_PATH' ) ) {
    define( 'EASYPRODUCTS_PATH', trailingslashit( plugin_dir_path( EASYPRODUCTS_PLUGIN_FILE ) ) );
}

/**
 * Define the main plugin URL.
 *
 * @since 1.0.0
 */
if ( ! defined( 'EASYPRODUCTS_URL' ) ) {
    define( 'EASYPRODUCTS_URL', trailingslashit( plugin_dir_url( EASYPRODUCTS_PLUGIN_FILE ) ) );
}


if ( ! class_exists( 'EasyProducts' ) ) :

    /**
     * The main plugin class.
     */
    class EasyProducts {

        /**
         * EasyProducts constructor.
         */
        public function __construct() {
            // Register activation and deactivation hooks.
            register_activation_hook( EASYPRODUCTS_PLUGIN_FILE, [ $this, 'activate' ] );
            register_deactivation_hook( EASYPRODUCTS_PLUGIN_FILE, [ $this, 'deactivate' ] );

            // Hooks for frontend
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
            add_action( 'widgets_init', [ $this, 'register_widgets' ] );
            add_filter( 'plugin_action_links_' . plugin_basename( EASYPRODUCTS_PLUGIN_FILE ), [ $this, 'plugin_action_links' ] );
        }

        /**
         * Runs on plugin activation.
         * Fixes the permalink issue by manually registering CPTs before flushing rules.
         */
        public function activate() {
            // Manually register CPTs before flushing rewrite rules.
            // This is required to ensure the permalinks for the CPT archives (like /products/) are added on activation.
            if ( class_exists( 'EasyProductsCpt' ) ) {
                $cpt_class = new EasyProductsCpt();
                // We call the method responsible for registering CPTs, normally hooked to 'init'.
                $cpt_class->custom_post_type();
            }

            flush_rewrite_rules();
        }

        /**
         * Runs on plugin deactivation.
         */
        public function deactivate() {
            flush_rewrite_rules();
        }

        /**
         * Enqueue frontend styles.
         */
        public function enqueue_styles() {
            wp_enqueue_style( 'easyproducts-style', EASYPRODUCTS_URL . 'assets/css/front/style.css', [], EASYPRODUCTS_VERSION );
        }

        /**
         * Register the custom widget.
         */
        public function register_widgets() {
            if ( class_exists( 'EasyProducts_Filter_Widget' ) ) {
                register_widget( 'EasyProducts_Filter_Widget' );
            }
        }

        /**
         * Add a 'Products' link to the plugin action row on the Plugins screen.
         *
         * @param array $links The array of action links.
         * @return array
         */
        public function plugin_action_links( $links ) {
            $settings_link = '<a href="' . esc_url( admin_url( 'edit.php?post_type=easypr_products&page=easyproducts-settings' ) ) . '">' . esc_html__( 'Settings', 'easylaunchproducts' ) . '</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }

        /**
         * Helper function to get terms in a hierarchical format for select fields.
         * Used in parts/filter.php.
         *
         * @param string $taxonomy Taxonomy slug.
         * @param string $selected_id ID of the term to be selected.
         * @param int $parent Parent term ID for recursion.
         * @param int $level Current depth level for indentation.
         * @return void Outputs HTML directly.
         */
        public function get_terms_hierarchical( $taxonomy, $selected_id = '', $parent = 0, $level = 0 ) {

            $terms = get_terms( array(
                'taxonomy'   => $taxonomy,
                'parent'     => $parent,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ) );

            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    $pad = str_repeat( '- ', $level );
                    echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( $selected_id, $term->term_id, false ) . '>' . esc_html( $pad . $term->name ) . '</option>';
                    // Recursive call for children
                    $this->get_terms_hierarchical( $taxonomy, $selected_id, $term->term_id, $level + 1 );
                }
            }
        }
    }

endif;


// --- INCLUDE CLASSES ---

$easyproducts_includes = [
    '/inc/class-easyproducts-gamajo-template-loader.php',
    '/inc/class-easyproducts-template-loader.php',
    '/inc/class-easyproducts-cpt.php',
    '/inc/class-easyproducts-shortcodes.php',
    '/inc/class-easyproducts-filter-widget.php',
    '/inc/class-easyproducts-admin-settings.php',
    '/inc/class-easyproducts-order-form.php',
];

foreach ( $easyproducts_includes as $easyproducts_file ) {
    if ( file_exists( EASYPRODUCTS_PATH . $easyproducts_file ) ) {
        require_once EASYPRODUCTS_PATH . $easyproducts_file;
    }
}

// Instantiate and register main plugin hooks
if ( class_exists( 'EasyProducts' ) ) {
    $GLOBALS['easyProducts'] = new EasyProducts();

    // Register CPT class actions (which includes metaboxes and admin styles)
    if ( class_exists( 'EasyProductsCpt' ) ) {
        $easyProductsCpt = new EasyProductsCpt();
        $easyProductsCpt->register();
    }

    // Register Shortcodes
    if ( class_exists( 'EasyProducts_Shortcodes' ) ) {
        $easyProductsShortcodes = new EasyProducts_Shortcodes();
        $easyProductsShortcodes->register();
    }

    // Register Admin Settings
    if ( class_exists( 'EasyProducts_Admin_Settings' ) ) {
        $easyProductsAdminSettings = new EasyProducts_Admin_Settings();
        $easyProductsAdminSettings->register();
    }

    // Register Order Form Handler
    if ( class_exists( 'EasyProducts_Order_Form' ) ) {
        $GLOBALS['easyProducts_Order_Form'] = new EasyProducts_Order_Form();
        $GLOBALS['easyProducts_Order_Form']->register();
    }

    // Initialize Template Loader globally and register its filters
    if ( class_exists( 'EasyProducts_Template_Loader' ) ) {
        // Global for easy access in templates (e.g., archive-products.php)
        $GLOBALS['easyProducts_Template'] = new EasyProducts_Template_Loader();
        $GLOBALS['easyProducts_Template']->register();
    }
}