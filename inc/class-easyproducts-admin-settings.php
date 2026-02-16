<?php
/**
 * EasyLaunchProducts Admin Settings Class
 *
 * Registers the plugin's settings page and fields.
 *
 * @package EasyLaunchProducts
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'EasyProducts_Admin_Settings' ) ) :

    class EasyProducts_Admin_Settings {

        /**
         * Option group name for settings.
         * @var string
         */
        private $option_group = 'easyproducts_settings_group';

        /**
         * Option name for the checkbox setting.
         * @var string
         */
        private $order_form_option_name = 'easypr_show_order_form';

        public function register() {
            // Add admin menu page
            add_action( 'admin_menu', [ $this, 'add_settings_page' ] );

            // Register settings, sections, and fields
            add_action( 'admin_init', [ $this, 'register_settings' ] );
        }

        /**
         * Add the settings page to the WordPress admin menu.
         * Location: Products -> Settings
         */
        public function add_settings_page() {
            add_submenu_page(
                'edit.php?post_type=easypr_products', // Parent menu slug (Products CPT menu)
                esc_html__( 'EasyProducts Settings', 'easylaunchproducts' ), // Page title
                esc_html__( 'Settings', 'easylaunchproducts' ), // Menu title
                'manage_options', // Capability required
                'easyproducts-settings', // Menu slug
                [ $this, 'render_settings_page' ] // Function to render the page content
            );
        }

        /**
         * Register settings, sections, and fields.
         */
        public function register_settings() {
            // Register the setting
            register_setting(
                $this->option_group, // Option group
                $this->order_form_option_name, // Option name (the key in the wp_options table)
                [ 'sanitize_callback' => [ $this, 'sanitize_checkbox' ] ] // Sanitization function
            );

            // Add a settings section
            add_settings_section(
                'easypr_form_section', // ID
                esc_html__( 'Order Form Integration', 'easylaunchproducts' ), // Title
                [ $this, 'form_section_callback' ], // Callback function
                'easyproducts-settings' // Page slug
            );

            // Add the checkbox field
            add_settings_field(
                'easypr_show_form_field', // ID
                esc_html__( 'Enable Quick Order Form', 'easylaunchproducts' ), // Field title
                [ $this, 'show_form_checkbox_callback' ], // Field rendering function
                'easyproducts-settings', // Page slug
                'easypr_form_section' // Section ID
            );
        }

        /**
         * Sanitize the checkbox field value.
         * @param string $input Input value.
         * @return string Validated value ('1' or '').
         */
        public function sanitize_checkbox( $input ) {
            return ( isset( $input ) && '1' === $input ) ? '1' : '';
        }

        /**
         * Callback for the form settings section.
         */
        public function form_section_callback() {
            echo '<p>' . esc_html__( 'Control the display of the quick order form on single product pages.', 'easylaunchproducts' ) . '</p>';
        }

        /**
         * Callback for the 'Enable Quick Order Form' checkbox field.
         */
        public function show_form_checkbox_callback() {
            // Get the current value from the database
            $value = get_option( $this->order_form_option_name );

            echo '<input type="checkbox" id="easypr_show_form_field" name="' . esc_attr( $this->order_form_option_name ) . '" value="1" ' . checked( '1', $value, false ) . ' />';
            echo '<label for="easypr_show_form_field">' . esc_html__( 'Check to display the quick order form under all single product pages.', 'easylaunchproducts' ) . '</label>';
        }

        /**
         * Render the full settings page.
         */
        public function render_settings_page() {
            ?>
            <div class="wrap">
                <h1><?php echo esc_html__( 'EasyProducts Settings', 'easylaunchproducts' ); ?></h1>
                <form method="post" action="options.php">
                    <?php
                    // Output necessary fields for the settings group
                    settings_fields( $this->option_group );
                    // Output sections and fields
                    do_settings_sections( 'easyproducts-settings' );
                    // Output submit button
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        /**
         * Public static helper method to check if the form should be displayed.
         * This method is called in templates/single-product.php
         * @return bool
         */
        public static function should_display_order_form() {
            return '1' === get_option( 'easypr_show_order_form' );
        }
    }

endif;