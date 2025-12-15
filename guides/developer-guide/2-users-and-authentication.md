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