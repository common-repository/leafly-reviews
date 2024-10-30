=== Leafly Reviews for WordPress ===
Contributors: deviodigital
Tags: leafly, reviews, widget, shortcode
Requires at least: 3.0
Tested up to: 4.3.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily display your dispensary reviews from Leafly on your own site with a widget or shortcode

== Description ==

**THIS PLUGIN NO LONGER WORKS**

Leafly removed their public API so this plugin does not currently work. If/when they decide to get their act together and get the API back in action, I'll update this plugin.

Until then, make sure you check out **[WP Dispensary](https://www.wpdispensary.com)**, the complete marijuana menu solution for WordPress.

**Original Details**

Easily display your dispensary reviews from Leafly on your own site with a widget or shortcode.

This plugin utilizes the Leafly API to pull your dispensaries reviews from the Leafly website and saves them in a cache file to display on your own website through the use of a shortcode, which is placed on a page or post, or a widget which is placed in your sidebar.

You can get your own Leafly API app ID and KEY from the <a href="http://developer.leafly.com" target="_blank">Leafly Developer</a> website.

**Widget Option**

You can drag and drop the Leafly Reviews widget to any widgetized area on your website, through the `Appearance > Widgets` section of the admin dashboard. There are options you can fill out to choose what is actually displayed in the widget.

**Shortcode Option**

Here is the basic shortcode:

`[leaflyreviews slug="denver-relief"]`

You will need to add in your slug, just like the widget options. The shortcode will default to showing 5 reviews, and all of the options given in the widget (avatar, star rating, detailed rating, recommendation, shop again and comments.

If you'd like to remove some of these options from showing, you can add the option to the shortcode with the value of <em>no</em>, like this:

`[leaflyreviews slug="denver-relief" limit="5" avatar="no" stars="no" ratings="no" recommend="no" shopagain="no" comments="no"]`

== Installation ==

1. Upload the `leafly-reviews` folder to the `/wp-content/plugins/` directory or add it directly through your WordPress admin dashboard
2. Activate the plugin through the `Plugins` menu in WordPress
3. Go to `Settings > Leafly Reviews` and add in your Leafly API ID and KEY (required in order for the plugin to work)
4. Add the Leafly Reviews widget through the `Appearance > Widgets` area of your dashboard, or use the shortcode to display your reviews on any page or post

== Changelog ==

= 1.0.1 =
* Added option to show a link to view all reviews in your Leafly profile

== Upgrade Notice ==

= 1.0.1 =
Added option to show a link to view all reviews in your Leafly profile

== Screenshots ==

1. The widget options that you can use to customize the way your reviews display on your website
2. Sample layout of how the Leafly reviews will show (all options are showing in this demo)
