<?php

class WP_Resize_Process_GD extends WP_Resize_Process {

    /**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task($size_data) {

        parent::task($size_data);
        
        $orig_size  = $this->editor->size;
        
        $image = $this->editor->_resize( $size_data['width'], $size_data['height'], $size_data['crop'] );
        $duplicate = ( ( $orig_size['width'] == $size_data['width'] ) && ( $orig_size['height'] == $size_data['height'] ) );
        if ( ! is_wp_error( $image ) && ! $duplicate ) {
            $resized = $this->editor->_save( $image );
            imagedestroy( $image );
            if ( ! is_wp_error( $resized ) && $resized ) {
                unset( $resized['path'] );
                $this->metadata[ $size ] = $resized;
            }
        }

		return false;
    }

}