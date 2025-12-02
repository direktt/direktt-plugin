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
- **Generate** via the Direktt Web Console or WordPress API.
- **Automate actions** when scanned - add items to cart, validate passes, trigger loyalty updates, etc.
- **Tie QR actions** directly to your WordPress REST API for unlimited extensions.

**Examples:**
- Add a product to a shopping cart.
- Validate an event ticket.
- Redeem a personalized coupon.
- Trigger instant loyalty updates.

## How You Can Extend Direktt as a WordPress Developer

Direktt is designed for developers: leverage familiar WordPress paradigms with new mobile power.

### Extend & Integrate by:

1. **Adding Digital Services:**
    - Configure custom service menu links in the Admin Console.
    - Serve member-only pages, apps, offers, and automations - Direktt auto-authenticates users.

2. **Enhancing User Profiles:**
    - Add custom admin tools or interfaces to the profile area.
    - Enable real-time updates for points, tickets, orders, etc.

3. **Hooking Into Message Flow:**
    - Intercept/respond to messages (for AI/chatbots, automations, etc.).
    - Build conversational triggers for advanced engagement.

4. **Sending Interactive Messages:**
    - Use the Direktt API to deliver messages with buttons or interactive elements (booking, voting, purchases, etc.).
    - All interactions are securely routed to your WordPress site using retVar and API payloads.

5. **Smart QR Interactions:**
    - Generate custom QR codes linked to REST API actions.
    - Automate any workflow (redemptions, access, validation, etc.) with a quick scan.