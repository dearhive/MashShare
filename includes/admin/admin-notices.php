<?php
/**
 * Admin Notices
 *
 * @package     MASHSB
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @since 1.0
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_admin_messages() {
	global $mashsb_options;

	

	/*if ( ( empty( $mashsb_options['purchase_page'] ) || 'trash' == get_post_status( $mashsb_options['purchase_page'] ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_mashsb_set_checkout_dismissed' ) ) {
		echo '<div class="error">';
			echo '<p>' . sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'mashsb' ), admin_url( 'edit.php?post_type=download&page=mashsb-settings' ) ) . '</p>';
			echo '<p><a href="' . add_query_arg( array( 'mashsb_action' => 'dismiss_notices', 'mashsb_notice' => 'set_checkout' ) ) . '">' . __( 'Dismiss Notice', 'mashsb' ) . '</a></p>';
		echo '</div>';
	}*/
 

	//settings_errors( 'mashsb-notices' );
}
add_action( 'admin_notices', 'mashsb_admin_messages' );

/**
 * Admin Add-ons Notices
 *
 * @since 1.0
 * @return void
*/
function mashsb_admin_addons_notices() {
	add_settings_error( 'mashsb-notices', 'mashsb-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'mashsb' ), 'error' );
	settings_errors( 'mashsb-notices' );
}

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since 1.8
 * @return void
*/
function mashsb_dismiss_notices() {

	$notice = isset( $_GET['mashsb_notice'] ) ? $_GET['mashsb_notice'] : false;
	if( ! $notice )
		return; // No notice, so get out of here

	update_user_meta( get_current_user_id(), '_mashsb_' . $notice . '_dismissed', 1 );
      
	wp_redirect( remove_query_arg( array( 'mashsb_action', 'mashsb_notice' ) ) ); exit;

}
add_action( 'mashsb_dismiss_notices', 'mashsb_dismiss_notices' );

/*
 * Show big colored update information below the official update notification in /wp-admin/plugins
 * @since 2.0.8
 * @return void
 * 
 */

function in_plugin_update_message( $args ) {
    $transient_name = 'mashsb_upgrade_notice_' . $args['Version'];

    if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

      $response = wp_remote_get( 'https://plugins.svn.wordpress.org/mashsharer/trunk/readme.txt' );

      if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {

        // Output Upgrade Notice
        $matches        = null;
        $regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WC_VERSION ) . '\s*=|$)~Uis';
        $upgrade_notice = '';

        if ( preg_match( $regexp, $response['body'], $matches ) ) {
          $version        = trim( $matches[1] );
          $notices        = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );
          
          if ( version_compare( WC_VERSION, $version, '<' ) ) {

            $upgrade_notice .= '<div class="mashsb_plugin_upgrade_notice" style="padding:10px;background-color: #479CCF;color: #FFF;">';

            foreach ( $notices as $index => $line ) {
              $upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}" style="text-decoration:underline;color:#ffffff;">${1}</a>', $line ) );
            }

            $upgrade_notice .= '</div> ';
          }
        }

        set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
      }
    }

    echo wp_kses_post( $upgrade_notice );
  }
 add_action ( "in_plugin_update_message-mashsharer/mashshare.php", 'in_plugin_update_message'  );