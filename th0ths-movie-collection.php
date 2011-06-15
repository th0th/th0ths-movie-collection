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

global $wpdb, $th0ths_movie_collection_plugin_version, $th0ths_movie_collection_post_type;

$th0ths_movie_collection_plugin_version = "0.1";
$th0ths_movie_collection_post_type = "movies";

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
        'menu_icon' => WP_PLUGIN_URL . '/th0ths-movie-collection/images/admin/menu-icon.png',
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
    global $post;
    
    $movie['imdb_id'] = get_post_meta($post->ID, 'imdb_id', true);
    
    if (get_post_meta($post->ID, 'imdb_fetched', true) != 'yes' && $movie['imdb_id'] != '')
    {    
	$imdb_labels = array(
	    'title' => 'title',
	    'year' => 'year',
	    'rating' => 'rating',
	    'genres' => 'genres',
	    'directors' => 'directors',
	    'writers' => 'writers',
	    'stars' => 'stars',
	    'cast' => 'cast',
	    'storyline' => 'storyline'
	);
	
	include dirname(realpath(__FILE__)) . '/imdb_fetcher.php';
	
	$imdb = new Imdb();
	$imdb_fetch = $imdb->getMovieInfoDirect($movie['imdb_id']);
	
	foreach (array_keys($imdb_labels) as $movie_meta)
	{
	    update_post_meta($post->ID, $movie_meta, $imdb_fetch[$imdb_labels[$movie_meta]]);
	}
	
	if (get_post_meta($post->ID, 'poster', true) != $imdb_fetch['poster'])
	{
	    $poster_info = media_sideload_image($imdb_fetch['poster'], $post->ID, __("Movie Poster"));
	    update_post_meta($post->ID, 'poster', $imdb_fetch['poster']);
	    update_post_meta($post->ID, 'poster_html', $poster_info);
	}
	
	update_post_meta($post->ID, 'imdb_fetched', 'yes');
    }
}

function th0ths_movie_collection_content_filter($context)
{
    global $post;
    
    $options = get_option('th0ths-movie-collection-settings');
    $labels = $options['labels'];
    
    if (get_post_type($post) == 'movies')
    {
        foreach ($labels as $movie_meta)
        {
	    $movie[$movie_meta] = get_post_meta($post->ID, $movie_meta, true);
        }
        ?>
        
	<?php if (in_array('poster', $labels)) { ?>
        <div class="th0ths_movie_collection_poster"><?php echo get_post_meta($post->ID, 'poster_html', true); ?></div>
	<?php } ?>
        
	<div class="th0ths_movie_collection_labels">
        <?php
        foreach (array_keys($movie) as $meta_key)
        {
	    if ($meta_key != 'poster')
	    {
		?>
		<div class="<?php echo $meta_key; ?>"><b><?php _e(strtoupper($meta_key)); ?>: </b>
		<?php
		if (is_array($movie[$meta_key]))
		{
		    echo implode(', ', $movie[$meta_key]);
		}
		elseif (!is_array($movie[$meta_key]))
		{
		    echo $movie[$meta_key];
		}
		?></div>
		<?php
	    }
        }
	?>
	</div>
	<?php
        
        if (!is_single($post))
        {
            ?>
            <a href="<?php the_permalink(); ?>"><?php _e("Read review..."); ?></a>
            <?php
        }
        else
        {
	    if (strlen($context) != 0)
	    {
		?>
		<hr class="th0ths_movie_collection_seperate" />
		<?php
		echo $context;
	    }
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

function th0ths_movie_collection_options()
{
    if (!empty($_POST))
    {
	update_option('th0ths-movie-collection-settings', $_POST);
    }
    ?>
    <div class="wrap" id="th0ths_movie_collection_options">
        <h2><?php _e("Options"); ?></h2>
        <form method="post">
	    <h3><?php _e("General Options"); ?></h3>
	    <table class="form-table">
		<tbody>
		    <tr>
			<th><label><?php _e("Labels to show"); ?></label></th>
			<td>
			    <select name="labels[]" id="labels" multiple="multiple" size="10">
				<?php th0ths_movie_collection_options_option('labels', 'title', __("Title"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'poster', __("Poster"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'year', __("Year"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'rating', __("Rating"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'genres', __("Genres"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'directors', __("Directors"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'writers', __("Writers"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'stars', __("Stars"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'cast', __("Cast"), true); ?>
				<?php th0ths_movie_collection_options_option('labels', 'storyline', __("Storyline"), true); ?>
			    </select>
			    <span class="description"><?php _e("You can select more than one by holding CTRL button while selecting."); ?>
			</td>
		    </tr>
		    <tr>
			<th><label><?php _e("Fetch info on edit"); ?></label></th>
			<td>
			    <select name="fetch" id="fetch">
				<?php th0ths_movie_collection_options_option('fetch', 'yes', __("Yes")); ?>
				<?php th0ths_movie_collection_options_option('fetch', 'no', __("No")); ?>
			    </select>
			    <span class="description"><?php _e("Fetch movie info from IMDB each time post is edited."); ?></span>
		    </tr>
		</tbody>
	    </table>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e("Save Changes") ?>" />
            </p>
        </form>
    </div>
    <?php
}

function th0ths_movie_collection_options_option($name, $value, $text, $array=false)
{
    ?>
    <option value="<?php echo $value; ?>"
    <?php
    
    $options = get_option('th0ths-movie-collection-settings');
    $option = $options[$name];
    
    if ($array == false)
    {
	if ($option == $value)
	{
	    ?> selected="selected"<?php
	}
	?>><?php echo $text; ?></option><?php
    }
    elseif ($array == true)
    {
	if (in_array($value, $option))
	{
	    ?> selected="selected"<?php
	}
	?>><?php echo $text; ?></option><?php
    }
}

function th0ths_movie_collection_admin_menus()
{
    add_submenu_page('edit.php?post_type=movies', __("Options"), __("Options"), 'manage_options', 'th0ths_movie_collection_options', 'th0ths_movie_collection_options');
}

function th0ths_movie_collection_wp_head()
{
    ?>
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL . '/th0ths-movie-collection/style/wp_head.css'; ?>" />
    <?php
}

function th0ths_movie_collection_admin_head()
{
    ?>
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL . '/th0ths-movie-collection/style/admin_head.css'; ?>" />
    <?php
}

/* register plugin status functions */
register_activation_hook(__FILE__, 'th0ths_movie_collection_activate');

/* register plugin post-type */
add_action('init', 'th0ths_movie_collection_post_type');

add_action('admin_menu', 'th0ths_movie_collection_admin_menus');

/* fetch data from imdb */
add_action('edit_post', 'th0ths_movie_collection_fetch_data');

/* movies custom post type content filter */
add_action('the_content', 'th0ths_movie_collection_content_filter');

/* css */
add_action('wp_head', 'th0ths_movie_collection_wp_head');
add_action('admin_head', 'th0ths_movie_collection_admin_head');

/* register shortcodes */
add_shortcode('th0ths-movie-collection-newests', 'th0ths_movie_collection_sc_newest');
add_shortcode('th0ths-movie-collection-bests', 'th0ths_movie_collection_sc_best');

?>
