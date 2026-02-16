<?php
/**
 * EasyLaunchProducts Order Form Class
 *
 * Handles the display, submission, and email processing of the product order form.
 *
 * @package EasyLaunchProducts
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'EasyProducts_Order_Form' ) ) :

    class EasyProducts_Order_Form {

        const FORM_ANCHOR = '#product-form';

        public function register() {
            // Form handler via admin-post.php
            add_action( 'admin_post_nopriv_easypr_order_submit', [ $this, 'handle_submission' ] );
            add_action( 'admin_post_easypr_order_submit', [ $this, 'handle_submission' ] );

            // Enqueue styles
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        }

        public function enqueue_styles() {
            if ( ! wp_style_is( 'easyproducts-style', 'enqueued' ) ) {
                wp_enqueue_style( 'easyproducts-style', EASYPRODUCTS_URL . 'assets/css/style.css', [], EASYPRODUCTS_VERSION );
            }
        }

        /**
         * Renders/displays the order form. This function will be called directly from the single-product template.
         *
         * @return void Outputs the HTML directly.
         */
        public function easypr_display_order_form() {
            global $easyProducts_Template;

            // FIX: Removed ob_start() and ob_get_clean() to resolve OutputNotEscaped error.
            // get_template_part outputs directly, which is the intended behavior.

            $product_title = get_the_title();

            // Pass empty values - template handles easypr_submitted from GET
            $data = [
                'product_title'    => $product_title,
                'is_submitted'     => false,
                'submission_error' => '',
            ];

            if ( isset( $easyProducts_Template ) ) {
                $easyProducts_Template->get_template_part( 'parts/form', '', $data );
            } else {
                // Escaped output for safety, though string is hardcoded
                echo wp_kses_post( '<p>Error: EasyProducts Template Loader not initialized.</p>' );
            }
        }

        /**
         * Handle form submission and send email.
         */
        public function handle_submission() {
            // Check action
            if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'easypr_order_submit' ) {
                wp_die( esc_html__( 'Invalid request.', 'easylaunchproducts' ) );
            }

            // Form redirect URL - to referer or permalink
            $referer = wp_get_referer();
            if ( ! $referer ) {
                $referer = get_permalink();
            }

            // Security
            if (
                ! isset( $_POST['easypr_order_nonce'] ) ||
                ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['easypr_order_nonce'] ) ), 'easypr_order_action' )
            ) {
                // Redirect back with error
                $redirect_url = add_query_arg( 'easypr_error', urlencode( 'Security check failed.' ), $referer ) . self::FORM_ANCHOR;
                wp_safe_redirect( $redirect_url );
                exit;
            }

            // Sanitize and validate fields
            $name          = isset( $_POST['easypr_name'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_name'] ) ) : '';
            $email         = isset( $_POST['easypr_email'] ) ? sanitize_email( wp_unslash( $_POST['easypr_email'] ) ) : '';
            $phone         = isset( $_POST['easypr_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_phone'] ) ) : '';
            $qty           = isset( $_POST['easypr_quantity'] ) ? absint( wp_unslash( $_POST['easypr_quantity'] ) ) : 0;
            $comment       = isset( $_POST['easypr_comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['easypr_comment'] ) ) : '';
            $product_title = isset( $_POST['easypr_product_title'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_product_title'] ) ) : '';

            $errors = [];
            if ( empty( $name ) ) $errors[] = esc_html__( 'Your name is required.', 'easylaunchproducts' );
            if ( ! is_email( $email ) ) $errors[] = esc_html__( 'A valid email is required.', 'easylaunchproducts' );
            if ( $qty <= 0 ) $errors[] = esc_html__( 'Quantity must be greater than zero.', 'easylaunchproducts' );

            if ( ! empty( $errors ) ) {
                $error_msg = implode( ' ', $errors );
                $redirect_url = add_query_arg( 'easypr_error', urlencode( $error_msg ), $referer ) . self::FORM_ANCHOR;
                wp_safe_redirect( $redirect_url );
                exit;
            }

            // Prepare and send email
            $recipient = get_option( 'admin_email' );
            $site_name = get_bloginfo( 'name' );

            /* translators: 1: Product Title, 2: Site Name */
            $subject = sprintf( esc_html__( 'ORDER: %1$s - %2$s', 'easylaunchproducts' ), $product_title, $site_name );

            $body = esc_html__( 'Order from your website', 'easylaunchproducts' ) . " " . $site_name . "\n";
            $body .= "==============================================\n";
            $body .= esc_html__( 'Product', 'easylaunchproducts' ) . ": " . $product_title . "\n";
            $body .= esc_html__( 'Amount', 'easylaunchproducts' ) . ": " . $qty . "\n";
            $body .= esc_html__( 'Client Name', 'easylaunchproducts' ) . ": " . $name . "\n";
            $body .= esc_html__( 'Email', 'easylaunchproducts' ) . ": " . $email . "\n";
            $body .= esc_html__( 'Phone', 'easylaunchproducts' ) . ": " . $phone . "\n";
            $body .= esc_html__( 'Comment', 'easylaunchproducts' ) . ": " . ( empty( $comment ) ? esc_html__( 'No comment provided.', 'easylaunchproducts' ) : $comment ) . "\n";
            $body .= "==============================================\n";
            $body .= esc_html__( 'Submitted from:', 'easylaunchproducts' ) . ' ' . $referer;

            // Ensure From is within the site domain to pass SPF/DMARC
            // FIX: Replaced parse_url with wp_parse_url for WP standards
            $domain = wp_parse_url( home_url(), PHP_URL_HOST );
            $from_email = 'wordpress@' . $domain;

            $headers = [
                'Content-Type: text/plain; charset=' . get_bloginfo( 'charset' ),
                'Reply-To: ' . $name . ' <' . $email . '>',
                'From: ' . $site_name . ' <' . $from_email . '>',
            ];

            // FIX: Removed debug hook (error_log/print_r) for production safety

            $mail_sent = wp_mail( $recipient, $subject, $body, $headers );

            // Redirect back to the product page (referer) + query flag + anchor
            if ( $mail_sent ) {
                $redirect_url = add_query_arg( 'easypr_submitted', 'true', $referer ) . self::FORM_ANCHOR;
            } else {
                $error_msg = esc_html__( 'Email failed to send. Please check your mail server configuration.', 'easylaunchproducts' );
                $redirect_url = add_query_arg( 'easypr_error', urlencode( $error_msg ), $referer ) . self::FORM_ANCHOR;
            }

            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

endif;