<?php
/**
 * EasyLaunchProducts Uninstall
 *
 * This script is executed only when the user chooses to completely remove the plugin
 * from the WordPress site. It deletes all associated data, including custom post types
 * and plugin options.
 *
 * @package EasyLaunchProducts
 * @since 1.0.0
 */

// CRITICAL SECURITY FIX:
// 1. Exit if WP_UNINSTALL_PLUGIN constant is not defined.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// 2. CAPABILITY CHECK: Ensure only users with plugin management rights can run this.
if ( ! current_user_can( 'activate_plugins' ) ) {
    return;
}

// --- CLEANUP ACTIONS: DELETE ALL DATA ---

// 3. Delete Custom Post Types data (Product and Brand posts).
// CPT SLUGS: easypr_products, easypr_brands
$easypr_post_types_to_delete = array( 'easypr_products', 'easypr_brands' );

foreach ( $easypr_post_types_to_delete as $easypr_post_type ) {
    // Retrieve all post IDs for the current post type.
    $easypr_posts_to_delete = get_posts( array(
        'post_type'      => $easypr_post_type,
        'posts_per_page' => -1, // Retrieve all posts
        'fields'         => 'ids', // Optimization: retrieve only IDs
        'post_status'    => 'any', // Include posts in any status (draft, trash, published)
        'no_found_rows'  => true, // Further query optimization
    ) );

    // Permanently delete each post.
    if ( $easypr_posts_to_delete ) {
        foreach ( $easypr_posts_to_delete as $easypr_post_id ) {
            wp_delete_post( $easypr_post_id, true ); // true = force delete, bypass trash.
        }
    }
}


// 4. Delete Custom Taxonomies and all their terms.
// TAX SLUGS: easypr_cat, easypr_product-types
$easypr_taxonomies_to_delete = array( 'easypr_cat', 'easypr_product-types' );

foreach ( $easypr_taxonomies_to_delete as $easypr_taxonomy ) {
    $easypr_terms_to_delete = get_terms( array(
        'taxonomy'   => $easypr_taxonomy,
        'hide_empty' => false,
        'fields'     => 'ids', // Optimization: retrieve only IDs
    ) );

    if ( $easypr_terms_to_delete ) {
        foreach ( $easypr_terms_to_delete as $easypr_term_id ) {
            wp_delete_term( $easypr_term_id, $easypr_taxonomy );
        }
    }
}


// 5. Delete Plugin Options/Settings (no options found in provided files).
$easypr_options_to_delete = array(
    // No plugin options found in the provided files (e.g., easyproducts_version, etc.)
);

if ( ! empty( $easypr_options_to_delete ) ) {
    foreach ( $easypr_options_to_delete as $easypr_option_key ) {
        // Delete option for single site
        delete_option( $easypr_option_key );

        // Delete option for multisite
        delete_site_option( $easypr_option_key );
    }
}


// Final cleanup: flush rewrite rules to remove CPT/taxonomy rules from options
flush_rewrite_rules();