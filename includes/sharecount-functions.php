<?php

/**
 * Helper functions for retriviving the share counts from social networks
 *
 * @package     MASHSB
 * @subpackage  Functions/sharecount
 * @copyright   Copyright (c) 2015, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Check if the facebook rate limit has been exceeded
 * @return boolean
 */
function mashsb_rate_limit_exceeded(){
    //return true; // Uncomment this for testing
    if (false === get_transient('mashsb_rate_limit')){
        return false;
    }
    return true;
}


    /**
     * Make sure that requests do not exceed 1req / 25second
     * @return boolean
     */
    function mashsb_is_req_limited() {
        global $mashsb_error;
        
        $data_timeout = get_option('_transient_timeout_mashsb_limit_req');
        
        // Rewrite this because wordpress sometimes does not store the expiration value so we need to check this value first
        //if (false === get_transient('mashsb_limit_req')) {
        
        if (false === $data_timeout || empty($data_timeout) || $data_timeout < time() ){
            set_transient('mashsb_limit_req', '1', 25);
            $mashsb_error[] = 'MashShare: Temp Rate Limit not exceeded';
            return false;
        }
            $mashsb_error[] = 'MashShare: Temp Rate Limit Exceeded';
        return true;
        
    }

/**
 * Check if cache time is expired and post must be refreshed
 * 
 * @global array $post
 * @return boolean 
 */
function mashsb_is_cache_refresh() {
    global $post, $mashsb_options;
    
    
    // Debug mode or cache activated
    if( MASHSB_DEBUG || isset( $mashsb_options['disable_cache'] ) ) {
        MASHSB()->logger->info( 'mashsb_is_cache_refresh: MASHSB_DEBUG - refresh Cache' );
        return true;
    }
    
    // if it's a crawl deactivate cache
    if( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'] ) ) {
        return false;
    }
    
    /*
     * Deactivate share count on:
     * - 404 pages
     * - search page
     * - empty url
     * - disabled permalinks
     * - admin pages
     * 
        Exit here to save cpu time
     */

    if( is_404() || is_search() || is_admin() || !mashsb_is_enabled_permalinks() ) {
        return false;
    }

    // New cache on singular pages
    // 
    // Refreshing cache on blog posts like categories will lead 
    // to high load and multiple API requests so we only check 
    // the main url on these other pages
    if( is_singular() ) {
        // last updated timestamp 
        $last_updated = get_post_meta( $post->ID, 'mashsb_timestamp', true );
        if( !empty( $last_updated ) ) {
            MASHSB()->logger->info( 'mashsb_is_cache_refresh - is_singular() url: ' . get_permalink($post->ID) . ' : last updated:' . date( 'Y-m-d H:i:s', $last_updated ) );
        }
    } else if( mashsb_get_main_url() ) {

        // Get transient timeout and calculate last update time
        $url = mashsb_get_main_url();
        $transient = '_transient_timeout_mashcount_' . md5( mashsb_get_main_url() );
        $last_updated = get_option( $transient ) - mashsb_get_expiration();
        if( !empty( $last_updated ) ) {
            MASHSB()->logger->info( 'mashsb_is_cache_refresh() mashsb_get_main_url() url: ' . $url . ' last updated:' . date( 'Y-m-d H:i:s', $last_updated ) );
        }
    } else {
        // No valid URL so do not refresh cache
        MASHSB()->logger->info( 'mashsb_is_cache_refresh: No valid URL - do not refresh cache' );
        return false;
    }

    // No timestamp so let's create cache for the first time
    if( empty( $last_updated ) ) {
        MASHSB()->logger->info( 'mashsb_is_cache_refresh: No Timestamp. Refresh Cache' );
        return true;
    }

    // The caching expiration
    $expiration = mashsb_get_expiration();
    $next_update = $last_updated + $expiration;
    MASHSB()->logger->info( 'mashsb_is_cache_refresh. Next update ' . date( 'Y-m-d H:i:s', $next_update ) . ' current time: ' . date( 'Y-m-d H:i:s', time() ) );

    // Refresh Cache when last update plus expiration is older than current time
    if( ($last_updated + $expiration) <= time() ) {
        MASHSB()->logger->info( 'mashsb_is_cache_refresh: Refresh Cache!' );
        return true;
    }
}

/**
 * Check via ajax if cache should be updated
 * 
 * @deprecated not used
 * @return string numerical 
 */
function mashsb_ajax_refresh_cache() {
    if( mashsb_is_cache_refresh() ) {
        wp_die( '1' );
    } else {
        wp_die( '0' );
    }
}

add_action( 'wp_ajax_mashsb_refresh_cache', 'mashsb_ajax_refresh_cache' );
add_action( 'wp_ajax_nopriv_mashsb_refresh_cache', 'mashsb_ajax_refresh_cache' );

/**
 * Get expiration time for new Asyn Cache Method
 * 
 * @since 3.0.0
 * @return int
 */
function mashsb_get_expiration_method_async() {
    // post age in seconds
    $post_age = floor( date( 'U' ) - get_post_time( 'U', true ) );
    
    $three_months_period = apply_filters('mashsb_three_months', 5184000);
    
    $three_weeks_period = apply_filters('mashsb_three_weeks', 1814400);

    if( isset( $post_age ) && $post_age > $three_months_period ) {
        // Post older than 60 days - expire cache after 12 hours
        $seconds = apply_filters('mashsb_refresh_60_days', 43200);
    } else if( isset( $post_age ) && $post_age > $three_weeks_period ) {
        // Post older than 21 days - expire cache after 4 hours.
        $seconds = apply_filters('mashsb_refresh_21_days', 14400);
    } else {
        // expire cache after one hour
        $seconds = apply_filters('mashsb_refresh_1_hour', 3600);;
    }

    return $seconds;
}

/**
 * Get expiration time for old method "Refresh On Loading"
 * 
 * @since 3.0.0
 * @return int
 */
function mashsb_get_expiration_method_loading() {
    global $mashsb_options;
    // Get the expiration time
    $seconds = isset( $mashsb_options['mashsharer_cache'] ) ? ( int ) ($mashsb_options['mashsharer_cache']) : 300;

    return $seconds;
}

/**
 * Get expiration time
 * 
 * @return int
 */
function mashsb_get_expiration() {
    global $mashsb_options;
    $expiration = (isset( $mashsb_options['caching_method'] ) && $mashsb_options['caching_method'] == 'async_cache') ? mashsb_get_expiration_method_async() : mashsb_get_expiration_method_loading();

    // Set expiration time to zero if debug mode is enabled or cache deactivated
    if( MASHSB_DEBUG || isset( $mashsb_options['disable_cache'] ) ) {
        $expiration = 0;
    }

    return ( int ) $expiration;
}

/**
 * Check if we can use the REST API
 * 
 * @deprecated not used
 * @return boolean true
 */
//function mashsb_allow_rest_api() {
//    if( version_compare( get_bloginfo( 'version' ), '4.4.0', '>=' ) ) {
//        return true;
//    }
//}

/**
 * Check via REST API if cache should be updated
 * 
 * @since 3.0.0
 * @deprecated not used
 * @return string numerical 
 */
//function mashsb_restapi_refresh_cache( $request ) {
//    if( mashsb_is_cache_refresh() ) {
//        return '1';
//    } else {
//        return '0';
//    }
//}

/**
 * Register the API route
 * Used in WP 4.4 and later The WP REST API got a better performance than native ajax endpoints
 * Endpoint: /wp-json/mashshare/v1/verifycache/
 * 
 * @since 3.0.0
 * @deprecated not used
 * */
//if( mashsb_allow_rest_api() ) {
//    add_action( 'rest_api_init', 'mashsb_rest_routes' );
//}
//
//function mashsb_rest_routes() {
//    register_rest_route( 'mashshare/v1', '/verifycache/', array(
//        'methods' => \WP_REST_Server::READABLE,
//        'callback' => 'mashsb_restapi_refresh_cache'
//            )
//    );
//}

/**
 * Check if permalinks are enabled
 * 
 * @return boolean true when enabled
 */
function mashsb_is_enabled_permalinks() {
    $permalinks = get_option('permalink_structure');
    if (!empty($permalinks)) {
        return true;
    }
    return false;
}

/**
 * Return the current main url
 * 
 * @return mixed string|bool current url or false
 */
function mashsb_get_main_url() {
    global $wp;

    $url = home_url( add_query_arg( array(), $wp->request ) );
    if( !empty( $url ) ) {
        return mashsb_sanitize_url( $url );
    }
}

/**
 * Sanitize url and remove mashshare specific url parameters
 * 
 * @param string $url
 * @return string $url
 */
function mashsb_sanitize_url( $url ) {
    if( empty( $url ) ) {
        return "";
    }

    $url1 = str_replace( '?mashsb-refresh', '', $url );
    $url2 = str_replace( '&mashsb-refresh', '', $url1 );
    $url3 = str_replace( '%26mashsb-refresh', '', $url2 );
    
    return $url3;
}
