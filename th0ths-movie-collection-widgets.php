<?php

class th0ths_Movie_Collection_Most_Recent extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::WP_Widget('th0th-movie-collection-most-recent', 'Movie Collection - Newest Movie', array( 'description' => 'Newest movie from your collection' ) );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
        $args = array(
            'post_type' => 'movies',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'desc'
        );
        
        $movies = get_posts($args);
        
        $movie_id = $movies[0]->ID;
        
        $movie = array(
            'poster' => get_post_meta($movie_id, 'poster_html', TRUE),
            'title' => get_post_meta($movie_id, 'title', TRUE)
        );
        
        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title; ?>
        <div class="th0ths-movie-collection-widget-poster">
            <a href="<?php echo get_permalink($movie_id); ?>"><?php echo $movie['poster']; ?></a>
        </div>
        <div class="th0ths-movie-collection-widget-title">
            <a href="<?php echo get_permalink($movie_id); ?>"><strong><?php echo $movie['title']; ?></strong></a>
        </div>
        <?php echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
        if ( $instance ) {
            $title = esc_attr( $instance[ 'title' ] );
        }
        else {
            $title = __( 'New title', 'text_domain' );
        }
        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php 
    }

}

add_action('widgets_init', create_function('', 'return register_widget("th0ths_Movie_Collection_Most_Recent");'));

?>
