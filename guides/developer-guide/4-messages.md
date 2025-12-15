Direktt messages are the primary way to communicate with your subscribers and channel admin from WordPress. This section explains:

- How Direktt messages are structured.
- How to build advanced “rich” messages (buttons).
- How message templates and replacement tags work.
- How to use the Direktt_Message API methods in your own code.
- Practical code examples.

## Message Structure Overview

In Direktt, a **message** contains a single content part **(e.g., one text or one image)**.

When sending via the **Direktt WordPress API**, each content part possibly sent as an array will end up being a separate, single Direktt message.

**Supported content part types / message types:**

- `text`
- `image`
- `video`
- `file`
- `rich` (interactive content; currently used for buttons)

Each content part is a JSON object with type-specific properties.

### Content Part Properties

Common properties within each content part:

- `type` (string)  
  The type of the content part. Possible values:
  - `"text"`
  - `"image"`
  - `"video"`
  - `"file"`
  - `"rich"` (for interactive content such as buttons)
- `content` (string)
  - For `text`, `image`, `video`, `file`: human-readable text content or caption.
  - For `rich`: **JSON-encoded string** describing the rich content object (e.g., buttons definition).
- `media` (string)
  - URL of the associated file:
    - For `image`: image URL.
    - For `video`: video file URL.
    - For `file`: file URL (e.g., PDF).
- `thumbnail` (string, optional)
  - URL of a thumbnail image used for previews.
  - If omitted, the `media` URL may be used as fallback.
- `width` (int)
  - Width of the thumbnail in pixels (for images and videos).
- `height` (int)
  - Height of the thumbnail in pixels (for images and videos).
- `unfurl` (string, optional)
  - JSON-encoded object describing “unfurl” data for the first URL in the message:
    - `url`: URL of the unfurled link.
    - `image`: URL of the preview image.
    - `title`: Title of the unfurled link.
- `reply` (string, optional)
  - JSON-encoded object describing the original message when this message is a reply:
    - `id`: ID of the original message.
    - `quoteAuthor`: Author name of the quoted message.
    - `thumbnail`: Thumbnail image URL for the quoted message.
    - `text`: Quoted text content.

### Message JSON Examples

**Text Message**
```json
{
  "type": "text",
  "content": "Welcome to our channel"
}
```

**Image Message**
```json
{
  "type": "image",
  "content": "Image description HERE",
  "thumbnail": "https://placehold.in/600x200.png/dark",
  "media": "https://placehold.in/600x200.png/dark",
  "width": 600,
  "height": 200
}
```

**Video Message**
```json
{
  "type": "video",
  "content": "Video description HERE",
  "media": "https://videos.pexels.com/video-files/3195394/3195394-uhd_2560_1440_25fps.mp4",
  "thumbnail": "https://placehold.in/2560x1440.png",
  "width": 2560,
  "height": 1440
}
```

**File Message**
```json
{
  "type": "file",
  "content": "File description HERE",
  "media": "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf"
}
```

### Rich Messages (Buttons)

Rich messages allow you to send **interactive** content (currently buttons) via a type: `"rich"` content part.

For `rich` types, the `content` property is a **JSON-encoded string**, containing an object with:

- subtype (string)  
  Currently supported: "buttons".

- disabled (bool, optional)    
  - `true` Interactivity will be disabled
  - `false` Buttons will be enabled

- msgObj (array)  
  An array of button definitions. Each entry is an object with:
  - txt (string) Text shown above the button.
  - label (string) Text shown on the button itself.
  - accent (bool, optional) `true` indicates that the button should be colored in accent color
  - action (object) Direktt action object that defines what happens when the button is tapped.

**Button Action Object**

Each button’s action has:

- type (string)  
  One of the supported action types:
  - "link": open a URL.
  - "api": call back into your WordPress instance via Direktt API.
  - "chat": open chat (admin only).
  - "profile": open user profile (admin only).
- params (object)  
  Parameters vary by type. Example for link:
  - url (string): URL to open.
  - target (string):
    - "app": open inside Direktt app (in-app WebView).
    - "browser": open in device browser.
-  retVars (object)  
  Key/value pairs returned to your server when the button is tapped:
  - For link, returned as query vars.
  - For api, returned in the payload.

You can use these to capture user choices, run workflows, or track interactions.

### Buttons Rich Content JSON Example

Content (decoded form):

```json
{
  "subtype": "buttons",
  "msgObj": [
    {
      "txt": "This is Text above Button 1",
      "label": "This is Button 1's Label",
      "action": {
        "type": "link",
        "params": {
          "url": "https://direktt.com",
          "target": "app"
        },
        "retVars": {}
      }
    },
    {
      "txt": "This is Text above Button 2",
      "label": "This is Button 2's Label",
      "action": {
        "type": "link",
        "params": {
          "url": "https://wordpress.org",
          "target": "app"
        },
        "retVars": {}
      }
    }
  ]
}
```

Wrapped in a `rich` content part:

```json
{
  "type": "rich",
  "content": "{... JSON above encoded as a string ...}"
}
```

Buttons Rich Content PHP Example
Here’s how you might build a single `rich` content part in PHP:

```php
$richMessageObj = array(
  'type'    => 'rich',
  'content' => wp_json_encode(
    array(
      'subtype' => 'buttons',
      'msgObj'  => array(
        array(
          'txt'    => 'Text message content',
          'label'  => 'CLICK HERE',
          'action' => array(
            'type'   => 'link',
            'params' => array(
              'url'    => 'https://example.com',
              'target' => 'app',
            ),
            'retVars' => new stdClass(), // Empty object.
          ),
        ),
      ),
    )
  ),
);
```

You could then include `$richMessageObj` as one item in your message array when sending from **Direktt WordPress API**.

## Direktt Message Templates & Replacement Tags

Direktt provides **message templates** via a custom post type (**Direktt > Message Templates**). Each template stores one or more content parts as a JSON representation (`direkttMTJson` post meta).

Each content part will be a separate Direktt message with the order set in the template preserved.

You can send templates:

- To one or more subscribers (bulk or individually).
- To the channel admin.
- From:
  - The Direktt mobile app UI, or
  - wp-admin
  - Your own plugin code using Direktt_Message methods.

### Replacement Tags

Templates can contain **replacement tags**, which look like:

`#tag_name#`

At sending time, these tags are replaced with actual values from an associative array you provide in your code.

Common tags:

- `#direktt_display_name#`  
  Replaced with the subscriber’s display name.

- `#direktt_channel_name#`  
  Replaced with the channel’s title.

You can add your own tags as well, as long as you pass a replacement value when calling `send_message_template()` or `send_message_template_to_admin()`, or implement filters (see below).

**Replacement Tag Processing:**

All replacement logic goes through:

```php
Direktt_Message::replace_tags_in_template( $input_string, $replacements, $direktt_user_id = null )
```

- Scans `$input_string` for tokens matching `#something#`.
- For each token:
  - Takes the inner part as `$tag` (e.g. `direktt_display_name`).
  - Looks for `$replacements[ $tag ]`:
    - If found, uses that value.
    - If not found, the default is the tag name itself.
  - Applies a filter based on the tag name:
    - Hook name: `direktt/message/template/<tag>`.
    - Filter parameters: `$value`, `$direktt_user_id`.

This means you can:

- Provide runtime replacements via `$replacements` array.
- And/or define a filter for any tag to auto-compute its value.

**Adding Your Own Tag Filters**

You can implement custom, computed replacement tags like this:

```php
add_filter(
  'direktt/message/template/custom_loyalty_points',
  function( $value, $direktt_user_id ) {

      // Lookup user by subscription ID.
      $user = Direktt_User::get_user_by_subscription_id( $direktt_user_id );

      if ( ! $user ) {
          return $value;
      }

      // For example, read loyalty points from user meta or a custom extension.
      $points = get_post_meta( $user['ID'], 'my_loyalty_points', true );

      return $points ? $points : 0;
  },
  10,
  2
);
```

Then use `#custom_loyalty_points#` in your message templates. When sending with `send_message_template()`, the filter will compute the final value.

## Message-Related API Methods

The `Direktt_Message` class provides several helper methods for sending and updating messages.

### Send Message

```php
Direktt_Message::send_message( $messages )
```

Send one or more messages directly to specific subscribers, without using templates.

**Parameters:**

- `$messages` (array) Associative array where:
  - **Key:** Direktt Subscription ID (`subscriptionId` string).
  - **Value:** Message object to send (as a PHP object) for that subscription.

Internally, for each entry:
- Builds a payload object:
  - `subscriptionId` → key.
  - `pushNotificationMessage` → value.
- POSTs to Direktt remote endpoint (`/sendbulkmessages`).

You are responsible for building the message object in the correct format.

**Returns:**

- Array of responses from the remote endpoint regardless of whether the remote call succeeded or failed. 

**Example:**

```php
$message = array(
  'type'    => 'text',
  'content' => 'Hello from WordPress!',
);

Direktt_Message::send_message(
  array(
    'SUBSCRIPTION_ID_123' => $message,
  )
);
```

### Update Sent Message

```php
Direktt_Message::update_message( $subscription_uid, $message_uid, $content )
```

Update an existing message’s content (e.g., for editing or correcting a previous message).

**Parameters:**

- `$subscription_uid` (string)  
  The Direktt subscription UID of the target user (same as subscriptionId).

- `$message_uid` (string)
  The unique ID of the message you want to update (provided by Direktt).

- `$content` (mixed)  
  The new content you want to set for the message. This should match the expected message format.

Internally:

- Builds a POST payload:
  - `subscriptionUid`
  - `messageUid`
  - `content`
- Sends it to the Direktt `updateMessage` endpoint.

**Returns:**

- No explicit return value (void).

### Send Message Template

```php
Direktt_Message::send_message_template( $direktt_user_ids, $message_template_id, $replacements = array() )
```

Send a message template to one or more Direktt users, with optional tag replacements.

**Parameters:**

- `$direktt_user_ids` (array)  
  Array of subscription IDs (strings). Each element is a subscriptionId for a subscriber.

- `$message_template_id` (int)  
  ID of the Direktt Message Template (CPT) to send.  
  The JSON template is stored in post meta key direkttMTJson.

- `$replacements` (array, optional)  
  Associative array of replacement values for template tags:
  -  Key: tag name without # (e.g. 'title', 'direktt_display_name').
  -  Value: string to be inserted.

**What it does:**

- Retrieves the JSON template from meta (`direkttMTJson`).
- For each subscription ID in `$direktt_user_ids`:
  - Calls `replace_tags_in_template()` with:
    - The raw JSON template.
    - `$replacements`.
    - `$direktt_user_id` (subscription ID for the target user).
  - This:
    - Replaces all `#tag#` placeholders.
    - Applies tag-specific filters (e.g. user display name).
  - Decodes the JSON into `$messages` (array of content parts).
- For each `$message` in the decoded list:
  - If `$message->content` is an array or object, it is JSON-encoded to a string.
  - Builds a payload object:
    - `subscriptionId` → current user id.
    - `pushNotificationMessage` → current `$message`.
- Sends all built payload objects to `/sendbulkmessages` in batches.

**Returns:**

- If a template is found and at least one message is processed:
  - Returns the responses array from remote endpoint.
- If no template is found:
  - Returns `false`.

**Notes on Replacement Logic:**

- Both **provided replacements** (`$replacements`) and **filters** (`direktt/message/template/<tag>`) are applied.
- Each user's subscription ID is passed as `$direktt_user_id` to `replace_tags_in_template()`, enabling per-user dynamic data (e.g. display name, points, etc.).

### Send Message to Admin

```php
Direktt_Message::send_message_to_admin( $message )
```

Send a message(s) directly to the **channel admin** via a dedicated admin endpoint.

**Parameters:**

- `$message` (object / Array)  
  A message object representing the admin message. It should be formatted the same way as subscriber messages. If given array of message objects, each will be sent while preserving the order in the array

Internally:
- Builds payload:
  - `pushNotificationMessage` → `$message`.
- Sends a POST to the `sendadminmessage` endpoint for the linked channel.

**Returns:**

- Return an array of responses from remote endpoint.

### Send Message Template to Admin

```php
Direktt_Message::send_message_template_to_admin( $message_template_id, $replacements = array() )
```

Send a template-based message to the **admin user**. Works similarly to `send_message_template()`, but targets the admin-only endpoint.

**Parameters:**

- `$message_template_id` (int)  
  ID of the message template (CPT). The template JSON is stored in `direkttMTJson`.

- `$replacements` (array, optional)  
  Associative array of replacement values for tags inside the template, same format as for `send_message_template()`.

**What it does:**

- Loads the raw JSON template from `direkttMTJson`.
- Runs `replace_tags_in_template()`:
  - `$direktt_user_id` is `null` here (admin is not referenced as a subscription).
  - `$replacements` are applied.
  - Tag filters can still contribute values if they don’t depend on user.
- Decodes `$messages` (array of content parts).
- For each `$message`:
  - JSON-encodes `$message->content` if it is an array or object.
  - Builds payload:
    - `pushNotificationMessage` → `$message`.
  - Sends a POST to `sendadminmessage` endpoint.

**Returns:**

- `true` if messages were found and processed.
- `false` if no template JSON is stored for the given ID.

**Notes on Replacement Logic:**

- Since `$direktt_user_id` is `null`, user-specific filters (like `direktt_display_name_filter`) may not apply in the same way - use admin or channel - wide tags instead (e.g. `#direktt_channel_name#`), or pass values explicitly via `$replacements`.

## Message-Related Code Example (Welcome Message on Subscription)

The following simplified example shows how to:

- Listen to the “user subscribed” hook.
- Fetch the user’s display name.
- Read configuration options for a welcome flow.
- Send a template message with dynamic `#title#` replacement.

Hook and handler:

```php
add_action( 'direktt/user/subscribe', 'on_direktt_subscribe_user' );

function on_direktt_subscribe_user( $direktt_user_id ) {

  // Obtain the subscribing user's object and display name.
  $user_obj   = Direktt_User::get_user_by_subscription_id( $direktt_user_id );
  $user_title = get_the_title( $user_obj['ID'] );

  // Retrieve the configured welcome message template ID and check if welcome messaging is activated.
  $welcome_user          = get_option( 'direktt_welcome_user', 'no' ) === 'yes';
  $welcome_user_template = intval( get_option( 'direktt_welcome_user_template', 0 ) );

  if ( $welcome_user && 0 !== $welcome_user_template ) {

      // Send the message template to the user, providing the tag replacement values in an associative array.
      Direktt_Message::send_message_template(
          array( $direktt_user_id ), // Array of subscription IDs.
          $welcome_user_template,
          array(
              'title' => $user_title, // Replaces #title# in the template.
          )
      );
  }
}
```

This relies on:

- A configured **message template** that uses `#title#` inside its content.
- Plugin options `direktt_welcome_user` and `direktt_welcome_user_template` (as used in your extension).
- The Direktt Messaging API (`send_message_template`) to handle replacements and sending.

By combining rich message structures, templates, replacement tags, and the `Direktt_Message` API, you can build powerful, personalized messaging flows that react to user events and behaviors across your WordPress site and the Direktt mobile app.