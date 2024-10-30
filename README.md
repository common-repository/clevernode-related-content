# CleverNode Related Content

Plugin Name: CleverNode Related Content for WP
Contributors: metup
Tags: related posts, semantic related posts, linked posts, semantic learning, connected posts, similar posts, text scan, blog smart, semantic textual
Requires at least: 5.9
Tested up to: 6.3.1
Requires PHP: 7.4
Stable tag: 1.1.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

CleverNode Related Content is a semantic correlation service that allows you to place a collection of related articles on your WordPress site.

## Description

The **CleverNode Related Content** plugin allows you to **display a collection of related articles**, picked among those on your own site.
The correlation is made through a **semantic algorithm** that scans both the title and content of your articles in order to select a collection of articles.
Articles are displayed as a grid containing the featured image, title and link to the featured article. The **position** of this grid within the page may be **freely chosen**.

**New in version 1.0.2**
Added AMP support: using the official [AMP plugin for WordPress](https://it.wordpress.org/plugins/amp/) and the [AMP for WP - Accelerated Mobile Pages](https://it.wordpress.org/plugins/accelerated-mobile-pages/) plugin, the widget will be rendered as an `amp-embed`.

## Installation

To install this plugin download the .zip archive and upload it directly in Wordpress _Upload Plugin_ page, or unzip it and use an FTP client to upload the `clevernode-related-content` folder to your `wp-content/plugins` directory.

## Frequently Asked Questions

### How to activate the plugin?

To enable the plugin, first go to `Plugin >Installed plugin`, search for the **CleverNode Related Content** plugin and then click on the "Activate" link.

### How to connect to CleverNode?

To connect to the semantic content correlation service **CleverNode** just click the _"Connect to CleverNode"_ button on the first setup tab of the plugin.
This will place a single call to the **CleverNode** service endpoint and allow you to assign a token, which will be then passed as a parameter to the widget display script.

### How does the plugin work?

Once the plugin is connected, a set of meta tags will be added to the `head` of the individual post. These meta tags will contain the title, URL of the featured image, and the date of posting and are used to create the post element in the widget. They may be disabled if Open Graph data is already present.

The main post content will be tagged by an HTML comment, so that **CleverNode** will be able to read it, to extract the semantic argument.
Eventually, there will be inserted the script that displays the related posts widget, by default placed at the bottom of the content on individual post pages. It could be displayed in other locations using the `[clevernode-related]` shortcode.

When the single post page is loaded, this script will make a call to the **CleverNode** correlation service, passing the post URL and token as parameters, so the service can read the meta tags and content of the post to extract the topic and can send back a JSON with the correlated results for that article.

For more information about the plugin options, you can find the documentation here: [https://clevernode.it/docs/](https://clevernode.it/docs/)

### What data is required by the plugin?

The plugin needs the website URL to generate the website token, parse individual posts, and return results, which are semantically related to the specific site.
The URL of the single posts, the meta tags and the name of the identified topic are recorded in our database, for a 15 days from each call, to allow **CleverNode** to perform the correlation and display the widget.

The plugin does NOT save or use any cookies and NO browsing events are tracked in any way. None of the saved meta tags will be used for purposes other than providing related results, nor transferred to third parties, and NO textual or visual content (HTML or images) will be stored, excluding meta tags as specified above.

For more information about the privacy policy, you can find the documentation here:
[https://clevernode.it/privacy-policy/](https://clevernode.it/privacy-policy/)

### What to do if the widget is not displayed?

If the related articles widget is not visible immediately, please try browsing through the articles on your site to give **CleverNode** time to process the content.
Within a minute or two of browsing, the widget will start to get more crowded. This widget can be found at the bottom of each article or next to the shortcode location.

### Changelog

*20230927*
- Fix: check response object on admin init.
- Fix: the_content filter display condition.
- Added notice for review request.

*20230323*
- Fix: HTTP request settings.
- Fix: HTTP client exceptions to catch all response errors.
- Disabled connection button after submit to prevent multiple requests.
- Fix: script duplication issue for shortcode.

*20230208*
- Changed emoji on feedback message.
- Set flag to avoid widget duplication in filter mode.
- Added setting metadata.

*20230202*
- Checked if shortcode is enabled on the script filter.
- Added Clevernode version meta.
- Added settings data object.

*20230130*
- Fix: post title for the EdTitle meta tag.
- Added F.A.Q. for script optimization plugins.

*20230118*
- Fix for unique script id.

*20230117*
- Checked required PHP version before plugin initialization.
- Added a persistent and dismissible connection notice.

*20230109*
- Connection and settings saved feedback.
- Script attributes to avoid removal by optimization plugins.

*20221222*
- Fix: account disconnection.
- Added widget preview screenshot.

*20221201*
- Fix: PHP notices in Meta Tags and Settings.

*20221130*
- Fix: connection styles.

*20221122*
- Loading scripts and style only on settings page.
- Meta Tags: disable others meta if OG enabled.
- Fix: AMP embed for shortcode.

*20221012*
- Fix: AMP embed height.

*20220920*
- Added AMP support.

*20220915*
- Fix: PHP required to 7.4.
- Fix: get IP for support on admin init.
- Catch all exceptions in Guzzle HTTP requests.

*20220810*
- First release.

## Screenshots

1. Connect to CleverNode
2. Plugin options - Meta Tags
3. Plugin options - Display
