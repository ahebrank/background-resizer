<?php
/*
Plugin Name: Background Resizer
Plugin URI: https://github.com/ahebrank/background-resizer
Description: Drop-in background image resizing in WordPress.
Author: Andy Hebrank
Version: 0.1
*/

class Background_Resizer {

    private $queue;

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_filter( 'intermediate_image_sizes_advanced', array($this, 'filter_image_sizes_advanced'), 10, 3 );
        add_filter( 'wp_image_editors', array($this, 'set_image_editor') );

        require_once plugin_dir_path( __FILE__ ) . 'vendor/prospress/action-scheduler/action-scheduler.php';
    }

    public function init() {
        require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
        require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
        require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';

        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-background-resizer-callbacks.php';        
        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-image-editor-imagick-queued.php';
        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-image-editor-gd-queued.php';

        add_action('wc_background_resizer_imagick', array('WP_Background_Resizer_Callbacks', 'resize_imagick'), 10, 6);
        add_action('wc_background_resizer_gd', array('WP_Background_Resizer_Callbacks', 'resize_gd'), 10, 6);
    }

    /**
     * prepend the queued version fo the current image editor
     *
     * @param [type] $editors
     * @return void
     */
    public function set_image_editor($editors) {
        $current = $editors[0];
        $queued = $current . '_Queued';
        if (class_exists($queued)) {
            if (!in_array($queued, $editors)) {
                array_unshift($editors, $queued);
            }
        }
        return $editors;
    }

    /**
     * save the attachment_id for later use
     *
     * @param [type] $sizes
     * @param [type] $metadata
     * @param [type] $attachment_id
     * @return void
     */
    public function filter_image_sizes_advanced($sizes, $metadata, $attachment_id = null) {
        if (!is_null($attachment_id)) {
            if ($sizes) {
                foreach ($sizes as $k => $v) {
                    $sizes[$k]['attachment_id'] = $attachment_id;
                }
            }
        }
        return $sizes;
    }
}

new Background_Resizer();