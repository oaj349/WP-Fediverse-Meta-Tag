# Fediverse Meta Tag

The "Fediverse Meta Tag" plugin adds a custom meta tag `fediverse:creator` to posts on your WordPress blog. It allows you to associate a Fediverse user identifier (e.g., Mastodon) with a specific post, making it easier for other platforms to identify the content creator within the Fediverse social networks.

The first site that uses the plugin is [fossgralnia.pl](https://fossgralnia.pl) - on it you can see how it works!

## Features

- Adds a metadata field to the post editor for entering the `fediverse:creator` tag.
- Automatically assigns a default tag for posts based on the author's username if the field is empty.
- For pages and other post types, assigns a static `fediverse:creator` tag.

![](image.png)

## Installation

> [!WARNING]
> Before installing the plugin, make sure you have a backup of your WordPress site. I am not responsible for any data loss or other issues that may arise from using this plugin.

**Downloading**: Download the newest version from [releases](https://github.com/MStankiewiczOfficial/WP-Fediverse-Meta-Tag/releases/latest).

> [!IMPORTANT]
> Before uploading the plugin to the server, open the file `fediverse-meta-tag.php` in text editor and customize the appropriate Fediverse handles for users (change lines 51-54 for users and 56 and 61 for the default handles).

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

The extension automatically adds the `fediverse:creator` tag with the author to the page header for each post (according to the settings before installation). To modify the tag for a specific file only, follow these steps:

1. Navigate to the post editor.
2. You will see a new field "Fediverse Creator Tag" in the metabox on the right side.
3. Enter the Fediverse username (e.g., `user@mastodon.social`).
4. After saving the post, the meta tag will be added to the page header.

## Customization

You can customize the default Fediverse handles assigned to specific users in the plugin code. Change `user1@example.com` and `user2@example.com` to the appropriate Fediverse handles, and also set the default handles for posts.
