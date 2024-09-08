<?php

// Register settings
function gotomenu_register_settings() {
    add_option('gotomenu_enable_frontend', '0');
    add_option('gotomenu_enable_backend', '0');

    register_setting('gotomenu_options_group', 'gotomenu_enable_frontend', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '0'
    ));

    register_setting('gotomenu_options_group', 'gotomenu_enable_backend', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '0'
    ));
}
add_action('admin_init', 'gotomenu_register_settings');

// Create settings page
function gotomenu_register_options_page() {
    add_options_page(
        __('GoToMenu Settings', 'gotomenu-skori'),
        __('GoToMenu', 'gotomenu-skori'),
        'manage_options',
        'gotomenu',
        'gotomenu_options_page'
    );
}
add_action('admin_menu', 'gotomenu_register_options_page');

// Display settings page
function gotomenu_options_page() {
?>
    <div class="wrap">
    <h2><?php esc_html_e('GoToMenu Settings', 'gotomenu-skori'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('gotomenu_options_group'); ?>
            <?php do_settings_sections('gotomenu'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Enable for Frontend', 'gotomenu-skori'); ?></th>
                    <td>
                        <input type="checkbox" id="gotomenu_enable_frontend" name="gotomenu_enable_frontend" value="1" <?php checked('1', get_option('gotomenu_enable_frontend')); ?> />
                        <label for="gotomenu_enable_frontend"><?php esc_html_e('Yes', 'gotomenu-skori'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Enable for Backend Admin Area', 'gotomenu-skori'); ?></th>
                    <td>
                        <input type="checkbox" id="gotomenu_enable_backend" name="gotomenu_enable_backend" value="1" <?php checked('1', get_option('gotomenu_enable_backend')); ?> />
                        <label for="gotomenu_enable_backend"><?php esc_html_e('Yes', 'gotomenu-skori'); ?></label>
                    </td>
                </tr>
            </table>
            <div class="notice inline notice-info notice-alt">
                <p><span class="dashicons dashicons-info"></span> <?php esc_html_e('Press F2 to use GoToMenu and search and select menu from lists to redirect.', 'gotomenu-skori'); ?></p>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
