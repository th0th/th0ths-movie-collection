<?php

class th0ths_Movie_Collection_Most_Recent extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::WP_Widget('th0th-movie-collection-most-recent', 'Movie Collection - Newest Movies', array( 'description' => 'Newest movies from your collection' ) );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
        $args = array(
            'post_type' => 'movies',
            'numberposts' => $instance['number_of_movies'],
            'orderby' => 'date',
            'order' => 'desc'
        );
        
        $post_movies = get_posts($args);
        $movies = array();
        $i = 0;
        
        foreach ($post_movies as $post_movie)
        {
            $movies[$i]->id = $post_movie->ID;
            $movies[$i]->poster_html = get_post_meta($post_movie->ID, 'poster_html', TRUE);
            $movies[$i]->title = get_post_meta($post_movie->ID, 'title', TRUE);
            
            $i = $i + 1;
        }
                
        echo $before_widget;
        if ($title)
            echo $before_title . $title . $after_title; ?>
            <?php foreach ($movies as $movie) { ?>
                <div class="th0ths-movie-collection-widget-poster">
                    <a href="<?php echo get_permalink($movie->id); ?>"><?php echo $movie->poster_html; ?></a>
                </div>
                <div class="th0ths-movie-collection-widget-title">
                    <a href="<?php echo get_permalink($movie->id); ?>"><strong><?php echo $movie->title; ?></strong></a>
                </div>
            <?php } ?>
        <?php echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number_of_movies'] = strip_tags($new_instance['number_of_movies']);
        
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        if ($instance) {
            $title = esc_attr($instance['title']);
            $number_of_movies = esc_attr($instance['number_of_movies']);
        }
        else
        {
            $title = __( 'New title', 'text_domain' );
            $number_of_movies = 1;
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('number_of_movies'); ?>"><?php _e('Number of movies:'); ?></label> 
            <select name="<?php echo $this->get_field_name('number_of_movies'); ?>">
                <option value="1"<?php if ($number_of_movies == 1) { ?> selected="selected"<?php } ?>>1</option>
                <option value="2"<?php if ($number_of_movies == 2) { ?> selected="selected"<?php } ?>>2</option>
                <option value="3"<?php if ($number_of_movies == 3) { ?> selected="selected"<?php } ?>>3</option>
                <option value="4"<?php if ($number_of_movies == 4) { ?> selected="selected"<?php } ?>>4</option>
                <option value="5"<?php if ($number_of_movies == 5) { ?> selected="selected"<?php } ?>>5</option>
            </select>
        </p>
        <?php 
    }

}

add_action('widgets_init', create_function('', 'return register_widget("th0ths_Movie_Collection_Most_Recent");'));

?>
