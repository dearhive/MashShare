<?php
/**
 * License handler for Mashshare LikeAfterShare Add-On
 *
 * This class should simplify the process of adding license information
 * to new MASHSB extensions.
 *
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MASHSB_License' ) ) :

/**
 * MASHSB_License Class
 */
class MASHSB_License {
	private $file;
	private $license;
	private $item_name;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = 'https://www.mashshare.net';

	/**
	 * Class constructor
	 *
	 * @global  array $mashsb_options
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 */
	function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null ) {
		global $mashsb_options;

		$this->file           = $_file;
		$this->item_name      = $_item_name;
		$this->item_shortname = 'mashsb_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->license        = isset( $mashsb_options[ $this->item_shortname . '_license_key' ] ) ? trim( $mashsb_options[ $this->item_shortname . '_license_key' ] ) : '';
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		/**
		 * Allows for backwards compatibility with old license options,
		 * i.e. if the plugins had license key fields previously, the license
		 * handler will automatically pick these up and use those in lieu of the
		 * user having to reactive their license.
		 */
		if ( ! empty( $_optname ) && isset( $mashsb_options[ $_optname ] ) && empty( $this->license ) ) {
			$this->license = trim( $mashsb_options[ $_optname ] );
		}

		// Setup hooks
		$this->includes();
		$this->hooks();
		$this->auto_updater();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'MASHSB_SL_Plugin_Updater' ) ) require_once 'MASHSB_SL_Plugin_Updater.php';
                
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {
		// Register settings
		add_filter( 'mashsb_settings_licenses', array( $this, 'settings' ), 1 );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

		//Updater | We call it directly in $this->auto_updater. This action is not running here
		//add_action( 'plugins_loaded', array( $this, 'auto_updater' ) );
	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @global  array $mashsb_options
	 * @return  void
	 */
	public function auto_updater() {

		if ( 'valid' !== get_option( $this->item_shortname . '_license_active' ) )
			return;

		// Setup the updater
		$mashsb_updater = new MASHSB_SL_Plugin_Updater(
			$this->api_url,
			$this->file,
			array(
				'version'   => $this->version,
				'license'   => $this->license,
				'item_name' => $this->item_name,
				'author'    => $this->author
			)
		);
	}


	/**
	 * Add license field to settings
	 *
	 * @access  public
	 * @param array   $settings
	 * @return  array
	 */
	public function settings( $settings ) {
		$mashsb_license_settings = array(
			array(
				'id'      => $this->item_shortname . '_license_key',
				'name'    => sprintf( __( '%1$s License Key', 'mashsb' ), $this->item_name ),
				'desc'    => '',
				'type'    => 'license_key',
				'options' => array( 'is_valid_license_option' => $this->item_shortname . '_license_active' ),
				'size'    => 'regular'
			)
		);

		return array_merge( $settings, $mashsb_license_settings );
	}


	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license() {
		if ( ! isset( $_POST['mashsb_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['mashsb_settings'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		foreach( $_POST as $key => $value ) {
			if( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}

		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( 'valid' == get_option( $this->item_shortname . '_license_active' ) ) {
			return;
		}

		$license = sanitize_text_field( $_POST['mashsb_settings'][ $this->item_shortname . '_license_key' ] );

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

                
		// Call the API
		$response = wp_remote_get(
			esc_url(add_query_arg( $api_params, $this->api_url ),
			array(
				'timeout'   => 15,
				'sslverify' => false
			))
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) )
			return;

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( $this->item_shortname . '_license_active', $license_data->license );

	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license() {

		if ( ! isset( $_POST['mashsb_settings'] ) )
			return;

		if ( ! isset( $_POST['mashsb_settings'][ $this->item_shortname . '_license_key' ] ) )
			return;

		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Run on deactivate button press
		if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate' ] ) ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_get(
				esc_url(add_query_arg( $api_params, $this->api_url ),
				array(
					'timeout'   => 15,
					'sslverify' => false
				))
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) )
				return;

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			delete_option( $this->item_shortname . '_license_active' );
		}
	}
}

endif; // end class_exists check
