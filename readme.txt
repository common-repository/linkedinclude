=== LinkedInclude ===
Contributors: era404
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JE3VMPUQ6JRTN
Tags: LinkedIn
Requires at least: 3.2.1
Tested up to: 5.7.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import your LinkedIn articles into a WordPress widget.

== Description ==

This plugin operates by scraping an Author Post Activity page (e.g.: https://www.linkedin.com/today/author/[author]) to collect information about the author(s)' LinkedIn articles.
The properties of these articles are then sanitized and saved to a new table in WordPress, preparing them for output into your widget. 
The widget can be positioned in sidebars or footers like any other feed.



**Boost the Traffic to your LinkedIn Articles**

* Industry Leaders using LinkedIn for their social or news broadcasts can direct their blog traffic back to LinkedIn. 
* Plugin can import LinkedIn articles from any number of authors.
* Separate widgets can be placed into the templates to show articles by one author, or all author articles sorted by newest.
* Additional widget controls built in for number of articles, module title and excerpt length.
* Imported articles can be individually toggled for visibility. 

== Installation ==

1. Install LinkedInclude either via the WordPress.org plugin directory, or by uploading the files to your server (in the `/wp-content/plugins/` directory).
2. Activate the plugin.
3. Visit Tools > LinkedInclude to find the import utility.
4. Enter a permalink to an Author Post Activity page (e.g.: https://www.linkedin.com/today/author/[author]) and click **[ TEST ]**.
    1. This feature will attempt to scrape available content found on that page on LinkedIn, and present what it was able to read.
    2. If you don't see any results following a test, it's very likely that attempting to FETCH ARTICLES will yield the same results.
    3. It appears that LinkedIn throttles the type of connection LinkedInclude uses to scrape page content. If you get the desired results only periodically, it's advised you wait 24hrs before trying again. 
    4. If this plugin is successful in reading the content of the author's articles while performing the TEST, the next step is to FETCH ARTICLES.
5. If you wish to continue, click **[ FETCH ARTICLES ]** and LinkedInclude will attempt to import into WordPress all of the articles shown in the previous test operation.  
6. These articles will be listed for your moderation, all hidden by default.
    1. Click the checkbox beside each article to attempt fetching the HTML content from the LinkedIn permalink page.
    2. Activated articles will be shown in your LinkedInclude widget(s). Hidden articles will not.
7. Add a LinkedInclude Widget to your sidebar, footer, etc. through the Appearance > Widgets page.
8. Configure the display of the LinkedInclude feed by setting properties in the widget: Title, Source/Author (or All Sources), # Posts to Show, Excerpt Length.
9. Add as many widgets as needed.

== Screenshots ==

1. How your LinkedIn Articles feed will appear on the front end
2. Enter a permalink to an article on LinkedIn to fetch Related Content
3. Activate an article to fetch the content and include the article among those displayed in the widget
4. The widget gives you control over how the feed is displayed
5. Test first whether LinkedIn's Related Content feed will return your own articles

== Frequently Asked Questions ==

= Are there any new features planned? =
Not at this time.

= Can i propose a feature? =
Not at this time.

== Changelog ==

= 3.0.4 =
* Tested on WordPress v5.8;
* Fallback to a secondary body tag ( div.article-content__body ) for scraping content;

= 3.0.3 =
* Tested on WordPress v5.7.2;

= 3.0.2 =
* Adjusted styles to work better with WordPress 5.3.2

= 3.0.1 =
* Updated plugin documentation.

= 3.0.0 =
* Plugin rewritten to AGAIN use the Author Post Activity page (https://www.linkedin.com/today/author/[author]), in place of the previous method of scraping the Related Content page.
* **This version will drop & replace your data table**, effectively starting fresh. Consequently, any articles you may have imported with previous versions of the plugin will be cleared to prepare for the new operation of the plugin. 
* Please note that this plugin is to be considered *experimental* and not guaranteed to function consistently for all WordPress Admins or LinkedIn Authors. As such, a tester function is built into the plugin to help you identify whether your content can be imported properly.  
* You are welcome to rate this plugin poorly if your content is not capable of being imported, however it would be much more informative/beneficial to the plugin authors and other WordPress users if you document the difficulties you experience.

= 2.0.0 =
* Plugin rewritten to use Related Content, in place of the Author Post Activity. 
* **This version will drop & replace your data table**, effectively starting fresh. Consequently, any articles you may have imported with previous versions of the plugin will be cleared to prepare for the new operation of the plugin. 
* Please note that this plugin is to be considered *experimental* and not guaranteed to function consistently for all LinkedIn authors. As such, a tester function is built into the plugin to help you identify whether your content can be imported properly.
* In its current state of rewrite, only article images at 360x120 will be imported. This size is provided by the Related Content feed, and conveniently works well for a sidebar or footer.  
* You are welcome to rate this plugin poorly if your content is not capable of being imported, however it would be much more informative/beneficial to the plugin authors and other WordPress users if you document the difficulties you experience.

= 1.0.1 =
* LinkedInclude now uses [guzzlehttp](http://docs.guzzlephp.org/en/latest/) to fetch articles list and [fabpot/goutte](https://packagist.org/packages/fabpot/goutte) to read article properties
* Author ID now resembles a LinkedIn hash ( e.g.: [https://www.linkedin.com/today/author/0_3O4HsOCdgaCMqMujqsA8Yb](https://www.linkedin.com/today/author/0_3O4HsOCdgaCMqMujqsA8Yb) ) instead of a simple integer User ID. Please see the [installation instructions](https://wordpress.org/plugins/linkedinclude/installation/) for steps to find your Author ID hash, or the [screenshot](https://wordpress.org/plugins/linkedinclude/screenshots/)
* Because the table structure has changed, you will need to deactivate and reactivate this plugin if you have been using older versions of this plugin, before installation.

= 0.9.1 =
* Suppressed warnings about linkedIn articles published without body copy

= 0.9.0 =
* Plugin-out only in beta, currently. Standby for official release.