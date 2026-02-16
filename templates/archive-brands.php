<?php
/**
 * The template for displaying the Brands archive.
 *
 * @package     EasyLaunchProducts
 * @subpackage  Templates
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header(); ?>

    <div class="wrapper archive_brands"> <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('brands-card'); ?>> <?php if ( has_post_thumbnail() ) : ?>
                        <div class="brand-thumb"> <?php the_post_thumbnail('large'); ?>
                        </div>
                    <?php endif; ?>

                    <h2><?php the_title(); ?></h2>

                    <div class="description">
                        <?php the_excerpt(); ?>
                    </div>

                    <a href="<?php the_permalink(); ?>" class="learn-more">
                        <?php esc_html_e( 'View Brand...', 'easylaunchproducts' ); ?>
                    </a>

                </article>
            <?php endwhile; ?>

            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'No Brands found.', 'easylaunchproducts' ); ?></p>
        <?php endif; ?>
    </div>

<?php get_footer(); ?>