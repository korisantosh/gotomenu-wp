<?php

/**
 * Plugin Name: Go To Menu - Quick Go to Menu Navigator
 * Plugin URI: https://www.santoshkori.com/gotomenu-wordpress/
 * Description: GoToMenu - Quick Go to Menu Navigator is tool that boosts efficiency by offering rapid access to any registered menu. A simple F2 keypress opens a search box, letting users quickly find and open their desired menu. Save time and streamline workflow.
 * Version: 1.0.0
 * Author: Santosh Kori
 * Author URI: http://santoshkori.com
 * License: GPLv2 or later
 * Text Domain: gotomenu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// Include settings page
require_once plugin_dir_path(__FILE__) . 'includes/gotomenu-settings.php';

// Enqueue scripts and styles
function gtmsk_enqueue_scripts()
{
    if (!is_admin() && get_option('gtmsk_enable_frontend') === '1') {
        // Get file modification time for cache busting
        $css_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/gotomenu.css');
        $js_version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/gotomenu.js');

        wp_enqueue_style('gtmsk-style', plugin_dir_url(__FILE__) . 'assets/css/gotomenu.css', array(), $css_version);
        wp_enqueue_script('gtmsk-script', plugin_dir_url(__FILE__) . 'assets/js/gotomenu.js', array('jquery'), $js_version, true);

        // Localize script to pass PHP data to JavaScript securely
        wp_localize_script('gtmsk-script', 'gotomenuData', array(
            'menus' => gtmsk_menus()
        ));
    }
}
add_action('wp_enqueue_scripts', 'gtmsk_enqueue_scripts');

// Enqueue scripts and styles for admin
function gtmsk_admin_enqueue_scripts()
{
    if (is_admin() && get_option('gtmsk_enable_backend') === '1') {
        // Get file modification time for cache busting
        $css_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/gotomenu.css');
        $js_version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/gotomenu.js');

        wp_enqueue_style('gtmsk-admin-style', plugin_dir_url(__FILE__) . 'assets/css/gotomenu.css', array(), $css_version);
        wp_enqueue_script('gtmsk-admin-script', plugin_dir_url(__FILE__) . 'assets/js/gotomenu.js', array('jquery'), $js_version, true);

        // Localize script to pass PHP data to JavaScript securely
        wp_localize_script('gtmsk-admin-script', 'gotomenuData', array(
            'menus' => gtmsk_admin_menus()
        ));
    }
}
add_action('admin_enqueue_scripts', 'gtmsk_admin_enqueue_scripts');

// Function to get available menus
function gtmsk_menus()
{
    $menu_items = array();
    $wp_menus = wp_get_nav_menus();
    foreach ($wp_menus as $menu) {
        $items = wp_get_nav_menu_items($menu->term_id);
        if ($items) {
            foreach ($items as $item) {
                $menu_items[] = array(
                    'title' => esc_html($item->title),
                    'url'   => esc_url($item->url)
                );
            }
        }
    }

    usort($menu_items, function ($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    // Remove specific item based on URL
    $menu_items = array_filter($menu_items, function ($item) {
        return $item['url'] !== esc_url(get_bloginfo('url'));
    });

    $homeItem = array(
        'title' => __('Home', 'gotomenu'),
        'url'  => esc_url(get_bloginfo('url'))
    );

    array_unshift($menu_items, $homeItem);
    return $menu_items;
}

/**
 * Get the menu title without HTML tags.
 *
 * @param string $menu_title The menu title.
 * @return string The menu title without HTML tags.
 */
function gtmsk_menu_title($menu_title)
{
    // Check if the string contains an opening HTML tag
    if (strpos($menu_title, '<') !== false) {
        // Find the position of the first `<` character
        $tag_start_pos = strpos($menu_title, '<');
        // Extract the text before the first tag
        $menu_text = substr($menu_title, 0, $tag_start_pos);
    } else {
        // If no HTML tag is found, the entire string is the plain text
        $menu_text = $menu_title;
    }
    return esc_html($menu_text);
}

/**
 * Get the menu icon
 *
 * @param string $menu_icon The menu icon.
 * @return string The menu icon .
 */
function gtmsk_menu_icon($menu_icon)
{
    // Check if the string contains an opening HTML tag
    if ($menu_icon === 'none') {
        $menu_text = 'dashicons-admin-generic';
    } else {
        $menu_text = $menu_icon;
    }
    return esc_attr($menu_text);
}

/**
 * Get the menu url
 *
 * @param string $menu_url The menu url.
 * @return string The menu url .
 */
function gtmsk_menu_url($menu)
{
    // Check if the string contains an opening HTML tag
    if (strpos($menu, '.php')) {
        $menu_url = admin_url($menu);
    } else {
        $menu_url = admin_url('admin.php?page=' . $menu);
    }
    return esc_url($menu_url);
}

// Function to get available menus
function gtmsk_admin_menus()
{
    global $menu;
    $admin_menu_items = array();

    foreach ($menu as $item) {
        if (isset($item[4]) && $item[4] !== 'wp-menu-separator') {
            $menu_title = gtmsk_menu_title($item[0]);
            $menu_icon = (isset($item[6])) ?  gtmsk_menu_icon($item[6]) : 'dashicons-admin-generic';

            $admin_menu_items[] = array(
                'title' => $menu_title,  // The menu title
                'url'   => gtmsk_menu_url($item[2]), // The URL to the admin page
                'icon'   => $menu_icon // The menu icon
            );
        }
    }

    usort($admin_menu_items, function ($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    $homeItem = array(
        'title' => __('Visit Site', 'gotomenu'),
        'url'  => esc_url(get_bloginfo('url')),
        'icon'   => 'dashicons-admin-site'
    );

    array_unshift($admin_menu_items, $homeItem);
    return $admin_menu_items;
}

// Add settings link on plugin page
function gtmsk_add_plugin_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=gtmsk') . '">' . __('Settings', 'gotomenu') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'gtmsk_add_plugin_link');

// Hook into plugin activation
register_activation_hook(__FILE__, 'gtmsk_plugin_activate');

function gtmsk_plugin_activate()
{
    // Set a transient to display the admin notice after activation
    set_transient('gtmsk_activation_notice', true, 5);
}

// Hook into the admin notices to display the activation message
add_action('admin_notices', 'gtmsk_activation_admin_notice');

function gtmsk_activation_admin_notice()
{
    // Check if the transient is set
    if (get_transient('gtmsk_activation_notice')) {
        // Display the admin notice
?>
        <div class="notice notice-success is-dismissible">
            <p><span class="dashicons dashicons-smiley"></span>
                <?php
                echo wp_kses_post('<strong>' . esc_html__('You activated Go to Menu Plugin.', 'gotomenu') . '</strong>');
                ?>

                <?php
                echo wp_kses_post(
                    sprintf(
                        /* translators: %1$s is the URL to the settings page */
                        __('Please visit the <a href="%1$s">settings page</a> to enable it for backend and frontend.', 'gotomenu'),
                        esc_url(admin_url('options-general.php?page=gtmsk'))
                    )
                );
                ?>
            </p>
        </div>
<?php
        delete_transient('gtmsk_activation_notice');
    }
}
