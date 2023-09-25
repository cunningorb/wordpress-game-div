<?php
/*
Plugin Name: Game Embedder
Description: Extend is_user_logged_in with custom conditions and configure role names.
Version: 1.0
Author: cunningorb
*/

// Activation hook to create the restricted directory
register_activation_hook(__FILE__, 'custom_logged_in_check_activate');

function custom_logged_in_check_activate() {
    // Get the plugin directory path
    $plugin_dir = plugin_dir_path(__FILE__);

    // Create the restricted directory if it doesn't exist
    $restricted_dir = $plugin_dir . 'restricted-content';

    if (!is_dir($restricted_dir)) {
        mkdir($restricted_dir, 0755, true);
    }
}

// Add an options page to the WordPress admin menu
function custom_logged_in_check_menu() {
    add_options_page(
        'Custom Logged-In Check Settings',
        'Custom Logged-In Check',
        'manage_options',
        'custom-logged-in-check',
        'custom_logged_in_check_options_page'
    );
}
add_action('admin_menu', 'custom_logged_in_check_menu');

// Create the options page
function custom_logged_in_check_options_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    // Save settings if the form is submitted
    if (isset($_POST['submit'])) {
        $role_name = sanitize_text_field($_POST['role_name']);
        update_option('custom_logged_in_role', $role_name);
        echo '<div class="updated"><p>Role name updated.</p></div>';
    }

    // Display the settings form
    $current_role = get_option('custom_logged_in_role', 'editor'); // Default role name
    ?>
    <div class="wrap">
        <h2>Custom Logged-In Check Settings</h2>
        <form method="post">
            <label for="role_name">Custom Role Name:</label>
            <input type="text" id="role_name" name="role_name" value="<?php echo esc_attr($current_role); ?>" />
            <p class="description">Enter the role name for custom login checks.</p>
            <input type="submit" name="submit" class="button button-primary" value="Save Changes" />
        </form>
    </div>
    <?php
}

// Create the custom logged-in function
function is_user_logged_in_custom() {
    // Get the configured custom role name from the options
    $custom_role_name = get_option('custom_logged_in_role', 'editor'); // Default role name

    // Check if a user is logged in and has the custom role
    return current_user_can($custom_role_name);
}

// Create the plugin shortcode
function custom_logged_in_shortcode($atts, $content = null) {
    if (is_user_logged_in_custom()) {
        return do_shortcode($content);
    } else {
        return '<p>You must be logged in with the custom role to view this content.</p>';
    }
}
add_shortcode('custom_logged_in', 'custom_logged_in_shortcode');
