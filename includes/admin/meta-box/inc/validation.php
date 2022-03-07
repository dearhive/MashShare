<?php
/**
 * Validation module.
 * @package Meta Box
 */

/**
 * Validation class.
 */
class MASHSB_RWMB_Validation
{
	/**
	 * Add hooks when module is loaded.
	 */
	public function __construct()
	{
		add_action( 'mashsb_rwmb_after', array( $this, 'rules' ) );
		add_action( 'mashsb_rwmb_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Output validation rules of each meta box.
	 * The rules are outputted in [data-rules] attribute of an hidden <script> and will be converted into JSON by JS.
	 * @param MASHSB_RW_Meta_Box $object Meta Box object
	 */
	public function rules( MASHSB_RW_Meta_Box $object )
	{
		if ( ! empty( $object->meta_box['validation'] ) )
		{
			echo '<script type="text/html" class="mashsb-rwmb-validation-rules" data-rules="' . esc_attr( json_encode( $object->meta_box['validation'] ) ) . '"></script>';
		}
	}

	/**
	 * Enqueue scripts for validation.
	 */
	public function scripts()
	{
		wp_enqueue_script( 'jquery-validate', MASHSB_RWMB_JS_URL . 'jquery.validate.min.js', array( 'jquery' ), MASHSB_RWMB_VER, true );
		wp_enqueue_script( 'mashsb-rwmb-validate', MASHSB_RWMB_JS_URL . 'validate.js', array( 'jquery-validate' ), MASHSB_RWMB_VER, true );
		wp_localize_script( 'mashsb-rwmb-validate', 'rwmbValidate', array(
			'summaryMessage' => __( 'Please correct the errors highlighted below and try again.', 'meta-box' ),
		) );
	}
}
