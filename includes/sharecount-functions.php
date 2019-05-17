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
 * Save fb share count asnycronically via ajax
 */
function mashsb_set_fb_sharecount() {

   $postId = isset( $_POST['postid'] ) ? $_POST['postid'] : false;
   
   // Ajax result
   $result = isset( $_POST['shares'] ) ? $_POST['shares'] : false;
   $comment_count = isset( $result['comment_count'] ) ? (int)$result['comment_count'] : 0;
   $share_count = isset( $result['share_count'] ) ? (int)$result['share_count'] : 0;
   
   if( !$postId || empty($postId) ) {
      wp_die('MashShare: do not collect shares');
   }

   // Cache results
   $cacheJsonShares = mashsb_get_jsonshares_post_meta( $postId );
   $cacheTotalShares = mashsb_get_total_shares_post_meta( $postId );
   
   // New shares
   $cacheJsonShares['facebook_total'] = $share_count + $comment_count;
   $cacheJsonShares['facebook_likes'] = $share_count;
   $cacheJsonShares['facebook_comments'] = $comment_count;

   // Update shares but only if new shares are larger than cached values
   // Performance reasons AND to ensure that shares does not get lost if page permalink is changed
   update_post_meta( $postId, 'mashsb_jsonshares', json_encode($cacheJsonShares) );
   
   $newTotalShares = mashsb_get_total_shares($postId);
   if ($newTotalShares > $cacheTotalShares && is_numeric($newTotalShares) ){
      update_post_meta( $postId, 'mashsb_shares', $newTotalShares );
   }
   wp_die( json_encode( $cacheJsonShares ) );
}

add_action( 'wp_ajax_mashsb_set_fb_shares', 'mashsb_set_fb_sharecount' );
add_action( 'wp_ajax_nopriv_mashsb_set_fb_shares', 'mashsb_set_fb_sharecount' );

/**
 * 
 * @param string $url
 * @param int $comment_count
 * @param int $share_count
 * @return int
 */
//function mashsb_set_fb_shares_transient( $url, $comment_count = 0, $share_count = 0) {
//   if (empty($url)){
//      return 0;
//   } 
//   
//   $mode = isset( $mashsb_options['facebook_count_mode'] ) ? $mashsb_options['facebook_count_mode'] : 'total';
//
//   // Expiration
//   $expiration = mashsb_get_expiration();
//
//   // Remove variables, parameters and trailingslash
//   $url_clean = mashsb_sanitize_url( $url );
//   
//   // Get existing share count
//   $current_shares = mashsbGetShareCountFromTransient( $url_clean );
//
//
//      // It's request limited
//      if( mashsb_is_req_limited() ) {
//         return mashsbGetShareCountFromTransient( $url_clean );
//      }
//
//      // Regenerate the data and save the transient
//
//      // Get the share counts
//      if( $mode === 'total' ) {
//         $shares = $current_shares + $comment_count + $share_count;
//      }
//      if( $mode === 'shares' ) {
//         $shares = $current_shares + $share_count;
//      }
//      // Update shares only if resulted shares are more than stored shares
//      if ($shares > $current_shares){
//         // Set the transient and return shares
//         set_transient( 'mashcount_' . md5( $url_clean ), $shares, $expiration );
//         MASHSB()->logger->info( 'mashsb_set_fb_shares_transient set_transient - shares:' . $shares . ' url: ' . $url_clean );
//      }
//      return $shares + getFakecount();
//   
//}

/**
 * Get post meta mashsb_jsonshares
 * @param bool $postId
 * @return array
 */
function mashsb_get_jsonshares_post_meta( $postId = false ) {
   $result = array();

   if( $postId === false )
      return $result;

   $result = json_decode(get_post_meta( $postId, 'mashsb_jsonshares', true ), true);

   return $result;
}

/**
 * Get total shares
 * @param int  $postId
 * @return int
 */
function mashsb_get_total_shares($postId){
      $mode = isset( $mashsb_options['facebook_count_mode'] ) ? $mashsb_options['facebook_count_mode'] : 'total';
   
      $result = json_decode(get_post_meta( $postId, 'mashsb_jsonshares', true ), true);
      
      $fbtotal = isset($result['facebook_total']) ? $result['facebook_total'] : 0;
      $fbshares = isset($result['facebook_likes']) ? $result['facebook_likes'] : 0;
      $fbcomments = isset($result['facebook_comments']) ? $result['facebook_comments'] : 0;
      $twitter = isset($result['twitter']) ? $result['twitter'] : 0;
      $google = isset($result['google']) ? $result['google'] : 0;
      $pinterest = isset($result['pinterest']) ? $result['pinterest'] : 0;
      $linkedin = isset($result['linkedin']) ? $result['linkedin'] : 0;
      $stumbleupon = isset($result['stumbleupon']) ? $result['stumbleupon'] : 0;
      
      
   if( $mode === 'total' ) {
      $shares = $fbtotal + $twitter + $google + $pinterest + $linkedin + $stumbleupon;
   }
   if( $mode === 'shares' ) {
      $shares = $fbshares + $twitter + $google + $pinterest + $linkedin + $stumbleupon;
   }
   
   return (int)$shares;

}

/**
 * Get post meta mashsb_shares
 * @param bool $postId
 * @return mixed string|boolean
 */
function mashsb_get_total_shares_post_meta($postId = false){
   if ($postId === false)
      return false;
   
   $result = get_post_meta( $postId, 'mashsb_shares', true );
   
   return (int)$result;        
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
     * Make sure that requests do not exceed 1req / 60second
     * @return boolean
     */
    function mashsb_is_req_limited() {
        global $mashsb_debug;
        
        $data_timeout = get_option('_transient_timeout_mashsb_limit_req');
        
        if (false === $data_timeout || empty($data_timeout) || $data_timeout < time() ){
            set_transient('mashsb_limit_req', '1', 5);
            $mashsb_debug[] = 'Temp Rate Limit not exceeded';
            return false;
        }
            $mashsb_debug[] = 'Temp Rate Limit Exceeded';
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
    
    if (isset($mashsb_options['disable_sharecount'])){
       return false;
    }
    
    // Force Cache Reload
    if( isset( $_GET['mashsb-refresh'] ) ) {
        return true;
    }
    
    // Preview Mode
    if( isset($_GET['preview_id'] ) ) {
        return false;
    }
    
    
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
    
    if( is_admin() || is_404() || is_search() || !mashsb_is_enabled_permalinks() ) {
         return false;
    }
    
    /* 
     * Refreshing cache on multiple blog posts like categories will lead 
     * to high load and multiple API requests so we only check
     * the main url
    */
   if( !is_singular() || !isset($post->ID) ) {
      return false;
    }
      $last_updated = 0;
      $last_updated = get_post_meta( $post->ID, 'mashsb_timestamp', true );

//    else {
//        $url = mashsb_get_main_url();
//        if (empty($url))
//           return false;
//        $transient = '_transient_timeout_mashcount_' . md5( $url );
//        $last_updated = get_option( $transient ) - mashsb_get_expiration();      
//    }
    
    // No timestamp! So let's create cache for the first time
    if( empty( $last_updated ) || $last_updated < 0 ) {
        // Write timestamp (Use this on top of this condition. If this is not on top following return statements will be skipped and ignored - possible bug?)
        return true;
    }
    
    // The caching expiration
    $expiration = mashsb_get_expiration();
    $next_update = $last_updated + $expiration;
    
    // Refresh Cache when last update plus expiration time is older than current time
    if( ($last_updated + $expiration) <= time() ) {
        // Write timestamp (Use this on top of this condition. If this is not on top following return statements will be skipped and ignored - possible bug?)
        return true;
    }
    
    return false;
}
/**
 * Check if cache time is expired and post must be refreshed
 * This is used for the caching method async only
 * 
 * @global array $post
 * @return boolean 
 */
function mashsb_is_async_cache_refresh() {
    global $post, $mashsb_options;
    
    // Never
    if (isset($mashsb_options['refresh_loading'])){
       return false;
    }
    
    if (isset($mashsb_options['disable_sharecount'])){
       return false;
    }
    
    // Force Cache Reload
    if( isset( $_GET['mashsb-refresh'] ) ) {
        return true;
    }
    
    // Preview Mode
    if( isset($_GET['preview_id'] ) ) {
        return false;
    }
    
    
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
    
    if( is_admin() || is_404() || is_search() || !mashsb_is_enabled_permalinks() ) {
         return false;
    }
    
    /* 
     * Refreshing cache on multiple blog posts like categories will lead 
     * to high load and multiple API requests so we only check
     * the main url
    */
   if( !is_singular() || !isset($post->ID) ) {
      return false;
    }
      $last_updated = 0;
      $last_updated = get_post_meta( $post->ID, 'mashsb_timestamp', true );
    
    // No timestamp! So let's create cache for the first time
    if( empty( $last_updated ) || $last_updated < 0 ) {
        // Write timestamp (Use this on top of this condition. If this is not on top following return statements will be skipped and ignored - possible bug?)
        return true;
    }
    
    // The caching expiration
    $expiration = mashsb_get_expiration();
    $next_update = $last_updated + $expiration;
    
    // Refresh Cache when last update plus expiration time is older than current time
    if( ($last_updated + $expiration) <= time() ) {
        // Write timestamp (Use this on top of this condition. If this is not on top following return statements will be skipped and ignored - possible bug?)
        return true;
    }
    
    return false;
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
        $seconds = apply_filters('mashsb_refresh_1_hour', 3600);
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

    //$expiration = 10;
    
    // Set expiration time to zero if debug mode is enabled or cache deactivated
    if( MASHSB_DEBUG || isset( $mashsb_options['disable_cache'] ) ) {
        $expiration = 0;
    }

    return ( int ) $expiration;
}

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
    } else {
       return '';
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
