<?php
/**
 * Template part for displaying the Products filter form.
 *
 * @package     EasyProducts
 * @subpackage  Templates
 * @since       1.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Explicitly declare global variables (must match archive-products.php)
global $easypr_selected_cat, $easypr_selected_prod_type, $easypr_selected_price, $easypr_selected_status, $easypr_selected_brand, $easyProducts;


// Ensure the EasyProducts helper class is available
if ( ! isset( $easyProducts ) && class_exists( 'EasyProducts' ) ) {
    $easyProducts = new EasyProducts();
}
?>

<div class="wrapper filter_form">
    <form method="post" action="<?php echo esc_url( get_post_type_archive_link( 'easypr_products' ) ); ?>">

        <?php
        // Nonce field for security
        wp_nonce_field( 'easyproducts_filter_action', 'easyproducts_filter_nonce' );
        ?>

        <select name="f_cat">
            <option value=""><?php echo esc_html__( 'Select Category', 'easylaunchproducts' ); ?></option>
            <?php
            if ( isset( $easyProducts ) && method_exists( $easyProducts, 'get_terms_hierarchical' ) ) {
                $easyProducts->get_terms_hierarchical( 'easypr_cat', $easypr_selected_cat );
            }
            ?>
        </select>

        <select name="f_prod_type">
            <option value=""><?php echo esc_html__( 'Select Product Type', 'easylaunchproducts' ); ?></option>
            <?php
            if ( isset( $easyProducts ) && method_exists( $easyProducts, 'get_terms_hierarchical' ) ) {
                $easyProducts->get_terms_hierarchical( 'easypr_product-types', $easypr_selected_prod_type );
            }
            ?>
        </select>

        <input
                type="text"
                name="easypr_price"
                placeholder="<?php echo esc_attr__( 'Max Price', 'easylaunchproducts' ); ?>"
                value="<?php echo esc_attr( $easypr_selected_price ); ?>"
        >

        <select name="easypr_status">
            <option value=""><?php echo esc_html__( 'Select Status', 'easylaunchproducts' ); ?></option>
            <option value="Available" <?php selected( $easypr_selected_status, 'Available' ); ?>><?php echo esc_html__( 'Available', 'easylaunchproducts' ); ?></option>
            <option value="On Order" <?php selected( $easypr_selected_status, 'On Order' ); ?>><?php echo esc_html__( 'On Order', 'easylaunchproducts' ); ?></option>
            <option value="Discontinued" <?php selected( $easypr_selected_status, 'Discontinued' ); ?>><?php echo esc_html__( 'Discontinued', 'easylaunchproducts' ); ?></option>
        </select>

        <select name="easypr_brand">
            <option value=""><?php echo esc_html__( 'Select Brand', 'easylaunchproducts' ); ?></option>
            <?php
            $easypr_filter_brands = get_posts(
                array(
                    'post_type'   => 'easypr_brands',
                    'numberposts' => -1,
                    'post_status' => 'publish',
                    'orderby'     => 'title',
                    'order'       => 'ASC',
                )
            );

            if ( ! empty( $easypr_filter_brands ) ) {
                foreach ( $easypr_filter_brands as $easypr_brand_post ) {
                    echo '<option value="' . esc_attr( $easypr_brand_post->ID ) . '"' . selected( $easypr_selected_brand, (int) $easypr_brand_post->ID, false ) . '>' . esc_html( $easypr_brand_post->post_title ) . '</option>';
                }
            }
            ?>
        </select>

        <input type="submit" name="submit" value="<?php echo esc_attr__( 'Filter', 'easylaunchproducts' ); ?>">
    </form>
</div>