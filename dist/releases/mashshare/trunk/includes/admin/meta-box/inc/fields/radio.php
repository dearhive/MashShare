<?php
/**
 * Radio field class.
 */
class MASHSB_RWMB_Radio_Field extends MASHSB_RWMB_Input_List_Field
{
	/**
	 * Normalize parameters for field
	 * @param array $field
	 * @return array
	 */
	static function normalize( $field )
	{
		$field['multiple'] = false;
		$field = parent::normalize( $field );

		return $field;
	}
}
