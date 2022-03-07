<?php
/**
 * Checkbox list field class.
 */
class MASHSB_RWMB_Checkbox_List_Field extends MASHSB_RWMB_Input_List_Field
{
	/**
	 * Normalize parameters for field
	 * @param array $field
	 * @return array
	 */
	static function normalize( $field )
	{
		$field['multiple'] = true;
		$field = parent::normalize( $field );		

		return $field;
	}
}
