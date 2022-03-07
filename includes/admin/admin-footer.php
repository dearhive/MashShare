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
 * Add rating links to the settings footer
 *
 * @since	1.0.0
 * @return      string
 */
function mashsb_admin_rate_us() {
	if ( mashsb_is_admin_page() ) {
//		$rate_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">Mashshare</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a> and help to support this project.<br>Something not working as expected or need help? Read our <a href="%3$s" target="blank">Documentation</a>', 'mashsb' ),
//			'https://www.mashshare.net',
//			'http://wordpress.org/support/view/plugin-reviews/mashsharer?filter=5#postform',
//			'http://docs.mashshare.net/'
//		);
		$rate_text = sprintf( __( 'Please do us a BIG favor and give us a 5 star rating <a href="%1$s" target="blank">here.</a> Need help? Read our <a href="%2$s" target="blank">Documentation</a><br>If you`re not happy, please <a href="%3$s" target="blank">get in touch with us</a>, so that we can sort it out. Thank you!', 'mashsb' ),
			'http://wordpress.org/support/view/plugin-reviews/mashsharer?filter=5#postform',
                        'http://docs.mashshare.net/',
			'https://www.mashshare.net/contact-developer/'
		);
                

		return $rate_text;
	}
}
