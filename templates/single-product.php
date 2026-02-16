<?php
/**
 * Template for displaying a single product.
 *
 * @package EasyLaunchProducts
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

    <div class="wrapper single_product">
        <main id="main" class="site-main" role="main">

            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'products' ); ?>>

                        <div class="product-thumb">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'large' ); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( EASYPRODUCTS_URL . 'assets/images/placeholder.png' ); ?>" alt="<?php the_title_attribute(); ?>">
                            <?php endif; ?>
                        </div>

                        <div class="product-content">
                            <header class="entry-header">
                                <h1 class="entry-title"><?php the_title(); ?></h1>
                            </header>

                            <div class="product-text">
                                <?php the_content(); ?>
                            </div>

                            <div class="products_info">
                                <?php
                                // Price
                                $easypr_price = get_post_meta( get_the_ID(), 'easypr_price', true );
                                if ( $easypr_price ) {
                                    echo '<p><strong>' . esc_html__( 'Price:', 'easylaunchproducts' ) . '</strong> ' . esc_html( $easypr_price ) . '</p>';
                                }

                                // Lead Time
                                $easypr_lead_time = get_post_meta( get_the_ID(), 'easypr_lead_time', true );
                                if ( $easypr_lead_time ) {
                                    echo '<p><strong>' . esc_html__( 'Lead Time:', 'easylaunchproducts' ) . '</strong> ' . esc_html( $easypr_lead_time ) . '</p>';
                                }

                                // Status
                                $easypr_status = get_post_meta( get_the_ID(), 'easypr_status', true );
                                if ( $easypr_status ) {
                                    echo '<p><strong>' . esc_html__( 'Status:', 'easylaunchproducts' ) . '</strong> ' . esc_html( $easypr_status ) . '</p>';
                                }

                                // Brand Taxonomy
                                $easypr_brands = get_the_terms( get_the_ID(), 'easypr_brands' );
                                if ( $easypr_brands && ! is_wp_error( $easypr_brands ) ) {
                                    $easypr_brand_names = wp_list_pluck( $easypr_brands, 'name' );
                                    echo '<p><strong>' . esc_html__( 'Brand:', 'easylaunchproducts' ) . '</strong> ' . esc_html( implode( ', ', $easypr_brand_names ) ) . '</p>';
                                }
                                ?>
                            </div>

                            <?php
                            // Conditional output of the order form, controlled by plugin settings.
                            global $easyProducts_Order_Form;

                            if ( class_exists( 'EasyProducts_Admin_Settings' ) && EasyProducts_Admin_Settings::should_display_order_form() ) {
                                if ( isset( $easyProducts_Order_Form ) ) {
                                    // Call the prefixed method to display the form.
                                    $easyProducts_Order_Form->easypr_display_order_form();
                                }
                            }
                            ?>

                            <div class="back-to-archive">
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'easypr_products' ) ); ?>">
                                    &larr; <?php esc_html_e( 'Back to Products', 'easylaunchproducts' ); ?>
                                </a>
                            </div>

                        </div></article>

                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e( 'No product found.', 'easylaunchproducts' ); ?></p>
            <?php endif; ?>

        </main>
    </div>

<?php get_footer(); ?>