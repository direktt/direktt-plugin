Direktt Extensions are regular WordPress plugins that “plug into” existing Direktt user interfaces and APIs. With them you can:

- Add custom tools to the **Direktt user profile UI** (used by channel admins in the mobile app).
- Add custom **settings panels** under **Direktt > Settings** in wp-admin.
- Optionally add **front-end services** (pages/shortcodes) that are opened from:
  - **User Services** (subscriber-facing Services in the app).
  - **Admin Services** (admin-only Services in the app).
This section explains where you can integrate, how to do it, and how the **Direktt Extension Boilerplate** is structured as a starting point.

## Where You Can Plug Into the Direktt UI

There are four main integration points:

### User Services (subscriber-facing)

- Shown as buttons in the Services section of the channel in the Direktt mobile app.
- Implemented as URLs (usually WordPress pages with shortcodes) configured as **Service Links** in the Direktt Admin Console.

### Admin Services (admin-facing)

- Shown as buttons in the Admin Services section when the channel admin is in **Admin mode**.
- Also implemented as URLs (WordPress pages with shortcodes) configured as **Admin Links** in the Direktt Admin Console.

### Profile Tools – extension integration point

- Appear as extra “tools” / in the **Direktt user profile UI - left site drawer menu**, opened from the Direktt mobile app.
- Implemented via `Direktt_Profile::add_profile_tool()` inside the `direktt_setup_profile_tools` action.

### Direktt Settings Panels in wp-admin – extension integration point

- Appear under **Direktt > Settings** in wp-admin as extra settings screens for your extension.
- Implemented via `Direktt::add_settings_page()` inside the `direktt_setup_settings_pages` action.

The first two are covered in the User Guide (Service Links & Admin Links).
This section focuses on **Profile Tools and Settings Panels**, using the Direktt Extension Boilerplate as a concrete example.

## Extending the Direktt User Profile (Profile Tools)

The Direktt **user profile page** is a WordPress page rendered by the `[direktt_user_profile]` shortcode (see User Guide: Setting Up User Profile).

- Default URL: `https://your-domain/direktt-profile/`
- Or: a **custom URL** you configure in the Direktt Admin Console as **User Profile Url**.

From the Direktt mobile app (Admin mode):

- The admin opens a chat with a subscriber.
- Taps the **Profile** button.
- The app opens the profile page (using `[direktt_user_profile]`), including:
  - Basic user info.
  - Built‑in tools (Notes, Taxonomies, Messaging).
  - Any **custom profile tools** registered by your extensions.

### How Profile Tools Are Registered

Profile tools are registered via the static method:

```php
Direktt_Profile::add_profile_tool( $params );
```

You call this from a function hooked to:

```php
add_action( 'direktt_setup_profile_tools', 'your_extension_setup_profile_tool' );
```

The Direktt core calls `do_action( 'direktt_setup_profile_tools' );` on `init.`
Each extension listening to this action can register its own tools.

### `add_profile_tool()` Parameter Reference

`$params` is an associative array. Important keys:

| Key               | Type     | Required | Description                                                                                                                          |
|-------------------|----------|----------|--------------------------------------------------------------------------------------------------------------------------------------|
| `id`              | string   | Yes      | Unique ID for the tool (used in the `subpage` query var and as part of CSS classes).                                                |
| `label`           | string   | Yes      | Label shown in the profile tools menu in the user profile UI.                                                                       |
| `callback`        | callable | Yes      | PHP function/method that renders the tool’s content when this tool is active.                                                       |
| `categories`      | array    | No       | Array of Direktt User Category **slugs**. Tool is visible only if the current Direktt user has at least one of these categories or is admin. |
| `tags`            | array    | No       | Array of Direktt User Tag **slugs**. Tool is visible only if the current Direktt user has at least one of these tags or is admin.  |
| `priority`        | int      | No       | Sort order for this tool in the tools list (lower values appear first).                                                             |
| `cssEnqueueArray` | array    | No       | List of associative arrays describing CSS files to register and enqueue when this tool is active (compatible with `wp_register_style()`). |
| `jsEnqueueArray`  | array    | No       | List of associative arrays describing JS files to register and enqueue when this tool is active (compatible with `wp_register_script()`).  |

Each element of `cssEnqueueArray` / `jsEnqueueArray` is itself an associative array suitable to pass directly into `wp_register_style()` / `wp_register_script()` via splat (...$css_file / ...$js_file), for example:

```php
array(
  'handle' => 'my-extension-profile-css',
  'src'    => plugin_dir_url( __FILE__ ) . 'css/profile.css',
  'deps'   => array(),
  'ver'    => '1.0.0',
  'media'  => 'all',
)
array(
  'handle'    => 'my-extension-profile-js',
  'src'       => plugin_dir_url( __FILE__ ) . 'js/profile.js',
  'deps'      => array( 'jquery' ),
  'ver'       => '1.0.0',
  'in_footer' => true,
)
```

Direktt core:

- Registers these scripts/styles during `enqueue_profile_scripts()`.
- Enqueues them **only** when:
  - The current subpage matches your `id`, and
  - The user is allowed to see the tool (matching categories/tags or admin).

### Profile Tool Example 

```php

// Hook: register our profile tool when Direktt sets up profile tools.
add_action( 'direktt_setup_profile_tools', 'direktt_extension_boilerplate_setup_profile_tool' );

function direktt_extension_boilerplate_setup_profile_tool() {
    Direktt_Profile::add_profile_tool(
        array(
            "id"    => "direktt-boilerplate-profile-tool",
            "label" => __( 'Direktt Extension Tool', 'direktt-extension-boilerplate' ),

            // This function renders the content of the tool.
            "callback" => 'direktt_extension_boilerplate_render_profile_tool',

            // Restrict to specific user categories/tags if needed.
            "categories" => [], // e.g. array( 'vip', 'beta-testers' )
            "tags"       => [],

            // Display order; lower values appear first.
            "priority" => 1,

            // Optional: register & enqueue extra CSS when this tool is active.
            "cssEnqueueArray" => [
                array(
                    "handle" => "direktt-extension-boilerplate",
                    "src"    => plugin_dir_url( __FILE__ ) . 'css/direktt-extension-boilerplate.css',
                    // Optional: 'deps', 'ver', 'media' can be added here as usual.
                ),
            ],

            // Optional: register & enqueue extra JS when this tool is active.
            "jsEnqueueArray"  => [
                array(
                    "handle" => "direktt-extension-boilerplate-profile",
                    "src"    => plugin_dir_url( __FILE__ ) . 'js/direktt-extension-boilerplate-profile.js',
                    "deps"   => array( 'jquery', 'direktt-extension-boilerplate-public' ),
                    // Optional: 'ver', 'in_footer' can be added.
                ),
            ],
        )
    );
}

function direktt_extension_boilerplate_render_profile_tool() {
    echo __( '<h3 class="direktt-extension-boilerplate">Direktt Extension Boilerplate Profile Interface Goes Here.</h3>', 'direktt-extension-boilerplate' );
}

```

What happens in the UI:

- In the Direktt mobile app (Admin mode), when the admin opens a user profile:
  - A new tool entry “Direktt Extension Tool” appears in the left tools panel.
  - When tapped, the profile page reloads with `?subscriptionId=...&subpage=direktt-boilerplate-profile-tool`.
  - Direktt core:
    - Checks authorization (categories/tags/admin).
    - Calls `direktt_extension_boilerplate_render_profile_tool()`.
    - Enqueues your CSS/JS defined in `cssEnqueueArray` / `jsEnqueueArray`.

> **Tip:** Use categories/tags on the tool definition to expose profile tools only to specific **segments** (e.g. show a “Loyalty” tool only for `vip` users).

## Adding a Custom Settings Panel Under Direktt > Settings

The second extension point lets you add your own **settings panel** to the Direktt Settings area in wp-admin.

- Core Direktt plugin calls `do_action( 'direktt_setup_settings_pages' );`.
- Extensions hook into it and call `Direktt::add_settings_page()`.

### How Settings Pages Are Registered

Register via:

```php
Direktt::add_settings_page( $params );
```

From a function hooked to:

```php
add_action( 'direktt_setup_settings_pages', 'your_extension_setup_settings_pages' );
```

The Direktt core then:

- Renders the corresponding settings page inside **Direktt > Settings**.
- Loads your assets declared via `cssEnqueueArray / jsEnqueueArray` on that page only.

### `add_settings_page()` Parameter Reference

`$params` is an associative array. Important keys:

| Key               | Type     | Required | Description |
|-------------------|----------|----------|-------------|
| `id`              | string   | Yes      | Unique page ID (used internally for routing and element IDs). |
| `label`           | string   | Yes      | Settings tab label shown in the Direktt Settings UI. |
| `callback`        | callable | Yes      | PHP function/method that renders your settings screen HTML. |
| `priority`        | int      | No       | Sorting priority among settings tabs. Lower values appear first. |
| `cssEnqueueArray` | array    | No       | List of CSS files to register & enqueue on this settings page. Each entry is an associative array suitable for `wp_register_style()`. |
| `jsEnqueueArray`  | array    | No       | List of JS files to register & enqueue on this settings page. Each entry is an associative array suitable for `wp_register_script()`. |

Each `cssEnqueueArray` / `jsEnqueueArray` element is an associative array directly suitable for `wp_register_style()` / `wp_register_script()` via spread operator.

Example element:

```php
array(
  'handle' => 'my-extension-settings-css',
  'src'    => plugin_dir_url( __FILE__ ) . 'css/settings.css',
  // Optional: 'deps', 'ver', 'media'
)
array(
  'handle' => 'my-extension-settings-js',
  'src'    => plugin_dir_url( __FILE__ ) . 'js/settings.js',
  'deps'   => array( 'jquery' ),
  // Optional: 'ver', 'in_footer'
)
```

### Settings Page Example

```php
// Hook: register our settings page when Direktt sets up settings pages.
add_action( 'direktt_setup_settings_pages', 'direktt_extension_boilerplate_setup_settings_pages' );

function direktt_extension_boilerplate_setup_settings_pages() {
    Direktt::add_settings_page(
        array(
            "id"       => "extension-boilerplate",
            "label"    => __( 'Extension Boilerplate Settings', 'direktt-extension-boilerplate' ),

            // This function renders the settings panel HTML.
            "callback" => 'direktt_extension_boilerplate_render_settings',

            "priority" => 1,

            // Optional CSS assets for this settings screen only.
            "cssEnqueueArray" => [
                array(
                    "handle" => "direktt-extension-boilerplate",
                    "src"    => plugin_dir_url( __FILE__ ) . 'css/direktt-extension-boilerplate.css',
                ),
            ],

            // Optional JS assets for this settings screen only.
            "jsEnqueueArray"  => [
                array(
                    "handle" => "direktt-extension-boilerplate-settings",
                    "src"    => plugin_dir_url( __FILE__ ) . 'js/direktt-extension-boilerplate-settings.js',
                    "deps"   => array( 'jquery' ),
                ),
            ],
        )
    );
}

function direktt_extension_boilerplate_render_settings() {
    echo __( '<h3 class="direktt-extension-boilerplate">Direktt Extension Boilerplate Settings Go Here.</h3>', 'direktt-extension-boilerplate' );
}

```

**What happens in wp-admin:**

- In **wp-admin > Direktt > Settings**, a new tab/section labeled **“Extension Boilerplate Settings”** appears.
- When that tab is active:
  -  Direktt calls `direktt_extension_boilerplate_render_settings()`.
  -  CSS/JS from `cssEnqueueArray` / `jsEnqueueArray` are enqueued for that screen.
- This is where you build your extension’s configuration UI (options forms, toggles, etc.), usually storing values with `get_option()` / `update_option()` or via Ajax.

## Front-End Assets and `direktt_enqueue_public_scripts`

Often you’ll want front-end JS/CSS that run on **Direktt-protected pages** (for Services, profile tools, etc.).

The Direktt core triggers:

```php
do_action( 'direktt_enqueue_public_scripts' );
```

during its public scripts flow. Your extension can hook into this to register/enqueue assets only when there is a current Direktt user.

Example from the Boilerplate:

```php
add_action( 'init', 'direktt_extension_boilerplate_init' );

function direktt_extension_boilerplate_init() {
    // Attach our public enqueue function to Direktt's custom hook.
    add_action( 'direktt_enqueue_public_scripts', 'direktt_extension_boilerplate_enqueue_public_assets' );
}

function direktt_extension_boilerplate_enqueue_public_assets() {

    $direktt_user = Direktt_User::direktt_get_current_user();

    if ( $direktt_user ) {
        wp_enqueue_script(
            'direktt-extension-boilerplate-public',
            plugin_dir_url( __FILE__ ) . 'js/direktt-extension-boilerplate-public.js',
            array( 'jquery', 'direktt_public' ),
            '',
            array(
                'in_footer' => true,
            )
        );
    }
}
```

**Key points:**

- `Direktt_User::direktt_get_current_user()` is used to check if this request is from a **Direktt-authenticated user**.
- If yes, we enqueue `direktt-extension-boilerplate-public.js`, depending on the main direktt_public script (which provides `direktt_public` JS object with useful data such as `direktt_post_id`, `direktt_ajax_url`, `direktt_rest_base`, etc.).
- This script can be used both:
  - In your **profile tools** JS (deps can include `'direktt-extension-boilerplate-public'`).
  - In **service pages** (custom shortcodes, etc.).

## Direktt Extension Boilerplate: What It Implements

The **Direktt Extension Boilerplate** (GitHub: https://github.com/direktt/direktt-extension-boilerplate) provides a minimal, working example of a Direktt extension plugin. It demonstrates:

- **Safety & Activation Check**
  - Ensures the **Direktt WordPress Plugin** is active before this extension is activated.
  - If not, it:
    - Deactivates itself.
    - Shows an admin notice and a plugins list row message.

- **Public Asset Hook (`direktt_enqueue_public_scripts`)**
  - Shows how to enqueue a front-end script **only when there is a Direktt user**.
  - Provides a base JS file (`direktt-extension-boilerplate-public.js`) you can extend.

- **Custom Settings Page (Direktt > Settings)**
  - Hooks `direktt_setup_settings_pages`.
  - Calls `Direktt::add_settings_page()` with:
    - `id`, `label`, `callback`
    - `cssEnqueueArray` / `jsEnqueueArray`
  - Renders a minimal settings screen (“Direktt Extension Boilerplate Settings Go Here.”).

- **Custom Profile Tool (User Profile UI)**
  - Hooks `direktt_setup_profile_tools`.
  - Calls `Direktt_Profile::add_profile_tool()` with:
    - `id`, `label`, `callback`
    - Optional categories/tags filters.
    - `cssEnqueueArray` / `jsEnqueueArray`.
  - Renders a minimal profile tool tab (“Direktt Extension Boilerplate Profile Interface Goes Here.”).

You can **clone this boilerplate** and:

- Rename the plugin, text-domain, and handles.
- Replace the settings and profile tool callbacks with your own logic.
- Add further hooks:
  - Direktt actions (`direktt/action/...`).
  - Direktt message hooks (`direktt/message/template/...`).
  - Direktt user events (`direktt/user/...`).
- Add Service pages with your own shortcodes, then wire them into **Service Links/Admin Links** in the Direktt Admin Console.

With these integration points, you can build powerful Direktt Extensions that feel native inside both **Direktt mobile app** and **WordPress wp-admin**, while leveraging standard WordPress plugin patterns.