<?php
/**
 * Weclome Page Class
 *
 * @package     MASHSB
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MASHSB_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */
class MASHSB_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 1.0.1
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	

	/**
	 * Sends user to the Settings page on first activation of MASHSB as well as each
	 * time MASHSB is upgraded to a new version
	 *
	 * @access public
	 * @since 1.0.1
	 * @global $mashsb_options Array of all the MASHSB Options
	 * @return void
	 */
	public function welcome() {
		global $mashsb_options;

		// Bail if no activation redirect
		if ( ! get_transient( '_mashsb_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_mashsb_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		$upgrade = get_option( 'mashsb_version_upgraded_from' );
                
                //@since 2.0.3
		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'options-general.php?page=mashsb-settings&tab=visual#mashsb_settingslocation_header' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'options-general.php?page=mashsb-settings&tab=visual#mashsb_settingslocation_header' ) ); exit;
		}
	}
}
new MASHSB_Welcome();
