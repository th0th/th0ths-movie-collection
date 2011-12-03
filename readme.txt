=== th0th's Movie Collection ===
Contributors: th0th
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9D9EFHMXPRUW6
Tags: movie collection, movies, sidebar, widget
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 0.1

A plugin that enables you to share your movie collection with ratings on your WordPress.

== Description ==

**th0th's Movie Collection** is a plugin that enables you to share collection of your favorite movies on your WordPress blog.

Plugin fetches movie information from internet using imdb id of movie. You write your review of movie just as you are writing a blog post and you are ready to go!

**th0th's Movie Collection** offers you three ways to share your movies:

* A custom post type page (you can display your movies just like your regular blog posts)
* Widgets (you can use two types of widgets. newest movies widget for movies that you added recently and best movies for movies with the highest rating)
* Shortcodes (you can show your movies in a post or in a page)

**NOTE:** Shortcode feature is under development and may be a bit unstable right now.

**IMPORTANT:** IMDb have a notice in their ToS stating scraping data from IMDb is not allowed and this plugin does some scraping. I, as developer of this plugin, don't take any responsibility about this issue. Using this plugin means you accept this responsibility.

= Usage =

First, get your movie's IMDb id, this is required to fetch movie information. Use the *Movies -> Add Menu* menu in your WordPress' administration panel to add a new movie. You will see the page you see when you are adding a new post, actually adding a movie is not very different. Post's title is up to you, you can write movie's title or anything about movie, or just anything else. Post body is the part that will input your review of movie.

Movie information fetching will be done using *Custom Fields*. Scroll down a bit and you will see *Custom Fields* part. Type `imdb_id` as the `Name` of the custom field and your movie's IMDb id as `value`. And you are done. When you press the `Publish` button, movie information will be fetched and you will have a new posts-page-like movies-page in `http://your_blog_url.tld/movies`.

Please don't hesitate to leave your comments and point the issues you encountered on project's [GitHub page](https://github.com/th0th/th0ths-movie-collection "th0ths-movie-collection - GitHub").

== Installation ==

1. Upload th0ths-movie-collection folder to your `wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Displaying a movie on /movies page
2. A movie's individual page including review
3. Widget for newest movies
4. Movie management page
5. Plugin's options page

== Changelog ==

= 0.1 =
* First release.

== More ==
* You can support development of this plugin by donations. https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9D9EFHMXPRUW6[Donate via Paypal]
