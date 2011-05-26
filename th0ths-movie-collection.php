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

/* admin pages */
function th0ths_movie_collection_admin_menus()
{
	/* menu item */
	add_menu_page("th0th's Movie Collection", "th0th's Movie Collection", "manage_options", "th0ths_movie_collection", "th0ths_movie_collection_manage_movies");
	
	/* submenu - main */
	add_submenu_page("th0ths_movie_collection", "Manage Movies", "Manage Movies", "manage_options", "th0ths_movie_collection", "th0ths_movie_collection_manage_movies");
	
	/* submenu - options */
	add_submenu_page("th0ths_movie_collection", "Settings", "Settings", "manage_options", "th0ths_movie_collection_options", "th0ths_movie_collection_general_settings");
}

function th0ths_movie_collection_general_settings()
{
	echo "Settings page.";
}

function th0ths_movie_collection_manage_movies()
{
	echo "Manage movies.";
}

/* register menus */
add_action('admin_menu', 'th0ths_movie_collection_admin_menus');

?>
