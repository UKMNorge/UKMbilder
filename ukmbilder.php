<?php
/* 
Plugin Name: UKM Bilder
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://www.ukm-norge.no
*/

use UKMNorge\Wordpress\Modul;

require_once('UKM/Autoloader.php');

class UKMbilder extends Modul
{
    public static $action = 'home';
    public static $path_plugin = null;

    /**
     * Register hooks
     */
    public static function hook()
    {
        // Kun mønstringssider skal ha bilder
        if (is_numeric(get_option('pl_id'))) {
            add_action(
                'admin_menu',
                ['UKMbilder', 'meny']
            );
        }

        add_action(
            'network_admin_menu',
            ['UKMbilder', 'network_menu'],
            2000
        );

        add_action(
            'wp_ajax_UKMbilder_ajax',
            ['UKMbilder', 'ajax']
        );
    }

    /**
     * Rendre meny
     *
     */
    public static function meny()
    {
        $page = add_submenu_page(
            'edit.php',
            'Bilder',
            'Bilder',
            'edit_posts',
            'UKMbilder',
            ['UKMbilder','renderAdmin']
        );
        add_action(
            'admin_print_styles-' . $page,
            ['UKMbilder','scripts_and_styles']
        );
    }

    /**
     * Menu function called by network admin
     */
    public static function network_menu() {
    }

    /**
     * Scripts and styles for non-network admin
     *
     */
    public static function scripts_and_styles()
    {
        wp_enqueue_script('WPbootstrap3_js');
        wp_enqueue_style('WPbootstrap3_css');
        wp_enqueue_script('dropzone');
        wp_enqueue_style('ukmbilder_css', self::getPluginUrl(). 'ukmbilder.css');

        wp_enqueue_script('ukmbilder_app', self::getPluginUrl() . 'js/app.js');
        wp_enqueue_script('ukmbilder_app_upload', self::getPluginUrl() . 'js/upload.js');
        wp_enqueue_script('ukmbilder_app_imageList', self::getPluginUrl() . 'js/imageList.js');
        wp_enqueue_script('ukmbilder_app_convert', self::getPluginUrl() . 'js/convert.js');
        wp_enqueue_script('ukmbilder_app_tagger', self::getPluginUrl() . 'js/tagger.js');
        wp_enqueue_script('ukmbilder_app_hendelser', self::getPluginUrl() . 'js/hendelser.js');
        
    }
}

UKMbilder::init(__DIR__);
UKMbilder::hook();