Welcome to the Direktt Developer's Guide! This section introduces the key concepts, architecture, and main extension points of the Direktt platform - so you can quickly understand how to build integrated, interactive customer experiences using WordPress.

## What is Direktt?

**Direktt** is a flexible customer communication platform that brings together private mobile chat, in-store engagement, and digital services - all managed through a seamless integration with your WordPress site.

With Direktt, your business can:
- **Chat directly** with customers via mobile.
- **Deliver membership/loyalty programs** using QR codes.
- **Run digital services** such as ticket validation, order pickups, offers, and more - either online or in-store.
- **Easily extend and customize** your workflows using familiar WordPress plugin techniques.

## Platform Architecture: The Three Core Parts

Direktt is made up of three main components:

### 1. Direktt Server & Web Admin Console
- **What it does:** Handles all core communication and channel management.
- **How you use it:** Access via the [Direktt Admin Console](https://direktt.com/wp-content/direkttweb/) to create channels, configure services, and manage integrations.

### 2. Direktt Mobile App
- **What it does:** Lets end-users subscribe to your channels, chat instantly, and access digital services - all from their mobile device.
- **Key features:** Join channels, message admins, scan QR codes for actions, access personal membership IDs, and use in-channel digital services.

### 3. Direktt WordPress Plugin (and Extensions)
- **What it does:** Brings Direktt’s advanced engagement tools and digital services into your WordPress site. Optional but highly recommended for full functionality.
- **How you use it:** Install on your WordPress site to:
    - Integrate with a Direktt Channel.
    - Build and serve custom member services, loyalty features, and automations.
    - Develop extensions for unique business workflows.

> **Note:** You can use basic chat functions without the WordPress plugin, but features like digital services, membership management, and advanced workflows require it.

## How Direktt Works: Key Concepts for Developers

### Channels & Channel Admins

- **What is a channel?**  
  A channel is your dedicated customer space - a private chat environment for your business and its audience.
- **How does admin work?**  
  - Each channel is managed by a single administrator (the creator).
  - One admin can manage multiple channels.
  - One WordPress site can be linked to one channel (for more channels, use separate WP installs).

### Mobile App Users & Subscriptions

- **Users (Subscribers):**
  - Any Direktt mobile app user can subscribe to one or more channels.
  - Subscribers can join, chat, and use digital services in any channel they've joined.
  - Admins can also subscribe to other channels as regular users or even their own for testing purposes.

### Chatting and Engagement

- **Direct chat:**  
  Admins and subscribers enjoy secure, 1:1 conversations within the app.
- **Bulk messaging:**  
  Admins can send bulk messages, announcements or promotions to all subscribers of their channel.

### Privacy by Design

- **What admins see:**  
  Only the subscriber’s display name, avatar, and channel-specific chat/thread contents.
- **What is never shared:**  
  No email addresses, phone numbers, or cross-channel tracking. Every subscription is unique to each channel to preserve privacy.

## Direktt Mobile App: What Subscribers & Admins Can Do

### For Subscribers

After joining your channel (by scanning QR, following a link, or searching channel handle):
- **Chat instantly** with your business (channel admin).
- **Scan QR codes** to unlock actions or deals.
- **Show their Membership ID** for in-person perks and services.
- **Access digital services** via the in-app Services menu.

### For Channel Administrators

From the Direktt app, admins can:
- **Initiate private chats** with any subscriber.
- **Send bulk messages** (announcements, updates).
- **Access subscriber profiles** - including assigning/removing categories & tags.
- **Use specialized admin tools:** Triggered from within subscriber profiles or as Admin Services in the app (e.g., loyalty tools, ticket checks, or other workflows).

> **Tip:** Admin services are configured via the Direktt Web Admin Console and can be linked to custom WordPress workflows or tools.

## Subscriber Profile & Admin Tools

When an admin (or authorized staff) scans a user's Membership ID QR code, the **subscriber's profile** appears in the app - allowing:

- **Quick assignments:** Add or remove categories/tags for segmentation.
- **Custom workflows:** Extend this area with WordPress-powered tools (e.g., validate tickets, top-up accounts, or issue rewards).

> Extend with custom logic for bespoke field updates, action buttons, or real-time operations using the Direktt WordPress API.

## QR Codes: Powerful Building Blocks

Use QR codes to create frictionless, smart interactions:
- **Generate** via the Direktt Web Console or within WordPress wp-admin.
- **Automate actions** when scanned - assign taxonomies to subscribers, add items to cart, validate passes, trigger loyalty updates, etc.
- **Tie QR actions** directly to your WordPress REST API for unlimited extensions.

**Examples:**
- Add a product to a shopping cart.
- Validate an event ticket.
- Redeem a personalized coupon.
- Trigger instant loyalty updates.

## Direktt WordPress Plugin: Developer Overview

The **Direktt WordPress plugin** enables you to deeply connect your WordPress site to the Direktt platform—bringing powerful communication, automation, and digital service features to both your website and your customers’ mobile devices.

Use this plugin as your gateway to create unified customer experiences, automate key processes, and unlock powerful integration points—all using familiar WordPress practices.

### Key Features: What You Can Do Out of the Box

The Direktt WordPress plugin ships with a rich set of features ready for immediate use:

**Full Subscriber Management**
- **Custom Post Type integration:** Every Direktt subscriber is represented as a custom post in WordPress.
- **User roles:** Direktt user role assigned to matched WP users.
- **View, organize, and edit** subscriber details directly from the WP dashboard.

**Segmentation & Targeting with Taxonomies**
- **Group subscribers** using built-in and custom taxonomies (Categories & Tags).
- **Segment your users** for messaging, digital service delivery, or analytics.

**Reusable Message Templates**
- **Compose message templates** with mixed content: text, media, files, and interactive elements.
- **Send templates** from the Direktt Mobile App or programmatically via API.

**Bulk Messaging**
- **Targeted bulk send:** Message all subscribers, or segment by category/tag.
- **Reach the right audience** instantly with announcements, promotions, or important updates.

**Content Authorization & Access Control**
- **Restrict access** to Direktt-powered pages, Ajax endpoints, and REST API endpoints.
- **Authorize by role and taxonomy**—lock content for members, admins, or specific groups.

**Direct Extension Support**
- **Plug in new features:** Enable Direktt Extensions for instant access to advanced digital services:
    - Appointment bookings
    - Real-time updates
    - Order status alerts
    - Loyalty/reward programs
    - WooCommerce integrations
    - Coupons, surveys, and more!

### Direktt WordPress Plugin API: Extend and Automate

The plugin exposes a robust developer API that lets you connect, automate, and extend Direktt with your site’s custom code and existing plugins.

#### Core API Capabilities

**Send Custom Messages**
- **Programmatically send** messages to subscribers from your own plugin or theme code, including:
    - Plain text
    - Images & media
    - Structured interactive messages (buttons for actions, carousels, polls, and more)

**React to Direktt & WP Events with Hooks**
- **On Subscriber Join:** Welcome new users, trigger onboarding workflows, or alert your admins.
- **On Incoming Message:** Parse and respond automatically (e.g., trigger AI replies, support bots, etc.).
- **On Custom Actions:** Respond when someone scans a QR code or interacts with a rich message.
- **WooCommerce Integration:** Handle events like abandoned carts, order status changes, or purchases, and notify shoppers via the mobile app.
- **Any WordPress Event:** Integrate with general WP hooks—trigger Direktt workflows on logins, form submissions, page actions, and more.

**Secure & Extend Digital Services**
- **Authenticate & authorize** Direktt users for your pages and custom front-end tools.
- **Extend user profiles:** Add custom admin panels or profile fields, visible within the mobile app.
- **Secure API Calls:** Restrict REST endpoints and Ajax calls to verified Direktt users.

**Integrate with Your Favorite Plugins**
- **Leverage existing plugins:** Run automations or add digital services based on what’s installed—no need to reinvent the wheel.

**Generate & Display QR Codes for Actions**
- **Trigger actions via scanning:** Create QR codes that subscribers scan via the Direktt app to:
    - Redeem offers
    - Check-in at locations
    - Validate tickets
    - Start custom workflows

- **Show codes:** Use on your site, printed in-store, or at events for frictionless engagement.

> **Tip:** Whether you’re building new membership perks, automating notifications, integrating e-commerce, or launching brand new digital services—Direktt plugin’s hooks, APIs, and extension points put all the power in your hands.