<?php
/*
Plugin Name: Auto Share to Social Media
Description: Automatically share posts sequentially to Facebook, Telegram, Instagram, and Threads (placeholder).
Version: 4.0
Author: Your Name
*/

// ======================
// 🔹 Activation & Deactivation
// ======================

/**
 * Run on plugin activation: schedule social media shares.
 */
register_activation_hook(__FILE__, 'asft_activate');
function asft_activate() {
    asft_schedule_shares();
}

/**
 * Run on plugin deactivation: clear all scheduled events.
 */
register_deactivation_hook(__FILE__, 'asft_deactivate');
function asft_deactivate() {
    asft_clear_schedules();
}

// ======================
// 🔹 Cron Scheduling
// ======================

/**
 * Clear all existing scheduled cron hooks.
 */
function asft_clear_schedules() {
    for ($i = 0; $i < 10; $i++) {
        $hook = 'asft_share_event_' . $i;
        wp_clear_scheduled_hook($hook);
    }
}

/**
 * Schedule daily post shares (1–10/day, evenly spaced starting at 6 AM).
 */
function asft_schedule_shares() {
    asft_clear_schedules();
    $count = (int) get_option('asft_daily_count', 1);
    if ($count < 1) $count = 1;
    if ($count > 10) $count = 10;

    $base_time = strtotime(date('Y-m-d 06:00:00')); // start at 6 AM
    $interval = (24 / $count) * HOUR_IN_SECONDS;

    for ($i = 0; $i < $count; $i++) {
        $time = $base_time + ($i * $interval);
        if ($time < time()) {
            $time += DAY_IN_SECONDS; // shift to next day if already passed
        }
        $hook = 'asft_share_event_' . $i;
        if (!wp_next_scheduled($hook)) {
            wp_schedule_event($time, 'daily', $hook);
        }
        add_action($hook, 'asft_share_next_post');
    }
}

/**
 * Share the next sequential post from the blog.
 */
function asft_share_next_post() {
    $last_shared = (int) get_option('asft_last_shared', 0);

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'offset'         => $last_shared,
        'fields'         => 'ids'
    ];
    $posts = get_posts($args);

    if ($posts) {
        $post_id = $posts[0];
        asft_do_share($post_id);
        update_option('asft_last_shared', $last_shared + 1);
    }
}

// ======================
// 🔹 Sharing Logic
// ======================

/**
 * Share a given post to all configured platforms.
 *
 * @param int $post_id Post ID to share
 */
function asft_do_share($post_id) {
    $title = get_the_title($post_id);
    $link  = get_permalink($post_id);

    // ✅ Facebook
    $fb_page_id = get_option('asft_facebook_page_id');
    $fb_token   = get_option('asft_facebook_token');
    if ($fb_page_id && $fb_token) {
        $url = "https://graph.facebook.com/{$fb_page_id}/feed";
        wp_remote_post($url, [
            'body' => [
                'message'     => $title . ' ' . $link,
                'access_token'=> $fb_token
            ]
        ]);
    }

    // ✅ Telegram
    $tg_bot = get_option('asft_telegram_bot');
    $tg_chat = get_option('asft_telegram_chat');
    if ($tg_bot && $tg_chat) {
        $url = "https://api.telegram.org/bot{$tg_bot}/sendMessage";
        wp_remote_post($url, [
            'body' => [
                'chat_id' => $tg_chat,
                'text'    => $title . "\n" . $link
            ]
        ]);
    }

    // ✅ Instagram
    $ig_id = get_option('asft_instagram_id');
    $ig_token = get_option('asft_instagram_token');
    if ($ig_id && $ig_token) {
        $caption = $title . ' ' . $link;
        $create_url = "https://graph.facebook.com/v20.0/{$ig_id}/media";
        $publish_url = "https://graph.facebook.com/v20.0/{$ig_id}/media_publish";

        // Step 1: create container
        $resp = wp_remote_post($create_url, [
            'body' => [
                'caption'      => $caption,
                'image_url'    => 'https://via.placeholder.com/600x400.png?text=Law+Learning', // replace if needed
                'access_token' => $ig_token
            ]
        ]);

        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if (!empty($body['id'])) {
            wp_remote_post($publish_url, [
                'body' => [
                    'creation_id'  => $body['id'],
                    'access_token' => $ig_token
                ]
            ]);
        }
    }

    // ✅ Threads (placeholder)
    // Currently no API available; future expansion possible.

    // ✅ Log shared post
    asft_log_share($post_id, ['facebook','telegram','instagram','threads']);
}

// ======================
// 🔹 Logging
// ======================

/**
 * Save a log entry when a post is shared.
 *
 * @param int   $post_id   Shared post ID
 * @param array $platforms Platforms it was shared to
 */
function asft_log_share($post_id, $platforms) {
    $logs = get_option('asft_share_logs', []);
    if (!is_array($logs)) $logs = [];

    $logs[] = [
        'time'      => current_time('mysql'),
        'post_id'   => $post_id,
        'title'     => get_the_title($post_id),
        'platforms' => $platforms
    ];

    if (count($logs) > 50) {
        $logs = array_slice($logs, -50); // keep last 50 logs
    }

    update_option('asft_share_logs', $logs);
}

// ======================
// 🔹 Admin Menu & Pages
// ======================

/**
 * Add admin menu for plugin settings and logs.
 */
add_action('admin_menu', 'asft_admin_menu');
function asft_admin_menu() {
    add_menu_page('Auto Share', 'Auto Share', 'manage_options', 'asft-settings', 'asft_settings_page');
    add_submenu_page('asft-settings', 'Shared Log', 'Shared Log', 'manage_options', 'asft-log', 'asft_log_page');
}

/**
 * Render plugin settings page.
 */
function asft_settings_page() {
    if (isset($_POST['asft_save'])) {
        update_option('asft_daily_count', intval($_POST['asft_daily_count']));
        update_option('asft_facebook_page_id', sanitize_text_field($_POST['asft_facebook_page_id']));
        update_option('asft_facebook_token', sanitize_text_field($_POST['asft_facebook_token']));
        update_option('asft_telegram_bot', sanitize_text_field($_POST['asft_telegram_bot']));
        update_option('asft_telegram_chat', sanitize_text_field($_POST['asft_telegram_chat']));
        update_option('asft_instagram_id', sanitize_text_field($_POST['asft_instagram_id']));
        update_option('asft_instagram_token', sanitize_text_field($_POST['asft_instagram_token']));
        update_option('asft_threads_placeholder', sanitize_text_field($_POST['asft_threads_placeholder']));

        asft_schedule_shares();
        echo '<div class="updated"><p>Settings saved & schedule updated.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Auto Share Settings</h1>
        <form method="post">
            <label>Daily Share Count (1–10):</label><br>
            <input type="number" name="asft_daily_count" value="<?php echo esc_attr(get_option('asft_daily_count',1)); ?>" min="1" max="10"><br><br>

            <h2>Facebook</h2>
            <label>Page ID:</label><br>
            <input type="text" name="asft_facebook_page_id" value="<?php echo esc_attr(get_option('asft_facebook_page_id')); ?>"><br>
            <label>Access Token:</label><br>
            <input type="text" name="asft_facebook_token" value="<?php echo esc_attr(get_option('asft_facebook_token')); ?>"><br><br>

            <h2>Telegram</h2>
            <label>Bot Token:</label><br>
            <input type="text" name="asft_telegram_bot" value="<?php echo esc_attr(get_option('asft_telegram_bot')); ?>"><br>
            <label>Channel/Group ID:</label><br>
            <input type="text" name="asft_telegram_chat" value="<?php echo esc_attr(get_option('asft_telegram_chat')); ?>"><br><br>

            <h2>Instagram</h2>
            <label>Business User ID:</label><br>
            <input type="text" name="asft_instagram_id" value="<?php echo esc_attr(get_option('asft_instagram_id')); ?>"><br>
            <label>Access Token:</label><br>
            <input type="text" name="asft_instagram_token" value="<?php echo esc_attr(get_option('asft_instagram_token')); ?>"><br><br>

            <h2>Threads (Placeholder)</h2>
            <label>Reserved Field:</label><br>
            <input type="text" name="asft_threads_placeholder" value="<?php echo esc_attr(get_option('asft_threads_placeholder')); ?>"><br><br>

            <input type="submit" name="asft_save" value="Save Settings" class="button-primary">
        </form>
    </div>
    <?php
}

/**
 * Render shared log page in admin.
 */
function asft_log_page() {
    $logs = get_option('asft_share_logs', []);
    ?>
    <div class="wrap">
        <h1>Shared Posts Log</h1>
        <table class="widefat">
            <thead>
                <tr><th>Time</th><th>Post</th><th>Platforms</th></tr>
            </thead>
            <tbody>
                <?php if ($logs): foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log['time']); ?></td>
                        <td><a href="<?php echo get_permalink($log['post_id']); ?>" target="_blank"><?php echo esc_html($log['title']); ?></a></td>
                        <td><?php echo esc_html(implode(', ', $log['platforms'])); ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3">No logs yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
