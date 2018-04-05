<?php
/*
Plugin Name: Background Resizer
Plugin URI: https://github.com/ahebrank/background-resizer
Description: Drop-in background image resizing in WordPress.
Author: Andy Hebrank
Version: 0.1
*/

class Background_Resizer {

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_filter( 'intermediate_image_sizes_advanced', array($this, 'filter_image_sizes_advanced'), 10, 3 );
        add_filter( 'wp_image_editors', array($this, 'set_image_editor') );
    }

    public function init() {
        require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-resize-process.php';
        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-resize-process-imagick.php';
        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-resize-process-gd.php';
        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-image-editor-imagick-queued.php';
        require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-image-editor-gd-queued.php';
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
    public function filter_image_sizes_advanced($sizes, $metadata, $attachment_id) {
        if ($sizes) {
            foreach ($sizes as $k => $v) {
                $sizes[$k]['attachment_id'] = $attachment_id;
            }
        }
        return $sizes;
    }
}

new Background_Resizer();