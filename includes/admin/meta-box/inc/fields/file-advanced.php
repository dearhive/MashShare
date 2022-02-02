<?php
/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class MASHSB_RWMB_File_Advanced_Field extends MASHSB_RWMB_Media_Field
{
	/**
	 * Get the field value.
	 * @param array $field
	 * @param array $args
	 * @param null  $post_id
	 * @return mixed
	 */
	static function get_value( $field, $args = array(), $post_id = null )
	{
		return MASHSB_RWMB_File_Field::get_value( $field, $args, $post_id );
	}

	/**
	 * Output the field value.
	 * @param array $field
	 * @param array $args
	 * @param null  $post_id
	 * @return mixed
	 */
	static function the_value( $field, $args = array(), $post_id = null )
	{
		return MASHSB_RWMB_File_Field::the_value( $field, $args, $post_id );
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file_id Attachment image ID (post ID). Required.
	 * @param array $args    Array of arguments (for size).
	 * @return array|bool False if file not found. Array of image info on success
	 */
	static function file_info( $file_id, $args = array() )
	{
		return MASHSB_RWMB_File_Field::file_info( $file_id, $args );
	}

}
