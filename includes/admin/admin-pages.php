<?php
/**
 * Admin Pages
 *
 * @package     MASHSB
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Mashshare menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $mashsb_settings_page
 * @global $mashsb_add_ons_page
 * @global $mashsb_tools_page
 * @return void
 */
function mashsb_add_options_link() {
	global $mashsb_parent_page, $mashsb_add_ons_page, $mashsb_add_ons_page2, $mashsb_settings_page, $mashsb_tools_page;

        //$mashsb_parent_page = add_menu_page( 'Mashshare Welcome Screen' , 'Mashshare' , 'manage_options' , 'mashshare-welcome' , 'mashshare_welcome_conf');   
	$mashsb_parent_page   = add_menu_page( 'Mashshare Settings', __( 'Mashshare', 'mashsb' ), 'manage_options', 'mashsb-settings', 'mashsb_options_page' );
        $mashsb_settings_page = add_submenu_page( 'mashsb-settings', __( 'Mashshare Settings', 'mashsb' ), __( 'Settings', 'mashsb' ), 'manage_options', 'mashsb-settings', 'mashsb_options_page' );
        $mashsb_add_ons_page  = add_submenu_page( 'mashsb-settings', __( 'Mashshare Add Ons', 'mashsb' ), __( 'Add Ons', 'mashsb' ), 'manage_options', 'mashsb-addons', 'mashsb_add_ons_page' ); 
        $mashsb_tools_page = add_submenu_page( 'mashsb-settings', __( 'Mashshare Tools', 'mashsb' ), __( 'Tools', 'mashsb' ), 'manage_options', 'mashsb-tools', 'mashsb_tools_page' );

}
add_action( 'admin_menu', 'mashsb_add_options_link', 10 );

/**
 *  Determines whether the current admin page is an MASHSB admin page.
 *  
 *  Only works after the `wp_loaded` hook, & most effective 
 *  starting on `admin_menu` hook.
 *  
 *  @since 1.9.6
 *  @return bool True if MASHSB admin page.
 */
function mashsb_is_admin_page() {
        $currentpage = isset($_GET['page']) ? $_GET['page'] : '';
	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		return false;
	}
	
	global $mashsb_parent_page, $pagenow, $typenow, $mashsb_settings_page, $mashsb_add_ons_page, $mashsb_tools_page;

	if ( 'mashsb-settings' == $currentpage || 'mashsb-addons' == $currentpage || 'mashsb-tools' == $currentpage) {
                mashdebug()->info("mashsb_is_admin_page() = true");
		return true;      
	}
	
	//$mashsb_admin_pages = apply_filters( 'mashsb_admin_pages', array( $mashsb_parent_page, $mashsb_settings_page, $mashsb_add_ons_page, ) );
	
	/*if ( in_array( $currentpage, $mashsb_admin_pages ) ) {
            mashdebug()->info("mashsb_is_admin_page() = true");
		return true;
	} else {
		return false;
                mashdebug()->info("mashsb_is_admin_page() = false");
	}
         * */
         
}
