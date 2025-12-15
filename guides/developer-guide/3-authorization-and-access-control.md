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