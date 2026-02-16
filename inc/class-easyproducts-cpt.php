<?php
/**
 * Custom Post Types and Taxonomies Registration for EasyLaunchProducts
 *
 * @package EasyLaunchProducts
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'EasyProductsCpt' ) ) {

    class EasyProductsCpt {

        /**
         * Register hooks
         */
        public function register() {
            // Register CPTs
            add_action( 'init', [ $this, 'custom_post_type' ] );

            // Register taxonomies
            add_action( 'init', [ $this, 'register_taxonomies' ] );

            // Metaboxes
            add_action( 'add_meta_boxes', [ $this, 'add_meta_box_products' ] );

            // Save post meta
            add_action( 'save_post', [ $this, 'save_metabox' ] );

            // Enqueue admin scripts
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
        }

        /**
         * Register Custom Post Types (easypr_products, easypr_brands)
         */
        public function custom_post_type() {
            // 1. PRODUCTS CPT
            register_post_type( 'easypr_products', [
                'label'        => esc_html__( 'Products', 'easylaunchproducts' ),
                'public'       => true,
                'show_in_menu' => true,
                'menu_position'=> 5,
                'menu_icon'    => 'dashicons-cart',
                'has_archive'  => true,
                'rewrite'      => [ 'slug' => 'products' ],
                'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
                'labels'       => [
                    'name'          => esc_html__( 'Products', 'easylaunchproducts' ),
                    'singular_name' => esc_html__( 'Product', 'easylaunchproducts' ),
                    'add_new_item'  => esc_html__( 'Add New Product', 'easylaunchproducts' ),
                    'edit_item'     => esc_html__( 'Edit Product', 'easylaunchproducts' ),
                    'all_items'     => esc_html__( 'All Products', 'easylaunchproducts' ),
                    'view_item'     => esc_html__( 'View Product', 'easylaunchproducts' ),
                    'search_items'  => esc_html__( 'Search Products', 'easylaunchproducts' ),
                    'not_found'     => esc_html__( 'No products found', 'easylaunchproducts' ),
                ],
            ] );

			// 2. BRANDS CPT
            register_post_type( 'easypr_brands', [
                'label'        => esc_html__( 'Brands', 'easylaunchproducts' ),
                'public'       => true,
                'show_in_menu' => 'edit.php?post_type=easypr_products', 
                'has_archive'  => true,
                'rewrite'      => [ 'slug' => 'brands' ],
                'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
                'labels'       => [
                    'name'          => esc_html__( 'Brands', 'easylaunchproducts' ),
                    'singular_name' => esc_html__( 'Brand', 'easylaunchproducts' ),
                    'add_new_item'  => esc_html__( 'Add New Brand', 'easylaunchproducts' ),
                    'edit_item'     => esc_html__( 'Edit Brand', 'easylaunchproducts' ),
                    'all_items'     => esc_html__( 'Brands', 'easylaunchproducts' ), // Изменил для красоты в меню
                    'view_item'     => esc_html__( 'View Brand', 'easylaunchproducts' ),
                    'search_items'  => esc_html__( 'Search Brands', 'easylaunchproducts' ),
                    'not_found'     => esc_html__( 'No brands found', 'easylaunchproducts' ),
                ],
            ] );
        }

        /**
         * Register Taxonomies
         */
        public function register_taxonomies() {

            // 1. Product Categories Taxonomy (Slug: easypr_cat)
            register_taxonomy( 'easypr_cat', 'easypr_products', [
                'label'             => esc_html__( 'Product Categories', 'easylaunchproducts' ),
                'hierarchical'      => true,
                'public'            => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [ 'slug' => 'category' ],
                'labels'            => [
                    'name'          => esc_html__( 'Product Categories', 'easylaunchproducts' ),
                    'singular_name' => esc_html__( 'Product Category', 'easylaunchproducts' ),
                    'menu_name'     => esc_html__( 'Categories', 'easylaunchproducts' ),
                    'all_items'     => esc_html__( 'All Categories', 'easylaunchproducts' ),
                    'add_new_item'  => esc_html__( 'Add New Category', 'easylaunchproducts' ),
                ],
            ] );

            // 2. Product Types Taxonomy (Slug: easypr_product-types)
            register_taxonomy( 'easypr_product-types', 'easypr_products', [
                'label'             => esc_html__( 'Product Types', 'easylaunchproducts' ),
                'hierarchical'      => true,
                'public'            => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [ 'slug' => 'product-type' ],
                'labels'            => [
                    'name'          => esc_html__( 'Product Types', 'easylaunchproducts' ),
                    'singular_name' => esc_html__( 'Product Type', 'easylaunchproducts' ),
                    'menu_name'     => esc_html__( 'Product Types', 'easylaunchproducts' ),
                    'all_items'     => esc_html__( 'All Product Types', 'easylaunchproducts' ),
                    'add_new_item'  => esc_html__( 'Add New Product Type', 'easylaunchproducts' ),
                ],
            ] );
        }

        /**
         * Adds the Products metabox.
         */
        public function add_meta_box_products() {
            add_meta_box(
                'easyproducts_meta_box',
                esc_html__( 'Product Details', 'easylaunchproducts' ),
                [ $this, 'display_metabox_products' ],
                'easypr_products',
                'normal',
                'high'
            );
        }

        /**
         * Displays the Products metabox fields.
         */
        public function display_metabox_products( $post ) {
            // Nonce for security
            wp_nonce_field( basename( __FILE__ ), 'easyproducts_nonce' );

            // Fetch current meta values with new keys
            $current_price      = get_post_meta( $post->ID, 'easypr_price', true );
            $current_lead_time  = get_post_meta( $post->ID, 'easypr_lead_time', true );
            $current_status     = get_post_meta( $post->ID, 'easypr_status', true );
            $current_brand      = get_post_meta( $post->ID, 'easypr_brand', true );

            // 1. Price
            ?>
            <p>
                <label for="easypr_price"><strong><?php esc_html_e( 'Price / Cost', 'easylaunchproducts' ); ?>:</strong></label><br>
                <input type="number" id="easypr_price" name="easypr_price" value="<?php echo esc_attr( $current_price ); ?>" min="0" style="width:100%; max-width:200px;" />
                <span class="description"><?php esc_html_e( 'Max limit for filter range.', 'easylaunchproducts' ); ?></span>
            </p>

            <p>
                <label for="easypr_lead_time"><strong><?php esc_html_e( 'Lead Time / Short Info', 'easylaunchproducts' ); ?>:</strong></label><br>
                <input type="text" id="easypr_lead_time" name="easypr_lead_time" value="<?php echo esc_attr( $current_lead_time ); ?>" style="width:100%;" />
            </p>

            <p>
                <label for="easypr_status"><strong><?php esc_html_e( 'Product Status', 'easylaunchproducts' ); ?>:</strong></label><br>
                <select id="easypr_status" name="easypr_status" style="width:100%; max-width:200px;">
                    <option value="Available" <?php selected( $current_status, 'Available' ); ?>><?php esc_html_e( 'Available', 'easylaunchproducts' ); ?></option>
                    <option value="On Order" <?php selected( $current_status, 'On Order' ); ?>><?php esc_html_e( 'On Order', 'easylaunchproducts' ); ?></option>
                    <option value="Discontinued" <?php selected( $current_status, 'Discontinued' ); ?>><?php esc_html_e( 'Discontinued', 'easylaunchproducts' ); ?></option>
                </select>
            </p>

            <p>
                <label for="easypr_brand"><strong><?php esc_html_e( 'Brand / Manufacturer', 'easylaunchproducts' ); ?>:</strong></label><br>
                <?php
                // Fetch Brands
                $brands = get_posts( [
                    'post_type'   => 'easypr_brands',
                    'numberposts' => -1,
                    'post_status' => 'publish',
                    'orderby'     => 'title',
                    'order'       => 'ASC',
                ] );
                ?>
                <select id="easypr_brand" name="easypr_brand" style="width:100%; max-width:300px;">
                    <option value="0"><?php esc_html_e( 'Select Brand', 'easylaunchproducts' ); ?></option>
                    <?php
                    if ( ! empty( $brands ) ) {
                        foreach ( $brands as $brand ) {
                            echo '<option value="' . esc_attr( $brand->ID ) . '"' . selected( $current_brand, (int) $brand->ID, false ) . '>' . esc_html( $brand->post_title ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </p>
            <?php
        }

        /**
         * Saves the Products metabox data.
         */
        public function save_metabox( $post_id ) {
            // Check nonce
            if ( ! isset( $_POST['easyproducts_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['easyproducts_nonce'] ) ), basename( __FILE__ ) ) ) {
                return $post_id;
            }

            // Autosave check and permissions check remain the same

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $post_id;
            }
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }

            // Only save for 'easypr_products' post type
            if ( isset( $_POST['post_type'] ) && 'easypr_products' === $_POST['post_type'] ) {

                // 1. Price
                $price_value = isset( $_POST['easypr_price'] ) ? absint( $_POST['easypr_price'] ) : '';
                update_post_meta( $post_id, 'easypr_price', $price_value );

                // 2. Lead Time
                $lead_time_value = isset( $_POST['easypr_lead_time'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_lead_time'] ) ) : '';
                update_post_meta( $post_id, 'easypr_lead_time', $lead_time_value );

                // 3. Status
                $status_value = isset( $_POST['easypr_status'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_status'] ) ) : '';
                update_post_meta( $post_id, 'easypr_status', $status_value );

                // 4. Brand
                $brand_value = isset( $_POST['easypr_brand'] ) ? absint( $_POST['easypr_brand'] ) : '';
                update_post_meta( $post_id, 'easypr_brand', $brand_value );
            }
        }

        /**
         * Enqueue admin styles and scripts
         *
         * @param string $hook The current admin page.
         */
        public function enqueue_admin( $hook ) {
            // Placeholder for admin scripts
        }
    }
}