Direktt Actions let you instruct the **Direktt mobile app** to perform specific operations when a user taps a button in an interactive message, or when a QR code is scanned.

You will most often use Direktt Actions to:

- Open a **link** in the app or browser.
- Call back into your **WordPress** instance to run custom logic.
- Open a **chat** with a user (admin only).
- Open a **user profile** (admin only).

This section explains:

- How Direktt Actions are structured.
- The supported action types and their parameters.
- How `retVars` work (including automatic taxonomy updates).
- How to handle `api` actions in your WordPress plugin.

## How Direktt Actions Work (Conceptual Overview)

A Direktt Action is a small JSON object that tells the app what to do next. It is usually embedded in:

- An **action QR code** (e.g. “scan this to check in”).
- A **button** inside a rich (interactive) Direktt message.

**Example flow (Membership QR scan):**

1. An admin scans a user’s **Membership ID** in the Direktt app.
2. The QR code contains a **profile action** with the user’s subscriptionId.
3. The Direktt app opens that user’s profile screen.
4. The admin can then:
  - Invalidate a ticket.
  - Assign/remove user categories/tags.
  - Update loyalty fields (via your custom extensions).
  - Issue rewards, etc.

Behind the scenes, the Membership QR code’s action looks conceptually like:

```json
{
  "type": "profile",
  "params": {
    "subscriptionId": "XXXXXXXXXXXX"
  },
  "retVars": {}
}
```

## Action JSON Structure

All Direktt Actions share the same basic JSON structure:

```json
{
  "type": "<String>",
  "params": { /* Object */ },
  "retVars": { /* Object */ }
}
```

- `type`  
  The type of action (what the app should do).
- `params`  
  Type-specific parameters for this action.
- `retVars`  
  Custom key–value data that will be sent back to your WordPress instance when the action is executed.
  - For `link` actions: sent as **query parameters**.
  - For `api` actions: sent in the **payload** to your WordPress handler.

## Supported Action Types

Direktt currently supports the following action types:

- `"link"` – Open a URL (in-app WebView or device browser).
- `"api"` – Call your WordPress instance for custom server-side handling.
- `"chat"` – Open a chat (admin only).
- `"profile"` – Open a user profile (admin only).

### `link` Action

Opens a URL when the user taps the button or scans the QR.

**Required** `params`:

- `url` (string) – The full URL to open.
- `target` (string) – Where to open the link:
  - `"app"` – Inside the Direktt app (WebView).
  - `"browser"` – In the device’s default browser.

**Example:**

```json
{
  "type": "link",
  "params": {
    "url": "https://hexxu.dev",
    "target": "app"
  },
  "retVars": {}
}
```

### `api` Action

Triggers a **callback to your WordPress instance** via the Direktt plugin. Use it for any custom logic (check-in, coupon redemption, loyalty updates, etc.).

**Required** `params`:

- `actionType` (string) – A key that identifies which WordPress hook should handle this action.
- `successMessage` (string, optional) – A short message shown to the user in the app after success.

**Example:**

```json
{
  "type": "api",
  "params": {
    "actionType": "apply_ticket",
    "successMessage": "Your application has been recorded"
  },
  "retVars": {}
}
```

In this example, the WordPress plugin will fire:

```php
do_action( 'direktt/action/apply_ticket', $params );
```

So your code should hook into `direktt/action/apply_ticket`.

### `chat` Action

Opens a chat with a specific user (used in admin flows and admin-only buttons).

**Required** `params`:

- `subscriptionId` (string) – Direktt subscription ID of the user whose chat should be opened.

**Example:**

```json
{
  "type": "chat",
  "params": {
    "subscriptionId": "XXXXXXXXXXXX"
  },
  "retVars": {}
}
```

### `profile` Action

Opens a user’s Direktt profile (admin-only context).

**Required** `params`:

- `subscriptionId` (string) – Direktt subscription ID of the user whose profile should be opened.

**Example:**

```json
{
  "type": "profile",
  "params": {
    "subscriptionId": "XXXXXXXXXXXX"
  },
  "retVars": {}
}
```

### Using Actions for Event Check‑in (Example)

A common use case is **event check‑in** via QR code:

- User arrives at event and scans a QR code using Direktt mobile app.
- The app sends an `api` action back to your WordPress site.
- Your code:  
  - Marks the user as checked in.
  - Optionally assigns a user **category/tag** (e.g. `registered`).
  - Optionally sends a confirmation message.

**Basic** `api` **Check-in Action**

```json
{
  "type": "api",
  "params": {
    "actionType": "entry_checkin"
  },
  "retVars": {}
}
```

**Adding Context with** `retVars`

To link the check-in to a specific event (for example, a WordPress post ID), you can pass custom data via retVars:

```json
{
  "type": "api",
  "params": {
    "actionType": "entry_checkin"
  },
  "retVars": {
    "eventId": "12345"
  }
}
```

On the WordPress side, your handler will receive:

```json
{
  "eventId": "12345"
}
```

(Along with other internal fields like subscriptionId, see below.)

## `retVars` for Custom Data (Link & API)

retVars are **custom variables** bundled with the action. They are sent back to your WordPress instance as:

- Extra **query parameters** (for `link` actions).
- Extra **payload properties** (for `api` actions).

This is useful to:

- Pass event IDs, campaign codes, or any other context.
- Trigger different logic per button or per QR code.
- Avoid encoding logic into the URL itself.

### Example: Link Action with `retVars`

```json
{
  "action": {
    "type": "link",
    "params": {
      "url": "https://example.com/sample-page/?sampleVar=1",
      "target": "app"
    },
    "retVars": {
      "eventId": "XXXXXXX"
    }
  }
}
```

Resulting URL in the app:

```
https://example.com/sample-page/?sampleVar=1&eventId=XXXXXXX
```

**Keep in mind that the retVars values have to be strings, so if you want to pass a JSON object, you need to stringify / wp_json_encode it**

### Example: API Action with `retVars`

```json
{
  "action": {
    "type": "api",
    "params": {
      "actionType": "entry_checkin"
    },
    "retVars": {
      "eventId": "XXXXXXX"
    }
  }
}
```

On the WordPress side, the $params passed to your action hook will include:

```php
array(
  'eventId' => 'XXXXXXX',
  // ... plus other fields added by the plugin
)
```

### The Special `messageId` retVar

There is a **built‑in / “hidden”** `retVar` automatically added to every link and api action that originates from an **interactive message button:**

- `messageId` – The ID of the **message** that triggered this action.

You can use `messageId` to **update** the original message after the action is processed, for example to:

- Mark it as “used”.
- Change its text to “Ticket validated”.
- Disable buttons.

Use the `Direktt_Message::update_message()` method:

```php
Direktt_Message::update_message( $subscription_uid, $message_uid, $content );
```

- `$subscription_uid` – User’s subscription ID.
- `$message_uid` – The message ID (messageId retVar).
- `$content` – New content for the message (message object / content parts).

### Built‑in Taxonomy Management via `retVars`

Certain `retVars` keys are **reserved** and understood by the Direktt WordPress plugin. They enable you to **assign or remove user taxonomies automatically** after a `link` or `api` action – without writing any custom taxonomy code.

Supported keys:

- `addDirekttUserCategory`
- `removeDirekttUserCategory`
- `addDirekttUserTag`
- `removeDirekttUserTag`

Each of these expects a **stringified / wp_json_encode(d) array** of either:

- Term **slugs (strings)**, or
- Term **IDs (ints)**

for the corresponding taxonomy.

The plugin then automatically:

- Fetches the calling user (`$direktt_user`).
- Adds or removes the given terms on that Direktt user.

### Example: Auto‑assign “registered” Category on Check‑in

```json
{
  "action": {
    "type": "api",
    "params": {
      "actionType": "entry_checkin"
    },
    "retVars": {
      "eventId": "XXXXXXX",
      "addDirekttUserCategory": "[\"registered\"]",
      "removeDirekttUserTag": "[3]"
    }
  }
}
```

When this action is executed:

- Your `entry_checkin` handler runs.
- The plugin **automatically adds** the registered category to the current Direktt user (no extra code needed).
- Any other `retVars` (`eventId`, etc.) are passed to your handler.

### What Happens When an `api` Action Is Triggered

When a user triggers an `api` action (via button tap or QR scan), the flow is:

- The Direktt app sends the action (with `params` and `retVars`) to the Direktt server.
- Direktt forwards this to your **connected WordPress instance** using the plugin’s API endpoint.
- The Direktt plugin:
  - Authenticates the request and sets `$direktt_user`.
  - Merges:
    - `params`,
    - `retVars`,
    - and internal values like `subscriptionId` and `messageId` (if present), into one `$params` array.
  - Fires a **WordPress action hook:**
    ```php
    do_action( 'direktt/action/' . $actionType, $params );
    ```
- Your custom handler runs and can:
  - Inspect `$direktt_user`.
  - Use `$params` (`eventId`, `messageId`, etc.).
  - Send messages, update meta, run business logic, etc.
- If `successMessage` was provided in `params`, that text is displayed back to the user in the app.

### Handling Custom API Actions in WordPress

To handle your custom api actions, register a hook that matches the actionType defined in your action.

For example, for:

```json
{
  "type": "api",
  "params": {
    "actionType": "entry_checkin"
  },
  "retVars": {
    "eventId": "XXXXXXX"
  }
}
```

Your WordPress code:

```php
add_action( 'direktt/action/entry_checkin', 'on_entry_checkin' );

function on_entry_checkin( $params ) {

    // $params includes:
    // - Any retVars (e.g. 'eventId')
    // - messageId (if triggered from interactive message), etc.

    // Example: just notify the channel admin with the received params.
    $message_to_admin = array(
        'type'    => 'text',
        'content' => 'API call received. Params are: ' . wp_json_encode( $params ),
    );

    Direktt_Message::send_message_to_admin( $message_to_admin );

    // Optionally:
    // - Use $params['eventId'] to look up event data.
    // - Use $params['messageId'] with Direktt_Message::update_message().
}
```

If you also include taxonomy `retVars` like `addDirekttUserCategory`, the plugin will **automatically update** the user’s categories/tags after your handler runs.

## Summary

- Direktt Actions are small JSON objects that tell the app what to do:
  - `link`, `api`, `chat`, or `profile`.
- `params` define the action behavior, `retVars` carry extra context back to WordPress.
- `messageId` is automatically sent for interactive-message actions, allowing you to update the original message.
- Special `retVars` (`addDirekttUserCategory`, `addDirekttUserTag`, etc.) let you manage user taxonomies **without writing taxonomy code**.
- For `api` actions, the Direktt plugin triggers `direktt/action/<actionType>` with a `$params` array. You hook into it and implement your business logic.

Using these patterns, you can build rich, QR‑driven and button‑driven workflows that connect the Direktt app tightly with your WordPress services and data.