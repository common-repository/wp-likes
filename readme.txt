=== WP likes ===
Contributors: Aakash Bapna
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=G66QZRRR7J9HG&lc=IN&item_name=WP%20Likes&item_number=wp%2dlikes&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: likes,like,voting, feedback
Requires at least: 2.0
Tested up to: 3.3
Stable tag: trunk
  
WP Likes lets your blog visitors 'like' your posts on the go.
  
== Description ==
WP Likes lets visitors "like" your posts on the fly. No logging in!
Easily customizable to be used in various other scenarios.
Shows top liked posts in sidebar widget or on a separate page.

== Installation ==

Upload the WP Likes plugin to your blog, Activate it from admin panel and forget it!
If you get errors or it doesnot works, check the wp-likes plugin folder permissions. They should be 755.
Plugin requires php 5.2

Now works with WP Super Cache plugin, check the settings page.

== Frequently Asked Questions ==

= Is there a sidebar widget ? =

Yes! it shows top liked and commented upon posts in your sidebar.

= Does it use jQuery for ajax? =

Yes, for now.I will remove this dependency in future. 
jQuery if being already called on page is reused, otherwise its fetched from Google AJAX APIs. 

= Can I see who likes my post? =

Not right now.

= How do I use shortcode? =
Create a new page with shortcode like- [wp_likes] [wp_likes show="monthly"] [wp_likes show="weekly" count="10"]

= I have a feature request or its not working on my blog. =

Please drop a mail to me[at]aakash[dot]me with blog link.

== Screenshots ==

1. The WP Likes plugin in action on a blogpost.
2. The plugin is customizable to your needs. Fit it the way you want!

== Changelog ==

= 3.1.1 =
* Fixed the breaking compatibility with PHP version <5.3, removed array_replace usages.

= 3.1 =
* Enable/Disable WP likes on specific categories. (check settings page).
* Support for %NUM+1%, %NUM-2% etc in texts.
* Shortcode for displaying top(monthly, weekly) posts on a page.
* Change in behavior- like link always shows now, added- "Be the first to like" text.

= 3.0.2 =
* Fixed a bug with custom rendering when calling function in theme template.
* Now fully compatible with Google Analytics Plugin by Yoast.

= 3.0 =
* Major release. Frontend rewritten, renders in single line now. Will break most of your customizations.
* Much easy to modify & translate plugin to your needs.
* Now compatible with WP super cache plugin.
* Smoother and more stable experience. Bug fixes all around.

= 2.2 =
* Fixed a bug with XHTML validation and sidebar widget.
* The like count appears in RSS feeds now, fixed an issue with loader and like button also showing up in feeds.
* Better compatability with other plugins and javascript frameworks like prototype.

= 2.1 =
* Comment count of popular posts now shown in sidebar widget! 
* More fixes with code not working when php version < 5.3 . Probably an end to call_user_func_array() errors.
* jQuery if not present on page, now doesnot wait for the whole page to load. Instead loads as when required.

= 2.0.5 =
* Fixed issues with PHP versions < 5.3 where call_user_func_array() was not accepting static class methods.
* Issue with WP Likes and wordpress 2.8.4 where it was appearing while  creating new posts should be fixed.
* New function code to freely render pludgin for better compatibility. 

= 2.0.1 =
* fixed an issue with text when no likes are there.
* changed function_exists to is_callable in code for manually calling WP likes on a post.

= 2.0.0 =
* Major release featuring customizing options under settings, admin dashboard and sidebar widgets.
* Changes to UI on posts.
* Ability to hide plugin on front-page and other pages.

= 1.2.0 =
* Fixed issues with plugin and XHTML validation.
* Now on you will see the upgrade details before upgrading.