<?php
/**
 * EasyLaunchProducts List Widget
 *
 * Displays a list of products with various sorting options (Newest, Price, etc.).
 *
 * @package     EasyLaunchProducts
 * @subpackage  Widgets
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'EasyProducts_Filter_Widget' ) ) :

    /**
     * EasyProducts List Widget Class.
     */
    class EasyProducts_Filter_Widget extends WP_Widget {

        /**
         * Widget constructor.
         */
        public function __construct() {
            parent::__construct(
                'easypr_list_widget', // Base ID
                esc_html__( 'EasyProducts List', 'easylaunchproducts' ), // Name
                array( 'description' => esc_html__( 'Displays a list of products sorted by date, price, or popularity.', 'easylaunchproducts' ) )
            );
        }

        /**
         * Front-end display of widget.
         *
         * @see WP_Widget::widget()
         *
         * @param array $args     Widget arguments.
         * @param array $instance Saved values from database.
         */
        public function widget( $args, $instance ) {
            $title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
            $number   = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
            $sort_by  = ! empty( $instance['sort_by'] ) ? $instance['sort_by'] : 'newest';

            // Build Query Arguments
            $query_args = array(
                'post_type'           => 'easypr_products',
                'posts_per_page'      => $number,
                'post_status'         => 'publish',
                'ignore_sticky_posts' => true,
            );

            // Handle Sorting Logic
            switch ( $sort_by ) {
                case 'oldest':
                    $query_args['orderby'] = 'date';
                    $query_args['order']   = 'ASC';
                    break;

                case 'popular':
                    $query_args['orderby'] = 'comment_count';
                    $query_args['order']   = 'DESC';
                    break;

                case 'price_high':
                    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    $query_args['meta_key']  = 'easypr_price';
                    $query_args['orderby']   = 'meta_value_num';
                    $query_args['order']     = 'DESC';
                    $query_args['meta_type'] = 'NUMERIC';
                    break;

                case 'price_low':
                    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    $query_args['meta_key']  = 'easypr_price';
                    $query_args['orderby']   = 'meta_value_num';
                    $query_args['order']     = 'ASC';
                    $query_args['meta_type'] = 'NUMERIC';
                    break;

                case 'newest':
                default:
                    $query_args['orderby'] = 'date';
                    $query_args['order']   = 'DESC';
                    break;
            }

            $products_query = new WP_Query( $query_args );

            echo wp_kses_post( $args['before_widget'] );

            if ( ! empty( $title ) ) {
                echo wp_kses_post( $args['before_title'] ) . wp_kses_post( apply_filters( 'widget_title', $title ) ) . wp_kses_post( $args['after_title'] );
            }

            if ( $products_query->have_posts() ) {
                echo '<ul class="easypr-products-list-widget">';
                while ( $products_query->have_posts() ) {
                    $products_query->the_post();
                    ?>
                    <li>
                        <a href="<?php the_permalink(); ?>">
                            <?php get_the_title() ? the_title() : the_ID(); ?>
                        </a>
                    </li>
                    <?php
                }
                echo '</ul>';
                wp_reset_postdata();
            } else {
                echo '<p>' . esc_html__( 'No products found.', 'easylaunchproducts' ) . '</p>';
            }

            echo wp_kses_post( $args['after_widget'] );
        }

        /**
         * Back-end widget form.
         *
         * @see WP_Widget::form()
         *
         * @param array $instance Previously saved values from database.
         */
        public function form( $instance ) {
            $title   = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Our Products', 'easylaunchproducts' );
            $number  = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
            $sort_by = ! empty( $instance['sort_by'] ) ? $instance['sort_by'] : 'newest';
            ?>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'easylaunchproducts' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of products to show:', 'easylaunchproducts' ); ?></label>
                <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $number ); ?>" size="3">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'sort_by' ) ); ?>"><?php esc_html_e( 'Sort by:', 'easylaunchproducts' ); ?></label>
                <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'sort_by' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sort_by' ) ); ?>">
                    <option value="newest" <?php selected( $sort_by, 'newest' ); ?>><?php esc_html_e( 'Newest first', 'easylaunchproducts' ); ?></option>
                    <option value="oldest" <?php selected( $sort_by, 'oldest' ); ?>><?php esc_html_e( 'Oldest first', 'easylaunchproducts' ); ?></option>
                    <option value="popular" <?php selected( $sort_by, 'popular' ); ?>><?php esc_html_e( 'Most Popular', 'easylaunchproducts' ); ?></option>
                    <option value="price_high" <?php selected( $sort_by, 'price_high' ); ?>><?php esc_html_e( 'Highest Price', 'easylaunchproducts' ); ?></option>
                    <option value="price_low" <?php selected( $sort_by, 'price_low' ); ?>><?php esc_html_e( 'Lowest Price', 'easylaunchproducts' ); ?></option>
                </select>
            </p>
            <?php
        }

        /**
         * Sanitize widget form values as they are saved.
         */
        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title']   = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
            $instance['number']  = ( ! empty( $new_instance['number'] ) ) ? absint( $new_instance['number'] ) : 5;
            $instance['sort_by'] = ( ! empty( $new_instance['sort_by'] ) ) ? sanitize_key( $new_instance['sort_by'] ) : 'newest';

            return $instance;
        }
    }

endif;