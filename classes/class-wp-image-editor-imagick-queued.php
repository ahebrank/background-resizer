<?php
/**
 * WordPress Imagick Image Editor (Queued)
 *
 * @package BackgroundImage
 * @subpackage Image_Editor
 */
class WP_Image_Editor_Imagick_Queued extends WP_Image_Editor_Imagick {

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
        $orig_image = $this->image->getImage();

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
            wc_schedule_single_action( time(), 'wc_background_resizer_imagick', array(
                'filename' => $filename, 
                'size' => $size, 
                'width' => $size_data['width'], 
                'height' => $size_data['height'], 
                'crop' => $size_data['crop'], 
                'attachment_id' => $size_data['attachment_id']
            ));
        }
        
        add_action($hook, array($this, 'resize_callback'), 10, 5);

        $this->image = $orig_image;
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
        $orig_image = $this->image->getImage();
        
        $resize_result = $this->resize( $width, $height, $crop );
        if (is_wp_error($resize_result)) {
            throw new Exception($resize_result->get_error_message(), $resize_result->get_error_code());
        }
        $duplicate     = ( ( $orig_size['width'] == $width ) && ( $orig_size['height'] == $height ) );
        if ( ! $duplicate ) {
                $resized = $this->_save($this->image);
                $this->image->clear();
                $this->image->destroy();
                $this->image = null;
                if ( is_wp_error($resized)) {
                    throw new Exception($resized->get_error_message(), $resized->get_error_code());
                }
                if ( $resized ) {
                        unset( $resized['path'] );
                        WP_Background_Resizer_Callbacks::update_metadata($size, $attachment_id, $resized);
                }
        }
    }
    
}