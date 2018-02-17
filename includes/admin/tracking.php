<?php
/**
 * Tracking functions for reporting plugin usage to the MASHSB site for users that have opted in
 *
 * @package     MASHSB
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, Rene Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5.1
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Usage tracking
 *
 * @access public
 * @since  2.5.1
 * @return void
 */
class MASHSB_Tracking {

	/**
	 * The data to send to the MASHSB site
	 *
	 * @access private
	 */
	private $data;

	/**
	 * Get things going
	 *
	 * @access public
	 */
	public function __construct() {

		//$this->schedule_send();
                add_action( 'init', array( $this, 'schedule_send' ) );

		add_action( 'mashsb_settings_general_sanitize', array( $this, 'check_for_settings_optin' ) );
		add_action( 'mashsb_opt_into_tracking', array( $this, 'check_for_optin' ) );
		add_action( 'mashsb_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

	}

	/**
	 * Check if the user has opted into tracking
	 *
	 * @access private
	 * @return bool
	 */
	private function tracking_allowed() {
		$allow_tracking = mashsb_get_option( 'allow_tracking', false );
		return $allow_tracking;
	}

	/**
	 * Setup the data that is going to be tracked
	 *
	 * @access private
	 * @return void
	 */
	private function setup_data() {

		$data = array();

		// Retrieve current theme info
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
		
		$data['url']    = home_url();
		$data['theme']  = $theme;
		$data['email']  = get_bloginfo( 'admin_email' );

		// Retrieve current plugin information
		if( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}

		$data['active_plugins']   = $active_plugins;
		$data['inactive_plugins'] = $plugins;
                $data['post_count'] = wp_count_posts( 'post' )->publish;

		$this->data = $data;
	}

	/**
	 * Send the data to the MASHSB server
	 *
	 * @access private
	 * @return void
	 */
	public function send_checkin( $override = false ) {

		if( ! $this->tracking_allowed() && ! $override )
			return;

		// Send a maximum of once per week
		$last_send = $this->get_last_send();
		if( $last_send && $last_send > strtotime( '-1 week' ) )
			return;

		$this->setup_data();
                
                
                $url = 'https://www.mashshare.net/?mashsb_action=checkin';
                if (MASHSB_DEBUG){
                    $url = 'http://src.wordpress-develop.dev/?mashsb_action=checkin'; // only for debugging
                }
                
                $request = wp_remote_post( $url, array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $this->data,
			'user-agent'  => 'MASHSB/' . MASHSB_VERSION . '; ' . get_bloginfo( 'url' )
		) );


                
                if (!MASHSB_DEBUG){
		update_option( 'mashsb_tracking_last_send', time() );
                }
                
                $data = $this->data;
	}

	/**
	 * Check for a new opt-in on settings save
	 *
	 * This runs during the sanitation of General settings, thus the return
	 *
	 * @access public
	 * @return array
	 */
	public function check_for_settings_optin( $input ) {
		// Send an intial check in on settings save

		if( isset( $input['allow_tracking'] ) ) {
			$this->send_checkin( true );
		}

		return $input;

	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @access public
	 * @return void
	 */
	public function check_for_optin( $data ) {

		global $mashsb_options;

		$mashsb_options['allow_tracking'] = '1';

                if (!MASHSB_DEBUG)
		update_option( 'mashsb_settings', $mashsb_options );

		$this->send_checkin( true );
                
                if (!MASHSB_DEBUG)
		update_option( 'mashsb_tracking_notice', '1' );

	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @access public
	 * @return void
	 */
	public function check_for_optout( $data ) {

		global $mashsb_options;
		if( isset( $mashsb_options['allow_tracking'] ) ) {
			unset( $mashsb_options['allow_tracking'] );
                         if (!MASHSB_DEBUG)
			update_option( 'mashsb_settings', $mashsb_options );
		}
                 if (!MASHSB_DEBUG)
		update_option( 'mashsb_tracking_notice', '1' );

		wp_redirect( remove_query_arg( 'mashsb_action' ) ); exit;

	}

	/**
	 * Get the last time a checkin was sent
	 *
	 * @access private
	 * @return false|string
	 */
	private function get_last_send() {
		return get_option( 'mashsb_tracking_last_send' );
	}

	/**
	 * Schedule a weekly checkin
	 *
	 * @access public
	 * @return void
	 */
	public function schedule_send() {
		// We send once a week (while tracking is allowed) to check in, which can be used to determine active sites
		add_action( 'mashsb_weekly_scheduled_events', array( $this, 'send_checkin' ) );
	}

	/**
	 * Display the admin notice to users that have not opted-in or out
	 *
	 * @access public
	 * @return void
	 */
	public function admin_notice() {
            
                if (!current_user_can('update_plugins'))
                    return;

		$hide_notice = get_option( 'mashsb_tracking_notice' );

		if( $hide_notice ) {
			return;
		}

		if( mashsb_get_option( 'allow_tracking', false ) ) {
			return;
		}

		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if(
			stristr( network_site_url( '/' ), '_dev'       ) !== false ||
			stristr( network_site_url( '/' ), 'localhost' ) !== false ||
			stristr( network_site_url( '/' ), ':8888'     ) !== false // This is common with MAMP on OS X
		) {
                         if (!MASHSB_DEBUG)
                            update_option( 'mashsb_tracking_notice', '1' );
		} else {
			$optin_url  = add_query_arg( 'mashsb_action', 'opt_into_tracking' );
			$optout_url = add_query_arg( 'mashsb_action', 'opt_out_of_tracking' );

			//$source         = substr( md5( get_bloginfo( 'name' ) ), 0, 10 );
                        $source         = substr( md5( get_bloginfo( 'admin_email' ) ), 0, 10 );
			$extensions_url = 'https://www.mashshare.net/add-ons/?utm_source=' . $source . '&utm_medium=admin&utm_term=notice&utm_campaign=MASHSBUsageTracking';
			echo '<div class="updated"><p>';
				echo sprintf( __( 'Allow Mashshare to track plugin usage? Opt-in to tracking and our newsletter and immediately be emailed a <strong>20%% discount to the Mashshare shop</strong>, valid towards the <a href="%s" target="_blank">purchase of Add-Ons</a>. No sensitive data is tracked.', 'mashsb' ), $extensions_url );
				echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . __( 'Allow', 'mashsb' ) . '</a>';
				echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'mashsb' ) . '</a>';
			echo '</p></div>';
		}
	}

}
//$mashsb_tracking = new MASHSB_Tracking;