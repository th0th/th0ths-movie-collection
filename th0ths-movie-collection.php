<?php
/*
Plugin Name: th0th's Movie Collection
Plugin URI: https://returnfalse.net/log/
Description: A plugin that enables you to your movie collection with ratings on your WordPress.
Version: 0.1
Author: Hüseyin Gökhan Sarı
Author URI: https://returnfalse.net/
License: GPL3
*/

/*  Copyright 2011 Hüseyin Gökhan Sarı  (email : th0th -at- returnfalse.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wpdb, $th0ths_movie_collection_plugin_version, $th0ths_movie_collection_post_type, $th0ths_movie_collection_movie_data;

$th0ths_movie_collection_plugin_version = "0.1";
$th0ths_movie_collection_post_type = "movies";

$th0ths_movie_collection_movie_data = array(
	'imdb_id' => 'title_id',
	'title' => 'title',
	'year' => 'year',
	'imdb_rating' => 'rating',
	'genres' => 'genres',
	'directors' => 'directors',
	'writers' => 'writers',
	'stars' => 'stars',
	'cast' => 'cast',
	'plot' => 'plot',
	#'poster' => 'poster',
	'runtime' => 'runtime',
	'storyline' => 'storyline',
	'imdb_url' => 'imdb_url'
);
	
	

/* activation function */
function th0ths_movie_collection_activate()
{
    global $th0ths_movie_collection_plugin_version;

    add_option("th0ths_movie_collection_version", $th0ths_movie_collection_plugin_version);
    
    /* get permalinks working */
    th0ths_movie_collection_post_type();
    flush_rewrite_rules();
}

/* upgrade function */
function th0ths_movie_collection_upgrade()
{
    
}

/* deactivation function */
function th0ths_movie_collection_deactivate()
{
    
}

function th0ths_movie_collection_post_type()
{
	global $th0ths_movie_collection_post_type;
	
	$post_type_labels = array(
		'name' => __("Movies"),
		'singular_name' => __("Movie"),
		'add_new' => __("Add New"),
		'add_new_item' => __("Add New Movie"),
		'edit_item' => __("Edit Movie"),
		'new_item' => __("New Movie"),
		'view_item' => __("View Movie"),
		'search_items' => __("Search Movies"),
		'not_found' => __("No movies found"),
		'not_found_in_trash' => __("No movies found in Trash"),
		'menu_name' => __("Movies")
	);
	
	$post_type_args = array(
		'label' => __("Movies"),
		'labels' => $post_type_labels,
		'description' => __("Movie Collection"),
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 5,
		'menu_icon' => WP_PLUGIN_URL . "/th0ths-movie-collection/images/admin/menu-icon.png",
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => array('title', 'editor', 'custom-fields', 'comments'),
		'has_archive' => true,
		'rewrite' => true,
		'can_export' => true
	);
		
	
	register_post_type($th0ths_movie_collection_post_type, $post_type_args);
}

function th0ths_movie_collection_fetch_data()
{
	global $post, $th0ths_movie_collection_movie_data;
	
	include dirname(realpath(__FILE__)) . '/imdb_fetcher.php';
	
	$movie['name'] = $post->post_title;
	
	$imdb = new Imdb();
	$imdb_fetch = $imdb->getMovieInfo($movie['name']);
	
	foreach (array_keys($th0ths_movie_collection_movie_data) as $movie_meta)
	{
		update_post_meta($post->ID, $movie_meta, $imdb_fetch[$th0ths_movie_collection_movie_data[$movie_meta]]);
	}
}

function th0ths_movie_collection_content_filter($context)
{
	global $post, $th0ths_movie_collection_movie_data;
	
	if (get_post_type($post) == 'movies')
	{
		foreach (array_keys($th0ths_movie_collection_movie_data) as $movie_meta)
		{
			$movie[$movie_meta] = get_post_meta($post->ID, $movie_meta, true);
		}
		
		foreach (array_keys($movie) as $meta_key)
		{
			?>
			<div class="<?php echo $meta_key; ?>"><b><?php _e(strtoupper($meta_key)); ?>: </b><?php _e($movie[$meta_key]); ?></div>
			<?php
		}
	}
	else
	{
		return $context;
	}
}

function th0ths_movie_collection_sc_newest($atts)
{
    echo "Newest movies.";
}

function th0ths_movie_collection_sc_best($atts)
{
    if ( isset($atts['n']) )
    {
        echo "Best " . $atts['n'] . " movies.";
    }
    else
    {
        echo "Best movies.";
    }
}

/* register plugin status functions */
register_activation_hook(__FILE__, 'th0ths_movie_collection_activate');

/* register plugin post-type */
add_action('init', 'th0ths_movie_collection_post_type');

add_action('edit_post', 'th0ths_movie_collection_fetch_data');

add_action('the_content', 'th0ths_movie_collection_content_filter');

/* register shortcodes */
add_shortcode('th0ths-movie-collection-newests', 'th0ths_movie_collection_sc_newest');
add_shortcode('th0ths-movie-collection-bests', 'th0ths_movie_collection_sc_best');

?>
