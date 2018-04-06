<?php

class WP_Background_Resizer_Callbacks {

	/**
	 * update one sizes key for a particular attachment (called after resizing)
	 *
	 * @param [type] $size
	 * @param [type] $attachment_id
	 * @param [type] $data
	 * @return void
	 */
	public static function update_metadata($size, $attachment_id, $data) {
		$metadata = wp_get_attachment_metadata($attachment_id);
		$metadata['sizes'][$size] = $data;
		wp_update_attachment_metadata($attachment_id, $metadata);
	}
	
	/**
	 * wrapper for resizing call, imagick version
	 *
	 * @param [type] $filename
	 * @param [type] $size
	 * @param [type] $width
	 * @param [type] $height
	 * @param [type] $crop
	 * @param [type] $attachment_id
	 * @return void
	 */
	public static function resize_imagick($filename, $size, $width, $height, $crop, $attachment_id) {
		$editor = new WP_Image_Editor_Imagick_Queued($filename);
		$editor->resize_callback($size, $width, $height, $crop, $attachment_id);
	}

	/**
	 * wrapper for resizing call, GD version
	 *
	 * @param [type] $filename
	 * @param [type] $size
	 * @param [type] $width
	 * @param [type] $height
	 * @param [type] $crop
	 * @param [type] $attachment_id
	 * @return void
	 */
	public static function resize_gd($filename, $size, $width, $height, $crop, $attachment_id) {
		$editor = new WP_Image_Editor_GD_Queued($filename);
		$editor->resize_callback($size, $width, $height, $crop, $attachment_id);
	}
}