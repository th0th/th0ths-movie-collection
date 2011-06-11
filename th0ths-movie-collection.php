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
$th0ths_movie_collection_post_type = "th0ths-movies";

/* activation function */
function th0ths_movie_collection_activate()
{
    global $th0ths_movie_collection_plugin_version;

    add_option("th0ths_movie_collection_version", $th0ths_movie_collection_plugin_version);
}

/* upgrade function */
function th0ths_movie_collection_upgrade()
{
    
}

/* deactivation function */
function th0ths_movie_collection_deactivate()
{
    
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

/* register shortcodes */
add_shortcode('th0ths-movie-collection-newests', 'th0ths_movie_collection_sc_newest');
add_shortcode('th0ths-movie-collection-bests', 'th0ths_movie_collection_sc_best');

?>
