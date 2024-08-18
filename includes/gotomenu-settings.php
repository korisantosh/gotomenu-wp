<?php

// Register settings
function gotomenu_register_settings()
{
    add_option('gotomenu_enable_frontend', '0');
    register_setting('gotomenu_options_group', 'gotomenu_enable_frontend', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '0'
    ));
}
add_action('admin_init', 'gotomenu_register_settings');

// Create settings page
function gotomenu_register_options_page()
{
    add_options_page('GoToMenu Settings', 'GoToMenu', 'manage_options', 'gotomenu', 'gotomenu_options_page');
}
add_action('admin_menu', 'gotomenu_register_options_page');

// Display settings page
function gotomenu_options_page()
{
?>
    <div class="wrap">
        <h2><?php esc_html_e('GoToMenu Settings', 'gotomenu'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('gotomenu_options_group'); ?>
            <?php do_settings_sections('gotomenu'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Enable for Frontend', 'gotomenu'); ?></th>
                    <td>
                        <input type="checkbox" id="gotomenu_enable_frontend" name="gotomenu_enable_frontend" value="1" <?php checked('1', get_option('gotomenu_enable_frontend')); ?> />
                        <label for="gotomenu_enable_frontend"><?php esc_html_e('Yes', 'gotomenu'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Enable for Backend Admin Area', 'gotomenu'); ?></th>
                    <td>
                        <input type="checkbox" id="gotomenu_enable_frontend" name="gotomenu_enable_frontend" value="1" <?php checked('1', get_option('gotomenu_enable_backend')); ?> />
                        <label for="gotomenu_enable_frontend"><?php esc_html_e('Yes', 'gotomenu'); ?></label>
                    </td>
                </tr>
            </table>
            <div class="notice inline notice-info notice-alt">
                <p><span class="dashicons dashicons-info"></span> Press <strong>F2</strong> to use GoToMenu and instantly redirect to your selected page.</p>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
