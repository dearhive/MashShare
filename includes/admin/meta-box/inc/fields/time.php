<?php
/**
 * Time field class.
 */
class MASHSB_RWMB_Time_Field extends MASHSB_RWMB_Datetime_Field
{
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts()
	{
		parent::admin_register_scripts();
		wp_enqueue_style( 'jquery-ui-timepicker' );
		wp_enqueue_script( 'mashsb-rwmb-time' );
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	public static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field['js_options']['timeFormat'] = empty( $field['format'] ) ? $field['js_options']['timeFormat'] : $field['format'];
		return $field;
	}
}
