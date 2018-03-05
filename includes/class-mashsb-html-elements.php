<?php
/**
 * HTML elements
 *
 * A helper class for outputting common HTML elements, such as product drop downs
 *
 * @package     MASHSB
 * @subpackage  Classes/HTML
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MASHSB_HTML_Elements Class
 *
 * @since 2.0
 */
class MASHSB_HTML_Elements {


	/**
	 * Renders an HTML Dropdown
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function select( $args = array() ) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'mashsb' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'mashsb' )
		);

		$args = wp_parse_args( $args, $defaults );


		if( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
		} else {
			$multiple = '';
		}

		if( $args['chosen'] ) {
			$args['class'] .= ' mashsb-select-chosen';
		}

		$output = '<select name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( sanitize_key( str_replace( '-', '_', $args[ 'id' ] ) ) ) . '" class="mashsb-select ' . esc_attr( $args[ 'class'] ) . '"' . $multiple . '>';

		if ( ! empty( $args[ 'options' ] ) ) {
			if ( $args[ 'show_option_all' ] ) {
				if( $args['multiple'] ) {
					$selected = selected( true, in_array( 0, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], 0, false );
				}
				$output .= '<option value="all"' . $selected . '>' . esc_html( $args[ 'show_option_all' ] ) . '</option>';
			}

			if ( $args[ 'show_option_none' ] ) {
				if( $args['multiple'] ) {
					$selected = selected( true, in_array( -1, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], -1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args[ 'show_option_none' ] ) . '</option>';
			}

			foreach( $args[ 'options' ] as $key => $option ) {

				if( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( $key, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Renders an HTML Checkbox
	 *
	 * @since 1.9
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'     => null,
			'current'  => null,
			'class'    => 'mashsb-checkbox'
		);

		$args = wp_parse_args( $args, $defaults );

		$output = '<input type="checkbox" name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( $args[ 'name' ] ) . '" class="' . $args[ 'class' ] . ' ' . esc_attr( $args[ 'name'] ) . '" ' . checked( 1, $args[ 'current' ], false ) . ' />';

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 1.5.2
	 *
	 * @param string $value The value to prepopulate the field with
	 * @return string Text field
	 */
	public function text( $args = array() ) {
		// Backwards compatabliity
		if ( func_num_args() > 1 ) {
			$args = func_get_args();

			$name  = $args[0];
			$value = isset( $args[1] ) ? $args[1] : '';
			$label = isset( $args[2] ) ? $args[2] : '';
			$desc  = isset( $args[3] ) ? $args[3] : '';
		}

		$defaults = array(
			'name'         => isset( $name )  ? $name  : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc )  ? $desc  : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => ''
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="mashsb-' . sanitize_key( $args[ 'name' ] ) . '-wrap">';
			
			$output .= '<label class="mashsb-label" for="mashsb-' . sanitize_key( $args[ 'name' ] ) . '">' . esc_html( $args[ 'label' ] ) . '</label>';

			if ( ! empty( $args[ 'desc' ] ) ) {
				$output .= '<span class="mashsb-description">' . esc_html( $args[ 'desc' ] ) . '</span>';
			}

			$output .= '<input type="text" name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( $args[ 'name' ] )  . '" autocomplete="' . esc_attr( $args[ 'autocomplete' ] )  . '" value="' . esc_attr( $args[ 'value' ] ) . '" placeholder="' . esc_attr( $args[ 'placeholder' ] ) . '" class="' . $args[ 'class' ] . '"' . $disabled . '/>';

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an HTML textarea
	 *
	 * @since 1.9
	 *
	 * @return string textarea
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'        => 'textarea',
			'value'       => null,
			'label'       => null,
			'desc'        => null,
            'class'       => 'large-text',
			'disabled'    => false
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="mashsb-' . sanitize_key( $args[ 'name' ] ) . '-wrap">';

			$output .= '<label class="mashsb-label" for="mashsb-' . sanitize_key( $args[ 'name' ] ) . '">' . esc_html( $args[ 'label' ] ) . '</label>';

			$output .= '<textarea name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( $args[ 'name' ] ) . '" class="' . $args[ 'class' ] . '"' . $disabled . '>' . esc_attr( $args[ 'value' ] ) . '</textarea>';

			if ( ! empty( $args[ 'desc' ] ) ) {
				$output .= '<span class="mashsb-description">' . esc_html( $args[ 'desc' ] ) . '</span>';
			}

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an ajax user search field
	 *
	 * @since 2.0
	 *
	 * @param array $args
	 * @return string text field with ajax search
	 */
	public function ajax_user_search( $args = array() ) {

		$defaults = array(
			'name'        => 'user_id',
			'value'       => null,
			'placeholder' => __( 'Enter username', 'mashsb' ),
			'label'       => null,
			'desc'        => null,
            'class'       => '',
			'disabled'    => false,
			'autocomplete'=> 'off'
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] = 'mashsb-ajax-user-search ' . $args['class'];

		$output  = '<span class="mashsb_user_search_wrap">'; 
			$output .= $this->text( $args );
			$output .= '<span class="mashsb_user_search_results"></span>';
		$output .= '</span>';

		return $output;
	}
}
