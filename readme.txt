=== Direktt ===
Contributors: direkttwp
Tags: direktt, mobile, integration, api
Requires at least: 5.4
Tested up to: 6.8
Stable tag: 1.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Implements the WordPress based functionality of the Direktt mobile customer care platform.

== Description ==
Direktt plugin connects your WordPress site with the Direktt mobile customer care platform.

This plugin provides the foundational WordPress-side functionality required to integrate with Direktt, including setup for localization and a framework for adding settings, hooks, and extensions as needed.

Localization:

Text Domain: direktt
Domain Path: /languages
Learn more about Direktt at https://direktt.com

== Installation ==

1. Install the plugin using the wp-admin's Plugins screen or upload the plugin files manually to the /wp-content/plugins/direktt folder on your web server.
2. Activate the plugin using the Plugins screen in WordPress' wp-admin.
3. Login into the [Direktt management console](https://direktt.com/wp-content/direkttweb/), create the channel and copy your API Key on Channel Info Screen.
Add the API key to your Direktt Settings on Direktt > Settings screen in wp-admin to enable your WordPress Direktt extensions to make API calls and receive events from Direktt platform.

== Frequently Asked Questions ==

= What is Direktt? =
Direktt is a mobile customer care platform tightly integrated with WordPress. Visit [https://direktt.com](https://direktt.com) for more details.

= Where do I get API credentials or integration details? =
Login into the [Direktt management console](https://direktt.com/wp-content/direkttweb/), create the channel and copy your API Key on Channel Info Screen.

= I am Getting Your site url in your WordPress' General Settings not set to use https protocol. =
Direktt integration requires that your site works under https protocol. If you are not sure what that means, contact your hosting company for the instructions on how to setup https on your site.

== Screenshots ==

1. Direktt Dashboard
2. Direktt Settings
3. Message Templates Builder

== Changelog ==

= 1.0 =
*Initial public release.

== Upgrade Notice ==

= 1.0 =
Initial release of Direktt for WordPress.