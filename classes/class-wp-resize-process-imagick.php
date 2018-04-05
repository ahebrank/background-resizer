<?php

class WP_Resize_Process_Imagick extends WP_Resize_Process {

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
        $orig_image = $this->editor->image->getImage();
        
        $resize_result = $this->editor->resize( $size_data['width'], $size_data['height'], $size_data['crop'] );
        $duplicate     = ( ( $orig_size['width'] == $size_data['width'] ) && ( $orig_size['height'] == $size_data['height'] ) );
        if ( ! is_wp_error( $resize_result ) && ! $duplicate ) {
            $resized = $this->editor->_save($this->editor->image);
            $this->editor->image->clear();
            $this->editor->image->destroy();
            $this->editor->image = null;
            if ( ! is_wp_error( $resized ) && $resized ) {
                unset( $resized['path'] );
                $this->metadata[ $size ] = $resized;
            }
        }

		return false;
    }

}