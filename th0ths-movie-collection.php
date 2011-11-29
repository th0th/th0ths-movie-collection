<?php
/*
Plugin Name: th0th's Movie Collection
Plugin URI: https://returnfalse.net/log/
Description: A plugin that enables you to share your movie collection with ratings on your WordPress.
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
    flush_rewrite_rules(true);
    
    $default_plugin_settings = array(
		'labels' => array('title', 'poster', 'year', 'rating', 'genres', 'directors', 'writers', 'stars', 'cast', 'storyline'),
		'fetch' => 'no'
	);
	
	if (get_option('th0ths-movie-collection-settings') == '')
	{
		update_option('th0ths-movie-collection-settings', $default_plugin_settings);
	}
}

/* upgrade function */
function th0ths_movie_collection_upgrade()
{
    
}

/* deactivation function */
function th0ths_movie_collection_deactivate()
{
    
}

/* register movie post type */
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

/* imdb fetcher function */
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
        $imdb_fetch = $imdb->getMovieInfoById($movie['imdb_id']);
        
        if (empty($imdb_fetch['poster']))
        {
            $imdb_fetch['poster'] = WP_PLUGIN_URL . '/th0ths-movie-collection/images/no_poster.png';
            $poster_html = "<img src=\"" . $imdb_fetch['poster'] . "\" alt=\"Movie Poster\" / >";
        }
        else
        {
            $poster_html = media_sideload_image($imdb_fetch['poster'], $post->ID, __("Movie Poster"));
        }
        
        foreach (array_keys($imdb_labels) as $movie_meta)
        {
            update_post_meta($post->ID, $movie_meta, $imdb_fetch[$imdb_labels[$movie_meta]]);
        }
        
        $poster_html = media_sideload_image($imdb_fetch['poster'], $post->ID, __("Movie Poster"));
            
        update_post_meta($post->ID, 'poster_html', $poster_html);
        update_post_meta($post->ID, 'imdb_fetched', 'yes');
    }
}

/* content filter for movie displaying page */
function th0ths_movie_collection_content_filter($context)
{
    ob_start();
    
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
    
    <?php
    
    $poster_html = get_post_meta($post->ID, 'poster_html', true);
    
    if (in_array('poster', $labels)) { ?>
            <div class="th0ths_movie_collection_poster">
                <?php echo $poster_html; ?>
            </div>
    <?php } ?>
        
    <div class="th0ths_movie_collection_labels">
        <?php
        foreach (array_keys($movie) as $meta_key)
        {
            if (!empty($movie[$meta_key]))
            {
                if ($meta_key == 'rating')
                {
                    ?>
                    <strong><?php _e(strtoupper($meta_key)); ?>: </strong><?php th0ths_movie_collection_rating2stars($movie[$meta_key]); ?> (<?php echo $movie[$meta_key]; ?>)
                    <?php
                }
                elseif ($meta_key != 'poster')
                {
                    ?>
                    <div class="<?php echo $meta_key; ?>"><strong><?php _e(strtoupper($meta_key)); ?>: </strong>
                    <?php
                    if (is_array($movie[$meta_key]))
                    {
                        echo implode(', ', $movie[$meta_key]);
                    }
                    else
                    {
                        echo $movie[$meta_key];
                    }
                    ?></div>
                    <?php
                }
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
    
    return ob_get_clean();
}

/* shortcode for displaying newest movies */
function th0ths_movie_collection_sc_newest($atts)
{
    extract(shortcode_atts(array(
        'n' => 5
    ), $atts));
    
    $args = array(
        'post_type' => 'movies',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $n
    );
    
    query_posts($args);
    ?><div id="th0ths_movie_collection_sc_newest"><?php
    while (have_posts()) : the_post();
        $movie_poster = get_post_meta(get_the_ID(), 'poster_html', TRUE);
        $movie_storyline = get_post_meta(get_the_ID(), 'storyline', TRUE);
        ?>
        <h2 style="margin: 0 0 8px;"><?php the_title(); ?></h2>
        <div id="th0ths_movie_collection_sc_newest_inner">
            <a href="<?php the_permalink(); ?>"><?php echo $movie_poster; ?></a>
            <h3><?php _e("Storyline"); ?></h3>
            <div><?php echo $movie_storyline; ?></div>
        </div>
        <?php
    endwhile;
    ?></div><?php

    wp_reset_query();
}

/* shortcode for displaying best movies (based on rating) */
function th0ths_movie_collection_sc_best($atts)
{
    extract(shortcode_atts(array(
        'n' => 5
    ), $atts));
    
    $args = array(
        'post_type' => 'movies',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $n
    );
    
    query_posts($args);
    ?><div id="th0ths_movie_collection_sc_newest"><?php
    while (have_posts()) : the_post();
        $movie_poster = get_post_meta(get_the_ID(), 'poster_html', TRUE);
        $movie_storyline = get_post_meta(get_the_ID(), 'storyline', TRUE);
        ?>
        <h2 style="margin: 0 0 8px;"><?php the_title(); ?></h2>
        <div id="th0ths_movie_collection_sc_newest_inner">
            <a href="<?php the_permalink(); ?>"><?php echo $movie_poster; ?></a>
            <h3><?php _e("Storyline"); ?></h3>
            <div><?php echo $movie_storyline; ?></div>
        </div>
        <?php
    endwhile;
    ?></div><?php

    wp_reset_query();
}

/* plugin options page */
function th0ths_movie_collection_options()
{
    if (!empty($_POST))
    {
		$plugin_options = array(
			'labels' => $_POST['labels'],
			'fetch' => $_POST['fetch']
		);
		
        update_option('th0ths-movie-collection-settings', $plugin_options);
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

/* add admin menus */
function th0ths_movie_collection_admin_menus()
{
    add_submenu_page('edit.php?post_type=movies', __("Options"), __("Options"), 'manage_options', 'th0ths_movie_collection_options', 'th0ths_movie_collection_options');
}

/* add plugin's css to wordpress' header */
function th0ths_movie_collection_wp_head()
{
    ?>
<!-- th0th's Movie Collection header starts -->
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/th0ths-movie-collection/style/wp_head.css" />
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/th0ths-movie-collection/style/slider.css" />

<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/th0ths-movie-collection/js/tinyslider2.js"></script>
<!-- th0th's Movie Collection header ends -->
    <?php
}

/* add plugin's css to wordpress' admin header */
function th0ths_movie_collection_admin_head()
{
    ?>
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/th0ths-movie-collection/style/admin_head.css" />
    <?php
}

/* number of stars to show according to rating */
function th0ths_movie_collection_rating2stars($rating)
{
    if ($rating == 0)
    {
        $stars = "0";
    }
    elseif ($rating < 2)
    {
        $stars = "1";
    }
    elseif ($rating < 4)
    {
        $stars = "2";
    }
    elseif ($rating < 6)
    {
        $stars = "3";
    }
    elseif ($rating < 8)
    {
        $stars = "4";
    }
    elseif ($rating < 10)
    {
        $stars = "5";
    }
    ?>
    <img src="<?php echo WP_PLUGIN_URL . "/th0ths-movie-collection/images/rating/$stars.png"; ?>" />
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
add_shortcode('th0ths-movie-collection-newest-movies', 'th0ths_movie_collection_sc_newest');
add_shortcode('th0ths-movie-collection-best-movies', 'th0ths_movie_collection_sc_best');

include "th0ths-movie-collection-widgets.php";

?>
