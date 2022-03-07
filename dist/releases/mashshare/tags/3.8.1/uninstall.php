<?php
/**
 * Uninstall Mashshare
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
        delete_option( 'mashsb_update_notice');
        delete_option( 'mashsb_tracking_notice');
        delete_option( 'widget_mashsb_mostshared_posts_widget');
        delete_option( 'mashsb_tracking_last_send');
        delete_option( 'mashsb_update_notice_101');
        delete_option( 'mashsb_valid_fb_api_key');
        delete_option( 'mashsb_show_update_notice_gdpr');
        delete_option( 'mashsb_show_update_notice_gdpr1');
        delete_option( 'mashsb_show_new_fb_api');
        

        /* Delete all post meta options */
        delete_post_meta_by_key( 'mashsb_timestamp' );
        delete_post_meta_by_key( 'mashsb_shares' );
        delete_post_meta_by_key( 'mashsb_jsonshares' );
        
        //delete transients
        delete_transient('mashsb_rate_limit');
        delete_transient('mashsb_limit_req');
        
        wp_clear_scheduled_hook('mashsharer_transients_cron');
        wp_clear_scheduled_hook('mashsb_cron_daily');
}
