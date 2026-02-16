<?php
/**
 * The template for displaying the Products archive (archive-products.php).
 *
 * @package     EasyLaunchProducts
 * @subpackage  Templates
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// --- GLOBAL VARIABLES SETUP ---
global $easypr_selected_cat, $easypr_selected_prod_type, $easypr_selected_price, $easypr_selected_status, $easypr_selected_brand;

$easypr_selected_cat       = '';
$easypr_selected_prod_type = '';
$easypr_selected_price     = '';
$easypr_selected_status    = '';
$easypr_selected_brand     = '';


// --- QUERY ARGUMENTS INIT ---
$easypr_posts_per_page = 12;
$easypr_paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$easypr_args = array(
    'post_type'      => 'easypr_products',
    'posts_per_page' => $easypr_posts_per_page,
    'paged'          => $easypr_paged,
    'post_status'    => 'publish',
);

$easypr_meta_query_args = array( 'relation' => 'AND' );
$easypr_tax_query_args  = array( 'relation' => 'AND' );


// --- PROCESS FORM SUBMISSION ---
if ( isset( $_POST['submit'] ) ) {
    if ( isset( $_POST['easyproducts_filter_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['easyproducts_filter_nonce'] ) ), 'easyproducts_filter_action' ) ) {
        
        $easypr_selected_cat       = isset( $_POST['f_cat'] ) ? intval( wp_unslash( $_POST['f_cat'] ) ) : '';
        $easypr_selected_prod_type = isset( $_POST['f_prod_type'] ) ? intval( wp_unslash( $_POST['f_prod_type'] ) ) : '';
        $easypr_selected_price     = isset( $_POST['easypr_price'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_price'] ) ) : '';
        $easypr_selected_status    = isset( $_POST['easypr_status'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_status'] ) ) : '';
        $easypr_selected_brand     = isset( $_POST['easypr_brand'] ) ? intval( wp_unslash( $_POST['easypr_brand'] ) ) : '';

        if ( ! empty( $easypr_selected_status ) ) {
            $easypr_meta_query_args[] = array( 'key' => 'easypr_status', 'value' => $easypr_selected_status, 'compare' => '=' );
        }
        if ( ! empty( $easypr_selected_price ) ) {
            $easypr_numeric_price = preg_replace( '/[^\d]/', '', $easypr_selected_price );
            if ( ! empty( $easypr_numeric_price ) ) {
                $easypr_meta_query_args[] = array( 'key' => 'easypr_price', 'value' => intval( $easypr_numeric_price ), 'type' => 'NUMERIC', 'compare' => '<=' );
            }
        }
        if ( ! empty( $easypr_selected_brand ) ) {
            $easypr_meta_query_args[] = array( 'key' => 'easypr_brand', 'value' => $easypr_selected_brand, 'type' => 'NUMERIC', 'compare' => '=' );
        }
        if ( ! empty( $easypr_selected_cat ) ) {
            $easypr_tax_query_args[] = array( 'taxonomy' => 'easypr_cat', 'field' => 'term_id', 'terms' => $easypr_selected_cat );
        }
        if ( ! empty( $easypr_selected_prod_type ) ) {
            $easypr_tax_query_args[] = array( 'taxonomy' => 'easypr_product-types', 'field' => 'term_id', 'terms' => $easypr_selected_prod_type );
        }
    }
}

// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
if ( count( $easypr_meta_query_args ) > 1 ) { $easypr_args['meta_query'] = $easypr_meta_query_args; }

// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
if ( count( $easypr_tax_query_args ) > 1 ) { $easypr_args['tax_query'] = $easypr_tax_query_args; }

global $easyProducts_Template;
if ( isset( $easyProducts_Template ) ) {
    $easyProducts_Template->get_template_part( 'parts/filter' );
}

$easypr_query = new WP_Query( $easypr_args );
?>

<div class="wrapper archive_products"> 
    <?php if ( $easypr_query->have_posts() ) : ?>
        
        <?php while ( $easypr_query->have_posts() ) : $easypr_query->the_post(); ?>
            <?php 
            if ( isset( $easyProducts_Template ) ) {
                $easyProducts_Template->get_template_part( 'parts/content' );
            } 
            ?>
        <?php endwhile; ?>

        <?php 
        wp_reset_postdata(); 

        if ( $easypr_query->max_num_pages > 1 ) {
            echo '<div class="pagination" style="grid-column: 1 / -1; margin-top: 30px;">';
            $easypr_big = 999999999;
            $easypr_pagination = paginate_links( array(
                'base'      => str_replace( $easypr_big, '%#%', esc_url( get_pagenum_link( $easypr_big ) ) ),
                'format'    => '?paged=%#%',
                'current'   => max( 1, $easypr_paged ),
                'total'     => $easypr_query->max_num_pages,
                'prev_text' => '« Prev',
                'next_text' => 'Next »',
            ) );
            echo wp_kses_post( $easypr_pagination );
            echo '</div>';
        }
        ?>

    <?php else : ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>