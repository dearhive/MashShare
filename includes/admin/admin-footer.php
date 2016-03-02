<?php
/**
 * Admin Footer
 *
 * @package     MASHSB
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add rating links to the admin dashboard
 *
 * @since	    1.0.0
 * @global		string $typenow
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function mashsb_admin_rate_us( $footer_text ) {
	global $typenow;

	if ( mashsb_is_admin_page() ) {
		$rate_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">Mashshare</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a> and help to support this project.<br>Something not working as expected or need help for customizing Mashshare? Read our <a href="https://www.mashshare.net/faq/" target="blank">FAQ</a> and visit the Mashshare <a href="https://mashshare.net/support" target="blank">Support Forum</a>', 'mashsb' ),
			'https://www.mashshare.net',
			'http://wordpress.org/support/view/plugin-reviews/mashsharer?filter=5#postform'
		);

		return str_replace( '</span>', '', '' ) . $rate_text . '</span>';
	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'mashsb_admin_rate_us' );