<?php
/*
Plugin Name: GoToMenu
Description: Opens a modal with a dropdown list of available menus when F2 is pressed on the frontend.
Version: 1.0
Author: Your Name
License: GPLv2 or later
Text Domain: gotomenu
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// Include settings page
require_once plugin_dir_path(__FILE__) . 'includes/gotomenu-settings.php';
// Enqueue scripts and styles
function gotomenu_enqueue_scripts()
{
    if (get_option('gotomenu_enable_frontend') == '1') {
        // Get file modification time for cache busting
        $css_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/gotomenu.css');
        $js_version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/gotomenu.js');
        wp_enqueue_style('gotomenu-style', plugin_dir_url(__FILE__) . 'assets/css/gotomenu.css', array(), $css_version);
        wp_enqueue_script('gotomenu-script', plugin_dir_url(__FILE__) . 'assets/js/gotomenu.js', array('jquery'), $js_version, true);
        // Localize script to pass PHP data to JavaScript securely
        wp_localize_script('gotomenu-script', 'gotomenuData', array(
            'menus' => gotomenu_get_menus()
        ));
    }
}
add_action('wp_enqueue_scripts', 'gotomenu_enqueue_scripts');


function gotomenu_admin_enqueue_scripts()
{
    // Get file modification time for cache busting
    $css_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/gotomenu.css');
    $js_version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/gotomenu-admin.js');

    // Enqueue admin styles for all admin pages
    wp_enqueue_style('gotomenu-admin-style', plugin_dir_url(__FILE__) . 'assets/css/gotomenu.css', array(), $css_version);

    // Enqueue admin scripts for all admin pages
    wp_enqueue_script('gotomenu-admin-script', plugin_dir_url(__FILE__) . 'assets/js/gotomenu-admin.js', array('jquery'), $js_version, true);

    // Localize script to pass PHP data to JavaScript securely
    wp_localize_script('gotomenu-admin-script', 'gotomenuAdminData', array(
        'menus' => gotomenu_get_admin_menus()
    ));
}
add_action('admin_enqueue_scripts', 'gotomenu_admin_enqueue_scripts');
// Function to get available menus
function gotomenu_get_menus()
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
                error_log("Single menu_item");
                error_log(print_r($menu_items, true));
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
function get_menu_title($menu_title)
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

// Function to get available menus
function gotomenu_get_admin_menus()
{
    global $menu;
    $admin_menu_items = array();
     error_log("Admin menus");
    // error_log(print_r($menu, true));

    foreach ($menu as $item) {
        // error_log("Single admin menu 4th items===" . $item[4]);
        if(isset($item[4]) && $item[4] !== 'wp-menu-separator') {
            $menu_title = get_menu_title($item[0]);
            $menu_icon = (isset($item[6])) ? $item[6] : 'dashicons-admin-generic';
            $admin_menu_items[] = array(
                'title' => $menu_title,  // The menu title
                'url'   => esc_url(admin_url($item[2])), // The URL to the admin page
                'icon'   => $menu_icon // The menu icon
            );
            // error_log("Inside adding Single admin menu item");
            // error_log(print_r($admin_menu_items, true));
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
    // error_log("Admin menu items");
    // error_log(print_r($admin_menu_items, true));
    return $admin_menu_items;
}

// Add settings link on plugin page
function gotomenu_add_plugin_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=gotomenu') . '">' . __('Settings', 'gotomenu') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'gotomenu_add_plugin_link');
