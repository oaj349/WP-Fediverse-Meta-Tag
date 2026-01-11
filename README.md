# Fediverse Meta Tag

The "Fediverse Meta Tag" plugin adds a custom meta tag `fediverse:creator` to posts and pages on your WordPress blog. It allows you to associate a Fediverse user identifier (e.g., Mastodon) with a specific post or page, making it easier for other platforms to identify the content creator within the Fediverse social networks.

The first site that uses the plugin is [fossgralnia.pl](https://fossgralnia.pl) - on it you can see how it works!

## Features

- Adds a metadata field to the post editor (and optionally the page editor) for entering the `fediverse:creator` tag.
- Automatically uses the handle mapped to the post author's WordPress account (when defined in settings); if no handle exists, the meta tag is omitted.
- For other post types, assigns a static `fediverse:creator` tag.
- Configure fallback Fediverse handles for any WordPress user from **Settings > Fediverse Meta Tag** without editing code.

![](image.png)

## Installation

> [!WARNING]
> Before installing the plugin, make sure you have a backup of your WordPress site. I am not responsible for any data loss or other issues that may arise from using this plugin.

**Downloading**: Download the newest version from [releases](https://github.com/MStankiewiczOfficial/WP-Fediverse-Meta-Tag/releases/latest).

> [!IMPORTANT]
> After activating the plugin, head to **Settings > Fediverse Meta Tag** to assign Fediverse handles to your WordPress users. There is no need to edit the PHP file manually anymore.

### Option 1. **Uploading via WP Dashboard**:

1. Pack the file as a zip.
2. Log in to your WordPress admin panel.
3. Go to **Plugins** > **Add New**.
4. Click **Upload Plugin**.
5. Choose the zip file from your computer and click **Install Now**.

### Option 2. **Manual Upload**:

1. Download the plugin file `fediverse-meta-tag.php`.
2. Upload the file to the `wp-content/plugins/` directory on your server.
3. Log in to your WordPress admin panel.

After uploading, go to the **Plugins** section and activate "Fediverse Meta Tag".

## Usage

The extension automatically adds the `fediverse:creator` tag with the author to the page header for each post or page (according to the settings before installation). To modify the tag for a specific entry only, follow these steps:

1. Navigate to the post or (if enabled) page editor.
2. You will see a new field "Fediverse Creator Tag" in the metabox on the right side.
3. Enter the Fediverse username (e.g., `user@mastodon.social`).
4. After saving, the meta tag will only appear if this field or the author mapping contains a handle.

## Assigning handles to users

1. Navigate to **Settings > Fediverse Meta Tag** in the WordPress admin panel.
2. Use the dropdown to pick a WordPress user and enter their Fediverse handle next to it.
3. Click **Add user** to create additional rows and map as many users as you need.
4. Save the changes. These handles will now be used automatically whenever a post author has no per-post tag defined.

## Customization

- Open **Settings > Fediverse Meta Tag** to manage as many user-to-handle mappings as you need.
- Use the checkbox on the same settings screen to decide whether the plugin should add the meta box and meta tag to pages.
- If neither the per-entry field nor a mapped handle is present, the plugin intentionally skips adding the `fediverse:creator` meta tag, ensuring you never expose placeholder values.