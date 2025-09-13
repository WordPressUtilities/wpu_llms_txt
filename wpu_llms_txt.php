<?php
defined('ABSPATH') || die;
/*
Plugin Name: WPU LLMS txt
Plugin URI: https://github.com/WordPressUtilities/wpu_llms_txt
Update URI: https://github.com/WordPressUtilities/wpu_llms_txt
Description: Generate a llms.txt file for your site
Version: 0.0.1
Author: Darklg
Author URI: https://darklg.me/
Requires at least: 6.2
Requires PHP: 8.0
Network: Optional
License: MIT License
License URI: https://opensource.org/licenses/MIT
*/

class wpu_llms_txt {
    public function __construct() {
        add_filter('init', array(&$this, 'request_uri'));
    }

    /* Handle request */
    function request_uri() {
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (rtrim($request_uri, '/') != '/llms.txt') {
            return '';
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo $this->get_llms_txt_content();
        exit;
    }

    /* Get file content */
    function get_llms_txt_content() {
        $site_name = strip_tags(get_bloginfo('name'));
        $site_description = strip_tags(get_bloginfo('description'));
        $post_types = get_post_types(array('public' => true), 'objects');

        $content = "# {$site_name}\n\n";
        $content .= "> {$site_description}\n\n";

        /* Post types */
        $content .= "This website contains:\n";
        foreach ($post_types as $id => $post_type) {
            if (in_array($id, array('attachment', 'revision', 'nav_menu_item'))) {
                continue;
            }
            $content .= "- {$post_type->labels->name}\n";
        }
        $content .= "\n";

        /* Links */
        $links = $this->get_menu_links('main');
        if ($links) {
            $content .= "Main links:\n";
            foreach ($links as $link) {
                $content .= "- {$link['title']}: {$link['url']}\n";
            }
        }

        return $content;
    }

    function get_menu_links($location = 'main') {
        $loc = get_nav_menu_locations();
        if (!isset($loc[$location])) {
            return array();
        }

        $links = array();

        $menu = wp_get_nav_menu_object($loc[$location]);
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        if (!$menu_items) {
            return array();
        }
        foreach ($menu_items as $item) {
            $title = strip_tags($item->title);
            $url = esc_url($item->url);
            if ($url == '' || $url == '#') {
                continue;
            }

            $links[md5($url)] = array(
                'title' => $title,
                'url' => $url
            );

        }

        return $links;
    }

}

$wpu_llms_txt = new wpu_llms_txt();
