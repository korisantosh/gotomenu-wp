<?php

// Register settings
function gtmsk_register_settings() {
    add_option('gtmsk_enable_frontend', '0');
    add_option('gtmsk_enable_backend', '0');

    register_setting('gtmsk_options_group', 'gtmsk_enable_frontend', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '0'
    ));

    register_setting('gtmsk_options_group', 'gtmsk_enable_backend', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '0'
    ));
}
add_action('admin_init', 'gtmsk_register_settings');

// Create settings page
function gtmsk_register_options_page() {
    add_options_page(
        __('GoToMenu Settings', 'gotomenu'),
        __('GoToMenu', 'gotomenu'),
        'manage_options',
        'gtmsk',
        'gtmsk_options_page'
    );
}
add_action('admin_menu', 'gtmsk_register_options_page');

// Display settings page
function gtmsk_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify the nonce before processing the form
        if (isset($_POST['gtmsk_settings_nonce']) && check_admin_referer('gtmsk_settings_action', 'gtmsk_settings_nonce')) {
            // Handle the form submission and save settings securely here.
            $gtmsk_enable_frontend = isset($_POST['gtmsk_enable_frontend']) ? 1 : 0;
            $gtmsk_enable_backend = isset($_POST['gtmsk_enable_backend']) ? 1 : 0;
            // Save the settings
            update_option('gtmsk_enable_frontend', $gtmsk_enable_frontend);
            update_option('gtmsk_enable_backend', $gtmsk_enable_backend);

            // Success message
            echo '<div class="updated"><p>' . esc_html__('Settings saved successfully.', 'gotomenu') . '</p></div>';
        } else {
            // If nonce verification fails, display an error message
            wp_die(esc_html__('Security check failed. Please try again.', 'gotomenu'));
        }
    }
?>
    <div class="wrap">
    <h2><?php esc_html_e('GoToMenu Settings', 'gotomenu'); ?></h2>
        <form method="post" action="options.php">
        <?php wp_nonce_field('gtmsk_settings_action', 'gtmsk_settings_nonce'); ?>
            <?php settings_fields('gtmsk_options_group'); ?>
            <?php do_settings_sections('gtmsk'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Enable for Frontend', 'gotomenu'); ?></th>
                    <td>
                        <input type="checkbox" id="gtmsk_enable_frontend" name="gtmsk_enable_frontend" value="1" <?php checked('1', get_option('gtmsk_enable_frontend')); ?> />
                        <label for="gtmsk_enable_frontend"><?php esc_html_e('Yes', 'gotomenu'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Enable for Backend Admin Area', 'gotomenu'); ?></th>
                    <td>
                        <input type="checkbox" id="gtmsk_enable_backend" name="gtmsk_enable_backend" value="1" <?php checked('1', get_option('gtmsk_enable_backend')); ?> />
                        <label for="gtmsk_enable_backend"><?php esc_html_e('Yes', 'gotomenu'); ?></label>
                    </td>
                </tr>
            </table>
            <div class="notice inline notice-info notice-alt">
                <p><span class="dashicons dashicons-info"></span> <?php esc_html_e('Press F2 to use GoToMenu and search and select menu from lists to redirect.', 'gotomenu'); ?></p>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}