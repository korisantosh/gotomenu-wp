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
function gotomenu_enqueue_scripts() {
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

// Function to get available menus
function gotomenu_get_menus() {
    $menu_items = array();
    $wp_menus = wp_get_nav_menus();
    // error_log("wp_menus");
    // error_log(print_r($wp_menus, true));

    foreach ($wp_menus as $menu) {
        // error_log("menu");
        // error_log(print_r($menu, true));
        $items = wp_get_nav_menu_items($menu->term_id);
        // error_log("items");
        // error_log(print_r($items, true));
        if ($items) {
            foreach ($items as $item) {
                // error_log("Single item");
                // error_log(print_r($item, true));
                $menu_items[] = array(
                    'title' => esc_html($item->title),
                    'url'   => esc_url($item->url)
                );
                error_log("Single menu_item");
                error_log(print_r($menu_items, true));
            }
        }
    }
    error_log("menu_items");
    error_log(print_r($menu_items, true));
    // Sort menu items by title in ascending order
    usort($menu_items, function ($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    // Remove specific item based on URL
    $menu_items = array_filter($menu_items, function ($item) {
        return $item['url'] !== esc_url(get_bloginfo('url'));
    });

    $homeItem = array(
        'title' => 'Home',
        'url'  => esc_url(get_bloginfo('url'))
    );

    array_unshift($menu_items, $homeItem);


    return $menu_items;
}

// Function to get menu link
function get_menu_link($menu_id) {
    $menu_items = wp_get_nav_menu_items($menu_id);
    if (!empty($menu_items)) {
        return $menu_items[0]->url;
    }
    return home_url();
}
