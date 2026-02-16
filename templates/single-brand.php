<?php
/**
 * The template for displaying a single Brand.
 *
 * @package     EasyLaunchProducts
 * @subpackage  Templates
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header(); ?>

    <div class="wrapper single_brand"> <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('brands'); ?>>

                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="brand-thumb"> <?php the_post_thumbnail('large'); ?>
                        </div>
                    <?php endif; ?>

                    <h1><?php the_title(); ?></h1>

                    <div class="description">
                        <?php the_content(); ?>
                    </div>

                    <div class="back-to-archive">
                        <a href="<?php echo esc_url(get_post_type_archive_link('easypr_brands')); ?>">
                            <?php esc_html_e('Back to Brands', 'easylaunchproducts'); ?>
                        </a>
                    </div>

                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p><?php esc_html_e('No brand found.', 'easylaunchproducts'); ?></p>
        <?php endif; ?>
    </div>

<?php get_footer(); ?>