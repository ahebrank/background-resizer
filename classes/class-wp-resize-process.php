<?php

class WP_Resize_Process extends WP_Background_Process {

	/**
	 * @var string
	 */
    protected $action = 'background-image-resize-process';

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var int
     */
    protected $attachment_id;

    /**
     * an image editor
     *
     * @var WP_Image_Editor
     */
    protected $editor;
    
    function __construct($editor) {
        parent::__construct();
        $this->editor = $editor;
        $this->metadata = array();
    }

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
        $this->attachment_id = $size_data['attachment_id'];
        
        return false;
    }

    /**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

        // update the attachment with the complete metadata
		wp_update_attachment_metadata( $this->attachment_id, $this->metadata );
	}

}