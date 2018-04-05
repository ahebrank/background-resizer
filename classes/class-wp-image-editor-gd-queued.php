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

        $resize_process = new WP_Resize_Process_GD($this);

        $metadata   = array();
		$orig_size  = $this->size;
        
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
            list( $filename, $extension, $mime_type ) = $this->get_output_format( $filename, null );
            $metadata[$size] = array(
                'path'      => $filename,
                'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
                'width'     => $this->size['width'],
                'height'    => $this->size['height'],
                'mime-type' => $mime_type,
            );

            // queue the real processing
            $resize_process->push_to_queue($size_data);
        }
        
        $this->size = $orig_size;
        
        // send off the queue
        $resize_process->save()->dispatch();

		return $metadata;
    }

    
}