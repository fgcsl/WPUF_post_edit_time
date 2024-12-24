<?php
/*
Plugin Name: Custom Increase Post Edit Time
Description: This plugin was developed to increase the editing time for user posts after the WPUF allotted time expires.


Version: 1.0
Author: Abhishek Khatri
*/

// Step 1: Add the menu to the admin dashboard
function custom_post_edit_time_menu() {
    add_menu_page(
        'Increase Post Edit Time',         // Page title
        'Post Edit Time',                  // Menu title
        'manage_options',                  // Capability
        'post-edit-time',                  // Menu slug
        'render_post_edit_time_page',      // Callback function
        'dashicons-clock',                 // Icon
        80                                 // Position
    );
}
add_action('admin_menu', 'custom_post_edit_time_menu');

// Step 2: Render the page
function render_post_edit_time_page() {
    ?>
    <div class="wrap">
        <h1>Increase Post Editing Time</h1>
        <h1>Increase Post Editing Time</h1>
        <p><em>This plugin was developed by Abhishek Khatri to increase the editing time for user posts after the WPUF allotted time expires.</em></p>
        <form method="post" action="">
            <label for="user">Select User:</label>
            <select name="user" id="user" required>
                 <option value="">-- Select User --</option>
                <?php
                // Fetch all users
                $users = get_users();
                foreach ($users as $user) {
                    echo '<option value="' . $user->ID . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_login) . ')</option>';
                }
                ?>
            </select>
            <br><br>

            <label for="time_period">Time Period (Days):</label>
            <input type="number" name="time_period" id="time_period" min="1" max="90" required>
            <small>Enter a value between 1 and 90.</small>
            <br><br>

            <button type="submit" name="update_time" class="button button-primary">Increase Editing Time</button>
        </form>
        <br>
        <p><strong>Plugin Path:</strong> wp-content/plugins/custom-post-edit-time/custom-post-edit-time.php</p>
    </div>
    <?php

    // Step 3: Handle form submission
    if (isset($_POST['update_time']) && isset($_POST['user'])) {
        $user_id = intval($_POST['user']);
        $time_period = intval($_POST['time_period']);

        // Validate form inputs
        if (empty($user_id) || empty($time_period)) {
            echo '<div class="error"><p>Both fields are required.</p></div>';
            return;
        }

        if ($time_period < 1 || $time_period > 90) {
            echo '<div class="error"><p>Time Period must be between 1 and 90 days.</p></div>';
            return;
        }


        // Fetch posts by the selected user
        global $wpdb;
        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT pm.post_id 
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_wpuf_lock_user_editing_post_time' 
              AND p.post_author = %d",
            $user_id
        ));

        // Update editing time for each post
        if ($posts) {
            $new_time = time() + ($time_period * 24 * 60 * 60); // Convert days to seconds
            foreach ($posts as $post) {
                $wpdb->update(
                    $wpdb->postmeta,
                    array('meta_value' => $new_time),
                    array('meta_key' => '_wpuf_lock_user_editing_post_time', 'post_id' => $post->post_id),
                    array('%d'),
                    array('%s', '%d')
                );
            }
            echo '<div class="updated"><p>Editing time updated for the selected user\'s posts by ' . $time_period . ' days.</p></div>';
        } else {
            echo '<div class="error"><p>No posts found for the selected user.</p></div>';
        }
    }
}
?>
