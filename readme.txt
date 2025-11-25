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
Direktt plugin connects your WordPress site with the [Direktt mobile customer care platform](https://direktt.com/).

This plugin provides the foundational WordPress-side functionality required to integrate with Direktt, including developers' framework.

It provides wp-admin interface for: 

- General Direktt Settings 
- Direktt User Management
- Direktt Message Template Management with Bulk Messaging Tool
- Direktt Mobile App's User Profile Interface (Direktt User Taxonomy, User Notes and User Messaging).

Direktt platform sends your channel's events to your WordPress instance using API endopints implemented by Direktt WP plugin, so you can programmatically act on them.
The events include:

- New User Subscriptions
- User Unsubscribe events
- Sent and received user messages
- Page views by channel subscribers via Direktt mobile app
- QR code scans by channel subscribers via Direktt mobile app
- Chat interface user actions in chat interface within Direktt mobile app

Direktt WP plugin implements developer's framework which enables you to:

- Implement hooks and actions to act on Direktt events
- Plug into Direktt settings panel and user profile within Direktt mobile app
- Use api functions to verify and authorize users and events coming from Direktt mobile app / platform
- Send messages to channel subscribers

== External Service Disclosure ==

Direktt WordPress plugin does not send any user tracking data back to Direktt platform.

Direktt User related data sent from Direktt platform to WP API on these events include:

- Direktt User Display name,
- Direktt User Avatar and 
- Channel level unique subscription Id

No user trackable data is shared between channels or with third party platforms or services (this includes email or any other Direktt platform level user data)

Plugin implements calls to the Direktt API upon user actions (no calls are made automatically or in background by the plugin). They include following endpoints:

* https://getDataForChannel-lnkonwpiwa-uc.a.run.app - endpoint is called when user visits the Direktt dasboard in wp-admin to gather current channel status
* https://activatechannel-lnkonwpiwa-uc.a.run.app - endpont is called on channel activation
* https://getsubscriptionsforchannel-lnkonwpiwa-uc.a.run.app - endpont is called when channel user synchronization is initiated in wp-admin's Direktt settings

* https://sendbulkmessages-lnkonwpiwa-uc.a.run.app - endpoint is called when message is sent to channel subscribers
* https://sendadminmessage-lnkonwpiwa-uc.a.run.app - endpoint is called when message is sent to channel admin
* https://updateMessage-lnkonwpiwa-uc.a.run.app - endpoint is called when sent message is updated

All calls are authenticated using Direktt API key

Find Direktt Privacy Policy [HERE](https://direktt.com/privacy-policy/)
Find Direktt Terms of Service [HERE](https://direktt.com/terms-of-service/)  

Localization:

Text Domain: direktt
Domain Path: /languages
Learn more about Direktt at https://direktt.com

Direktt plugin uses a number of third party libraries. They include:

* Vue.js - https://github.com/vuejs/
* Vuetify - https://github.com/vuetifyjs/vuetify
* php-jwt - https://github.com/firebase/php-jwt
* Html2Text - https://github.com/mtibben/html2text

== Installation ==

1. Install the plugin using the wp-admin's Plugins screen or upload the plugin files manually to the /wp-content/plugins/direktt folder on your web server.
2. Activate the plugin using the Plugins screen in WordPress' wp-admin.
3. Login into the [Direktt management console](https://direktt.com/wp-content/direkttweb/), create the channel and copy your API Key on Channel Info Screen.
Add the API key to your Direktt Settings on Direktt > Settings screen in wp-admin to enable your WordPress to make API calls and receive events from Direktt platform.  

You can find the detailed Quick Start Guide [HERE](https://direktt.com/quick-start-guide/)

== Frequently Asked Questions ==

= What is Direktt? =
Direktt is a mobile customer care platform tightly integrated with WordPress. Visit [https://direktt.com](https://direktt.com) for more details.

= Where do I get API credentials or integration details? =
Login into the [Direktt management console](https://direktt.com/wp-content/direkttweb/), create the channel and copy your API Key on Channel Info Screen.

= I am Getting Your site url in your WordPress' General Settings not set to use https protocol error. How to resolve this? =
Direktt integration requires that your site works under https protocol. If you are not sure what that means, contact your hosting company for the instructions on how to setup https on your site.

= Where can I find the source code of front-end js components  =
You can find the source code of all Direktt plugin components in our [GitHib repository](https://github.com/direktt/direktt-plugin)

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