# Direktt Developer's Guide: Basic Concepts & Platform Overview

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

# Direktt Users and Authentication in WordPress

Direktt offers seamless integration with WordPress, letting you securely authenticate, manage, and pair your users across mobile and website experiences. This guide covers how Direktt users are represented in WordPress, the authentication flow, user data structure, essential helper methods, and developer integration tips.

## User Roles in Direktt

Within each channel, Direktt defines **two roles**:

- **Channel Administrator**
  - The creator and admin of the channel (one per channel)
- **Channel Subscribers** (Direktt Users)
  - All other members of that channel

### Feature Access by Role

| Channel Role       | Features & Capabilities                     |
|--------------------|---------------------------------------------|
| **Subscriber**     | 1-on-1 chat with admin<br>Access digital services (e.g., appointments, loyalty) |
| **Channel Admin**  | All Subscriber actions<br>Bulk messaging<br>Access and manage user profiles<br>Admin dashboard and tools in Direktt app<br>Admin-only notifications |

## How Direktt Users Are Managed in WordPress

Every Direktt subscriber - admin or user - is stored as a **Custom Post Type** (CPT) post in WordPress:  

- **CPT Key:** `direkttusers`
- Shown in: **Direktt > Direktt Users** (in wp-admin)

### User Creation Flow

1. A user subscribes to your channel in the Direktt mobile app.
2. WordPress receives a webhook/REST API call and creates a corresponding **Direktt User** post.

### Direktt User Meta Fields

Each `direkttusers` post stores:

| Meta Key                        | Description |
|----------------------------------|-------------|
| **direktt_user_id**              | Unique Subscription ID (channel-specific; *not* the WP post ID). |
| **direktt_admin_subscription**   | `true` if channel admin, `false`/unset if regular user. |
| **direktt_marketing_consent_status** | User's opt-in/out for marketing. |
| **direktt_membership_id**        | Membership ID for physical card or QR. |
| **direktt_avatar_url**           | URL of profile avatar. |
| Post Title                       | Display name |
| Post Content                     | User notes (editable by admin in-app) |

Your custom integrations can add further meta fields as needed.

### WordPress User Pairing Meta

If a WP User is **paired** with a Direktt User, the following meta exist on the WP User:

| Meta Key                | Description |
|-------------------------|-------------|
| **direktt_user_id**     | Post ID of related Direktt User (`direkttusers` CPT) |
| **direktt_test_user_id**| (For testing only) Post ID of Direktt Test User - see "Testing Direktt Pages..." in User Guide. |

## Session Authentication & `$direktt_user` Global Variable

Whenever a user visits your WordPress site via the Direktt mobile app (through Services, chat buttons, or QR codes), their **Direktt Subscription ID** is automatically passed and authenticated.

**Result:**  
A global PHP variable - `$direktt_user` - is set for that session, containing all relevant Direktt user data.

**Also:**  
If a logged-in WordPress user is paired to a Direktt User, `$direktt_user` is set for their web session as well.

### Structure of `$direktt_user`

The array structure closely matches the Direktt User CPT:

- `ID` (int): Direktt User post ID
- `direktt_display_name` (string)
- `direktt_avatar_url` (string)
- `direktt_user_id` (string, Subscription ID)
- `direktt_admin_subscription` (bool)
- `direktt_membership_id` (string)
- `direktt_marketing_consent_status` (bool)
- `direktt_user_categories` (array of category names)
- `direktt_user_tags` (array of tag names)
- `direktt_notes` (string)

## Working With Direktt Users: API Reference

You rarely access `$direktt_user` directly - use these helper functions from the `Direktt_User` class:

### Get the Current Direktt User

```php
Direktt_User::direktt_get_current_user()
```
- **Returns:** array (user data; structure as above) if set false otherwise

### Check for Channel Admin Status

```php
Direktt_User::is_direktt_admin()
```

- **Returns:** true if current $direktt_user is a channel admin false otherwise

### Lookup Users

- **By Direktt User Post ID**

  ```php
  Direktt_User::get_user_by_post_id($direktt_user_post_id)
  ```

  -  **Returns:** Associative array of user data, or false if not found

- **By Subscription ID**

Direktt_User::get_user_by_subscription_id($direktt_user_id)
Returns: Associative array of user data, or false if not found
By Membership ID

Direktt_User::get_user_by_membership_id($direktt_membership_id)
Returns: Associative array of user data, or false if not found

### List All Direktt Users

Direktt_User::get_users($include_admin = false)
Parameters:
$include_admin (bool): If true, includes channel admin in list; if false, returns only regular subscribers.
Returns: array of users, each:
value: post ID
title: display name

### Pairing and Cross-User Lookups

Get Direktt User Related to a WP User

Direktt_User::get_related_user($wp_user_id)
Parameters: WordPress User ID (int)
Returns: Direktt user array (see above), or false if not paired
Get WP User ID Related to a Direktt User

Direktt_User::get_related_wp_user_id($direktt_user)
Parameters: Direktt user array (as above)
Returns: WP User ID (int) if paired, else false

## Pairing WordPress Users and Direktt Users

Pairing allows you to bind a logged-in WP User with their Direktt app user profile—enabling unified messaging, services, automation, and cross-channel tracking without exposing private data.

Pairing Use Cases
Send reminders or order updates via Direktt after online actions
Deliver loyalty or promotional messages following e-commerce actions
Sync user access and content between website and mobile app
How Pairing Works
1. Text-based Pairing Code
Each WP User is assigned a code (direktt_user_pair_code meta)—viewable and regeneratable in their WP user profile.

Admin can display this using the [direktt_pairing_code] shortcode in pages or theme templates:

echo do_shortcode('[direktt_pairing_code]');
User sends this code in the Direktt app’s chat. The backend pairs the WP User with the Direktt User and confirms via customizable message templates.

2. Pairing QR Code
Display a scannable QR code ([direktt_qr_pairing_code]) anywhere on your site:

echo do_shortcode('[direktt_qr_pairing_code]');
User scans with Direktt app; pairing is handled instantly.

What the User Sees
If already paired: Message states association exists (shows related Direktt user).
If not paired: Shows pairing code or QR for the current user.
Admin Management
Pairing can be managed and removed in either WP user or Direktt user admin screens
All pairing activity is stored as user/post meta (extendable by your plugins)
Working With Direktt User Taxonomies (Categories & Tags)
Taxonomies are the key to segmentation, targeting, and access control. Each Direktt User supports two built-in taxonomies:

Direktt User Categories (direkttusercategories):
Examples: "VIP", "Frequent Shopper", "Beta Tester"
Direktt User Tags (direkttusertags):
Fine-grained labels: "2024 Contest", "Early Bird", "Coupon User"
Use Cases:

Target bulk messages to a specific group
Gate a digital service page or offer to "VIP" users
Trigger automations based on tag/category assignment
Taxonomy Helper Methods
Get All Categories
Direktt_User::get_all_user_categories()
Returns: Array of [ 'value' => term_id, 'name' => term name ]
Get User's Categories
Direktt_User::get_user_categories($direktt_user_post_id)
Returns: Array of category term IDs (integers)
Get All Tags
Direktt_User::get_all_user_tags()
Returns: Array of [ 'value' => term_id, 'name' => tag name ]
Get User's Tags
Direktt_User::get_user_tags($direktt_user_post_id)
Returns: Array of tag term IDs (integers)
Check If User Has Given Categories/Tags
Direktt_User::has_direktt_taxonomies($direktt_user, $categories, $tags)
Parameters:
$direktt_user: user data array (as above)
$categories: array of category slugs (strings)
$tags: array of tag slugs (strings)
Returns: true if user matches any provided category or tag, else false
Developer Tips
Use [direktt_pairing_code] and [direktt_qr_pairing_code] on logged-in only pages so the correct pairing code is shown per WP user.
Check and manage all pairings in Direktt > Direktt Users and regular WP user admin screens.
All pairing and taxonomy activity is logged as meta — perfect for automation and advanced integrations.
Taxonomies are your friend: Use for segmentation, feature gating, OR custom analytics.
Pro Tip:
You can freely extend all user meta, pairing flows, and hooks for advanced automation—see the [Developer API Reference] and example plugin snippets for inspiration.