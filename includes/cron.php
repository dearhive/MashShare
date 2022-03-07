<?php

/**
 * Cron functions
 *
 * @package     MASHSB
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, Rene Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.7
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

add_action( 'mashsb_cron_daily', 'mashsb_check_fb_api_key' );

// Check access token in admin-notice.php
add_action( 'mashsb_check_access_token', 'mashsb_check_fb_api_key' );

function mashsb_check_fb_api_key() {
    global $mashsb_options;

    $fb_token = !empty( $mashsb_options['fb_access_token_new'] ) ? $mashsb_options['fb_access_token_new'] : false;

    if( $fb_token && mashsb_curl_installed() ) {

        // Test facebook api with access token
        $url = 'https://graph.facebook.com/v2.7/?id=http://www.google.com&access_token=' . $mashsb_options['fb_access_token_new'];
        $curl_handle = curl_init();
        curl_setopt( $curl_handle, CURLOPT_URL, $url );
        curl_setopt( $curl_handle, CURLOPT_CONNECTTIMEOUT, 2 );
        curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, 1 );
        $buffer = curl_exec( $curl_handle );
        curl_close( $curl_handle );

        $data = json_decode( $buffer );

        if( empty( $buffer ) ) {
            update_option( 'mashsb_valid_fb_api_key', 'The access token is not working because facebook is returning unknown error. Delete the token or create a new one.' );
        } else if( is_object($data) && !empty( $data->error->message ) ) {
            update_option( 'mashsb_valid_fb_api_key', $data->error->message );
        } else if( is_object($data) && isset( $data->share->share_count ) ) {
            update_option( 'mashsb_valid_fb_api_key', 'success' );
        } else {
            update_option( 'mashsb_valid_fb_api_key', 'The access token is not working because faceboook is returning unknown error. Delete the token or create a new one.' );
        }
    }
    return false;
}
