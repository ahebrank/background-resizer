<?php
/**
 * WordPress GD Image Editor (Queued)
 *
 * @package BackgroundImage
 * @subpackage Image_Editor
 */
class WP_Image_Editor_GD_Queued extends WP_Image_Editor_GD {

    /**
	 * inheritdoc
	 */
    public function multi_resize($sizes) {

        // patch must be applied for 4.9
        // if not, fallback to regular version
        $first = reset($sizes);
        if (!isset($first['attachment_id'])) {
            return parent::multi_resize($sizes);
        }

        $metadata   = array();
        $orig_size  = $this->size;
        
        $filename = $this->file;
        list( $filename, $extension, $mime_type ) = $this->get_output_format( $filename, null );
        
		foreach ( $sizes as $size => $size_data ) {
			if ( ! isset( $size_data['width'] ) && ! isset( $size_data['height'] ) ) {
				continue;
			}
			if ( ! isset( $size_data['width'] ) ) {
				$size_data['width'] = null;
			}
			if ( ! isset( $size_data['height'] ) ) {
				$size_data['height'] = null;
			}
			if ( ! isset( $size_data['crop'] ) ) {
				$size_data['crop'] = false;
            }

            // save the original as a placeholder
            $metadata[$size] = array(
                'path'      => $filename,
                'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
                'width'     => $this->size['width'],
                'height'    => $this->size['height'],
                'mime-type' => $mime_type,
            );

            // queue the real processing
            $size_data['file'] = $filename;
            $size_data['size'] = $size;
            wc_schedule_single_action( time(), 'wc_background_resizer_gd', array(
                'filename' => $filename, 
                'size' => $size, 
                'width' => $size_data['width'], 
                'height' => $size_data['height'], 
                'crop' => $size_data['crop'], 
                'attachment_id' => $size_data['attachment_id']
            ));
        }
        
        $this->size = $orig_size;

		return $metadata;
    }

    /**
     * resize action called by the scheduler
     *
     * @param [type] $size
     * @param [type] $width
     * @param [type] $height
     * @param [type] $crop
     * @param [type] $attachment_id
     * @return void
     */
    public function resize_callback($size, $width, $height, $crop, $attachment_id) {
        $loaded = $this->load();
        if (is_wp_error($loaded)) {
            throw new Exception($loaded->get_error_message(), $loaded->get_error_code());
        }
        $orig_size  = $this->size;
        
        $image = $this->_resize( $width, $height, $crop );
        if (is_wp_error($image)) {
            throw new Exception($image->get_error_message(), $image->get_error_code());
        }
        $duplicate = ( ( $orig_size['width'] == $width ) && ( $orig_size['height'] == $height ) );
        if ( ! $duplicate ) {
            $resized = $this->_save( $image );
            imagedestroy( $image );
            if (is_wp_error($resized)) {
                throw new Exception($resized->get_error_message(), $resized->get_error_code());
            }
            if ( $resized ) {
                unset( $resized['path'] );
                WP_Background_Resizer_Callbacks::update_metadata($size, $attachment_id, $resized);
            }
        }
    }

}