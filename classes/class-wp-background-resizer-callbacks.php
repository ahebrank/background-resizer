<?php

class WP_Background_Resizer_Callbacks {
    public static function update_metadata($size, $attachment_id, $data) {
			$metadata = wp_get_attachment_metadata($attachment_id);
			$metadata['sizes'][$size] = $data;
			wp_update_attachment_metadata($attachment_id, $metadata);
		}
		
		public static function resize_imagick($filename, $size, $width, $height, $crop, $attachment_id) {
			$editor = new WP_Image_Editor_Imagick_Queued($filename);
			$editor->resize_callback($size, $width, $height, $crop, $attachment_id);
		}

		public static function resize_gd($filename, $size, $width, $height, $crop, $attachment_id) {
			$editor = new WP_Image_Editor_GD_Queued($filename);
			$editor->resize_callback($size, $width, $height, $crop, $attachment_id);
		}
}