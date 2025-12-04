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

  ```php
  Direktt_User::get_user_by_subscription_id($direktt_user_id)
  ```
  -  **Returns**: Associative array of user data, or false if not found

- **By Membership ID**

  ```php
  Direktt_User::get_user_by_membership_id($direktt_membership_id)
  ```
  -  **Returns**: Associative array of user data, or false if not found

### List All Direktt Users

```php
Direktt_User::get_users($include_admin = false)
```
- **Parameters:**
  - `$include_admin`(bool): If `true`, includes channel admin in list; if `false`, returns only regular subscribers.

- **Returns:** array of users, each:
  - `value`: post ID
  - `title`: display name

### Pairing and Cross-User Lookups

- **Get Direktt User Related to a WP User**

  ```php
  Direktt_User::get_related_user($wp_user_id)
  ```
  -  **Parameters:** WordPress User ID (int)
  -  **Returns:** Direktt user array (see above), or false if not paired

- **Get WP User ID Related to a Direktt User**

  ```php
  Direktt_User::get_related_wp_user_id($direktt_user)
  ```
  - **Parameters:** Direktt user array (as above)
  - **Returns:** WP User ID (int) if paired, else false

## Pairing WordPress Users and Direktt Users

**Pairing** allows you to bind a logged-in WP User with their Direktt app user profile—enabling unified messaging, services, automation, and cross-channel tracking without exposing private data.

### Pairing Use Cases

- Send reminders or order updates via Direktt after online actions
- Deliver loyalty or promotional messages following e-commerce actions
- Sync user access and content between website and mobile app

### How Pairing Works

**Text-based Pairing Code**
- Each WP User is assigned a code (direktt_user_pair_code meta)—viewable and regeneratable in their WP user profile.
- Admin can display this using the `[direktt_pairing_code]` shortcode in pages or theme templates:

  ```php
  echo do_shortcode('[direktt_pairing_code]');
  ```
- User sends this code in the Direktt app’s chat. The backend pairs the WP User with the Direktt User and confirms via customizable message templates.

**Pairing QR Code**
- Display a scannable QR code (`[direktt_qr_pairing_code]`) anywhere on your site:

  ```php
  echo do_shortcode('[direktt_qr_pairing_code]');
  ```
- User scans with Direktt app; pairing is handled instantly.

**What the User Sees**
- If already paired: Message states association exists (shows related Direktt user).
- If not paired: Shows pairing code or QR for the current user.

**Admin Management**
- Pairing can be managed and removed in either WP user or Direktt user admin screens
- All pairing activity is stored as user/post meta (extendable by your plugins)

## Working With Direktt User Taxonomies (Categories & Tags)

Taxonomies are the key to segmentation, targeting, and access control. Each Direktt User supports two built-in taxonomies:

- **Direktt User Categories** (`direkttusercategories`):
  - Examples: "VIP", "Frequent Shopper", "Beta Tester"
- **Direktt User Tags** (`direkttusertags`):
  - Fine-grained labels: "2024 Contest", "Early Bird", "Coupon User"

**Use Cases:**

- Target bulk messages to a specific group
- Gate a digital service page or offer to "VIP" users
- Trigger automations based on tag/category assignment

### Taxonomy Helper Methods

**Get All Categories**

```php
Direktt_User::get_all_user_categories()
```
- **Returns:** Array of `[ 'value' => term_id, 'name' => term name ]`

**Get User's Categories**

```php
Direktt_User::get_user_categories($direktt_user_post_id)
```
- **Returns:** Array of category term IDs (integers)

**Get All Tags**

```php
Direktt_User::get_all_user_tags()
```
- **Returns:** Array of `[ 'value' => term_id, 'name' => tag name ]`

**Get User's Tags**

```php
Direktt_User::get_user_tags($direktt_user_post_id)
```
- **Returns:** Array of tag term IDs (integers)

**Check If User Has Given Categories/Tags**

```php
Direktt_User::has_direktt_taxonomies($direktt_user, $categories, $tags)
```
- **Parameters:**
  - `$direktt_user:` user data array (as above)
  - `$categories:` array of category slugs (strings)
  - `$tags:` array of tag slugs (strings)
- **Returns:** `true` if user matches any provided category or tag, else `false`

### Developer Tips
- Use `[direktt_pairing_code]` and `[direktt_qr_pairing_code]` on **logged-in only** pages so the correct pairing code is shown per WP user.
- Check and manage all pairings in **Direktt > Direktt Users** and regular WP user admin screens.
- All pairing and taxonomy activity is logged as meta — perfect for automation and advanced integrations.
- Taxonomies are your friend: Use for segmentation, feature gating, OR custom analytics.

> **Pro Tip:**
> You can freely extend all user meta, pairing flows, and hooks for advanced automation—see the Developer API Reference and example plugin snippets for inspiration.

# Direktt Authorization & Access Control

Direktt lets you lock down parts of your WordPress site so they are only accessible to authenticated Direktt users (subscribers or channel admins), optionally filtered by Direktt taxonomies such as user categories or tags.

You can control access to:

- Static pages (front-end content).
- AJAX endpoints (admin-ajax.php handlers).
- Custom REST API endpoints.

This section explains:

- How page-level protection works (meta keys and flow).
- How to test as different Direktt users.
- How to use helper methods in your own code.
- How to secure AJAX and REST endpoints with practical examples.
- How to use localized JS data (direktt_public) on the front end.

## Protecting Pages with Direktt Access Rules

Direktt uses four page-level meta keys to determine whether a page is restricted to Direktt users and, if so, who may access it:

1. `direktt_custom_box`

  - When set to 1, the page is restricted to any authenticated Direktt user (subscriber or admin).

2. `direktt_custom_admin_box`

  - When set to 1, the page is restricted to the Direktt channel admin only.

3. `direktt_user_categories`

  - Stores an array of Direktt User Category term IDs.
  - Only Direktt users who belong to one of these categories may access the page.

4. `direktt_user_tags`

  - Stores an array of Direktt User Tag term IDs.
  - Only Direktt users who have at least one of these tags may access the page.

In practice, you do not set these meta values directly. Instead, you use the Direktt meta box on the page edit screen (wp-admin) to configure:

- “Allow access to Direktt users”
- “Allow access to Direktt admin”
- Allowed User Categories
- Allowed User Tags

The Direktt plugin writes the appropriate meta and enforces the rules automatically.

### Examples of Page Access Settings

Here are some common configurations using the Direktt meta box:

| Use Case                | Meta Box Settings |
|-------------------------|-------------|
| **Page for all Direktt users**     | Check “Allow access to Direktt users” |
| **Admin-only tools page**          | Check “Allow access to Direktt admin” |
| **VIP-only offers page**           | Select category “vip” in User Categories (leave others unchecked) |
| **Sales team dashboard**           | Select category “sales-representatives” in User Categories |
| **Limited-time campaign page for a specific tag only**     | Select tag “promo-2024” in User Tags |
| **Public, not Direktt-restricted** | Leave all Direktt options unchecked |

You can combine these; for example, a page accessible to both admin and “vip” category users.

## Testing Restricted Pages as a Specific Direktt User

During development and QA, repeatedly logging in through the Direktt mobile app is not practical. Instead, you can simulate a Direktt user in your browser:

1. Go to **Users > Profile** (or open any WP user’s profile).
2. In the **Direktt User Properties** section, set:
  - `Post Id of Test Direktt User` to the desired Direktt user post ID (from Direktt > Direktt Users).
3. Save the profile.

While this field is set:

- Any Direktt-restricted page you open while logged in as that WP user behaves as if it was opened from the Direktt mobile app by the chosen Direktt user.
- `$direktt_user` is populated accordingly.
- You can test access to:
  - Protected pages.
  - AJAX endpoints.
  - REST endpoints.

This is ideal for testing custom digital services and admin tools directly in your browser.

## Key Helper Methods in Direktt_Public
```php
Direktt_Public::is_restricted( $post )`
```

**Purpose:**
Determine if a given post/page is restricted by any Direktt rule.

- **Parameters:**
  - `$post` (WP_Post): The post object to check.
- **Returns:**
  - `true` if the post is restricted to:
    - Direktt users, or
    - Direktt admin, or
    - Any Direktt user categories, or
    - Any Direktt user tags.
  - `false` otherwise.

Internally, this checks:

- `direktt_custom_box`
- `direktt_custom_admin_box`
- `direktt_user_categories`
- `direktt_user_tags`

```php
Direktt_Public::not_auth_redirect()
```

**Purpose:**
Handle unauthorized access for Direktt-protected content.

- Clears the Direktt authentication cookie.
- Sets $direktt_user = false.
- If an unauthorized redirect URL is configured:
  - Sends a wp_safe_redirect() to that URL.
- Otherwise:
  - Sends an HTTP 403 (Unauthorized) and exits.

- **Parameters:**  
  - None

- **Returns:**
  - `void` (execution usually stops via redirect or exit)

```php
Direktt_Public::direktt_ajax_check_user( $post )
```

**Purpose:**
Server-side permission check for AJAX handlers. Reuses the same access logic as page rendering.

- **Parameters:**
  - `$post` (WP_Post): Post object that defines the Direktt access rules.

- **Returns:**
  - `true` if:
    - The post is not restricted, OR
    - The current Direktt user (`$direktt_user`) satisfies the access rules.
  - `false` if the post is restricted and the user is not authorized.
  - `false` (or early return) if $post is invalid.

Use this in your AJAX handlers to ensure only authorized Direktt users can run actions tied to a given page.

## Best Practices for Direktt Authorization

To keep your access control consistent and secure:

- **Always enforce on the server:** Do not rely on front-end checks alone. Use:
  - `Direktt_Public::direktt_ajax_check_user( $post )` for AJAX.
  - `permission_callback` in REST routes, using the same internal logic.
- **Leverage taxonomies for segmentation:** Use Direktt User Categories/Tags instead of custom flags wherever possible for targeted access and messaging.
- **Do not trust query parameters blindly:** Validate and sanitize IDs (e.g., post_id) and always re-check authorization for that resource.
- **Use nonces in AJAX:** Protect against CSRF using wp_create_nonce() + wp_verify_nonce().
- Reuse `$direktt_user helpers:` Use `Direktt_User::direktt_get_current_user()` and associated helpers to inspect the current Direktt user.
- **Keep logic centralized:** If you replicate similar checks across multiple shortcodes or endpoints, wrap them in your own helper function.

### Example: Role/Segment-Based Shortcode Content

You can personalize content on a Direktt-protected page by inspecting the current Direktt user’s role and taxonomies inside a shortcode.

The shortcode below:

- Accepts optional `categories` and `tags` attributes (comma-separated slugs).
- Shows:
  - “Channel Admin” if the current Direktt user is the channel admin;
  - “Sales Representative” if the user belongs to the category `sales-representatives`;
  - Otherwise “Channel Subscriber” for any other matched Direktt user;
  - Nothing if:
    - There is no valid Direktt user, or
    - They do not match required categories/tags.

Register this shortcode in your plugin or theme:

```php
function direktt_sample_shortcode( $atts ) {
  // Merge attributes with defaults (both attributes are comma-separated slugs).
  $atts = shortcode_atts(
      array(
          'categories' => '',
          'tags'       => '',
      ),
      $atts,
      'direktt_sample_shortcode'
  );

  // Parse categories/tags attributes into arrays, trim whitespace, ignore empty.
  $categories = array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );
  $tags       = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );

  global $direktt_user;

  ob_start();

  // Retrieve the Direktt user post using their subscription/user ID.
  $direktt_user_post = isset( $direktt_user['direktt_user_id'] )
      ? Direktt_User::get_user_by_subscription_id( $direktt_user['direktt_user_id'] )
      : false;

  /*
  * Show content only if:
  * - The Direktt user exists AND
  * - (No categories/tags were specified [show to any Direktt user]
  *    OR the user matches the specified categories/tags via custom taxonomy
  *    OR the user is a Direktt admin [always show for admin])
  */
  if (
      $direktt_user_post
      && (
          ( ! $categories && ! $tags )
          || Direktt_User::has_direktt_taxonomies( $direktt_user, $categories, $tags )
          || Direktt_User::is_direktt_admin()
      )
  ) {
      if ( Direktt_User::is_direktt_admin() ) {
          // If the user is admin, show "Channel Admin".
          echo '<p>Channel Admin</p>';

      } elseif ( Direktt_User::has_direktt_taxonomies( $direktt_user, array( 'sales-representatives' ), array() ) ) {
          // If user belongs to category with slug "sales-representatives".
          echo '<p>Sales Representative</p>';

      } else {
          // All other matched Direktt users.
          echo '<p>Channel Subscriber</p>';
      }
  }

  // Users without correct role/taxonomy or not Direktt users see nothing.
  return ob_get_clean();

}

add_shortcode( 'direktt_sample_shortcode', 'direktt_sample_shortcode' );
```

Place `[direktt_sample_shortcode]` on a Direktt-restricted page and configure allowed categories/tags via the meta box to fine-tune who sees what.

## Securing AJAX Endpoints for Direktt Users

When you implement AJAX on Direktt-protected pages, you must ensure:

1. **The UI is only shown to authorized Direktt users**  
  (using `$direktt_user` and taxonomies in your shortcode or template).

2. **The server-side handler re-checks authorization**  
  with `Direktt_Public::direktt_ajax_check_user( $post )`, using a trusted `post_id`.

3. **CSRF protection is in place**  
  with WordPress nonces.

### Example: AJAX Button With Direktt Authorization

The example below:

- Provides a `[direktt_sample_ajax]` shortcode that renders a “Click me” button only for qualified Direktt users.
- Uses Fetch API to hit an `admin-ajax.php` handler.
- Server:
  - Validates `post_id`.
  - Checks Direktt access via `direktt_ajax_check_user()`.
  - Verifies a nonce.
  - Returns the user’s `subscriptionId` if authorized.

Shortcode implementation:

```php
function direktt_sample_ajax( $atts ) {

  // Merge passed shortcode attributes with defaults.
  $atts = shortcode_atts(
      array(
          'categories' => '',
          'tags'       => '',
      ),
      $atts,
      'direktt_sample_ajax'
  );

  // Parse 'categories' and 'tags' attributes into trimmed arrays, filter out empty values.
  $categories = array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );
  $tags       = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );

  global $direktt_user;

  ob_start();

  // Retrieve user post by subscription ID if available.
  $direktt_user_post = isset( $direktt_user['direktt_user_id'] )
      ? Direktt_User::get_user_by_subscription_id( $direktt_user['direktt_user_id'] )
      : false;

  // Check user eligibility:
  //  1. User must have a valid direktt_user_post.
  //  2. If categories/tags are specified, user must have those taxonomies, or is a Direktt admin.
  if (
      $direktt_user_post
      && (
          ( ! $categories && ! $tags )
          || Direktt_User::has_direktt_taxonomies( $direktt_user, $categories, $tags )
          || Direktt_User::is_direktt_admin()
      )
  ) {

      // Generate a WP nonce for security, to validate AJAX requests.
      $nonce = wp_create_nonce( 'direktt_btnclick_nonce' );
      ?>

      <button id="btn">Click me</button>
      <script type="text/javascript">
          document.getElementById('btn').addEventListener('click', function() {
              var data = new FormData();
              data.append('action', 'direktt_btnclick'); // AJAX action hook name
              data.append('nonce', '<?php echo esc_js( $nonce ); ?>'); // Include the generated nonce for validation
              data.append('post_id', direktt_public.direktt_post_id); // Pass the relevant post ID

              // Use Fetch API to make an AJAX request to admin-ajax.php
              fetch(direktt_public.direktt_ajax_url, {
                  method: 'POST',
                  credentials: 'same-origin',
                  body: data
              })
              .then(response => response.json())
              .then(result => {
                  console.log('Server says: ' + result.message);
              });
          });
      </script>

      <?php
  }

  return ob_get_clean();
}
```

AJAX handler:

```php
function direktt_btnclick_handler() {

  // Ensure 'post_id' is present in POST for validation.
  if ( ! isset( $_POST['post_id'] ) ) {
      wp_send_json( array( 'status' => 'post_id_failed' ), 400 );
  }

  $post_id = intval( $_POST['post_id'] );
  $post    = get_post( $post_id );

  // Validate that post exists and the current user can perform the action.
  if ( $post && Direktt_Public::direktt_ajax_check_user( $post ) ) {

      // Verify nonce for security against CSRF attacks.
      if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'direktt_btnclick_nonce' ) ) {
          wp_send_json( array( 'status' => 'nonce_failed' ), 401 );
      }

      $direktt_user = Direktt_User::direktt_get_current_user();

      // Return subscription ID.
      wp_send_json(
          array(
              'message' => 'Subscription Id: ' . $direktt_user['direktt_user_id'],
          ),
          200
      );
  } else {

      // User not authorized or post not found.
      wp_send_json( array( 'status' => 'non_authorized' ), 401 );
  }
}

add_shortcode( 'direktt_sample_ajax', 'direktt_sample_ajax' );
add_action( 'wp_ajax_direktt_btnclick', 'direktt_btnclick_handler' );
add_action( 'wp_ajax_nopriv_direktt_btnclick', 'direktt_btnclick_handler' );
```

This pattern ensures:

- The UI only appears for eligible Direktt users.
- The server enforces the same access rules.
- CSRF is prevented via nonces.

## Securing Custom REST API Endpoints

All core Direktt REST routes (under `direktt/v1`) are already secured by the plugin and the Direktt platform. The platform injects and validates tokens, so `$direktt_user` is set correctly inside REST handlers.

When registering your own custom endpoints, you can:

- Use the same namespace (`direktt/v1`) or define another.
- Rely on the global `$direktt_user` inside callbacks.
- Enforce access rules `in a permission_callback` using:
  - `Direktt_User::direktt_get_current_user()`
  - `Direktt_Public::direktt_ajax_check_user( $post )` (for page-related logic)

### When `$direktt_user` Is Available in REST

`$direktt_user` is automatically set when:

- The call includes a valid Direktt token (from in-app Services, QR flows, etc.).
- The request is made from a Direktt-authenticated browser session (auth cookie present).
- A paired or test Direktt user applies to the current WordPress user.

You should still check that a valid page/post and user relationship exists before performing actions.

### Example: REST Button With Direktt Authorization

The following example:

- Provides `[direktt_sample_rest]` shortcode that renders a button for eligible Direktt users.
- On click, sends a JSON POST to a custom REST endpoint (`direktt/v1/sampleRest/`).
- Permission callback validates:
  - `post_id` from JSON body.
  - That the current Direktt user can access that post (via `direktt_ajax_check_user()`).
- REST callback returns the user’s subscription ID.

Shortcode:

```php
function direktt_sample_rest( $atts ) {

  // Merge supplied shortcode attributes with defaults.
  $atts = shortcode_atts(
      array(
          'categories' => '',
          'tags'       => '',
      ),
      $atts,
      'direktt_sample_rest'
  );

  // Convert attribute strings to trimmed, non-empty arrays.
  $categories = array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );
  $tags       = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );

  global $direktt_user;

  ob_start();

  // Get user post object if direktt_user_id is present in global $direktt_user.
  $direktt_user_post = isset( $direktt_user['direktt_user_id'] )
      ? Direktt_User::get_user_by_subscription_id( $direktt_user['direktt_user_id'] )
      : false;

  // Check access rules: must have a valid user, matching taxonomies, or be admin.
  if (
      $direktt_user_post
      && (
          ( ! $categories && ! $tags )
          || Direktt_User::has_direktt_taxonomies( $direktt_user, $categories, $tags )
          || Direktt_User::is_direktt_admin()
      )
  ) {
      ?>
      <button id="btnrest">Click me</button>
      <script type="text/javascript">
          document.getElementById('btnrest').addEventListener('click', function() {
              // Prepare payload with post_id taken from direktt_public (should be validated server-side).
              var data = JSON.stringify({
                  post_id: direktt_public.direktt_post_id
              });

              fetch(direktt_public.direktt_rest_base + 'sampleRest/', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-WP-Nonce': direktt_public.direktt_wp_rest_nonce // WP nonce for REST auth
                  },
                  credentials: 'same-origin',
                  body: data
              })
              .then(response => response.json())
              .then(result => {
                  console.log('Server says: ' + result.message);
              });
          });
      </script>
      <?php
  }

  return ob_get_clean();
}
```

REST route registration:

```php
function register_direktt_sample_rest() {

  register_rest_route(
      'direktt/v1',
      '/sampleRest/',
      array(
          'methods'             => 'POST',
          'callback'            => 'direktt_btnclick_rest_handler', // Handles the actual request.
          'args'                => array(),
          'permission_callback' => 'api_validate_sample_handler',  // Checks user permissions before callback runs.
      )
  );
}
```

Permission handler:

```php
function api_validate_sample_handler( WP_REST_Request $request ) {

  $parameters = json_decode( $request->get_body(), true );

  if ( is_array( $parameters ) && array_key_exists( 'post_id', $parameters ) ) {

      // For numeric IDs, intval is usually more appropriate.
      $post_id = intval( $parameters['post_id'] );
      $post    = get_post( $post_id );

      if ( $post && Direktt_Public::direktt_ajax_check_user( $post ) ) {
          return true;
      }
  }

  return false;
}
```

REST callback:

```php
function direktt_btnclick_rest_handler( WP_REST_Request $request ) {

  $direktt_user = Direktt_User::direktt_get_current_user();

  wp_send_json(
      array(
          'message' => 'Subscription Id: ' . $direktt_user['direktt_user_id'],
      ),
      200
  );
}
```

Hook everything up:

```php
add_shortcode( 'direktt_sample_rest', 'direktt_sample_rest' );
add_action( 'rest_api_init', 'register_direktt_sample_rest' );
```

This mirrors the AJAX approach but uses standard REST patterns:

- `permission_callback` for authorization.
- JSON request body.
- WordPress REST nonce in `X-WP-Nonce` header.
- `wp_send_json()` for responses.

By following these patterns and using the provided helper methods, you can build secure, role-aware digital services and tools that respect Direktt’s authorization model across pages, AJAX, and REST APIs.