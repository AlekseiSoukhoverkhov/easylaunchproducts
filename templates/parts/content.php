<?php
/**
 * Template part for displaying single product card in archive or shortcode output.
 *
 * @package EasyProducts
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'products' ); ?>>

    <?php if ( has_post_thumbnail() ) : ?>
        <div class="product-thumb">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php
                // Safely display featured image
                the_post_thumbnail( 'large' );
                ?>
            </a>
        </div>
    <?php endif; ?>

    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

    <div class="description">
        <?php
        // Output post excerpt
        the_excerpt();
        ?>
    </div>

    <div class="products_info"> <?php
        /**
         * Build product meta information array
         */
        $easypr_parts = [];

        // 1. Category taxonomy terms
        $easypr_cats = get_the_terms( get_the_ID(), 'easypr_cat' );
        if ( $easypr_cats && ! is_wp_error( $easypr_cats ) ) {
            $easypr_cat_names = wp_list_pluck( $easypr_cats, 'name' );
            $easypr_parts[]   = esc_html__( 'Category:', 'easylaunchproducts' ) . ' ' . esc_html( implode( ', ', $easypr_cat_names ) );
        }

        // 2. Product Types taxonomy terms
        $easypr_prod_types = get_the_terms( get_the_ID(), 'easypr_product-types' );
        if ( $easypr_prod_types && ! is_wp_error( $easypr_prod_types ) ) {
            $easypr_type_names = wp_list_pluck( $easypr_prod_types, 'name' );
            $easypr_parts[]    = esc_html__( 'Type:', 'easylaunchproducts' ) . ' ' . esc_html( implode( ', ', $easypr_type_names ) );
        }

        // 3. Status meta field
        $easypr_status = get_post_meta( get_the_ID(), 'easypr_status', true );
        if ( $easypr_status ) {
            $easypr_parts[] = esc_html__( 'Status:', 'easylaunchproducts' ) . ' ' . esc_html( $easypr_status );
        }

        // 4. Price meta field
        $easypr_price = get_post_meta( get_the_ID(), 'easypr_price', true );
        if ( $easypr_price ) {
            $easypr_parts[] = esc_html__( 'Price:', 'easylaunchproducts' ) . ' ' . esc_html( $easypr_price );
        }

        // 5. Lead Time meta field
        $easypr_lead_time = get_post_meta( get_the_ID(), 'easypr_lead_time', true );
        if ( $easypr_lead_time ) {
            $easypr_parts[] = esc_html__( 'Lead Time:', 'easylaunchproducts' ) . ' ' . esc_html( $easypr_lead_time );
        }

        // 6. Brand meta field
        $easypr_brand_id = get_post_meta( get_the_ID(), 'easypr_brand', true );
        if ( $easypr_brand_id ) {
            $easypr_brand_post = get_post( $easypr_brand_id );
            if ( $easypr_brand_post ) {
                $easypr_parts[] = esc_html__( 'Brand:', 'easylaunchproducts') . ' ' . esc_html( $easypr_brand_post->post_title );
            }
        }

        // Output all collected parts
        if ( ! empty( $easypr_parts ) ) {
            echo wp_kses_post( '<ul><li>' . implode( '</li><li>', $easypr_parts ) . '</li></ul>' );
        }

        ?>
    </div>

    <a href="<?php the_permalink(); ?>" class="learn-more">
        <?php esc_html_e( 'View Product...', 'easylaunchproducts' ); ?>
    </a>

</article>