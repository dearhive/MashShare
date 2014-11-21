<?php
/**
 * Contextual Help
 *
 * @package     MASHSB
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Settings contextual help.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function mashsb_settings_contextual_help() {
	$screen = get_current_screen();

	/*if ( $screen->id != 'mashsb-settings' )
		return;
*/
	$screen->set_help_sidebar(
		'<p><strong>' . $screen->id . sprintf( __( 'For more information:', 'mashsb' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Mashshare website.', 'mashsb' ), esc_url( 'https://www.mashshare.net/' ) ) ) . '</p>' .
		'<p>' . sprintf(
					__( '<a href="%s">Post an issue</a> on <a href="%s">Mashshare</a>. View <a href="%s">extensions</a>.', 'mashsb' ),
					esc_url( 'https://www.mashshare.net/contact-support/' ),
					esc_url( 'https://www.mashshare.net' ),
					esc_url( 'https://www.mashshare.net/downloads' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'mashsb-settings-general',
		'title'	    => __( 'General', 'mashsb' ),
		'content'	=> '<p>' . __( 'This screen provides the most basic settings for configuring Mashshare.', 'mashsb' ) . '</p>'
	) );


	

	do_action( 'mashsb_settings_contextual_help', $screen );
}
add_action( 'load-mashsb-settings', 'mashsb_settings_contextual_help' );
