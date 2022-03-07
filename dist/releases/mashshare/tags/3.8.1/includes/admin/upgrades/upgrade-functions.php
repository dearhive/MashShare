<?php
/**
 * Upgrade Functions
 *
 * @package     MASHSB
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, Ren´é Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Perform automatic upgrades when necessary
 *
 * @since 3.3.4
 * @return void
*/
function mashsb_do_automatic_upgrades() {

	$did_upgrade = false;
	$mashsb_version = preg_replace( '/[^0-9.].*/', '', get_option( 'mashsb_version' ) );

	if( version_compare( $mashsb_version, '3.2.5', '<' ) ) {
		mashsb_upgrade_v1a();
	}
	if( version_compare( $mashsb_version, '3.2.4', '<' ) ) {
		mashsb_upgrade_v1();
	}
       
//	if( version_compare( $mashsb_version, '3.5.3', '<' ) ) {
//		mashsb_upgrade_3_5_3();
//	}
        // Check if version number in DB is lower than version number in current plugin
	if( version_compare( $mashsb_version, MASHSB_VERSION, '<' ) ) {

		// Let us know that an upgrade has happened
		$did_upgrade = true;

	}

        // Update Version number
	if( $did_upgrade ) {

		update_option( 'mashsb_version', preg_replace( '/[^0-9.].*/', '', MASHSB_VERSION ) );

	}

}
add_action( 'admin_init', 'mashsb_do_automatic_upgrades' );


/**
 * Store default settings
 */
function mashsb_upgrade_v1a() {
    
    // Show Rating Div
    add_option( 'mashsb_RatingDiv', 'no' );
    // Show facebook access token notice
    add_option( 'mashsb_update_notice_101', 'yes' ); 
    
}



/**
 * Enable the margin option
 */
function mashsb_upgrade_v1() {
    
    // Try to load some settings.
    $settings = get_option( 'mashsb_settings' );
    // Enable the Margin Option. 
    if( !array_key_exists( 'button_margin', $settings ) ) {
        $button_margin = array('button_margin' => '1');
        $settings_upgrade = array_merge( $button_margin, $settings );
        update_option( 'mashsb_settings', $settings_upgrade );
    }
}
