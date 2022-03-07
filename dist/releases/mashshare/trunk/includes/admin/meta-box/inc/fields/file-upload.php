<?php
/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class MASHSB_RWMB_File_Upload_Field extends MASHSB_RWMB_File_Advanced_Field
{
	/**
	 * Add actions
	 *
	 * @return void
	 */
	static function add_actions()
	{
		parent::add_actions();
		// Print attachment templates
		add_action( 'print_media_templates', array( __CLASS__, 'print_templates' ) );
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'mashsb-rwmb-upload', MASHSB_RWMB_CSS_URL . 'upload.css', array( 'mashsb-rwmb-media' ), MASHSB_RWMB_VER );
		wp_enqueue_script( 'mashsb-rwmb-file-upload', MASHSB_RWMB_JS_URL . 'file-upload.js', array( 'mashsb-rwmb-media' ), MASHSB_RWMB_VER, true );
	}

	/**
	 * Template for media item
	 * @return void
	 */
	static function print_templates()
	{
		require_once( MASHSB_RWMB_INC_DIR . 'templates/upload.php' );
	}
}
