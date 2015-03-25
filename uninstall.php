<?php
/**
 * Uninstall Mashshare LikeAfterShare
 *
 * @package     MASHSB
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load MASHSB file
include_once( 'mashshare.php' );

global $wpdb, $mashsb_options;

if( mashsb_get_option( 'uninstall_on_delete' ) ) {
	/** Delete all the Plugin Options */
	delete_option( 'mashsb_settings' );
        delete_option( 'mashsb_networks');
        delete_option( 'mashsb_installDate');
        delete_option( 'mashsb_RatingDiv');
        delete_option( 'mashsb_version');
        delete_option( 'mashsb_version_upgraded_from');
        
        
        /* Delete all post meta options */
        delete_post_meta_by_key( 'mashsb_timestamp' );
        delete_post_meta_by_key( 'mashsb_shares' );
        delete_post_meta_by_key( 'mashsb_jsonshares' );
        
        /* Delete the database */
        //$sql = "DROP TABLE " . MASHSB_TABLE;
        //require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        //$wpdb->query($sql);
        /* Delete transients */
        wp_clear_scheduled_hook('mashsharer_transients_cron');
}
