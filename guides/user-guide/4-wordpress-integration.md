## Activating WordPress Instance

Connecting your WordPress site to Direktt unlocks all the features of real-time messaging, loyalty integration, and subscriber management - directly within your WordPress dashboard. 

Follow these step-by-step instructions to activate your instance. This process requires both the Direktt admin console and your WordPress admin panel.

### **Prerequisites**

* Direktt channel is created and you have access to the **[Direktt admin console](https://direktt.com/wp-content/direkttweb/)**.  
* WordPress administrator access (wp-admin).  
* **SSL/HTTPS enabled on your WordPress site**. (Required by Direktt for secure API communication.)

### **Step-by-Step Activation Guide**

#### **1. Retrieve Your WordPress API Key from Direktt**

* Log in to the **[Direktt admin console](https://direktt.com/wp-content/direkttweb/)** and navigate to your channel.  
* Scroll to the **WordPress Instance Info** and **API Key & API Access section**.  
* If your instance is not yet activated:  
  * You’ll see a status of **Inactive**.  
* **Copy the API Key** for WordPress by clicking the copy icon next to the key.

> **Tip:** If you regenerate your API Key, make sure to update it in your WordPress settings.

[![API Key](https://direktt.com/wp-content/screenshots/user-guide-4-1.webp)](https://direktt.com/wp-content/screenshots/user-guide-4-1.webp)

#### **2. Install & Activate the Direktt Plugin on WordPress**

* Log in to your WordPress admin panel (wp-admin).  
* Go to **Plugins > Add New** and search for **Direktt**.  
* Click **Install** and then **Activate the plugin**.

#### **3. Open Direktt Settings in WordPress**

* In your WordPress dashboard, find the **Direktt menu** on the left.  
* Click **Settings**.

#### **4. Enter Your API Key & Activate**

* Paste the **API Key** you copied from the **[Direktt admin console](https://direktt.com/wp-content/direkttweb/)** into the **Direktt API Key field**.  
* Click **Save Settings & Activate WP**.  
  * If your WordPress site does not use HTTPS, you will see an error. Enable SSL and update your WordPress site URL to use https://.  
  * Upon successful activation, the Activation Status will update to **Activated**, and your registered domain will be displayed.

#### **5. Verify Activation in Direktt Admin Console**

* Return to the **[Direktt admin console](https://direktt.com/wp-content/direkttweb/)** and refresh the channel info page.  
* You should now see your WordPress instance listed as **Active**, with the activation date and your domain.

If you run into any difficulties, contact **[Direktt support](https://direktt.com/contact-support/)** for assistance.

Your Direktt WordPress instance is now securely connected and ready to supercharge your engagement!

[![Direktt Settings](https://direktt.com/wp-content/screenshots/user-guide-4-2.webp)](https://direktt.com/wp-content/screenshots/user-guide-4-2.webp)

## Direktt WordPress Settings

The Direktt WordPress Settings page lets you configure and manage the integration between your WordPress site and your Direktt channel. Below are the available options and their purpose.

### Accessing Direktt Settings

To access the settings:
* Navigate to **Direktt > Settings** in your WordPress admin dashboard.

### Channel Info

* **Channel Title**: Displays your linked Direktt channel’s name.
* **Channel Id**: Unique identifier for your Direktt channel.
* **Direktt API Key**: The API key connecting your WordPress instance with Direktt. You can copy or update this if you regenerate it in the **[Direktt admin console](https://direktt.com/wp-content/direkttweb/)**.

### Activation & Instance Info

* **Activation Status**: Indicates whether your WordPress instance is currently activated and connected to Direktt.
* **Registered Domain**: Shows the domain name registered with Direktt.
* **Number of Subscribers**: Displays the count of Direktt and WordPress subscribers synced.
* **Sync Subscribers’ Database**: Click this button to manually trigger a resynchronization between your Direktt channel and the WordPress user records.

### Unauthorized Access Management

* **Optional redirect URL upon unauthorized access**: Specify a URL or path (e.g., `/unauthorized/`) where non-authorized users will be redirected if they attempt to access restricted content.

### Timestamp Display Format

Controls how date/time values are rendered across Direktt-facing UI in WordPress.

This format is used in:
- the **Direktt user profile** page (`[direktt_user_profile]`)
- **Direktt plugin shortcodes**
- **extension shortcodes** (wherever timestamps are displayed)

Options:
- **Date & Time**: Shows a full timestamp (e.g., `2025-12-16 13:39:00`)
- **Relative Time**: Shows a relative value (e.g., `5 minutes ago`)

### QR Code Customization

You can configure the look and feel of your channel’s subscription QR code which users scan to subscribe via the Direktt app.

* **QR Code Logo**: Upload or select an image to be placed in the center of your QR code.
* **QR Code Color**: Choose the dot color for the QR code using the color picker.
* **QR Code Background Color**: Select a background color for your QR code.
* **Subscription QR Code Preview**: Displays a live preview of your QR code reflecting your color and logo choices.

### Pairing and Automation Settings

Pairing settings control the workflow for connecting WordPress users with Direktt app users.

* **Prefix for pairing message**: Enter a keyword prefix (e.g., "pairing") that must start the pairing message sent by the user in chat, to initiate the pairing process.
* **Message template for successful pairing**: Select which Direktt Message Template will be used to confirm to the user that pairing was successful. (Choose from templates already created in **Direktt > Message Templates**.)
* **Reset all pairing codes**: Tick this option to reset pairing codes for all users if needed (for example, after a major membership update).

### Saving Settings

After changing any options, click **Save Settings** at the bottom of the page. Changes will apply instantly.

## Managing Direktt Users in WordPress

Direktt users are automatically synced and managed through your WordPress dashboard whenever users subscribe or unsubscribe through the Direktt mobile app. 

Each Direktt user’s WordPress post can be edited, categorized, tagged, or paired with a WP User for advanced integrations.

### **Viewing and Editing Direktt Users**

* Go to **Direktt > Direktt Users** in wp-admin.
* Each user subscription creates a new post.
  * **Post Title**: Shows user’s display name.
  * **Post Content**: Stores user notes from the user profile in Direktt mobile app.
  * **Channel Admin**: Is titled **“Channel Admin”** with **Admin Subscription: true**.

* Click any user to see and edit details:
  * **Direktt User Properties section**:
    * Subscription ID, Membership ID, avatar, QR code, and admin status.
    * These fields give you a snapshot of each user.
  * **User Categories and User Tags**:
    * In the sidebar, check relevant boxes/tags to organize and segment users for group messaging or access control.

* **When a user unsubscribes**: Their post is removed automatically from Direktt Users.

### **Managing Categories and Tags**

* Use **User Categories** to segment users into broad roles or groups. Go to **Direktt > User Categories** to create/edit categories.
* Use **User Tags** for granular segmentation and flexible access rules. Go to **Direktt > User Tags** to manage tags.

Categories and tags can be used in **access rights for pages**, **messaging campaigns**, and **automation**.

### **Using Direktt Extensions**

* When you install **Direktt extensions**, new meta fields will appear in each user’s post edit screen.
* These fields are tailored for each extension’s function (e.g., loyalty points, purchase data).
* Configure or review extension-specific data as needed.

### **Pairing Direktt Users with WordPress Users**

**Benefits:** Pairing enables automation between WP and Direktt (e.g., actions on your site that trigger app messages, custom experiences).

#### **How to Pair**

* Go to **Users > Profile** (for any WP User).
* Find Code for pairing with related Direktt User in the **“Direktt User Properties”** section.
* **Send or display this code** to a WP User   
  * WP User sends the code in a **chat message in Direktt Mobile App**.
* The Direktt user will then be linked (“paired”) to the WordPress user.
  * The relation can be viewed or deleted on the **WP user profile screen**

[![Pairing Users](https://direktt.com/wp-content/screenshots/user-guide-4-3.webp)](https://direktt.com/wp-content/screenshots/user-guide-4-3.webp)

### **Troubleshooting and Tips**

| Issue | Solution |
| ----- | ----- |
| User missing from Direktt Users | Check synchronization status on Direktt > Settings and Sync Subscriber's database if needed |
| Unpairing needed | Use “Delete Relation with Direktt User” in WP User's Profile. |
| Assigning categories/tags not working | Save changes in the user post and ensure taxonomy terms exist. |
| New extension field not visible | Make sure the extension is installed and active. |

### **Summary**

* Every Direktt subscriber/unsubscriber is managed as a WordPress post in **Direktt > Users**.
* Use categories and tags for segmentation, mass communication, or feature access.
* Pair Direktt users with WP users for automation and custom flows.
* User notes and user taxonomies are editable inside the post screen.

This setup ensures smooth, scalable management for even the largest subscriber bases.

## Setting Up User Profile

With the **Direktt Profile shortcode**, you can create a dedicated user profile page in WordPress - accessible directly from the **Direktt mobile app’s chat**. Here’s a step-by-step setup guide, from deciding on your profile URL to configuring per-user access rights.

### **(Optional) Define Your User Profile URL in Direktt Admin Console**

* By default: The user profile URL is 
  ```
  https://your-wordpress-domain/direktt-profile/
  ```

* To use a custom URL:  
  * Go to the **[Direktt admin console](https://direktt.com/wp-content/direkttweb/)**.  
  * Navigate to your **channel’s settings**.  
  * Enter your preferred URL in the **User Profile Url** field and click **Save Changes**.

### **Create a Profile Page in WordPress**

* Go to **Pages > Add New** in your WordPress admin.  
* Set a title (e.g., "Direktt Profile").  
* It is best to use a **Blank Template** for the **Profile page**. Keep in mind that template options may vary by theme. Block themes like Twenty Twenty-Three offer ‘Empty template’ for a minimal page. Refer to your theme's documentation or support for the theme specific instructions  
* Click **Save**.

### **Add the Profile Shortcode & Define Access**

* In your page editor, add a **Shortcode** block.  
* Use following shortcode:

  ```
  [direktt_user_profile]
  ```

* **Set access rights:** In the right sidebar (page settings), under **Direktt options** grant access to **Direktt admins** as the user profile functionality should be available to **channel admins only**. If any access right is set, the page becomes only accessible via the Direktt mobile app.  
* Click **Save** or **Publish**.

### **Accessing the Profile Page from the Direktt App**

User profile can be accessed from Admin mode in Direktt Mobile App  
* Open the Direktt App and tap **More** in the bottom-right corner.  
* On your More screen, tap the **Switch to Admin mode** button.

Open one of the user chats within your channel and tap the **Profile button** in the top right.  
* The app will open your WordPress profile page, displaying user info, categories, and tags.

### **Summary**

* You can set a **custom or default profile URL**.  
* Create a blank page with the `[direktt_user_profile]` shortcode.  
* Restrict access to Direktt admin for app-only visibility.  
* Admins reach the profile via the chat's **Profile button** in the app.

### **Tip:** 

> To test, use the Direktt mobile app from the Admin mode!  
  
Your Direktt profile page is now securely and seamlessly integrated with your WordPress site and accessible from the Direktt app.

## How to Test Direktt Pages from WordPress wp-admin

Once you enable Direktt user rights for a page, access is restricted - these pages are only visible from within the Direktt app for the intended user roles. This can make desktop/website testing tricky. However, you can simulate the exact experience of a Direktt user within wp-admin by following these steps:

### **Set Up "Test Direktt User" Mode for Your WP User**

* Go to your WordPress Dashboard.  
* From the main menu, select **Users > Profile** (or "Your Profile").  
* Find the **"Direktt User Properties"** section.  
* In the field labeled **Post Id of Test Direktt User**, enter the **Post ID** of the Direktt user you wish to simulate.  
  * Every Direktt user Post (including subscribers and channel admin) has a unique Post ID.  
* Click **Update Profile** to save.

[![Test Direktt User](https://direktt.com/wp-content/screenshots/user-guide-4-4.webp)](https://direktt.com/wp-content/screenshots/user-guide-4-4.webp)

### **Test Direktt Pages As the Selected User**

* With the **Post Id** set:  
  * Log in with your WordPress user.  
  * Open any Direktt-restricted page in your browser (e.g., the Taxonomies Service Page - check below in the Default Admin Services section for the setup instructions).  
  * The page will behave and display content as if you are logged in as the chosen Direktt user in the mobile app.

### **Pro Tip: Simulate Specific Subscriber Profiles**

> You can also preview a specific user profile using their subscription ID:

> * Set **Post Id of Test Direktt User** to the Post ID of the channel admin.  
> * Visit the profile page with the `?subscriptionId=` parameter, for example: 
  
>  ```
>  https://your-wordpress-domain/direktt-profile/?subscriptionId=SUBSCRIPTION_ID
>  ```

> * Replace `SUBSCRIPTION_ID` with the desired subscriber’s subscription ID.  
> * You will see the profile and page content exactly as if you were logged in as channel admin in the Direktt app.

### **Why This Works**

* **Bypassing In-App-Only Restriction**: This method grants your logged-in WP user temporary access to pages otherwise locked to Direktt app users.  
* **Test All Roles and Scenarios**: Instantly switch between different user views by changing the Post Id.

### **Troubleshooting**

| Issue | Solution |
| ----- | ----- |
| Page still inaccessible | Double-check you are logged in and Post Id is set correctly; clear caches if needed. |
| Unsure of correct Post Id | Go to **Direktt > Direktt Users**. Hover over the User for which you want to find the ID, and then click **Edit**. Once the post editor loads, look at the URL in your browser's address bar. You will see a URL similar to: `https://yoursite.com/wp-admin/post.php?post=123\&action=edit` The number following `post=` (in this example, `123`) is the **Post ID**. |
| No Direktt User Properties section | Ensure the Direktt plugin is active and up to date. |

Now you can test and preview any Direktt page in WordPress as if you were a real app user - no phone required!

## Creating Message Templates

Message Templates in Direktt allow you to define and reuse structured messages, including text, media, files, and interactive buttons. 
Templates are managed as a custom post type (CPT) in WordPress and can be sent to subscribers, used for bulk messaging, or referenced directly in your code.

This guide walks you step by step through creating a message template using the Direktt WordPress plugin.

### **Accessing Message Templates**

* 1. Go to WordPress Admin  
  * Log in to your WordPress admin dashboard.  
* 2. Navigate to **Direktt > Message Templates**  
  * You’ll see a **list of existing templates** and an option to add new ones.

### **Step 1: Add a New Message Template**

* Click **Add New Message Template** at the top of the **Message Templates screen**.

### **Step 2: Configure Basic Settings**

* **Title**: Enter a descriptive name for your template. (Example: "Welcome Message" or "Promo Newsletter")  
* **Where to display template**: Use the dropdown to choose template visibility:  
  * **Always display this template**: Available for both individual and bulk messaging.  
  * **Display only when sending Bulk Messages**  
  * **Display only when sending Individual Messages**  
  * **Never, I will use it only via API**: Hidden from UI, reference only in custom code.

### **Step 3: Add Content Parts**

Message templates can include multiple content types. Each part will be shown in the order added.

| Content Type | How to Add | Example Use |
| ----- | ----- | ----- |
| **Text** | Add Text | Greetings, info, etc. |
| **Image** | Add Image | Banners, product photos |
| **Video** | Add Video | Promo videos, demos |
| **File** | Add File | Attachments, docs, PDFs |
| **Interactive** | Add Interactive Content | Buttons for action steps |

#### **How to Add a Content Part**

* Click the corresponding Add `[Type]` button (e.g., *Add Text*, *Add Image*, etc.).  
* For media (image/video/file), select an item from the **WordPress Media Library**.  
* For text, **type your message**.  
* For each part, you’ll see a **Properties panel** for configuration.

**Example: Adding Text**

* Click **Add Text**.  
* Enter your message in the **"Message Content"** field. (e.g., *Hi, welcome to our channel!*)  
* The live preview and JSON structure will update as you type.

**Example: Adding Image**

* Click **Add Image**.  
* Click **Select Image** and choose from the Media Library.  
* Thumbnail width/height will be set automatically.  
* Add image-related message text.

**Example: Adding Video**

* Click **Add Video**.  
* Select the video file from the **Media Library**.  
* **Add a thumbnail** image.
* Thumbnail width/height will be set automatically.   
* Add a caption or description.

**Example: Adding File**

* Click **Add File**.  
* **Select the file** from the Media Library.  
* Add a message or instructions related to the file.

**Example: Adding Interactive Content (Buttons)**

* Click **Add Interactive Content Part**.  
* In the Properties panel, use **Add Button** for each button.  
  * **Button Label**: The button's visible label.  
  * **Text above button**: Description or instructions above the button.  
  * **Action Type**: Choose one:  
    * **Link**: Opens a URL (choose if to open in-app or browser).  
    * **API**: Makes an API callback to your WP instance. Enter the API action type and success message.  
    * **Chat**: Opens chat with user **(admin only)**.  
    * **Profile**: Opens user profile **(admin only)**.  
  * **Return Variables**: (Optional) Store data for the action. Key-value pairs.

#### **Using Dynamic Replacement Tags in Message Templates**

You can personalize any message content by adding dynamic replacement tags. 

When a message is sent, these tags are replaced with values specific to each recipient or channel.

**Available at all times:**

  * **Direktt user display name:** `#direktt_display_name#`

  * **Channel name:** `#direktt_channel_name#`

**How to add tags:** Type the tag name enclosed in two hash signs. 

**Example:**
  ```
  Hi #direktt_display_name#, welcome to #direktt_channel_name#!
  ```
 
When sent, users will see their actual display name and the channel’s name in place of these tags.

**Other tags:** Additional context-specific tags may be supported, depending on your workflows and installed extensions. Refer to Developer Documentation on how to use them via Direktt API

> **Tip:** Use tags in any text field, including text content, interactive part descriptions, and above/below buttons for a more personalized communication experience.

[![Direktt Message Templates](https://direktt.com/wp-content/screenshots/user-guide-4-5.webp)](https://direktt.com/wp-content/screenshots/user-guide-4-5.webp)

### **Step 4: Review and Edit Template JSON**

* The **Template JSON Content** area shows a live JSON representation.  
* For advanced users, you may use JSON representation in your Direktt extension code via the Direktt API. Refer to Developer Documentation for details

### **Step 5: Save and Publish**

* When finished, select **Save Draft or Publish**.  
* Your new message template will now appear in the templates listing, according to the visibility setting you chose.

### **Step 6: Sending Templates (Optional)**

* Templates can be sent directly from this screen using the **Send Template as Message** section.  
* You may send to:  
  * **All channel subscribers**  
  * **Selected subscribers** (using categories/tags)  
  * **Channel admin**

### **Additional Tips**

> * Drag and drop content parts to reorder them.  
> * Use concise, clear content for the best user experience on mobile devices.  
> * For code-based usage, reference the template by its CPT post Id via the Direktt API.  
> * Set template visibility to **Never, I will use it only via API** for system, plugin, or workflow integrations to keep the UI clean.

### **Frequently Asked Questions**

**Q: Can I include more than one type of content in a template?** 
* Yes, templates can include any mix and order of content types. For example, you can follow a text message with an image, then an interactive button part.

**Q: Can I edit a template after publishing?**
* Yes, just click on the template name in the list to edit it. All changes will apply going forward.

**Q: What's the purpose of the "Where to display template" option?** 
* This determines where the template is visible in the Direktt plugin and Direktt Mobile App - bulk messaging, individual messaging, API-only, or always available. Use this to keep template lists tidy and relevant for your use case.

**Q: How do I use a template from my own code?** 
* You can reference Direktt message templates by their post ID or slug using the Direktt API in your custom WordPress code or integrations. Refer to Developer Documentation for details

Your message template is now ready to use for targeted, automated, or bulk communication with your Direktt subscribers.

## Default Admin Services

Direktt provides default Admin Services designed to make channel management tasks fast and accessible for administrators from within the Direktt mobile app. The primary default service is the **Taxonomies Service**, which allows channel admins to assign or remove users from categories and tags - empowering easy and intuitive user segmentation right from your phone.

### Setting Up the Taxonomies Service Page in WordPress

To make the Taxonomies Service available to your channel admins in the app, you’ll first create a dedicated, Direktt-restricted page in WordPress and add the relevant shortcode.

**Follow these steps:**

1. **Create a New Empty Page**
    - In your WordPress dashboard, go to **Pages > Add New**.
    - Set a page title like **Admin Taxonomies Service**.
    - Choose an **Empty/Blank template** for a minimal appearance (check your theme for this option).

2. **Insert the Taxonomies Service Shortcode**
    - Add a **Shortcode** block to your page.
    - Paste this shortcode:

      ```
      [direktt_edit_taxonomies_service]
      ```

3. **Restrict Access to Direktt Admins Only**
    - In the right sidebar (page settings), locate the **Direktt** options panel.
    - Check **Allow access to Direktt admin**.
    - Leave “Allow access to Direktt users” unchecked. Only admins should have access.
    - Click **Publish** or **Update** to save the page.

> **Tip:** Restricting access ensures only authorized channel admins can use this management tool within the Direktt app.

### Linking the Taxonomies Service in Direktt Admin Console

To make your new service visible and accessible from the Direktt mobile app:

1. **Go to the [Direktt admin console](https://direktt.com/wp-content/direkttweb/)** and open your channel.
2. Select the **Admin Links** tab.
3. Click **Add New Admin Link**.
    - **Service Link Name:** Enter a name such as “Taxonomies Service”.
    - **Service Link URL:** Enter the full URL of your new Taxonomies Service page (e.g., `https://yourdomain.com/admin-taxonomies-service/`).
    - **Target:** Choose **App** to open the service within the Direktt mobile app.
4. Click **Submit** to save.

Your “Taxonomies Service” link will now appear in the Admin Services section in the Direktt app for channel admins.

### Functionality Overview

The Taxonomies Service provides a simple interface in the Direktt mobile app for managing user groupings:

- **View All Categories and Tags:** Admins see a list of all active user categories and tags.
- **Assign or Remove Users:** Tap a category or tag to view and modify the users assigned to it. Add or remove users with just a couple of taps.
- **Streamline Segmentation:** Quickly segment your audience for messaging, access control, and workflow automations—directly from your phone, without needing the desktop dashboard.

**At a Glance: Setting Up the Taxonomies Service**

| Task                          | Steps                                                                                   |
|-------------------------------|----------------------------------------------------------------------------------------|
| Create Taxonomies Service Page | New page, `[direktt_edit_taxonomies_service]` shortcode, restrict to Direktt admins    |
| Add Admin Service Link         | In Direktt admin console > Admin Links, add new, set target to "App"                   |
| Use in Direktt mobile app      | Assign/remove users from categories/tags, segment users instantly from anywhere        |

For more admin service options or troubleshooting, please contact **[Direktt support](https://direktt.com/contact-support/)**.