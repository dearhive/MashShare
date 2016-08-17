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
if( !defined( 'ABSPATH' ) )
    exit;

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
    global $mashsb_parent_page, $mashsb_add_ons_page, $mashsb_settings_page, $mashsb_tools_page, $mashsb_quickstart;

    $mashshare_logo = 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbDpzcGFjZT0icHJlc2VydmUiIGZpbGw9Im5vbmUiIHdpZHRoPSI1MDBweCIgaGVpZ2h0PSI1MDBweCIgdmlld0JveD0iMCAwIDUxMiA1MTIiPg0KPGc+DQoJPHBhdGggc3R5bGU9ImZpbGw6I2ZmZiIgZD0iTTIuOSw0OGgxNDZsMTAxLjMsMjM4TDM1Mi4xLDQ4aDE0NS43djQ0NC44SDM4OS4zVjE2Ny41TDI4Ni44LDQwNy4zaC03Mi43TDExMS42LDE2Ny41djMyNS4zSDIuOVY0OHoiLz4NCjwvZz4NCjwvc3ZnPg==';
    // Getting Started Page
    $mashsb_parent_page = add_menu_page( 'Mashshare Settings', __( 'MashShare', 'mashsb' ), 'manage_options', 'mashsb-settings', 'mashsb_options_page', 'data:image/svg+xml;base64,' . $mashshare_logo);
    $mashsb_settings_page = add_submenu_page( 'mashsb-settings', __( 'MashShare Settings', 'mashsb' ), __( 'Settings', 'mashsb' ), 'manage_options', 'mashsb-settings', 'mashsb_options_page' );
    $mashsb_add_ons_page = add_submenu_page( 'mashsb-settings', __( 'MashShare Add-Ons', 'mashsb' ), '<span style="color:#f18500">' . __( 'Install Add-Ons', 'mashsb' ) . '</span>', 'manage_options', 'mashsb-addons', 'mashsb_add_ons_page' );
    $mashsb_tools_page = add_submenu_page( 'mashsb-settings', __( 'MashShare Tools', 'mashsb' ), __( 'Im/Export & System', 'mashsb' ), 'manage_options', 'mashsb-tools', 'mashsb_tools_page' );

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
    $currentpage = isset( $_GET['page'] ) ? $_GET['page'] : '';
    if( !is_admin() || !did_action( 'wp_loaded' ) ) {
        return false;
    }

    global $mashsb_parent_page, $pagenow, $typenow, $mashsb_settings_page, $mashsb_add_ons_page, $mashsb_tools_page, $mashsb_quickstart;

    if( 'mashsb-settings' == $currentpage || 'mashsb-addons' == $currentpage || 'mashsb-tools' == $currentpage || 'mashsb-getting-started' == $currentpage || 'mashsb-credits' == $currentpage || 'mashsb-about' == $currentpage  ) {
        return true;
    }
}
