<?php
/*
Plugin Name: Fediverse Meta Tag
Description: Adds a custom “fediverse:creator” metatag.
Version: 2.0.1
Author: Michał Stankiewicz
Author URI: https://www.stankiewiczm.eu
*/

function add_fediverse_creator_meta_box() {
    $post_types = ['post'];

    if (fediverse_meta_tag_pages_enabled()) {
        $post_types[] = 'page';
    }

    foreach ($post_types as $screen) {
        add_meta_box(
            'fediverse_creator_meta_box',
            'Fediverse Creator Tag',
            'fediverse_creator_meta_box_callback',
            $screen,
            'side'
        );
    }
}
add_action('add_meta_boxes', 'add_fediverse_creator_meta_box');

function fediverse_creator_meta_box_callback($post) {
    $fediverse_creator = get_post_meta($post->ID, '_fediverse_creator', true);
    ?>
    <label for="fediverse_creator_field">Fediverse Creator Tag:</label>
    <input type="text" id="fediverse_creator_field" name="fediverse_creator_field" value="<?php echo esc_attr($fediverse_creator); ?>" placeholder="user@example.com" style="width:100%;" />
    <?php
}

function save_fediverse_creator_meta_box_data($post_id) {
    if (array_key_exists('fediverse_creator_field', $_POST)) {
        update_post_meta(
            $post_id,
            '_fediverse_creator',
            sanitize_text_field(wp_unslash($_POST['fediverse_creator_field']))
        );
    }
}
add_action('save_post', 'save_fediverse_creator_meta_box_data');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'fediverse_meta_tag_settings_link');
function fediverse_meta_tag_settings_link($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=fediverse-meta-tag')) . '">' . esc_html__('Settings', 'fediverse-meta-tag') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_action('admin_menu', 'fediverse_meta_tag_register_settings_page');
function fediverse_meta_tag_register_settings_page() {
    add_options_page(
        __('Fediverse Meta Tag', 'fediverse-meta-tag'),
        __('Fediverse Meta Tag', 'fediverse-meta-tag'),
        'manage_options',
        'fediverse-meta-tag',
        'fediverse_meta_tag_render_settings_page'
    );
}

function fediverse_meta_tag_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $users = get_users([
        'orderby' => 'display_name',
        'order'   => 'ASC',
        'fields'  => ['ID', 'display_name', 'user_login'],
    ]);

    $user_map       = fediverse_meta_tag_get_user_handle_map();
    $pages_enabled  = fediverse_meta_tag_pages_enabled();

    if (isset($_POST['fediverse_meta_tag_submit'])) {
        check_admin_referer('fediverse_meta_tag_save_settings');

        $user_ids_raw    = isset($_POST['fediverse_user_ids']) ? (array) wp_unslash($_POST['fediverse_user_ids']) : [];
        $handles_raw     = isset($_POST['fediverse_user_handles']) ? (array) wp_unslash($_POST['fediverse_user_handles']) : [];
        $new_map         = [];
        $pages_enabled   = isset($_POST['fediverse_enable_pages']) ? 1 : 0;

        foreach ($user_ids_raw as $index => $user_id_raw) {
            $user_id = absint($user_id_raw);
            $handle  = isset($handles_raw[$index]) ? sanitize_text_field($handles_raw[$index]) : '';

            if ($user_id && !empty($handle)) {
                $new_map[$user_id] = $handle;
            }
        }

        update_option('fediverse_user_handles', $new_map);
        update_option('fediverse_enable_pages', $pages_enabled);
        $user_map = $new_map;

        add_settings_error(
            'fediverse_meta_tag_messages',
            'fediverse_meta_tag_message',
            __('Settings saved.', 'fediverse-meta-tag'),
            'updated'
        );
    }

    settings_errors('fediverse_meta_tag_messages');

    $rows = [];
    if (!empty($user_map)) {
        foreach ($user_map as $user_id => $handle) {
            $rows[] = [
                'user_id' => $user_id,
                'handle'  => $handle,
            ];
        }
    }

    if (empty($rows)) {
        $rows[] = [
            'user_id' => '',
            'handle'  => '',
        ];
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Fediverse Meta Tag', 'fediverse-meta-tag'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('fediverse_meta_tag_save_settings'); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Fallback user handles', 'fediverse-meta-tag'); ?></th>
                        <td>
                            <div id="fediverse-user-rows">
                                <?php foreach ($rows as $row) : ?>
                                    <div class="fediverse-user-row">
                                        <select name="fediverse_user_ids[]" class="fediverse-user-select">
                                            <?php echo fediverse_meta_tag_render_user_options($users, (int) $row['user_id']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        </select>
                                        <input type="text" name="fediverse_user_handles[]" value="<?php echo esc_attr($row['handle']); ?>" placeholder="@user@example.com" class="regular-text" />
                                        <button type="button" class="button fediverse-remove-row"><?php esc_html_e('Remove', 'fediverse-meta-tag'); ?></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <p>
                                <button type="button" class="button button-secondary" id="fediverse-add-row"><?php esc_html_e('Add user', 'fediverse-meta-tag'); ?></button>
                            </p>
                            <p class="description"><?php esc_html_e('Select a WordPress user and assign the default Fediverse handle that should be inserted when no per-post handle is provided.', 'fediverse-meta-tag'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Apply to pages', 'fediverse-meta-tag'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="fediverse_enable_pages" value="1" <?php checked($pages_enabled); ?> />
                                <?php esc_html_e('Add the metabox and meta tag support to pages.', 'fediverse-meta-tag'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Uncheck if you only want the Fediverse tag on posts.', 'fediverse-meta-tag'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" name="fediverse_meta_tag_submit" class="button button-primary"><?php esc_html_e('Save Changes', 'fediverse-meta-tag'); ?></button>
            </p>
        </form>
    </div>
    <template id="fediverse-user-row-template">
        <div class="fediverse-user-row">
            <select name="fediverse_user_ids[]" class="fediverse-user-select">
                <?php echo fediverse_meta_tag_render_user_options($users); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </select>
            <input type="text" name="fediverse_user_handles[]" value="" placeholder="user@example.com" class="regular-text" />
            <button type="button" class="button fediverse-remove-row"><?php esc_html_e('Remove', 'fediverse-meta-tag'); ?></button>
        </div>
    </template>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('fediverse-user-rows');
        const template = document.getElementById('fediverse-user-row-template');
        const addButton = document.getElementById('fediverse-add-row');

        if (addButton && template && container) {
            addButton.addEventListener('click', function(event) {
                event.preventDefault();
                const clone = template.content.cloneNode(true);
                container.appendChild(clone);
            });
        }

        if (container) {
            container.addEventListener('click', function(event) {
                if (event.target && event.target.classList.contains('fediverse-remove-row')) {
                    event.preventDefault();
                    const row = event.target.closest('.fediverse-user-row');
                    if (row) {
                        row.remove();
                    }
                }
            });
        }
    });
    </script>
    <style>
    .fediverse-user-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 8px;
    }

    .fediverse-user-select {
        min-width: 220px;
    }
    </style>
    <?php
}

function fediverse_meta_tag_render_user_options($users, $selected_user_id = 0) {
    $options = '<option value="">' . esc_html__('Select a user', 'fediverse-meta-tag') . '</option>';

    foreach ($users as $user) {
        $label = sprintf('%s (%s)', $user->display_name, $user->user_login);
        $options .= sprintf(
            '<option value="%d" %s>%s</option>',
            (int) $user->ID,
            selected($selected_user_id, $user->ID, false),
            esc_html($label)
        );
    }

    return $options;
}

function fediverse_meta_tag_get_user_handle_map() {
    $handles = get_option('fediverse_user_handles', []);

    if (!is_array($handles)) {
        return [];
    }

    $clean_handles = [];
    foreach ($handles as $user_id => $handle) {
        $user_id = absint($user_id);
        $handle  = sanitize_text_field($handle);

        if ($user_id && !empty($handle)) {
            $clean_handles[$user_id] = $handle;
        }
    }

    return $clean_handles;
}

function fediverse_meta_tag_pages_enabled() {
    return (bool) get_option('fediverse_enable_pages', 1);
}

function fediverse_meta_tag_get_creator_for_current_view() {
    global $post;

    $fediverse_creator = '';
    $pages_enabled     = fediverse_meta_tag_pages_enabled();

    if ($post instanceof WP_Post) {
        if (is_page()) {
            if ($pages_enabled) {
                $fediverse_creator = get_post_meta($post->ID, '_fediverse_creator', true);

                if (empty($fediverse_creator)) {
                    $author_id       = (int) $post->post_author;
                    $user_handles    = fediverse_meta_tag_get_user_handle_map();
                    $fediverse_creator = isset($user_handles[$author_id]) ? $user_handles[$author_id] : '';
                }
            }
        } elseif (is_single()) {
            $fediverse_creator = get_post_meta($post->ID, '_fediverse_creator', true);

            if (empty($fediverse_creator)) {
                $author_id       = (int) $post->post_author;
                $user_handles    = fediverse_meta_tag_get_user_handle_map();
                $fediverse_creator = isset($user_handles[$author_id]) ? $user_handles[$author_id] : '';
            }
        }
    }

    return $fediverse_creator;
}

function add_fediverse_creator_meta_tag() {
    $fediverse_creator = fediverse_meta_tag_get_creator_for_current_view();

    if (!empty($fediverse_creator)) {
        echo '<meta name="fediverse:creator" content="' . esc_attr($fediverse_creator) . '">';
    }
}
add_action('wp_head', 'add_fediverse_creator_meta_tag');

function fediverse_meta_tag_start_buffer() {
    if (is_admin() || is_feed() || is_robots() || is_trackback()) {
        return;
    }

    ob_start('fediverse_meta_tag_filter_output');
}
add_action('template_redirect', 'fediverse_meta_tag_start_buffer', 0);

function fediverse_meta_tag_filter_output($html) {
    if (stripos($html, 'fediverse:creator') === false) {
        return $html;
    }

    $fediverse_creator = fediverse_meta_tag_get_creator_for_current_view();

    $clean_html = preg_replace("/<meta\s+[^>]*name\s*=\s*[\"']fediverse:creator[\"'][^>]*>/i", '', $html);

    if (empty($fediverse_creator)) {
        return $clean_html;
    }

    $meta_tag = "\n<meta name=\"fediverse:creator\" content=\"" . esc_attr($fediverse_creator) . "\">\n";

    $head_pos = stripos($clean_html, '</head>');
    if ($head_pos !== false) {
        return substr_replace($clean_html, $meta_tag, $head_pos, 0);
    }

    return $clean_html . $meta_tag;
}
