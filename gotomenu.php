<?php
/**
 * Plugin Name: GoToMenu - Menu Navigator
 * Plugin URI: https://www.santoshkori.com/gotomenu-wordpress/
 * Description: GoToMenu - Menu Navigator is tool that boosts efficiency by offering rapid access to any registered menu. A simple F2 keypress opens a search box, letting users quickly find and open their desired menu. Save time and streamline workflow.
 * Version: 1.0.0
 * Author: Santosh Kori
 * Author URI: http://santoshkori.com
 * License: GPLv2 or later
 * Text Domain: gotomenu-skori
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// Include settings page
require_once plugin_dir_path(__FILE__) . 'includes/gotomenu-settings.php';

// Enqueue scripts and styles
function gotomenu_enqueue_scripts() {
    if (!is_admin() && get_option('gotomenu_enable_frontend') === '1') {
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

// Enqueue scripts and styles for admin
function gotomenu_admin_enqueue_scripts() {
    if (is_admin() && get_option('gotomenu_enable_backend') === '1') {
        // Get file modification time for cache busting
        $css_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/gotomenu.css');
        $js_version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/gotomenu.js');

        wp_enqueue_style('gotomenu-admin-style', plugin_dir_url(__FILE__) . 'assets/css/gotomenu.css', array(), $css_version);
        wp_enqueue_script('gotomenu-admin-script', plugin_dir_url(__FILE__) . 'assets/js/gotomenu.js', array('jquery'), $js_version, true);

        // Localize script to pass PHP data to JavaScript securely
        wp_localize_script('gotomenu-admin-script', 'gotomenuData', array(
            'menus' => gotomenu_get_admin_menus()
        ));
    }
}
add_action('admin_enqueue_scripts', 'gotomenu_admin_enqueue_scripts');

// Function to get available menus
function gotomenu_get_menus() {
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
        'title' => __('Home', 'gotomenu-skori'),
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

/**
 * Get the menu icon
 *
 * @param string $menu_icon The menu icon.
 * @return string The menu icon .
 */
function get_menu_icon($menu_icon)
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
function get_menu_url($menu)
{
    // Check if the string contains an opening HTML tag
    if (strpos($menu, '.php')) {
        $menu_url = admin_url($menu);
    } else {
        $menu_url = admin_url( 'admin.php?page=' . $menu );
    }
    return esc_url($menu_url);
}

// Function to get available menus
function gotomenu_get_admin_menus() {
    global $menu;
    $admin_menu_items = array();

    foreach ($menu as $item) {
        if(isset($item[4]) && $item[4] !== 'wp-menu-separator') {
            $menu_title = get_menu_title($item[0]);
            $menu_icon =(isset($item[6])) ?  get_menu_icon($item[6]) : 'dashicons-admin-generic';

            $admin_menu_items[] = array(
                'title' => $menu_title,  // The menu title
                'url'   => get_menu_url($item[2]), // The URL to the admin page
                'icon'   => $menu_icon // The menu icon
            );
        }
    }

    usort($admin_menu_items, function ($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    $homeItem = array(
        'title' => __('Visit Site', 'gotomenu-skori'),
        'url'  => esc_url(get_bloginfo('url')),
        'icon'   => 'dashicons-admin-site'
    );

    array_unshift($admin_menu_items, $homeItem);
    return $admin_menu_items;
}

// Add settings link on plugin page
function gotomenu_add_plugin_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=gotomenu') . '">' . __('Settings', 'gotomenu-skori') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'gotomenu_add_plugin_link');
