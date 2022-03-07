<?php

/**
 * Most shared posts widget class
 * package MASHSB
 * 
 * @since 3.0.0
 *
 */
class mashsb_mostshared_posts_widget extends WP_Widget {

    function __construct() {
        parent::__construct( false, $name = __( 'MashShare - Most Shared Posts', 'mashsb' ) );
    }

    public function form( $instance ) {
        if( $instance ) {

            $title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : 'Most Shared Posts';
            $count = isset( $instance['count']) ? esc_attr( $instance['count'] ) : '10';
            $excerpt_length = !empty( $instance['excerpt_length']) ? esc_attr( $instance['excerpt_length'] ) : 0;
            $title_length = !empty( $instance['title_length']) ? esc_attr( $instance['title_length'] ) : 70;
            $showShares = isset($instance['showShares']) ? esc_textarea( $instance['showShares'] ) : 'true';
            $countLabel = isset($instance['countLabel']) ? esc_textarea( $instance['countLabel'] ) : 'Shares';
            $period = isset($instance['period'] ) ? esc_textarea( $instance['period'] ) : '365';
            $image_size = !empty($instance['image_size'] ) ? esc_textarea( $instance['image_size'] ) : 0;

                                
        } else {
            $title = 'Most Shared Posts';
            $count = '10';
            $showShares = 'true';
            $countLabel = 'Shares';
            $period = '365';
            $excerpt_length = 100;
            $title_length = 70;
            $image_size = 80;
        }

        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'mashsb' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'How many posts to display?', 'mashsb' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" value="<?php echo $count; ?>" min="0" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>"><?php _e( 'How many characters for the excerpt? Use 0 for not showing!', 'mashsb' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="number" value="<?php echo $excerpt_length; ?>" min="0" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'title_length' ); ?>"><?php _e( 'How many characters for the title?', 'mashsb' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title_length' ); ?>" name="<?php echo $this->get_field_name( 'title_length' ); ?>" type="number" value="<?php echo $title_length; ?>" min="0" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Width of the image in px? Use 0 for not showing a thumbnail', 'mashsb' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>" type="number" value="<?php echo $image_size; ?>" min="0" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'showShares' ); ?>"><?php _e( 'Show Shares? Say "No" when using fake shares!', 'mashsb' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'showShares' ); ?>" name="<?php echo $this->get_field_name( 'showShares' ); ?>">
                <option value="true" <?php if( $showShares === 'true' ) echo 'selected'; ?>>Yes</option>
                <option value="false" <?php if( $showShares === 'false' ) echo 'selected'; ?>>No</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'countLabel' ); ?>"><?php _e( 'Share Count Label', 'mashsb' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'countLabel' ); ?>" name="<?php echo $this->get_field_name( 'countLabel' ); ?>" type="text" value="<?php echo $countLabel; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'period' ); ?>"><?php _e( 'Time period and age of posts', 'mashsb' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'period' ); ?>" name="<?php echo $this->get_field_name( 'period' ); ?>">
                <option value="7" <?php if( $period === '7' ) echo 'selected'; ?>>7 Days</option>
                <option value="7" <?php if( $period === '14' ) echo 'selected'; ?>>14 Days</option>
                <option value="30" <?php if( $period === '30' ) echo 'selected'; ?>>1 Month</option>
                <option value="90" <?php if( $period === '90' ) echo 'selected'; ?>>3 Months</option>
                <option value="180" <?php if( $period === '180' ) echo 'selected'; ?>>6 Months</option>
                <option value="365" <?php if( $period === '365' ) echo 'selected'; ?>>1 Year</option>
                <option value="1095" <?php if( $period === '1095' ) echo 'selected'; ?>>3 Years</option>
            </select>
        </p>

        <?php
    }

    // update the widget
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['count'] = strip_tags( $new_instance['count'] );
        $instance['excerpt_length'] = strip_tags( $new_instance['excerpt_length'] );
        $instance['title_length'] = strip_tags( $new_instance['title_length'] );
        $instance['showShares'] = strip_tags( $new_instance['showShares'] );
        $instance['countLabel'] = strip_tags( $new_instance['countLabel'] );
        $instance['wrapShares'] = strip_tags( $new_instance['wrapShares'] );
        $instance['period'] = strip_tags( $new_instance['period'] );
        $instance['image_size'] = strip_tags( $new_instance['image_size'] );
        
        return $instance;
    }

    // display widget
    public function widget( $args, $instance ) {

        // extract widget options
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $count = $instance['count'];
        $excerpt_length = !empty( $instance['excerpt_length']) ? esc_attr( $instance['excerpt_length'] ) : 0;
        $title_length = !empty( $instance['title_length']) ? esc_attr( $instance['title_length'] ) : 70;
        $image_size= !empty( $instance['image_size']) ? esc_attr( $instance['image_size'] ) : 0;
        $showShares = $instance['showShares'];
        $countLabel = $instance['countLabel'];
        $period = !empty($instance['period']) ? $instance['period'] : '7';

        
        echo '<!-- MashShare Most Popular Widget //-->';
        echo $before_widget;
        // Display the widget
        // Check if title is set
        if( $title ) {
            echo $before_title . $title . $after_title;
        }
        

        // Check if text is set
        $args = array(
            'posts_per_page' => $count,
            'post_type' => 'post',
            'post_status' => 'publish',
            'meta_key' => 'mashsb_shares',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'date_query' => array(
                array(
                    'after' => $period . ' days ago', // or '-7 days'
                    'inclusive' => true,
                ),
            ),
        );
        //$wpq = new WP_Query( $args );
        $wpq = $this->get_qry_from_cache($args);
        //var_dump($wpq);
        if( $wpq->have_posts() ) :
            echo '<ul class="mashsb-share-widget">';
            while ( $wpq->have_posts() ):
                $wpq->the_post();
                $postID = get_the_ID();
                
                $image_url = wp_get_attachment_url( get_post_thumbnail_id($postID) );
                
                if (!empty($image_url)){
                    $css = 'background-image: url('.wp_get_attachment_url( get_post_thumbnail_id($postID) ).');background-size: cover;background-repeat: no-repeat;background-position: 50% 50%;width:'.$image_size.'px;height:' . $image_size . 'px;';
                    $image = !empty($image_size) ? '<div class="mashsb-widget-img" style="' . $css . '"><a class="mashsb-widget-link" href="' . get_the_permalink() . '" style="display:block;width:'.$image_size.'px;height:' . $image_size.'px;">&nbsp</a></div>' : '';
  
                } else {
                    $css = 'display:block;width:'.$image_size.'px;height:' . $image_size.'px;';
                    $image = !empty($image_size) ? '<div class="mashsb-widget-img" style="' . $css . '"><a class="mashsb-widget-link" href="' . get_the_permalink() . '">&nbsp</a></div>' : '';
                }
                               
   
                $title_result = '<div class="mashsb-widget-post-title"><a class="mashsb-widget-link" href="' . get_the_permalink() . '">' . $this->limit_title(get_the_title(), $title_length) . '</a></div>';

                $excerpt =  !empty($excerpt_length) ? '<div class="mashsb-excerpt">' . $this->limit_excerpt(get_the_excerpt($postID), $excerpt_length) . '</div>' : '';

                
                if( $showShares === 'true' ):
                    $shares = get_post_meta( $postID, 'mashsb_shares', true ) + getFakecount();
                    echo '<li>' . $image .  $title_result . $excerpt . ' <span class="mashicon-share">' . roundshares( $shares ) . ' ' . $countLabel . '</span></li>';
                else:
                    echo '<li>' . $image . $title_result . $excerpt. '</li>';
                endif;
            endwhile;
            echo '</ul>';
        endif;
        wp_reset_postdata();
        echo $after_widget;
        echo '<!-- MashShare Most Popular Widget End //-->';
    }
    
    /**
     * Cut characters of the title
     * 
     * @param type $string
     * @param type $int
     * @return type
     */
    private function limit_title($string, $int){
        if (empty($string) || !is_numeric( $int)){
            return $string;
        }
        $newstring = substr($string, 0, $int) . '...';
        return $newstring;
    }
    /**
     * Cut characters of the excerpt
     * 
     * @param type $excerpt
     * @param type $int
     * @return type
     */
    private function limit_excerpt($excerpt, $int){
        if (empty($excerpt) || !is_numeric( $int)){
            return $excerpt;
        }
        return substr($excerpt, 0, $int);
    }
    
    /**
     * Get and store query from transient
     * 
     * @param array $args
     * @return \WP_Query
     */
    public function get_qry_from_cache( $args ) {
        $expiration = mashsb_get_expiration();

        if (MASHSB_DEBUG){
            delete_transient('mashwidget_' . md5( json_encode( $args ) ));
        }
        
        if( false === ( $qry = get_transient( 'mashwidget_' . md5( json_encode( $args ) ) ) ) ) {
            $wpq = new WP_Query( $args );
            set_transient( 'mashwidget_' . md5( json_encode( $args ) ), $wpq, $expiration );
                        //wp_die( var_dump($wpq));  
            return $wpq; 
        } else {
                                //wp_die( var_dump($qry) );
            return $qry;
        }
    }

}

/* Register Widget */
function mashsb_register_widget() {
    register_widget( 'mashsb_mostshared_posts_widget' );
}
add_action( 'widgets_init', 'mashsb_register_widget', 1 );
