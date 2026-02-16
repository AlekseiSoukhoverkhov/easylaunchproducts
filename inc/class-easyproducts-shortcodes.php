<?php
/**
 * EasyLaunchProducts Shortcodes Class
 *
 * Provides shortcode-based filter functionality for Products.
 * Shortcode: [easyproducts_filter]
 *
 * @package EasyLaunchProducts
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'EasyProducts_Shortcodes' ) ) {

    class EasyProducts_Shortcodes {

        public function register() {
            add_action( 'init', [ $this, 'register_shortcode' ] );
        }

        public function register_shortcode() {
            add_shortcode( 'easyproducts_filter', [ $this, 'filter_shortcode' ] );
        }

        public function filter_shortcode( $atts ) {

            // Enqueue main styles (assuming you renamed the handle in easyproducts.php)
            wp_enqueue_style( 'easyproducts-style' );

            // Attributes (Updated for Products)
            $atts = shortcode_atts(
                [
                    'category'  => '1', // Was location
                    'prod_type' => '1', // Was type
                    'status'    => '1', // Was offer/type
                    'price'     => '1',
                    'brand'     => '1', // Was expert
                ],
                $atts,
                'easyproducts_filter'
            );

            ob_start();

            global $easyProducts_Template, $easyProducts;

            if ( ! isset( $easyProducts ) && class_exists( 'EasyProducts' ) ) {
                $easyProducts = new EasyProducts();
            }

            // --- 1. PROCESS POST DATA ---

            // Initialize variables
            $selected_cat       = '';
            $selected_prod_type = '';
            $selected_price     = '';
            $selected_status    = '';
            $selected_brand     = '';

            $args = array(
                'post_type'      => 'easypr_products', // Updated CPT
                'posts_per_page' => -1,
            );

            $meta_query_args = array( 'relation' => 'AND' );
            $tax_query_args  = array( 'relation' => 'AND' );

            if ( isset( $_POST['easyproducts_filter_submit'] ) ) {
                if ( isset( $_POST['easyproducts_filter_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['easyproducts_filter_nonce'] ) ), 'easyproducts_filter_action' ) ) {

                    $selected_cat       = isset( $_POST['f_cat'] ) ? intval( wp_unslash( $_POST['f_cat'] ) ) : '';
                    $selected_prod_type = isset( $_POST['f_prod_type'] ) ? intval( wp_unslash( $_POST['f_prod_type'] ) ) : '';
                    $selected_price     = isset( $_POST['easypr_price'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_price'] ) ) : '';
                    $selected_status    = isset( $_POST['easypr_status'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_status'] ) ) : '';
                    $selected_brand     = isset( $_POST['easypr_brand'] ) ? intval( wp_unslash( $_POST['easypr_brand'] ) ) : '';

                    // Meta Queries
                    if ( ! empty( $selected_status ) ) {
                        $meta_query_args[] = array( 'key' => 'easypr_status', 'value' => $selected_status, 'compare' => '=' );
                    }
                    if ( ! empty( $selected_price ) ) {
                        $numeric_price = preg_replace( '/[^\d]/', '', $selected_price );
                        if ( ! empty( $numeric_price ) ) {
                            $meta_query_args[] = array( 'key' => 'easypr_price', 'value' => intval( $numeric_price ), 'type' => 'NUMERIC', 'compare' => '<=' );
                        }
                    }
                    if ( ! empty( $selected_brand ) ) {
                        $meta_query_args[] = array( 'key' => 'easypr_brand', 'value' => $selected_brand, 'type' => 'NUMERIC', 'compare' => '=' );
                    }

                    // Tax Queries
                    if ( ! empty( $selected_cat ) ) {
                        $tax_query_args[] = array( 'taxonomy' => 'easypr_cat', 'field' => 'term_id', 'terms' => $selected_cat );
                    }
                    if ( ! empty( $selected_prod_type ) ) {
                        $tax_query_args[] = array( 'taxonomy' => 'easypr_product-types', 'field' => 'term_id', 'terms' => $selected_prod_type );
                    }
                }
            }

            // Apply queries
            if ( count( $meta_query_args ) > 1 ) {
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                $args['meta_query'] = $meta_query_args;
            }
            if ( count( $tax_query_args ) > 1 ) {
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                $args['tax_query'] = $tax_query_args;
            }


            // --- 2. RENDER FILTER FORM ---

            global $wp;
            $current_url = home_url( add_query_arg( array(), $wp->request ) );
            ?>

            <div class="wrapper filter_form">
                <form method="post" action="<?php echo esc_url( $current_url ); ?>">

                    <?php wp_nonce_field( 'easyproducts_filter_action', 'easyproducts_filter_nonce' ); ?>

                    <?php if ( '1' === $atts['category'] ) : ?>
                        <select name="f_cat">
                            <option value=""><?php echo esc_html__( 'Select Category', 'easylaunchproducts' ); ?></option>
                            <?php
                            if ( isset( $easyProducts ) && method_exists( $easyProducts, 'get_terms_hierarchical' ) ) {
                                $easyProducts->get_terms_hierarchical( 'easypr_cat', $selected_cat );
                            }
                            ?>
                        </select>
                    <?php endif; ?>

                    <?php if ( '1' === $atts['prod_type'] ) : ?>
                        <select name="f_prod_type">
                            <option value=""><?php echo esc_html__( 'Select Product Type', 'easylaunchproducts' ); ?></option>
                            <?php
                            if ( isset( $easyProducts ) && method_exists( $easyProducts, 'get_terms_hierarchical' ) ) {
                                $easyProducts->get_terms_hierarchical( 'easypr_product-types', $selected_prod_type );
                            }
                            ?>
                        </select>
                    <?php endif; ?>

                    <?php if ( '1' === $atts['price'] ) : ?>
                        <input type="text" name="easypr_price" placeholder="<?php echo esc_attr__( 'Max Price', 'easylaunchproducts' ); ?>" value="<?php echo esc_attr( $selected_price ); ?>">
                    <?php endif; ?>

                    <?php if ( '1' === $atts['status'] ) : ?>
                        <select name="easypr_status">
                            <option value=""><?php echo esc_html__( 'Select Status', 'easylaunchproducts' ); ?></option>
                            <option value="Available" <?php selected( $selected_status, 'Available' ); ?>><?php echo esc_html__( 'Available', 'easylaunchproducts' ); ?></option>
                            <option value="On Order" <?php selected( $selected_status, 'On Order' ); ?>><?php echo esc_html__( 'On Order', 'easylaunchproducts' ); ?></option>
                            <option value="Discontinued" <?php selected( $selected_status, 'Discontinued' ); ?>><?php echo esc_html__( 'Discontinued', 'easylaunchproducts' ); ?></option>
                        </select>
                    <?php endif; ?>

                    <?php if ( '1' === $atts['brand'] ) : ?>
                        <select name="easypr_brand">
                            <option value=""><?php echo esc_html__( 'Select Brand', 'easylaunchproducts' ); ?></option>
                            <?php
                            $brands = get_posts( array(
                                'post_type'   => 'easypr_brands',
                                'numberposts' => -1,
                                'post_status' => 'publish',
                                'orderby'     => 'title',
                                'order'       => 'ASC',
                            ) );

                            if ( ! empty( $brands ) ) {
                                foreach ( $brands as $brand ) {
                                    echo '<option value="' . esc_attr( $brand->ID ) . '"' . selected( $selected_brand, (int) $brand->ID, false ) . '>' . esc_html( $brand->post_title ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    <?php endif; ?>

                    <input type="submit" name="easyproducts_filter_submit" value="<?php echo esc_attr__( 'Filter', 'easylaunchproducts' ); ?>">
                </form>
            </div>

            <?php
            // --- 3. RENDER RESULTS (PRODUCT CARDS) ---

            $products_query = new WP_Query( $args );
            ?>

            <div class="wrapper archive_products easyproducts-list-embed">
                <?php
                if ( $products_query->have_posts() ) :
                    while ( $products_query->have_posts() ) :
                        $products_query->the_post();

                        if ( isset( $easyProducts_Template ) ) {
                            $easyProducts_Template->get_template_part( 'parts/content' );
                        }
                    endwhile;

                    wp_reset_postdata();

                else :
                    ?>
                    <p><?php esc_html_e( 'No products found based on your filter criteria.', 'easylaunchproducts' ); ?></p>
                <?php
                endif;
                ?>
            </div>
            <?php

            return ob_get_clean();
        }
    }
}