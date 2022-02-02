=== Social Media Share Buttons | MashShare ===

Author URL: https://www.mashshare.net
Plugin URL: https://www.mashshare.net
Contributors: ReneHermi, WP-Staging
Donate link: https://www.mashshare.net
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: Share buttons, Social Sharing, social media, Facebook, Twitter, Subscribe, social share buttons
Requires at least: 3.6+
Tested up to: 5.8
Stable tag: 3.8.0 
Requires PHP: 5.2

Social Media Share Buttons for Twitter, Facebook, and other social networks. Highly customizable Social Media ecosystem

== Description == 

#### MashShare - The Social Media Share Buttons Ecosystem (Twitter count supported with [Social Network Add-On](https://mashshare.net/downloads/mashshare-social-networks-addon/))
A free Social Media Share Buttons Plugin, professional and highly customizable<br />
ecosystem for social media sharing and optimizing your valuable content.<br /> 

<strong>Important for EU users: MashShare is DSGVO compliant!<br />
No IP data or any other personal data is sent to third parties or collected at all.</strong><br>
 
<strong>Stop slowing down your website and prevent ranking loss.</strong>
Other social share buttons are often using external scripts which are increasing loading times.
MashShare is using NO external script dependencies. All code is loaded directly from your website and <strong>MashShare ensures your and your visitor's privacy!</strong>  <br /> 

* <strong>New: </strong> Most Shared Posts Widget
* <strong>New: </strong> Async share count aggregation for Share Buttons share count
* <strong>New: </strong> Dashboard for total share buttons share count on posts screen
* <strong>New: </strong> Short URL integration for share buttons
* <strong>New: </strong> Support for Accelerated Mobile Pages (AMP) when using the [official WordPress AMP plugin](https://wordpress.org/plugins/amp/)

<strong>Installation</strong>
[youtube https://www.youtube.com/watch?v=vRSE-pQJTBQ]

It gives you per default a large total share button counter beside three large 
prominent Share Buttons for your Twitter tweets, Facebook share, and the option<br /> 
to place a prominent subscribe button for your news feed and mailing list. 
These services are free per default including great support. 
There is no need to create an account! <br /> 

We also offer free Add-Ons for specifying social sharing image, title, description, and Twitter hashtags and think this is satisfying for most website owners who need a free and effective working social sharing solution.<br /> 

If you need share buttons fore Whatsapp, Pinterest, Mail,<br />
Print, Linkedin, Odnoklassniki, etc., you get them on the Add-On Marketplace.<br />
 
MashShare can also be used in conjunction with other third-party share buttons vendors!

Free and paid Add-Ons available for:
 
- More Social Share Buttons<br /> 
- YouTube Video Share Popup<br />
- Share Button Responsive<br /> 
- Sticky Share bar<br /> 
- Social Sharing Optimization<br /> 
- Google Analytics<br /> 
- and more...<br /> 

[Demo](https://www.mashshare.net?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-info-link) | [More Add-Ons >>](https://www.mashshare.net/downloads?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=get-addons)

 
<h4> Social Media Share Buttons</h4>

This Social Media share buttons plugin in active development and will be updated regularly - Please do not rate negative before we tried to solve your issue.

= Main Features =

* High-Performance Social Media Share Buttons - easy to use - Share Buttons for the most common networks
* High-Resolution lossless vector font share button icons
* Show the Total Social Media Share count at a glance
* Object and transient caches to provide incredibly fast execution speed of Social Media Share Button Icons
* Shortcodes
* Extensible with many [Add-Ons](https://www.mashshare.net/downloads) (Google Analytics, More Social Networks, Responsive, YouTube Video Share and more...)
* Developer friendly with several filters and actions.
* Highly customizable
* Smart (virtual) share button count function. Add virtual shares to new articles. Use psychological aspects to increase real shares. 

= Recent Changes and New Features: =

* Social Media Share Buttons Icons with Improved performance
* Option to disable Social Media share button count completely (no SQL queries will be generated any longer)
* Shortcode option to disable share counts
* Checking if curl is working on the server
* Option to disable share count cache for testing purposes
* Use of sharp and crisp clear font Social Media Icons instead Social Media Icons images
* Button 'extra content' for content slider subscribe forms or any other content New: Use a link for the Subscribe button instead of the toggle dropdown
* Complete rewrite of CSS for easier modifications
* Improved MashShare Social Media Share Button extension system
* Improved backend, new MashShare Social Media Share Button Add-On page
* Multi-language capable, *.po files
* Change color of share counts via setting
* Count up animation for share buttons counts (Does not work for shortcodes and on blog pages)
* HTML5 Tag < aside > wrapped around to tell search engines that the share buttons are not part of the content
* Plus button moves to end of share buttons when activated and does not stay longer in place.
* Drag and drop sort order of share buttons services.
* Enable desired Social Media share buttons Icons with one click
* Choose which Social Media network should be visible all the time This one will be large-sized by default. Other Social Media networks are behind the plus sign
* Three different effective share button styles - Less is more here
* Choose border radius of the Social Media buttons
* Keep settings when share buttons are uninstalled - optional
* Custom CSS field for the individual styling of the social media share buttons

<strong> Social Media Add-Ons available for </strong>

* Google / G+
* Whatsapp (Whatsapp button is shown only on mobile devices)
* Pinterest
* Digg
* Linkedin
* Reddit
* Stumbleupon
* Vk / VKontakte
* Print
* Delicious
* Buffer
* Weibo
* Pocket
* Xing
* Tumblr
* Mail
* ManageWP
* Meneame
* Odnoklassniki
* Frype / Draugiem
* Skype
* Flipboard
* Hackernews

= High Performance =

MashShare Social Media ecosystem is *coded well and developed for high performance*. It´s making full use of available persistent and non-persistent caching techniques.
MashShare loads only the Javascript and PHP object classes it needs at the moment of execution, making it small and fast, and easily extensible by a third-party developer.

<h4>How fast is MashShare?</h4>

We published benchmarks of using MashShare compared with other plugins here:
https://www.mashshare.net/mashshare-proven-fast-benchmark/

**Shortcodes**

* Use `[mashshare]` anywhere in pages or post's text to show the buttons and total share count wherever you like.
Share Buttons will be shown exactly on the place where you copy the shortcode in your content.

There are more parameters available:

* Embed Share Buttons in pages or posts:  `[mashshare] `
* Buttons without sharecount: `[mashshare shares="false"]`
* Sharecount only: `[mashshare buttons="false"]`
* Share buttons alignment: `[mashshare shares="false" buttons="true" align="left|right"]`
* Shortcode in template files via php: `echo do_shortcode('[mashshare]');`
* Custom url:  `[mashshare url="http://www.google.de"]`
* Custom share text: `[mashshare text="This is my custom share text"]`

* For manual insertion of the Share Buttons in your template files use the following PHP code on the place you like to see the share buttons:`echo do_shortcode('[mashshare]');`
Configure the Share buttons sharing function in the settings page of the plugin.
* Change the color of MashShare count with the setting option.

**Full SEO third party plugin support**
MashShare integrates with [All in One SEO Pack](http://wordpress.org/plugins/all-in-one-seo-pack/) and [WordPress SEO by Yoast](http://wordpress.org/plugins/wordpress-seo/).
Any description and title which is defined in Yoast open graph settings will be used by MashShare Open Graph Settings

** GitHub **
Follow the development and improve MashShare.
You find us on GitHub at https://github.com/mashshare/MashShare 

** Languages **

MashShare has been translated into many languages:

1. English
2. German
3. Spanish
4. Turkish
5. Italy
6. Portuguese (Brazil)

Please help to translate the share button plugin into more languages: 
https://translate.wordpress.org/projects/wp-plugins/mashsharer

= How does it work? =

MashShare makes use of public available API endpoints that are delivered by social networks. It periodically checks for the total count of all your Facebook and Twitter shares and cumulates them. It then shows the total number beside the Share and Social Media Icons. 
No need to embed dozens of external slow loading scripts into your website. 

 
= How to install and setup? =
Install it via the admin dashboard and to 'Plugins', click 'Add New' and search the plugins for 'MashShare'. Install the plugin with 'Install Now'.
After installation go to the settings page Settings->MashShare and make your changes there.


== Frequently Asked Questions ==

> Find here the Frequently Asked Questions. Also, look into our docs which is often more up to date:
http://docs.mashshare.net/

<h4>There are no social share buttons visible after updating or installing MashShare</h4>
This happens sometimes when you are using the MashShare Network Add-On which is disabled during the update process or when you are updating from a very early MashShare version 1.x.
Solution: Disable MashShare Social Media Network Add-On and MashShare Core plugin. Enable first MashShare THAN the Social Media Network Add-On and all Social Media Share buttons to become visible again. (Activating order is important here)

<h4>Why is the Social Media total count not shown immediately after sharing?</h4>
It takes some time for the script to detect the sharing. So wait a few minutes then you see the total calculated clicks. Keep also in mind the caching time you defined in the admin panel.
So when you set the plugin to 5minutes caching time. You have to wait at least for 5minutes until the click count is shown.

<h4>Do I need a MashShare account?</h4>
There is no account needed. All code resides on your website and there is no account or any monthly fee necessary to use MashShare.

<h4>Does this plugin sends any personal user data to you or Facebook, Twitter, etc.?</h4>

No, there is no personal data send to Facebook, Twitter, Google, and other services. No data goes to MashShare that includes any IP or other data without your explicit grant.
The big advantage of using Mashable Share buttons is the independence in comparison to other plugins which creates steady connections to Facebook and Co. 
So there is no IP based data send to the social networks or shared count. 

<h4>Do I have to do manual changes in Javascript or HTML Code?</h4>
There is no need for you to make any manual changes. The plugin does everything for you. But if you are an experienced web-developer you are free to use the PHP function mashsharer(); in your templates.

<h4>Is there a shortcode for pages and posts?</h4>
Use the shortcode [mashshare] to embed the Share Buttons in pages or posts.

<h4>Why is Facebook sharing only the URL and not the title and description of my page?</h4>
You need to enable the open graph settings or install a plugin like Yoast which injects open graph tags into your site
Read here more about this: http://docs.mashshare.net/article/10-facebook-is-showing-wrong-image-or-share-text

== Official Site ==
* https://www.mashshare.net

== Installation ==
1. Download the share button plugin "MashShare", unzip and place it in your wp-content/plugins/ folder. You can alternatively upload and install it via the WordPress plugin backend.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Select Plugins->MashShare

== Screenshots ==

1. Subscribe form with Social Media Facebook share button and mail subscribe
2. Default share buttons with separate available responsive Add-On
3. Sortable Total Share Dashboard
4. Default Share buttons with separate available responsive Add-On
5. Responsive Design + Social-Networks (separate Add-Ons)
6. Default Share buttons + Subscribe Button opened (included)
7. Social Media Settings on the post edit screen
8. Sticky Sharebar Add-On on a desktop device
9. Circle style (included)
11. Default share buttons (included)
12. Custom Social Sharing descriptions with free MashShare Open Graph Add-On
13. Extend MashShare with great Add-Ons


== Changelog ==

= 3.8.0 =
* New: Compatible up to WordPress 5.8

= 3.7.9 =
* New: Compatible up to WordPress 5.7

= 3.7.8 =
* New: Compatible up to WordPress 5.5.3

= 3.7.7 =
* Fix: Network drag and drop does not work in WordPress 5.5
* Fix: Undefined var warning
* New: PHP 7.4 compatibility

= 3.7.6 =
* Fix: Minify mashsb-amp.css

= 3.7.5 =
* Fix: Validate Open Graph Data button is broken

= 3.7.4 =
* New: Supports up to WordPress 5.4
* Fix: Share count not collected for all pages on large sites with huge traffic if caching plugins are used

= 3.7.3 =
* New: Support for WhatsApp web.  Social network add-on needed https://mashshare.net/downloads/mashshare-social-networks-addon/
* Fix: Show correct results if debug mode is active and sharedcount.com integration works.

= 3.7.2 =
* New: Compatible to WordPress 5.3.0

= 3.7.1 =
* Fix: Shares are not updated as intended

= 3.7.0 =
* Fix: PHP Warning: Invalid argument supplied for foreach() in meta-box.php
* Fix: Set the share count query rate limit to 1req/5min
* New: Compatible up to WordPress 5.2.4

= 3.6.8 =
* New: Add new filter mashsb_allowed_post_types for allowing or disabling share counts on particular posts types to lower the API requests to sharedcount.com
* Tweak: sharedcount.com lowered their free API limit to 500 daily requests. Change description in MashShare! 
* Fix: Changed register link for sharedcount.com
* Fix: PHP Warning: Invalid argument supplied for foreach() in meta-box.php

= 3.6.7 =
* Fix: Whatsapp sharing does not work

= 3.6.6 =
* Tweak: Added an extra check for HTTP_HOST to make it more compliant with cron jobs and/or API workers

= 3.6.5 =
* Fix: Share count not collected with an async caching method

= 3.6.5 =
* New: Compatible up to WordPress 5.2
* Fix: Do not call the Facebook API directly any longer
* Fix: Undefined variable notice after uninstallation and deleting all MashShare settings an installation again


See release notes and complete changelog at:
https://www.mashshare.net/changelog/

== Upgrade Notice ==

= 3.8.0 =
* New: Compatible up to WordPress 5.8
