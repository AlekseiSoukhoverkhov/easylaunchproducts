<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * EasyLaunchProducts Template Loader.
 *
 * Extends the renamed Gamajo_Template_Loader to provide custom template loading logic
 * for the 'products' and 'brands' custom post types and archives.
 *
 * @package EasyLaunchProducts
 */

if ( ! class_exists( 'EasyProducts_Template_Loader' ) ) {

    class EasyProducts_Template_Loader extends EasyProducts_Gamajo_Template_Loader {

        /**
         * Filter prefix for Gamajo loader
         *
         * @var string
         */
        protected $filter_prefix = 'easyproducts';

        /**
         * Theme folder to look for templates
         *
         * @var string
         */
        protected $theme_template_directory = 'easyproducts';

        /**
         * Plugin directory
         *
         * @var string
         */
        protected $plugin_directory = EASYPRODUCTS_PATH;

        /**
         * Plugin template folder
         *
         * @var string
         */
        protected $plugin_template_directory = 'templates';

        /**
         * Register filters
         *
         * @return void
         */
        public function register() {
            add_filter( 'template_include', [ $this, 'load_templates' ] );
        }

        /**
         * Load CPT templates
         *
         * @param string $template The path of the template file to include.
         * @return string
         */
        public function load_templates( $template ) {

            // 1. Archive Products (easypr_products -> archive-products.php)
            if ( is_post_type_archive( 'easypr_products' ) || is_tax( 'easypr_cat' ) || is_tax( 'easypr_product-types' ) ) {
                $theme_files = [ 'archive-products.php', 'easyproducts/archive-products.php' ];
                $found = $this->locate_template( $theme_files, false );
                if ( $found ) {
                    return $found;
                } else {
                    return trailingslashit( $this->plugin_directory ) . $this->plugin_template_directory . '/archive-products.php';
                }
            }

            // 2. Archive Brands (easypr_brands -> archive-brands.php)
            if ( is_post_type_archive( 'easypr_brands' ) ) {
                $theme_files = [ 'archive-brands.php', 'easyproducts/archive-brands.php' ];
                $found = $this->locate_template( $theme_files, false );
                if ( $found ) {
                    return $found;
                } else {
                    return trailingslashit( $this->plugin_directory ) . $this->plugin_template_directory . '/archive-brands.php';
                }
            }

            // 3. Single Product (easypr_products -> single-product.php)
            if ( is_singular( 'easypr_products' ) ) {
                $theme_files = [ 'single-product.php', 'easyproducts/single-product.php' ];
                $found = $this->locate_template( $theme_files, false );
                if ( $found ) {
                    return $found;
                } else {
                    return trailingslashit( $this->plugin_directory ) . $this->plugin_template_directory . '/single-product.php';
                }
            }

            // 4. Single Brand (easypr_brands -> single-brand.php)
            if ( is_singular( 'easypr_brands' ) ) {
                $theme_files = [ 'single-brand.php', 'easyproducts/single-brand.php' ];
                $found = $this->locate_template( $theme_files, false );
                if ( $found ) {
                    return $found;
                } else {
                    return trailingslashit( $this->plugin_directory ) . $this->plugin_template_directory . '/single-brand.php';
                }
            }

            return $template;
        }
    }
}

// Initialize template loader
if ( class_exists( 'EasyProducts_Template_Loader' ) ) {
    $easyProducts_Template = new EasyProducts_Template_Loader();
    $easyProducts_Template->register();
}