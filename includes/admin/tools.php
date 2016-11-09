<?php
/**
 * Tools
 *
 * These are functions used for displaying MASHSB tools such as the import/export system.
 *
 * @package     MASHSB
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2014, Pippin Williamson, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains MASHSB-specific tools including the
 * built-in import/export system.
 *
 * @since       2.1.6
 * @author      Daniel J Griffiths
 * @return      void
 */
function mashsb_tools_page() {
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'import_export';
?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( mashsb_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$tab_url = remove_query_arg( array(
					'mashsb-message'
				), $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'mashsb_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.1.6
 * @return      array
 */
function mashsb_get_tools_tabs() {

	$tabs                  = array();
	$tabs['import_export'] = __( 'Import/Export', 'mashsb' );
        $tabs['system_info'] = __( 'System Info', 'mashsb' );

	return apply_filters( 'mashsb_tools_tabs', $tabs );
}



/**
 * Display the tools import/export tab
 *
 * @since       2.1.6
 * @return      void
 */
function mashsb_tools_import_export_display() {
    
        if( ! current_user_can( 'update_plugins' ) ) {
		return;
	}
    
	do_action( 'mashsb_tools_import_export_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Export Settings', 'mashsb' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the Mashshare settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'mashsb' ); ?></p>
			
			<form method="post" action="<?php echo admin_url( 'admin.php?page=mashsb-tools&tab=import_export' ); ?>">
				<p><input type="hidden" name="mashsb-action" value="export_settings" /></p>
				<p>
					<?php wp_nonce_field( 'mashsb_export_nonce', 'mashsb_export_nonce' ); ?>
					<?php submit_button( __( 'Export', 'mashsb' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox">
		<h3><span><?php _e( 'Import Settings', 'mashsb' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import the Mashshare settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'mashsb' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=mashsb-tools&tab=import_export' ); ?>">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="mashsb-action" value="import_settings" />
					<?php wp_nonce_field( 'mashsb_import_nonce', 'mashsb_import_nonce' ); ?>
					<?php submit_button( __( 'Import', 'mashsb' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'mashsb_tools_import_export_after' );
}
add_action( 'mashsb_tools_tab_import_export', 'mashsb_tools_import_export_display' );

/* check if function is disabled or not
 * 
 * @returns bool
 * @since 2.1.6
 */
function mashsb_is_func_disabled( $function ) {
  $disabled = explode( ',',  ini_get( 'disable_functions' ) );
  return in_array( $function, $disabled );
}

/**
 * Process a settings export that generates a .json file of the Mashshare settings
 *
 * @since       2.1.6
 * @return      void
 */
function mashsb_tools_import_export_process_export() {
	if( empty( $_POST['mashsb_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['mashsb_export_nonce'], 'mashsb_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_options' ) )
		return;

	$settings = array();
	$settings = get_option( 'mashsb_settings' );

	ignore_user_abort( true );

	//if ( ! mashsb_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
	if ( ! mashsb_is_func_disabled( 'set_time_limit' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . apply_filters( 'mashsb_settings_export_filename', 'mashsb-settings-export-' . date( 'm-d-Y' ) ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'mashsb_export_settings', 'mashsb_tools_import_export_process_export' );

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since 1.0
 * @param unknown $str File name
 * @return mixed File extension
 */
 function mashsb_get_file_extension( $str ) {
     $parts = explode( '.', $str );
     return end( $parts );
}

/* Convert an object to an associative array.
 * Can handle multidimensional arrays
 * 
 * @returns array
 * @since 2.1.6
 */
function mashsb_object_to_array( $data ) {
  if ( is_array( $data ) || is_object( $data ) ) {
    $result = array();
    foreach ( $data as $key => $value ) {
      $result[ $key ] = mashsb_object_to_array( $value );
    }
    return $result;
  }
  return $data;
}

/**
 * Process a settings import from a json file
 *
 * @since 2.1.6
 * @return void
 */
function mashsb_tools_import_export_process_import() {
	if( empty( $_POST['mashsb_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['mashsb_import_nonce'], 'mashsb_import_nonce' ) )
		return;

	if( ! current_user_can( 'update_plugins' ) )
		return;

    if( mashsb_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
        wp_die( __( 'Please upload a valid .json file', 'mashsb' ) );
    }

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'mashsb' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = mashsb_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'mashsb_settings', $settings );

	wp_safe_redirect( admin_url( 'admin.php?page=mashsb-tools&mashsb-message=settings-imported' ) ); exit;

}
add_action( 'mashsb_import_settings', 'mashsb_tools_import_export_process_import' );


/**
 * Display the system info tab
 *
 * @since       2.1.6
 * @return      void
 * @change      2.3.1
 */
function mashsb_tools_sysinfo_display() {
    
    if( ! current_user_can( 'update_plugins' ) ) {
		return;
	}
        
?>
	<form action="<?php echo esc_url( admin_url( 'admin.php?page=mashsb-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
		<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="mashsb-sysinfo" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac)."><?php echo mashsb_tools_sysinfo_get(); ?></textarea>
		<p class="submit">
			<input type="hidden" name="mashsb-action" value="download_sysinfo" />
			<?php submit_button( 'Download System Info File', 'primary', 'mashsb-download-sysinfo', false ); ?>
		</p>
	</form>
<?php
}
add_action( 'mashsb_tools_tab_system_info', 'mashsb_tools_sysinfo_display' );


/**
 * Get system info
 *
 * @since       2.1.6
 * @access      public
 * @global      object $wpdb Used to query the database using the WordPress Database API
 * @global      array $mashsb_options Array of all MASHSB options
 * @return      string $return A string containing the info to output
 */
function mashsb_tools_sysinfo_get() {
	global $wpdb, $mashsb_options;

	if( !class_exists( 'Browser' ) )
		require_once MASHSB_PLUGIN_DIR . 'includes/libraries/browser.php';

	$browser = new Browser();

	// Get theme info
	if( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
	}


	$return  = '### Begin System Info ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$return  = apply_filters( 'mashsb_sysinfo_after_site_info', $return );


	// The local users' browser information, handled by the Browser class
	$return .= "\n" . '-- User Browser' . "\n\n";
	$return .= $browser;

	$return  = apply_filters( 'mashsb_sysinfo_after_user_browser', $return );

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'     => false,
		'timeout'       => 60,
		'user-agent'    => 'MASHSB/' . MASHSB_VERSION,
		'body'          => $request
	);

	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return  = apply_filters( 'mashsb_sysinfo_after_wordpress_config', $return );

	// MASHSB configuration
	$return .= "\n" . '-- MASHSB Configuration' . "\n\n";
	$return .= 'Version:                  ' . MASHSB_VERSION . "\n";
	$return .= 'Upgraded From:            ' . get_option( 'mashsb_version_upgraded_from', 'None' ) . "\n";

	$return  = apply_filters( 'mashsb_sysinfo_after_mashsb_config', $return );


	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";
	
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach( $plugins as $plugin_path => $plugin ) {
		if( !in_array( $plugin_path, $active_plugins ) )
			continue;

		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
	}

	$return  = apply_filters( 'mashsb_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach( $plugins as $plugin_path => $plugin ) {
		if( in_array( $plugin_path, $active_plugins ) )
			continue;

		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
	}

	$return  = apply_filters( 'mashsb_sysinfo_after_wordpress_plugins_inactive', $return );

	if( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if( !array_key_exists( $plugin_base, $active_plugins ) )
				continue;

			$plugin  = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
		}

		$return  = apply_filters( 'mashsb_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return  = apply_filters( 'mashsb_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

	$return  = apply_filters( 'mashsb_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return  = apply_filters( 'mashsb_sysinfo_after_php_ext', $return );

	$return .= "\n" . '### End System Info ###';

	return $return;
}


/**
 * Generates a System Info download file
 *
 * @since       2.0
 * @return      void
 */
function mashsb_tools_sysinfo_download() {
    
        if( ! current_user_can( 'update_plugins' ) )
		return;
    
	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="mashsb-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['mashsb-sysinfo'] );
	wp_die();
}
add_action( 'mashsb_download_sysinfo', 'mashsb_tools_sysinfo_download' );
