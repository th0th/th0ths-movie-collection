<?php

class th0thsMovieCollectionWidgetNewests extends WP_Widget {

	public function __construct() {
		// widget actual processes
		parent::__construct('tmc_newest', __('Newest Movies'), array('description' => 'Testing.'));
	}

	public function form($instance) {
		if ( isset($instance['title']) ) {
			$title = $instance['title'];
			$number_of_movies = $instance['number_of_movies'];
		}
		else {
			$title = __('Newest Movies', 'text_domain');
			$number_of_movies = 5;
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?>:</label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('number_of_movies'); ?>"><?php _e('Number of movies'); ?>:</label> 
		<input id="<?php echo $this->get_field_id('number_of_movies'); ?>" name="<?php echo $this->get_field_name('number_of_movies'); ?>" type="number" value="<?php echo esc_attr($number_of_movies); ?>" style="width: 60px; margin-left: 2px;" />
		</p>
		<?php 
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number_of_movies'] = strip_tags($new_instance['number_of_movies']);

		return $instance;
	}

	public function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);

		# get newest movie posts
		$args = array(
			'numberposts' => $instance['number_of_movies'],
			'post_type' => 'movie',
			'post_status' => 'publish',
			'orderby' => 'post_date'
		);

		$newest_movies = get_posts($args);

		echo $before_widget;
		if ( empty($title) ) {
			echo $before_title . __("Newest Movies") . $after_title;
		} else {
			echo $before_title . $title . $after_title;
		}

		foreach ( $newest_movies as $movie ) {
			?>
			<a href="<?php echo get_permalink($movie->ID); ?>">
				<?php echo get_post_meta($movie->ID, 'html_poster', true); ?>
				<?php echo get_post_meta($movie->ID, 'title', true); ?>
			</a>
			<?php
		}
		echo $after_widget;
	}

}

?>