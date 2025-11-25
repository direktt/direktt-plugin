=== Direktt ===
Contributors: direkttwp
Tags: mobile app, customer care, messaging, push notifications, mobile integration
Requires at least: 5.4
Tested up to: 6.8
Stable tag: 1.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WordPress site to the Direktt mobile customer care platform for instant messaging and real-time user engagement.

== Description ==
Direktt helps you seamlessly integrate your WordPress website with the [Direktt mobile customer care platform](https://direktt.com/). With this plugin, you can:

- Access a wp-admin interface for Direktt settings, user management, and bulk messaging.
- Manage messaging templates and send announcements to your Direktt channel subscribers.
- View and manage user profiles, notes, and message history.
- Receive and act on user events such as subscriptions, messages, and activity from the Direktt mobile app.

For developers, Direktt provides a framework to:

- Add custom hooks and actions for Direktt events.
- Access and authorize users or events from the Direktt app.
- Integrate with the Direktt panel or user profiles.
- Send messages to app users programmatically.
- Implement powerful Direktt automations

== Data & External Service Disclosure ==

This plugin connects your website to the Direktt platform using secure API calls. No user tracking or personal data is automatically sent from your site to Direktt.

**When certain user actions occur (such as subscribing or messaging), the Direktt platform sends the following minimal user data to your WordPress site via API:**

- Display name
- Avatar
- Channel-specific subscription ID

No personally identifiable or trackable data (such as email addresses) is shared with your or other channels or with any third-party platforms. All API calls are authenticated with your Direktt API key.

**Plugin API Endpoints Used:**
The plugin communicates only during specific actions and uses the following Direktt API endpoints:

1. https://getDataForChannel-lnkonwpiwa-uc.a.run.app
(Called when you view the Direktt dashboard in wp-admin to fetch current channel status)

2. https://activatechannel-lnkonwpiwa-uc.a.run.app
(Called on channel activation)

3. https://getsubscriptionsforchannel-lnkonwpiwa-uc.a.run.app
(Called when channel user synchronization is initiated in the Direktt wp-admin settings)

4. https://sendbulkmessages-lnkonwpiwa-uc.a.run.app
(Called when sending a message to channel subscribers)

5. https://sendadminmessage-lnkonwpiwa-uc.a.run.app
(Called when sending a message to the channel admin)

6. https://updateMessage-lnkonwpiwa-uc.a.run.app
(Called when updating a sent message)

**No calls are made automatically or in the background without user/admin action.**

For more details, please see:

- Direktt Privacy Policy [HERE](https://direktt.com/privacy-policy/)
- Direktt Terms of Service [HERE](https://direktt.com/terms-of-service/)  

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

1. Upload the plugin to `/wp-content/plugins/direktt` or install via the WordPress Plugins menu.
2. Activate the plugin from the Plugins screen.
3. Get your API key from the [Direktt management console](https://direktt.com/wp-content/direkttweb/) and add it in Direktt > Settings in WordPress.
4. Follow the [Quick Start Guide](https://direktt.com/quick-start-guide/) for full setup instructions.

== Frequently Asked Questions ==

= What is Direktt? =
Direktt is a customer care platform integrated with WordPress. Learn more at [https://direktt.com](https://direktt.com).

= Where can I get API credentials? =
Login to the [Direktt management console](https://direktt.com/wp-content/direkttweb/) and copy your API key from the Channel Info screen.

= Why do I need HTTPS? =
Direktt requires your site to be served over HTTPS for security. Contact your hosting provider if you need help enabling HTTPS.

= Where is the source code for the front-end components? =
Find all plugin component code in our [GitHub repository](https://github.com/direktt/direktt-plugin).

== Screenshots ==

1. Direktt Dashboard
2. Direktt Settings
3. Message Templates Builder

== Changelog ==

= 1.0 =
* Initial public release.

== Upgrade Notice ==

= 1.0 =
Initial release of Direktt for WordPress.