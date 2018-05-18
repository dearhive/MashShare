<?php

/**
 * Shorturl functions
 *
 * @package     MASHSB
 * @subpackage  Functions/Shorturls
 * @copyright   Copyright (c) 2016, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Check if Google API Key is working properly
 * 
 * @since 3.0.0
 * @return string
 * @deprecated since 3.5.3
 */

function mashsb_check_google_apikey() {

    global $mashsb_options;
    $appid = isset( $mashsb_options['google_app_id'] ) ? $appid = $mashsb_options['google_app_id'] : $appid = '';

    if( function_exists( 'curl_init' ) ) {
        $shorturl = new mashsb_google_shorturl( $appid );
        $statusArr = $shorturl->checkApiKey( 'http://www.google.de' );
    }

    isset( $statusArr['error']['errors'][0]['reason'] ) ? $statusArr['error']['errors'][0]['reason'] : $statusArr['error']['errors'][0]['reason'] = '';

    if( !empty( $statusArr['error']['errors'][0]['reason'] ) ) {
        return '<strong style="color:red;font-weight:bold;"> Notice: </strong>' . $statusArr['error']['errors'][0]['reason'];
    }
}

/*
 * Check if Bitly API Key is working properly
 * 
 * @since 3.0.0
 * @return mixed string | bool false when key is invalid
 */

function mashsb_check_bitly_apikey() {
    global $mashsb_options;

    $bitly_access_token = isset( $mashsb_options['bitly_access_token'] ) ? $bitly_access_token = $mashsb_options['bitly_access_token'] : '';
    // url to check
    $url = "http://www.google.de";
    
        $params = array();
        $params['access_token'] = $bitly_access_token;
        $params['longUrl'] = $url;
        //$results = bitly_get('link/lookup', $params);
        $bitly = new mashsb_bitly_shorturl();
        $results = $bitly->bitly_get( 'shorten', $params );

        if( !empty( $results['data']['url'] ) ) {
            return $results['data']['url'];
        } else {
            // Error
            return false;
        }

}

/**
 * Get shorturls from the post meta
 * 
 * @global array $post
 * @return string
 */
function mashsb_get_shorturl_singular( $url ) {
    global $mashsb_options, $post, $mashsb_custom_url;
    
    
    // no shorturl
    if( isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'disabled' ) {
        return $url;
    }
    
    // Use native WP Shortlinks | only when $mashsb_custom_url is empty. WP Shortlinks are not possible for non wordpress urls like www.google.com
    if( isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'wpshortlinks' && empty($mashsb_custom_url) ) {
        return wp_get_shortlink();
    }

    
    $shorturl = "";

    // Force cache rebuild
    if( mashsb_force_cache_refresh() ) {
        
        MASHSB()->logger->info( 'mashsb_get_shorturl_singular() -> refresh cache' );

        // bitly shortlink
        if( isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'bitly' ) {
            $shorturl = mashsb_get_bitly_link( $url );
            MASHSB()->logger->info('create shorturl singular: ' . $url . ' ' . $shorturl);
        }

        // Google shortlink
        if( isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'google' ) {
            $shorturl = mashsb_get_google_link( $url );
        }
        update_post_meta( $post->ID, 'mashsb_shorturl', $shorturl );
    } else {
        $shorturl = get_post_meta( $post->ID, 'mashsb_shorturl', true );
    }

    if( !empty( $shorturl ) && empty($mashsb_custom_url)) {
        return $shorturl;
    } else {
        return $url;
    }
}

/*
 * Get URL shortened URL
 * 
 * @since 1.0.0
 * @param $url
 * @return string
 */

function mashsb_get_shortened_url( $url ) {
    global $mashsb_options;

    // Return empty
    if( empty( $url ) ) {
        return "";
    }

    // Use native WP Shortlinks
    if( $mashsb_options['mashsu_methods'] === 'wpshortlinks' ) {
        return wp_get_shortlink();
    }

    // If is_singular post store shorturl in custom post fields 
    // and return it from there so user has the power to change values 
    // on his own from the post edit page and the custom fields editor
    if( is_singular() ) {
        return mashsb_get_shorturl_singular( $url );
    }

    // Force cache rebuild
    if( mashsb_force_cache_refresh() ) {

        MASHSB()->logger->info( 'mashsb_get_shorturl() -> refresh cache' );

        // bitly shortlink
        if( isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'bitly' ) {
            $shorturl = mashsb_get_bitly_link( $url );
            MASHSB()->logger->info('create shorturl: ' . $url . ' ' . $shorturl);
        }

        // Google shortlink
//        if( isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'google' ) {
//            $shorturl = mashsb_get_google_link( $url );
//        }

        // Get expiration time
        $expiration = mashsb_get_expiration();
        set_transient( 'mash_url_' . md5( $url ), $shorturl, $expiration );
    } else {
        $shorturl = get_transient( 'mash_url_' . md5( $url ) );
    }

    if( !empty( $shorturl ) ) {
        return $shorturl;
    } else {
        return $url;
    }
}

/**
 * Get Google short url's (result: goo.gl)
 * 
 * @param string $url
 * @return string
 * @deprecated since 3.5.3
 */
function mashsb_get_google_link( $url ) {
    global $mashsb_options;

    $google_app_id = isset( $mashsb_options['google_app_id'] ) ? $mashsb_options['google_app_id'] : '';

    // get result and fill cache
    if( !empty( $google_app_id ) && !empty( $url ) ) {
        $shorturl = new mashsb_google_shorturl( $google_app_id );
        $shorturlres = $shorturl->shorten( $url, false );

        if( empty( $shorturlres ) ) {
            return $url;
        }

        unset( $shorturl );
        return $shorturlres;
    }
    return $url;
}

/*
 * Get shorturl generated by bitly
 * 
 * @return string
 */

function mashsb_get_bitly_link( $url ) {
    global $mashsb_options;

    $bitly_access_token = isset( $mashsb_options['bitly_access_token'] ) ? $bitly_access_token = $mashsb_options['bitly_access_token'] : '';

    if( !empty( $bitly_access_token ) && !empty( $url ) && !is_null( $url ) ) {

        $params = array();
        $params['access_token'] = $bitly_access_token;
        $params['longUrl'] = $url;
        //$results = bitly_get('link/lookup', $params);
        $bitly = new mashsb_bitly_shorturl();
        $results = $bitly->bitly_get( 'shorten', $params );

        if( !empty( $results['data']['url'] ) ) {
            return $results['data']['url'];
        }
    }
    return $url;
}
