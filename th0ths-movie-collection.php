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

global $wpdb, $th0ths_movie_collection_plugin_version, $th0ths_movie_collection_db_table;

$th0ths_movie_collection_plugin_version = "0.1";
$th0ths_movie_collection_db_table = $wpdb->prefix . "th0ths_movie_collection";

/* activation function */
function th0ths_movie_collection_activate()
{
    global $wpdb, $th0ths_movie_collection_plugin_version, $th0ths_movie_collection_db_table;
    
    $sql = "CREATE TABLE " . $th0ths_movie_collection_db_table . " (
                id INT(10) NOT NULL AUTO_INCREMENT ,
                name VARCHAR(255) NOT NULL ,
                cover VARCHAR(255) NOT NULL ,
                genre VARCHAR(255) NOT NULL ,
                year VARCHAR(20) NOT NULL ,
                rating INT(2) NOT NULL ,
                length INT(5) NOT NULL ,
                director VARCHAR(255) NOT NULL ,
                writers VARCHAR(255) NOT NULL ,
                stars VARCHAR(255) NOT NULL ,
                review TEXT NOT NULL ,
                PRIMARY KEY (id) ,
                INDEX (id)
			)";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option("th0ths_movie_collection_version", $th0ths_movie_collection_version);
}

function th0ths_movie_collection_default_data()
{
	global $wpdb, $th0ths_movie_collection_db_table;
	
	$init_movies = array();
	
	$init_movies[] = array(
		'name' => 'Seinfeld',
		'cover' => 'http://ia.media-imdb.com/images/M/MV5BMTM4MDI0NDQ1MF5BMl5BanBnXkFtZTcwNzI3NzYyMQ@@._V1._SY317_CR4,0,214,317_.jpg',
		'genre' => 'Comedy',
		'year' => '1990-1998',
		'rating' => '10',
		'length' => '23',
		'director' => '',
		'writers' => 'Jerry Seinfeld, Larry Davis',
		'stars' => 'Jerry Seinfeld, Julia Louis-Dreyfus, Michael Richards',
		'review' => 'The continuing misadventures of neurotic New York stand-up comedian Jerry Seinfeld and his equally neurotic New York friends.'
	);
	
	foreach ($init_movies as $movie)
	{
		$rows_affected = $wpdb->insert($th0ths_movie_collection_db_table, $movie);
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

/* admin pages */
function th0ths_movie_collection_admin_menus()
{
    /* menu item */
    add_menu_page("th0th's Movie Collection", "th0th's Movie Collection", "manage_options", "th0ths_movie_collection", "th0ths_movie_collection_manage_movies", WP_PLUGIN_URL . "/th0ths-movie-collection/images/admin/menu-icon.png");
    
    /* submenu - main */
    add_submenu_page("th0ths_movie_collection", "Manage Movies", "Manage Movies", "manage_options", "th0ths_movie_collection", "th0ths_movie_collection_manage_movies");
    
    /* submenu - options */
    add_submenu_page("th0ths_movie_collection", "Add Movie", "Add Movie", "manage_options", "th0ths_movie_collection_add", "th0ths_movie_collection_add_movie");
    
    /* submenu - options */
    add_submenu_page("th0ths_movie_collection", "Settings", "Settings", "manage_options", "th0ths_movie_collection_options", "th0ths_movie_collection_general_settings");
}

function th0ths_movie_collection_general_settings()
{
    ?>
    <div class="wrap">
        <h2>Settings</h2>
    </div>
    <?php
}

function th0ths_movie_collection_add_movie()
{
    ?>
    <div class="wrap">
        <h2>Add Movie</h2>
    </div>
    <?php
}

function th0ths_movie_collection_manage_movies()
{
	global $wpdb, $th0ths_movie_collection_db_table;
	
	$movies = $wpdb->get_results("SELECT * FROM " . $th0ths_movie_collection_db_table, ARRAY_A);
	
    ?>
    <div class="wrap">
		<h2>Manage Movies</h2>
		<div>
			<form method="post" name="">
				<table class="widefat">
					<thead>
						<tr>
							<th><input type="checkbox" onClick="checkAll('quoteCB',this)" /></th>
							<th>Cover</th>
							<th>Name</th>
							<th>Genre</th>
							<th>Year</th>
							<th>Rating</th>
							<th>Length</th>
						</tr>
						<?php foreach ($movies as $movie) { ?>
						<tr>
							<td><input type="checkbox" class="quoteCB" name="quoteIDs[]" value="<?php echo $movie['id']; ?>" /></td>
							<td><img src="<?php echo $movie['cover']; ?>" /></td>
							<td><?php echo $movie['name']; ?></td>
							<td><?php echo $movie['genre']; ?></td>
							<td><?php echo $movie['year']; ?></td>
							<td><?php echo $movie['rating']; ?></td>
							<td><?php echo $movie['length']; ?></td>
						</tr>
						<?php } ?>
					</thead>
				</table>
				<input name="action" class="button" type="submit" value="What to do?" />
			</form>
		</div>
    <?php
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
register_activation_hook(__FILE__, 'th0ths_movie_collection_default_data');

/* register menus */
add_action('admin_menu', 'th0ths_movie_collection_admin_menus');

/* register shortcodes */
add_shortcode('th0ths-movie-collection-newests', 'th0ths_movie_collection_sc_newest');
add_shortcode('th0ths-movie-collection-bests', 'th0ths_movie_collection_sc_best');

?>
