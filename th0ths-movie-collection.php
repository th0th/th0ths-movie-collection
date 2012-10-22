<?php
/*
Plugin Name: th0th's Movie Collection
Plugin URI: https://github.com/th0th/th0ths-movie-collection
Description: A movie collection plugin.
Version: 0.99
Author: H.Gökhan Sarı
Author URI: http://th0th.me
License: GPL2
*/

/*  Copyright 2012  H.Gökhan Sarı  (email : me@th0th.me)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class th0thsMovieCollection {
	protected $plugin_version = "0.99";
	protected $plugin_url;
	protected $plugin_path;
	protected $views_path;
	protected $imdb_fields;
	private $plugin_options_var = 'th0ths_movie_collection_options';

	public function __construct() {
		# set class variables
		$this->plugin_url = plugins_url('th0ths-movie-collection');
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->views_path = $this->plugin_path . 'views';

		# fields to be fetched from imdb
		$this->imdb_fields = array('title', 'year', 'rating', 'genres', 'directors', 'writers', 'stars', 'cast', 'release_date', 'country', 'plot', 'storyline');

		# HOOKS AND FILTERS START

		# activation hook
		register_activation_hook(__FILE__, array($this, 'activate_plugin'));

		# register post type
		add_action('init', array($this, 'register_post_type'));

		# add menu items
		add_action('admin_menu', array($this, 'add_admin_menus'));
		
		# meta_boxes
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

		# save imdb_id on save
		add_action('publish_movie', array($this, 'fetch_meta_data'));

		# css
		add_filter('wp_head', array($this, 'handle_style'));

		# admin header
		add_filter('admin_head', array($this, 'handle_admin_header'));

		# the_content filter
		add_filter('the_content', array($this, 'content_filter'));

		# register widgets
		add_action('widgets_init', array($this, 'register_widgets'));

		# slider css and js
		add_action('widgets_init', array($this, 'slider_stuff'));

		# HOOKS AND FILTERS END
	}

	public function activate_plugin() {
		$default_plugin_options = array(
			'fields_to_display' => array('title', 'year', 'rating', 'genres', 'plot'),
			'allow_comments' => true,
		);

		# set plugin options as default
		$this->set_plugin_options($default_plugin_options);
	}

	private function get_plugin_options() {
		return get_option($this->plugin_options_var);
	}

	private function set_plugin_options($arr_options) {
		return update_option($this->plugin_options_var, $arr_options);
	}

	public function register_post_type() {
		$labels = array(
			'name' => __('Movies'),
			'singular_name' => __('Movie'),
			'add_new' => __('Add New'),
			'add_new_item' => __('Add New Movie'),
			'edit_item' => __('Edit Movie'),
			'new_item' => __('New Movie'),
			'all_items' => __('All Movies'),
			'view_item' => __('View Movie'),
			'search_items' => __('Search Movies'),
			'not_found' => __('No movies found'),
			'not_found_in_trash' => __('No movies found in Trash'),
			'parent_item_colon' => __(''),
			'menu_name' => __('Movies')
		);

		$args = array(
			'labels' => $labels,
			'rewrite' => array(
				'with_front' => false,
				'slug' => 'movies'
			),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'has_archive' => true,
			'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'), // comments feature depends on plugin options
			'menu_icon' => $this->plugin_url . '/resources/images/movie-icon.png',
			'menu_position' => 5
		);

		# check if comments are enabled
		$plugin_options = $this->get_plugin_options();

		if ( $plugin_options['allow_comments'] ) {
			$args['supports'][] = 'comments';
		}

		# add movie custom post type
		register_post_type('movie', $args);
	}

	public function add_admin_menus() {
		add_submenu_page('edit.php?post_type=movie', __("Options"), __("Options"), 'manage_options', 'options', array($this, 'admin_page_options'));
	}

	public function admin_page_options() {
		if ( !empty($_POST) ) {
			$comments_allowed = isset($_POST['allow_comments']);

			$new_options = array(
				'fields_to_display' => $_POST['fields_to_display'],
				'allow_comments' => $comments_allowed
			);

			# save options
			$this->set_plugin_options($new_options);

			# set a message to display
			$this->msg_settings = __("Settings saved.");
		}

		$current_options = get_option($this->plugin_options_var);

		require $this->views_path . '/options.php';
	}
	
	public function add_meta_boxes() {
		# meta_box for imdb_id
		add_meta_box('tmc_imdb_id', __('IMDb ID'), array($this, 'html_meta_box_imdb_id'), 'movie', 'normal', 'high', null);

		global $post;

		error_log($post->post_type);
	}

	public function html_meta_box_imdb_id($post, $metabox) {
		?>
		<input id="imdb_id" type="text" name="imdb_id" value="<?php echo get_post_meta($post->ID, 'imdb_id', true); ?>" />
		<label title="<?php _e("WARNING: All information except the post title and review will be overwritten."); ?>" for="force_fetching">
			<input type="checkbox" name="force_fetching" id="force_fetching" style="margin-right: 4px;" /><?php _e("Force fetching data"); ?>
		</label>
		<div id="ajax_movie_info"></div>
		<div style="clear: both;"></div>
		<script>
		jQuery(function() {
			jQuery("input#imdb_id").change(function() {
				jQuery.ajax({
					type : 'GET',
					url: "http://www.omdbapi.com/?i=" + jQuery("input#imdb_id").val(),
					dataType: 'jsonp',
					success: function(movie) {
						if ( typeof(movie.Error) != "undefined" ) {
							jQuery("div#ajax_movie_info").html('<div><p style="font-weight: bold;"><?php _e("Not found."); ?></p>');
						} else {
							jQuery("div#ajax_movie_info").html('<img style="height: 120px; margin-right: 10px; float: left;" src="' + movie.Poster + '" /><div><p style="font-weight: bold;">' + movie.Title + '</p><p>' + movie.Plot + '</p></div>');
						}
					}
				});

			});
		});
		</script>
		<?php
	}

	public function fetch_meta_data($post_id) {
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			# don't do anything if it is an autosave
			return false;
		}

		if ( get_post_meta($post_id, 'fetched', true) != true || isset($_POST['force_fetching']) ) {
			$post = get_post($post_id);

			# delete old posters to prevent stacking of posters
			$attachments = get_posts(array(
				'post_type' => 'attachment',
				'post_parent' => $post_id,
			));

			foreach ( $attachments as $attachment ) {
				if ( $attachment->post_title == 'poster' ) {
					wp_delete_post($attachment->ID);
				}
			}

			$imdb_id = $_POST['imdb_id'];

			# save imdb id as meta data to post
			update_post_meta($post_id, 'imdb_id', $imdb_id);

			require $this->plugin_path . 'lib/imdb.php';
			$imdb_scraper = new Imdb();

			$imdb_scrap = $imdb_scraper->getMovieInfoById($imdb_id);

			# add imdb data as meta to post
			foreach ( $this->imdb_fields as $field ) {
				update_post_meta($post_id, $field, $imdb_scrap[$field]);
			}

			# now it's time to fetch poster if exists
			$html_poster = media_sideload_image($imdb_scrap['poster'], $post_id, 'poster');

			if ( !is_object($html_poster) ) {
				# means poster is fetched successfully
				update_post_meta($post_id, 'html_poster', $html_poster);
			} else {
				# imdb didn't return any posters, so we need to put our own
				ob_start();

				?>
				<img src="<?php echo $this->plugin_url; ?>/resources/images/no_poster.png" alt="poster" />
				<?php

				$html_no_poster = ob_get_clean();
				update_post_meta($post_id, 'html_poster', $html_no_poster);
			}

			# add a marker meta data to prevent future fetches
			update_post_meta($post_id, 'fetched', true);
		}

		return true;
	}

	public function content_filter($content) {
		$post_type = get_post_type();

		# apply filter only if post type is 'movie'
		if ( $post_type == 'movie' ) {
			global $post;

			# get plugin options to use in the view
			$plugin_options = $this->get_plugin_options();

			$fields_to_display = $plugin_options['fields_to_display'];

			# display excerpt or full text?
			$archive_display_mode = $plugin_options['archive_display_mode'];

			if ( $archive_display_mode == 'excerpt' ) {
				$excerpt_length = (int)$plugin_options['excerpt_length'];
			}

			# load view
			require $this->views_path . '/content-movie.php';
		}

		# people does this, dunno
		return;
	}

	public function handle_style() {
		# register style
		wp_register_style('th0ths_movie_collection', $this->plugin_url . '/resources/css/style.css', array(), false, 'all');

		# enqueue style
		wp_enqueue_style('th0ths_movie_collection');
	}

	public function handle_admin_header() {
		wp_enqueue_script("jquery-ui-sortable");
	}

	public function register_widgets() {
		# widgets
		require $this->plugin_path . 'lib/widgets.php';
		register_widget('th0thsMovieCollectionWidgetNewests');
		register_widget('th0thsMovieCollectionWidgetBests');

		# slider js and css
	}

	public function slider_stuff() {
		# load related css and js if widget is active
		if ( is_active_widget(false, false, 'tmc_newest', true) ) {
			# js
			wp_enqueue_script('jquery');
			wp_enqueue_script('tmc_slider_js', $this->plugin_url . '/resources/js/slider.js');

			# css
			wp_enqueue_style('tmc_slider_css', $this->plugin_url . '/resources/css/slider.css');
		}
	}
}

# initiate plugin
$th0ths_movie_collection = new th0thsMovieCollection();

?>