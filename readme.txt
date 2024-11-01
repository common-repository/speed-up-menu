=== Speed Up - Menu Cache ===
Contributors: nigro.simone
Donate link: http://paypal.me/snwp
Tags: performance, speed, menu, cache, cache dynamic menu, caching, navigation menu, page speed, performance, quick cache, reduce query, static menu, web performance optimization, wordpress optimization tool, wp-cache
Requires at least: 3.0
Tested up to: 6.0
Stable tag: 1.0.18
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The menu reduces speed of Wordpress. This plugin offers a caching solution that reduces this effects on performance.

== Description ==

For each menu, wordpress reads data from the database and elaborated the results. This process, repeated at each page view, is a waste time and resource.

This plugin offers a caching solution that reduces this effects on performance.

The only downside is that the menu will not show the active item with a different style. This plugin cache one version of the menu for all pages.

Configurations are not required! You just have to install it and after the plugin does it all, none further action it's required.

This plugin is very light: only 5 kb.

== Caveats ==

If you are using user-specific dynamic menu items, this plugin will break that functionality, as it will cache the state of the first page load and any changes will not be seen. There is no per-user cache.

== Installation ==

1. Upload the complete `speed-up-menu` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How can i deactivate cache on some menu? =

Simply add a string 'no-cache' in the menu (eg. in a css class).

== Changelog ==

= 1.0.18 =
* Tested up to Wordpress 6.0

= 1.0.17 =
* Tested up to Wordpress 5.9

= 1.0.16 =
* Tested up to Wordpress 5.7

= 1.0.15 =
* Tested up to Wordpress 5.5

= 1.0.14 =
* Tested up to Wordpress 5.3

= 1.0.13 =
* Update readme.txt

= 1.0.12 =
* Tested up to Wordpress 5.2

= 1.0.11 =
* Improve readme.txt

= 1.0.10 =
* Tested up to Wordpress 4.9

= 1.0.9 =
* Merge cache vary for "ie 5-8" and "ie 9" in unique "old ie"
* Fix exception when no menu are setted 

= 1.0.8 =
* Tested up to Wordpress 4.7

= 1.0.7 =
* Little fix

= 1.0.6 =
* Little fix

= 1.0.5 =
* Vary cache on mobile or old IE browser version (5-8 and 9)
* Vary cache on menu theme location

= 1.0.4 =
* Improve readme.txt

= 1.0.3 =
* Improve readme.txt
* Addded link in appearance menu for purge the cache

= 1.0.2 =
* Fix readme.txt

= 1.0.1 =
* Improve readme.txt

= 1.0.0 =
* Initial release.