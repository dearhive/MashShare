<?php

/**
 * Template Functions
 *
 * @package     MASHSB
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;


/* Load Hooks
 * @since 2.0
 * return void
 */

add_shortcode( 'mashshare', 'mashshareShortcodeShow' );
add_filter( 'the_content', 'mashshare_filter_content', getExecutionOrder(), 1 );
add_filter( 'widget_text', 'do_shortcode' );
add_action( 'mashshare', 'mashshare' );
add_filter( 'mash_share_title', 'mashsb_get_title', 10, 2 );


/* Get Execution order of injected Share Buttons in $content 
 *
 * @since 2.0.4
 * @return int
 */

function getExecutionOrder() {
    global $mashsb_options;
    isset( $mashsb_options['execution_order'] ) && is_numeric( $mashsb_options['execution_order'] ) ? $priority = trim( $mashsb_options['execution_order'] ) : $priority = 1000;
    return $priority;
}

/* 
 * Get mashsbShareObject 
 * depending on MashEngine (or sharedcount.com deprecated) is used
 * 
 * @since 2.0.9
 * @return object
 * @changed 3.1.8
 */

function mashsbGetShareObj( $url ) {
   global $mashsb_options;

   // Sharedcount.com is default option
   $mashengine = isset( $mashsb_options['mashsb_sharemethod'] ) && $mashsb_options['mashsb_sharemethod'] === 'mashengine' ? true : false;
   if( !$mashengine ) {
      require_once(MASHSB_PLUGIN_DIR . 'includes/sharedcount.class.php');
      $apikey = isset( $mashsb_options['mashsharer_apikey'] ) ? $mashsb_options['mashsharer_apikey'] : '';
      $mashsbSharesObj = new mashsbSharedcount( $url, 10, $apikey );
      return $mashsbSharesObj;
   }

   if( !class_exists( 'RollingCurlX' ) ) {
      require_once MASHSB_PLUGIN_DIR . 'includes/libraries/RolingCurlX.php';
   }
   if( !class_exists( 'mashengine' ) ) {
      require_once(MASHSB_PLUGIN_DIR . 'includes/mashengine.php');
   }

   MASHSB()->logger->info( 'mashsbGetShareObj() url: ' . $url );
   $mashsbSharesObj = new mashengine( $url );
   return $mashsbSharesObj;
}

/*
 * Use the correct share method depending on mashshare networks enabled or not
 * 
 * @since 2.0.9
 * @returns int share count
 */

function mashsbGetShareMethod( $mashsbSharesObj ) {
    if( class_exists( 'MashshareNetworks' ) ) {
        $mashsbShareCounts = $mashsbSharesObj->getAllCounts();
        return $mashsbShareCounts;
    }
    $mashsbShareCounts = $mashsbSharesObj->getFBTWCounts();
    return $mashsbShareCounts;
}

/**
 * Get share count for all non singular pages where $post is empty or a custom url is used E.g. category or blog list pages or for shortcodes
 * Uses transients 
 * 
 * @param string $url
 *  
 * @returns integer $shares
 */
function mashsbGetNonPostShares( $url ) {
    global $mashsb_debug;
    
    /*
     * Deactivate share count on:
     * - 404 pages
     * - search page
     * - empty url
     * - disabled permalinks
     * - disabled share count setting
     * - rate limit exceeded
     * - deprecated: admin pages (we need to remove this for themes which are using a bad infinite scroll implementation where is_admin() is always true)
     */

       
    if( is_404() || is_search() || empty($url) || !mashsb_is_enabled_permalinks() || isset($mashsb_options['disable_sharecount']) || mashsb_rate_limit_exceeded() ) {
        $mashsb_debug[] = 'MashShare: Share count (temporary) disabled';
        return apply_filters( 'filter_get_sharedcount', 0 );
    }
    
    
    // Expiration
    $expiration = mashsb_get_expiration();
    
    // Remove variables, parameters and trailingslash
    $url_clean = mashsb_sanitize_url( $url );

    // Get any existing copy of our transient data and fill the cache
    if( mashsb_force_cache_refresh() ) {
        
        // Its request limited
        if ( mashsb_is_req_limited() ){
            mashsbGetShareCountFromTransient($url_clean);
        }

        // Regenerate the data and save the transient
        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj( $url_clean );
        // Get the share counts object
        $mashsbShareCounts = mashsbGetShareMethod( $mashsbSharesObj );

        // Set the transient and return shares
        set_transient( 'mashcount_' . md5( $url_clean ), $mashsbShareCounts->total, $expiration );
        MASHSB()->logger->info( 'mashsbGetNonPostShares set_transient - shares:' . $mashsbShareCounts->total . ' url: ' . $url_clean );
        return $mashsbShareCounts->total + getFakecount();
    } else {
         mashsbGetShareCountFromTransient($url_clean);
    }
}
/**
 * get share count from transient
 * @param type string
 * @return int
 */
function mashsbGetShareCountFromTransient($url){
        $shares = get_transient( 'mashcount_' . md5( $url ) );
        if( isset( $shares ) && is_numeric( $shares ) ) {
            MASHSB()->logger->info( 'mashsbGetNonPostShares() get shares from get_transient. URL: ' . $url . ' SHARES: ' . $shares );
            return $shares + getFakecount();
        } else {
            return 0 + getFakecount(); // we need a result
        }
}


/*
 * Return the share count
 * 
 * @param string url of the page the share count is collected for
 * @returns int
 */

function getSharedcount( $url ) {
    global $mashsb_options, $post, $mashsb_sharecount, $mashsb_debug; // todo test a global share count var if it reduces the amount of requests
    
    /*
     * Deactivate share count on:
     * - 404 pages
     * - search page
     * - empty url
     * - disabled permalinks
     * - disabled share count setting
     * - rate limit exceeded
     * - deprecated: admin pages (we need to remove this for themes which are using a bad infinite scroll implementation where is_admin() is always true)
     */

    //|| mashsb_rate_limit_exceeded()
       
    if( is_404() || is_search() || empty($url) || !mashsb_is_enabled_permalinks() || isset($mashsb_options['disable_sharecount']) || isset($_GET['preview_id']) ) {
        $mashsb_debug[] = 'MashShare: Share count (temporary) disabled';
        return apply_filters( 'filter_get_sharedcount', 0 );
    }
    
    $mashsb_debug[] = 'Trying to get share count!';
        
    // Return global share count variable to prevent multiple execution
    if (is_array($mashsb_sharecount) && array_key_exists($url, $mashsb_sharecount) && !empty($mashsb_sharecount[$url]) && !mashsb_is_cache_refresh() ){
        return $mashsb_sharecount[$url] + getFakecount();
    }
   
    
    // Remove mashsb-refresh query parameter
    $url = mashsb_sanitize_url($url);
    

    /* 
     * Return share count on non singular pages when url is defined
       Possible: Category, blog list pages, non singular() pages. This store the shares in transients with mashsbGetNonPostShares();
     */


    if( !empty( $url ) && is_null( $post ) ) {
        $mashsb_debug[] = '$url or $post is empty. Return share count with mashsbGetNonPostShares';
        return apply_filters( 'filter_get_sharedcount', mashsbGetNonPostShares( $url ) );
    }

    /*
     * Refresh Cache
     */
    if( mashsb_force_cache_refresh() && is_singular() ) {
        
        $mashsb_debug[] = 'Force Cache Refresh for page type singular()';
        
        // Its request limited
        if ( mashsb_is_req_limited() ){ 
            $mashsb_debug[] = 'Rate limit reached: Return Share from custom meta field.';            
            return (int)get_post_meta( $post->ID, 'mashsb_shares', true ) + getFakecount();
        }

        // free some memory
        unset ( $mashsb_sharecount[$url] );
        
        // Write timestamp (Use this on top of this condition. If this is not on top following return statements will be skipped and ignored - possible bug?)
        update_post_meta( $post->ID, 'mashsb_timestamp', time() );

        MASHSB()->logger->info( 'Refresh Cache: Update Timestamp: ' . time() );
        $mashsb_debug[] = 'Refresh Cache: Update Timestamp: ' . time();
        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj( $url );
        // Get the share count Method
        $mashsbShareCounts = mashsbGetShareMethod( $mashsbSharesObj );
        // Get stored share count
        $mashsbStoredShareCount = get_post_meta( $post->ID, 'mashsb_shares', true );

        // Create global sharecount
        $mashsb_sharecount = array($url => $mashsbShareCounts->total);
        
        $mashsb_debug[] = 'Get Share count for URL: ' . $url . ' Shares: ' . $mashsbShareCounts->total;
        MASHSB()->logger->info( 'Get Share count for URL: ' . $url . ' Shares: ' . $mashsbShareCounts->total );
        /*
         * Update post_meta only when API is requested and
         * API share count is greater than real fresh requested share count ->
         */
        
        if( is_numeric($mashsbShareCounts->total) && $mashsbShareCounts->total >= $mashsbStoredShareCount ) {
            update_post_meta( $post->ID, 'mashsb_shares', $mashsbShareCounts->total );
            update_post_meta( $post->ID, 'mashsb_jsonshares', json_encode( $mashsbShareCounts ) );
            MASHSB()->logger->info( "Refresh Cache: Update database with share count: " . $mashsbShareCounts->total );
            
            /* return counts from getAllCounts() after DB update */
            return apply_filters( 'filter_get_sharedcount', $mashsbShareCounts->total + getFakecount() );
        }
        
        /* return previous counts from DB Cache | this happens when API has a hiccup and does not return any results as expected */
        return apply_filters( 'filter_get_sharedcount', $mashsbStoredShareCount + getFakecount() );
    } else {
        // Return cached results
        $cachedCountsMeta = is_numeric($var = get_post_meta( $post->ID, 'mashsb_shares', true )) ? (int)$var : 0;
        $cachedCounts = $cachedCountsMeta + getFakecount();
        $mashsb_debug[] = 'Cached Results: ' . $cachedCounts . ' url:' . $url;
        MASHSB()->logger->info( 'Cached Results: ' . $cachedCounts . ' url:' . $url );
        return apply_filters( 'filter_get_sharedcount', $cachedCounts );
    }
}

function mashsb_subscribe_button() {
    global $mashsb_options;
    if( $mashsb_options['networks'][2] ) {
        $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"><span class="text">' . __( 'Subscribe', 'mashsb' ) . '</span></span></a>';
    } else {
        $subscribebutton = '';
    }
    return apply_filters( 'mashsb_filter_subscribe_button', $subscribebutton );
}

/* Put the Subscribe container under the share buttons
 * @since 2.0.0.
 * @return string
 */

function mashsb_subscribe_content() {
    global $mashsb_options;
    if( isset( $mashsb_options['networks'][2] ) && isset( $mashsb_options['subscribe_behavior'] ) && $mashsb_options['subscribe_behavior'] === 'content' ) { //Subscribe content enabled
        $container = '<div class="mashsb-toggle-container">' . mashsb_cleanShortcode( 'mashshare', $mashsb_options['subscribe_content'] ) . '</div>';
    } else {
        $container = '';
    }
    return apply_filters( 'mashsb_toggle_container', $container );
}

/* Check if [mashshare] shortcode is used in subscribe field and deletes it
 * Prevents infinte loop
 * 
 * @since 2.0.9
 * @return string / shortcodes parsed
 */

function mashsb_cleanShortcode( $code, $content ) {
    global $shortcode_tags;
    $stack = $shortcode_tags;
    $shortcode_tags = array($code => 1);
    $content = strip_shortcodes( $content );
    $shortcode_tags = $stack;

    return do_shortcode( $content );
}

/* Round the totalshares
 * 
 * @since 1.0
 * @param $totalshares int
 * @return string
 */

function roundshares( $totalshares ) {
    if( $totalshares > 1000000 ) {
        $totalshares = round( $totalshares / 1000000, 1 ) . 'M';
    } elseif( $totalshares > 1000 ) {
        $totalshares = round( $totalshares / 1000, 1 ) . 'k';
    }
    return apply_filters( 'get_rounded_shares', $totalshares );
}

/* Return the more networks button
 * @since 2.0
 * @return string
 */

function onOffSwitch($size = false) {
    global $mashsb_options;
    
    // Get class names for buttons size
    $class_size = isset($mashsb_options['buttons_size']) ? ' ' . $mashsb_options['buttons_size'] : '';
    
    // Override size with shortcode argument
    $class_size = $size ? ' mash-'.$size : $class_size;
    
    // Get class names for button style
    $class_style = isset($mashsb_options['mash_style']) && $mashsb_options['mash_style'] === 'shadow' ? ' mashsb-shadow' : ' mashsb-noshadow';
    
    $output = '<div class="onoffswitch' . $class_size . $class_style . '"></div>';
    return apply_filters( 'mashsh_onoffswitch', $output );
}

/* Return the second more networks button after 
 * last hidden additional service. initial status: hidden
 * Become visible with click on plus icon
 * 
 * @since 2.0
 * @return string
 */

function onOffSwitch2( $size = false) {
    global $mashsb_options;
    
    // Get class names for buttons size
    $class_size = isset($mashsb_options['buttons_size']) ? ' ' . $mashsb_options['buttons_size'] : '';
    
    // Override size with shortcode argument
    $class_size = $size ? ' mash-'.$size : $class_size;
    
    // Get class names for button style
    $class_style = isset($mashsb_options['mash_style']) && $mashsb_options['mash_style'] === 'shadow' ? ' mashsb-shadow' : ' mashsb-noshadow';
    
    $output = '<div class="onoffswitch2' .$class_size . $class_style . '" style="display:none;"></div>';
    return apply_filters( 'mashsh_onoffswitch2', $output );
}

/*
 * Delete all services from array which are not enabled
 * @since 2.0.0
 * @return callback
 */

function isStatus( $var ) {
    return (!empty( $var["status"] ));
}

/*
 * Array of all available network share urls
 * 
 * @param string $name id of the network
 * @param bool $is_shortcode true when function is used in shortcode [mashshare]
 * 
 * @since 2.1.3
 * @return string
 */

function arrNetworks( $name, $is_shortcode ) {
    global $mashsb_custom_url, $mashsb_custom_text, $mashsb_twitter_url;

    if( $is_shortcode ) {
        $url = !empty( $mashsb_custom_url ) ? urlencode($mashsb_custom_url) : urlencode(mashsb_get_url());
        $title = !empty( $mashsb_custom_text ) ? $mashsb_custom_text : mashsb_get_title();
        $twitter_title = !empty( $mashsb_custom_text ) ? $mashsb_custom_text : mashsb_get_twitter_title();
    }
    if( !$is_shortcode ) {
        $url = urlencode(mashsb_get_url());
        $title = mashsb_get_title();
        $twitter_title = mashsb_get_twitter_title();
    }

    $via = mashsb_get_twitter_username() ? '&via=' . mashsb_get_twitter_username() : '';
    
    $networks_arr = array(
        'facebook' => 'https://www.facebook.com/sharer.php?u=' . mashsb_append_tracking_param($url, 'facebook'),
        'twitter' => 'https://twitter.com/intent/tweet?text=' . $twitter_title . '&url=' . $mashsb_twitter_url . $via,
        'subscribe' => '#',
        'url' => mashsb_append_tracking_param($url),
        'title' => $title
    );
    
    
    // Delete custom text
    unset ($mashsb_custom_text);
    // Delete custom url 
    unset ($mashsb_custom_url);

    $networks = apply_filters( 'mashsb_array_networks', $networks_arr );
    return isset( $networks[$name] ) ? $networks[$name] : '';
}

/* Returns all available networks
 * 
 * @since 2.0
 * @param bool true when used from shortcode [mashshare]
 * @param int number of visible networks (used for shortcodes)
 * @param array activated networks (used for shortcodes)
 * @param string button size (used for shortcodes)
 * @return string html
 */

function mashsb_getNetworks( $is_shortcode = false, $services = 0 ) {
    global $mashsb_options, $mashsb_custom_url, $enablednetworks, $mashsb_twitter_url;
    
    
    // define globals
    if( $is_shortcode ) {
        $mashsb_twitter_url = !empty( $mashsb_custom_url ) ? mashsb_get_shorturl( $mashsb_custom_url ) : mashsb_get_twitter_url();

    }else{
        $mashsb_twitter_url = mashsb_get_twitter_url();
    }
    
    // Get class names for buttons size
    $class_size = isset($mashsb_options['buttons_size']) ? ' ' . $mashsb_options['buttons_size'] : '';
    
    // Override size with shortcode argument
    //$class_size = $size ? ' mash-'.$size : $class_size;
    
    // Get class names for buttons margin
    $class_margin = isset($mashsb_options['button_margin']) ? '' : ' mash-nomargin';

    // Get class names for center align
    $class_center = isset($mashsb_options['text_align_center']) ? ' mash-center' : '';
    
    // Get class names for button style
    $class_style = isset($mashsb_options['mash_style']) && $mashsb_options['mash_style'] === 'shadow' ? ' mashsb-shadow' : ' mashsb-noshadow';

    $output = '';
    $startsecondaryshares = '';
    $endsecondaryshares = '';

    /* content of 'more services' button */
    $onoffswitch = '';

    /* counter for 'Visible Services' */
    $startcounter = 1;

    $maxcounter = isset( $mashsb_options['visible_services'] ) ? $mashsb_options['visible_services'] : 0;
    $maxcounter = ($maxcounter === 'all') ? 'all' : ($maxcounter + 1); // plus 1 to get networks correct counted (array's starting counting from zero)
    $maxcounter = apply_filters( 'mashsb_visible_services', $maxcounter );

    /* Overwrite maxcounter with shortcode attribute */
    $maxcounter = ($services === 0) ? $maxcounter : $services;

    /* 
     * Our list of available services, includes the disabled ones! 
     * We have to clean this array first!
     */
    $getnetworks = isset( $mashsb_options['networks'] ) ? apply_filters('mashsb_filter_networks', $mashsb_options['networks'])  : apply_filters('mashsb_filter_networks', '');
    
    
    /* Delete disabled services from array. Use callback function here. Do this only once because array_filter is slow! 
     * Use the newly created array and bypass the callback function
     */
    if( is_array( $getnetworks ) ) {
        if( !is_array( $enablednetworks ) ) {
            $enablednetworks = array_filter( $getnetworks, 'isStatus' );
        } else {
            $enablednetworks = $enablednetworks;
        }
    } else {
        $enablednetworks = $getnetworks;
    }

    
    // Use custom networks if available and override default networks
    //$enablednetworks = $networks ? $networks : $enablednetworks;
    
    //var_dump($enablednetworks);
    
    // Start Primary Buttons
    
    if( !empty( $enablednetworks ) ) {
        foreach ( $enablednetworks as $key => $network ):
                
            if( $maxcounter !== 'all' && $maxcounter < count( $enablednetworks ) ) { // $maxcounter + 1 for correct comparision with count()
                if( $startcounter == $maxcounter ) {
                    $onoffswitch = onOffSwitch(); // Start More Button
                    //$startsecondaryshares = '</div>'; // End Primary Buttons
                    $visibility = mashsb_is_amp_page() ? '' : 'display:none;';
                    $startsecondaryshares .= '<div class="secondary-shares" style="'.$visibility.'">'; // Start secondary-shares
                } else {
                    $onoffswitch = '';
                    $onoffswitch2 = '';
                    $startsecondaryshares = '';
                }
                if( $startcounter === (count( $enablednetworks )) ) {
                    $endsecondaryshares = '</div>';
                } else {
                    $endsecondaryshares = '';
                }
            }

            if( isset($enablednetworks[$key]['name']) && !empty($enablednetworks[$key]['name']) ) {
                /* replace all spaces with $nbsp; This prevents error in css style content: text-intend */
                $name = preg_replace( '/\040{1,}/', '&nbsp;', $enablednetworks[$key]['name'] ); // The custom share label
            } else {
                $name = ucfirst( $enablednetworks[$key]['id'] ); // Use the id as share label. Capitalize it!
            }
            
            $enablednetworks[$key]['id'] == 'whatsapp' && !mashsb_is_amp_page() ? $display = 'style="display:none;"' : $display = ''; // Whatsapp button is made visible via js when opened on mobile devices

            // Lets use the data attribute to prevent that pininit.js is overwriting our pinterest button - PR: https://secure.helpscout.net/conversation/257066283/954/?folderId=924740
            if ('pinterest' === $enablednetworks[$key]['id'] && !mashsb_is_amp_page() ) {
                $output .= '<a ' . $display . ' class="mashicon-' . $enablednetworks[$key]['id'] . $class_size . $class_margin . $class_center . $class_style . '" href="#" data-mashsb-url="'. arrNetworks( $enablednetworks[$key]['id'], $is_shortcode ) . '" target="_blank" rel="nofollow"><span class="icon"></span><span class="text">' . $name . '</span></a>';
            } else {
                $output .= '<a ' . $display . ' class="mashicon-' . $enablednetworks[$key]['id'] . $class_size . $class_margin . $class_center . $class_style . '" href="' . arrNetworks( $enablednetworks[$key]['id'], $is_shortcode ) . '" target="_blank" rel="nofollow"><span class="icon"></span><span class="text">' . $name . '</span></a>';
            }
            $output .= $onoffswitch;
            $output .= $startsecondaryshares;

            $startcounter++;
        endforeach;
        $output .= onOffSwitch2();
        $output .= $endsecondaryshares;
    }

    return apply_filters( 'return_networks', $output );
}
/* 
 * Return all available networks for Shortcode generated buttons
 * 
 * @since 2.0
 * @param bool true when used from shortcode [mashshare]
 * @param int number of visible networks (used for shortcodes)
 * @param array activated networks (used for shortcodes)
 * @param string button size (used for shortcodes)
 * @return string html
 */

function mashsb_getNetworksShortcode( $is_shortcode = false, $services = 0, $networks = false, $size = false, $icons = false ) {
    //global $mashsb_options, $mashsb_custom_url, $enablednetworks, $mashsb_twitter_url;
    global $mashsb_options, $mashsb_custom_url, $mashsb_twitter_url;
    
    
    // define globals
    if( $is_shortcode ) {
        $mashsb_twitter_url = !empty( $mashsb_custom_url ) ? mashsb_get_shorturl( $mashsb_custom_url ) : mashsb_get_twitter_url();

    }else{
        $mashsb_twitter_url = mashsb_get_twitter_url();
    }
    
    // Get class names for buttons size
    $class_size = isset($mashsb_options['buttons_size']) ? ' ' . $mashsb_options['buttons_size'] : '';
    
    // Override size with shortcode argument
    $class_size = $size ? ' mash-'.$size : $class_size;
    
    // Get class names for buttons margin
    $class_margin = isset($mashsb_options['button_margin']) ? '' : ' mash-nomargin';

    // Get class names for center align
    $class_center = isset($mashsb_options['text_align_center']) ? ' mash-center' : '';
    
    // Get class names for button style
    $class_style = isset($mashsb_options['mash_style']) && $mashsb_options['mash_style'] === 'shadow' ? ' mashsb-shadow' : ' mashsb-noshadow';
    
    $class_icons = $icons ? ' mashsb-pure-icons' : ''; 
    
    //$style = $fullwidth ? '' : 'style="min-width:0;flex:none;-webkit-flex:none;"';

    $output = '';
    $startsecondaryshares = '';
    $endsecondaryshares = '';

    /* content of 'more services' button */
    $onoffswitch = '';

    /* counter for 'Visible Services' */
    $startcounter = 1;

    $maxcounter = isset( $mashsb_options['visible_services'] ) ? $mashsb_options['visible_services'] : 0;
    $maxcounter = ($maxcounter === 'all') ? 'all' : ($maxcounter + 1); // plus 1 to get networks correct counted (array's starting counting from zero)
    $maxcounter = apply_filters( 'mashsb_visible_services', $maxcounter );

    /* Overwrite maxcounter with shortcode attribute */
    $maxcounter = ($services === 0) ? $maxcounter : $services;

    /* 
     * Our list of available services, includes the disabled ones! 
     * We have to clean this array first!
     */
    $getnetworks = isset( $mashsb_options['networks'] ) ? apply_filters('mashsb_filter_networks', $mashsb_options['networks'])  : apply_filters('mashsb_filter_networks', '');

    /* 
     * Delete disabled services from array. Use callback function here. Do this only once because array_filter is slow! 
     * Use the newly created array and bypass the callback function
     */
    if( is_array( $getnetworks ) ) {
        if( !isset($enablednetworks) || !is_array( $enablednetworks ) ) {
            $enablednetworks = array_filter( $getnetworks, 'isStatus' );
        } else {
            $enablednetworks = $enablednetworks;
        }
    } else {
        $enablednetworks = $getnetworks;
    }

    
    // Use custom networks if available and override default networks
    $enablednetworks = $networks ? $networks : $enablednetworks;
    
    //var_dump($enablednetworks);
    
    // Start Primary Buttons
    
    if( !empty( $enablednetworks ) ) {
        foreach ( $enablednetworks as $key => $network ):
                
            if( $maxcounter !== 'all' && $maxcounter < count( $enablednetworks ) ) { // $maxcounter + 1 for correct comparision with count()
                if( $startcounter == $maxcounter ) {
                    $onoffswitch = onOffSwitch($size); // Start More Button
                    //$startsecondaryshares = '</div>'; // End Primary Buttons
                    $startsecondaryshares .= '<div class="secondary-shares" style="display:none;">'; // Start secondary-shares
                } else {
                    $onoffswitch = '';
                    $onoffswitch2 = '';
                    $startsecondaryshares = '';
                }
                if( $startcounter === (count( $enablednetworks )) ) {
                    $endsecondaryshares = '</div>';
                } else {
                    $endsecondaryshares = '';
                }
            }

            if( isset($enablednetworks[$key]['name']) && !empty($enablednetworks[$key]['name']) ) {
                /* replace all spaces with $nbsp; This prevents error in css style content: text-intend */
                $name = !$icons ? preg_replace( '/\040{1,}/', '&nbsp;', $enablednetworks[$key]['name'] ) : ''; // The custom share label
            } else {
                $name = !$icons ? ucfirst( $enablednetworks[$key]['id'] ) : ''; // Use the id as share label. Capitalize it!
            }
            
            $enablednetworks[$key]['id'] == 'whatsapp' ? $display = 'style="display:none;"' : $display = ''; // Whatsapp button is made visible via js when opened on mobile devices

            // Lets use the data attribute to prevent that pininit.js is overwriting our pinterest button - PR: https://secure.helpscout.net/conversation/257066283/954/?folderId=924740
            if ('pinterest' === $enablednetworks[$key]['id'] && !mashsb_is_amp_page() ) {
                $output .= '<a ' . $display . ' class="mashicon-' . $enablednetworks[$key]['id'] . $class_size . $class_margin . $class_center . $class_style . $class_icons . '" href="#" data-mashsb-url="'. arrNetworks( $enablednetworks[$key]['id'], $is_shortcode ) . '" target="_blank" rel="nofollow"><span class="icon"></span><span class="text">' . $name . '</span></a>';
            } else {
                $output .= '<a ' . $display . ' class="mashicon-' . $enablednetworks[$key]['id'] . $class_size . $class_margin . $class_center . $class_style . $class_icons . '" href="' . arrNetworks( $enablednetworks[$key]['id'], $is_shortcode ) . '" target="_blank" rel="nofollow"><span class="icon"></span><span class="text">' . $name . '</span></a>';
            }
            $output .= $onoffswitch;
            $output .= $startsecondaryshares;

            $startcounter++;
        endforeach;
        $output .= onOffSwitch2($size);
        $output .= $endsecondaryshares;
    }

    return apply_filters( 'return_networks', $output );
}


/*
 * Render template
 * Returns share buttons and share count
 * 
 * @since 1.0
 * @returns string html
 */

function mashshareShow() {
    global $mashsb_options;
        
    $class_stretched = isset($mashsb_options['responsive_buttons']) ? 'mashsb-stretched' : '';

    $return = '<aside class="mashsb-container mashsb-main ' . $class_stretched . '">'
            . mashsb_content_above() .
            '<div class="mashsb-box">'
                . apply_filters( 'mashsb_sharecount_filter', mashsb_render_sharecounts() ) .
                '<div class="mashsb-buttons">'
                . mashsb_getNetworks() .
                '</div>
            </div>
                <div style="clear:both;"></div>'
            . mashsb_subscribe_content()
            . mashsb_content_below() .
            '</aside>
            <!-- Share buttons by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
    return apply_filters( 'mashsb_output_buttons', $return );
}

/**
 * Render the sharecount template
 * 
 * @param string $customurl default empty
 * @param string alignment default left
 * @return string html
 */
function mashsb_render_sharecounts( $customurl = '', $align = 'left', $size = false ) {
    global $mashsb_options;

    if( isset( $mashsb_options['disable_sharecount'] ) || !mashsb_curl_installed() || !mashsb_is_enabled_permalinks() ) {
        return;
    }

    $url = empty( $customurl ) ? mashsb_get_url() : $customurl;
    $sharetitle = isset( $mashsb_options['sharecount_title'] ) ? $mashsb_options['sharecount_title'] : __( 'SHARES', 'mashsb' );

    $shares = getSharedcount( $url );
    $sharecount = isset( $mashsb_options['mashsharer_round'] ) ? roundshares( $shares ) : $shares;

    // do not show shares after x shares
    if( mashsb_hide_shares( $shares ) ) {
        return;
    }
    
    // Get class names for buttons size
    $class_size = isset($mashsb_options['buttons_size']) ? ' ' . $mashsb_options['buttons_size'] : '';
    
    // Override size with shortcode argument
    $class_size = $size ? ' mash-'.$size : $class_size;
    
    // No inline style if it's amp
    $style = !mashsb_is_amp_page() ? 'style="float:' . $align . ';"' : '';

    $html = '<div class="mashsb-count'.$class_size . '" ' . $style . '><div class="counts mashsbcount">' . $sharecount . '</div><span class="mashsb-sharetext">' . $sharetitle . '</span></div>';
    return apply_filters('mashsb_share_count', $html);
}

/*
 * Shortcode function
 * Select Share count from database and returns share buttons and share counts
 * 
 * @since 1.0
 * @returns string
 */

function mashshareShortcodeShow( $args ) {
    global $mashsb_options, $mashsb_custom_url, $mashsb_custom_text;

    $sharecount = '';

    //Filter shortcode args to add an option for developers to change (add) some args
    apply_filters( 'mashsb_shortcode_atts', $args );

    extract( shortcode_atts( array(
        'cache' => '3600',
        'shares' => 'true',
        'buttons' => 'true',
        'services' => '0', //default is by admin option - plus 1 because array starts counting from zero
        'align' => 'left',
        'text' => '', // $text
        'url' => '', // $url
        'networks' => '', // List of networks separated by comma
        'size' => '', // small, medium, large button size
        'icons' => '0' // 1 
        ), $args ) );
    
    // Visible services
    $count_services = !empty($services) ? $services : 0;
    
    // Enable specific networks
    $networks = !empty($networks) ? explode(",", $networks) : false;
    
    // Convert into appropriate array structure
    if ($networks) {
        $new = array();
        foreach ($networks as $key => $value) {
            $new[$key]['id'] = $value;
            $new[$key]['status'] = '1';
            $new[$key]['name'] = $value;
        }
        $networks = $new;
    }
    
    //var_dump( $new );
    
    // Define custom url var to share
    //$mashsb_custom_url = empty( $url ) ? mashsb_get_url() : $url;
    // The global available custom url to share
    $mashsb_custom_url = !empty( $url ) ? $url : '';
    // local url
    $mashsb_url = empty( $url ) ? mashsb_get_url() : $url;

    // Define custom text to share
    $mashsb_custom_text = !empty( $text ) ? $text : false;

    if( $shares != 'false' ) {
        $sharecount = mashsb_render_sharecounts( $mashsb_url, $align, $size );
        // shortcode [mashshare shares="true" buttons="false"] 
        if( $shares === "true" && $buttons === 'false' ) {
            return $sharecount;
        }
    }
    
    $class_stretched = isset($mashsb_options['responsive_buttons']) ? 'mashsb-stretched' : '';

    $return = '<aside class="mashsb-container mashsb-main ' . $class_stretched . '">'
            . mashsb_content_above() .
            '<div class="mashsb-box">'
            . $sharecount .
            '<div class="mashsb-buttons">'
            . mashsb_getNetworksShortcode( true, $count_services, $networks, $size, $icons ) .
            '</div></div>
                    <div style="clear:both;"></div>'
            . mashsb_subscribe_content()
            . mashsb_content_below() .
            '</aside>
            <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';

    return apply_filters( 'mashsb_output_buttons', $return );
}


/* Returns active status of Mashshare.
 * Used for scripts.php $hook
 * @since 2.0.3
 * @return bool True if MASHSB is enabled on specific page or post.
 * @TODO: Check if shortcode [mashshare] is used in widget
 */

function mashsbGetActiveStatus() {
    global $mashsb_options, $post;

    $frontpage = isset( $mashsb_options['frontpage'] ) ? true : false;
    $current_post_type = get_post_type();
    $enabled_post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : array();
    $singular = isset( $mashsb_options['singular'] ) ? true : false;
    $loadall = isset( $mashsb_options['loadall'] ) ? $loadall = true : $loadall = false;


    if( mashsb_is_excluded() ) {
        mashdebug()->info( "mashsb_is_excluded()" );
        return apply_filters( 'mashsb_active', false );
    }

    if( $loadall ) {
        mashdebug()->info( "load all mashsb scripts" );
        return apply_filters( 'mashsb_active', true );
    }

    // Load on frontpage
    if( $frontpage === true && is_front_page() ) {
        mashdebug()->info( "allow frontpage and is frontpage" );
        return apply_filters( 'mashsb_active', true );
    }

    // Load scripts when shortcode is used
    /* Check if shortcode is used */
    if( function_exists( 'has_shortcode' ) && is_object( $post ) && has_shortcode( $post->post_content, 'mashshare' ) ) {
        mashdebug()->info( "has_shortcode" );
        return apply_filters( 'mashsb_active', true );
    }

    // No scripts on non singular page
    if( !is_singular() && !$singular ) {
        mashdebug()->info( "No scripts on non singular page" );
        return apply_filters( 'mashsb_active', false );
    }


    // Load scripts when post_type is defined (for automatic embeding)
    if( in_array( $current_post_type, $enabled_post_types ) ) {
        mashdebug()->info( "automatic post_type enabled" );
        return apply_filters( 'mashsb_active', true );
    }

    mashdebug()->info( "mashsbGetActiveStatus false" );
    return apply_filters( 'mashsb_active', false );
}

/**
 * Get the post meta value of position
 * 
 * @global int $post
 * @return mixed string|bool false
 */
function mashsb_get_post_meta_position() {
    global $post;
    
    if( isset( $post->ID ) && !empty($post->ID) ) {
        $check_position_meta_post = get_post_meta( $post->ID, 'mashsb_position', true );
        if( !empty( $check_position_meta_post ) ) {
            return $check_position_meta_post;
        }else{
            return false;
        }
    }
    return false;
}

/* Returns Share buttons on specific positions
 * Uses the_content filter
 * @since 1.0
 * @return string
 */

function mashshare_filter_content( $content ) {
    global $mashsb_options, $wp_current_filter;
    
    // Default position
    $position = !empty( $mashsb_options['mashsharer_position'] ) ? $mashsb_options['mashsharer_position'] : '';
    // Check if we have a post meta setting which overrides the global position than we use that one instead
    if ( true == ($position_meta = mashsb_get_post_meta_position() ) ){
        $position = $position_meta;
    }

    
    $enabled_post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : null;
    $current_post_type = get_post_type();
    $frontpage = isset( $mashsb_options['frontpage'] ) ? true : false;
    $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;
    $singular = isset( $mashsb_options['singular'] ) ? $singular = true : $singular = false;

    
    if( isset($mashsb_options['is_main_query']) && !is_main_query() ) {
        return $content;
    }
     
    if( mashsb_is_excluded() ){
        return $content;
    }
    
    if (is_feed()){
        return $content;
    }

    if( $frontpage == false && is_front_page() ) {
        return $content;
    }

    if( !is_singular() == 1 && $singular !== true ) {
        return $content;
    }

    if( $enabled_post_types == null or ! in_array( $current_post_type, $enabled_post_types ) ) {
        return $content;
    }

    if( in_array( 'get_the_excerpt', $wp_current_filter ) ) {
        return $content;
    }
    
    // Get one instance (prevents multiple similar calls)
    $mashsb_instance = apply_filters('mashsb_the_content', mashshareShow());
    switch ( $position ) {
        case 'manual':
            break;

        case 'both':
            $content = $mashsb_instance . $content . $mashsb_instance;
            break;

        case 'before':
            $content = $mashsb_instance . $content;
            break;

        case 'after':
            $content .= $mashsb_instance;
            break;
        
        case 'disable':
            break;
    }
    return $content;
}

/* Template function mashshare() 
 * @since 2.0.0
 * @return string
 */

function mashshare() {
    //global $atts;
    echo mashshareShow();
}

/* Deprecated: Template function mashsharer()
 * @since 1.0
 * @return string
 */

function mashsharer() {
    //global $atts;
    echo mashshareShow();
}

/**
 * Get Thumbnail featured image if existed
 *
 * @since 1.0
 * @param int $postID
 * @return string
 */
function mashsb_get_image( $postID ) {
    global $post;

    if( !isset( $post ) ) {
        return '';
    }

    if( has_post_thumbnail( $post->ID ) ) {
        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
        return isset($image[0]) ? $image[0] : '';
    }
}

add_action( 'mashsb_get_image', 'mashsb_get_image' );

/**
 * Get the excerpt
 *
 * @since 1.0
 * @param int $postID
 * @changed 3.0.0
 * @return string
 */
function mashsb_get_excerpt_by_id( $post_id ) {
    // Check if the post has an excerpt
    if( has_excerpt() ) {
        return get_the_excerpt();
    }

    if( !isset( $post_id ) ) {
        return "";
    }

    $the_post = get_post( $post_id ); //Gets post ID

    /*
     * If post_content isn't set
     */
    if( !isset( $the_post->post_content ) ) {
        return "";
    }

    $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
    // Strip all shortcodes
    $excerpt_length = 35; //Sets excerpt length by word count
    $the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images
    $words = explode( ' ', $the_excerpt, $excerpt_length + 1 );
    if( count( $words ) > $excerpt_length ) :
        array_pop( $words );
        array_push( $words, '…' );
        $the_excerpt = implode( ' ', $words );
    endif;
    $the_excerpt = '<p>' . $the_excerpt . '</p>';
    return wp_strip_all_tags( $the_excerpt );
}

add_action( 'mashsb_get_excerpt_by_id', 'mashsb_get_excerpt_by_id' );

/**
 * Create a factor for calculating individual fake counts 
 * based on the number of word within a page title
 *
 * @since 2.0
 * @return int
 */
function mashsb_get_fake_factor() {
    // str_word_count is not working for hebraic and arabic languages
    //$wordcount = str_word_count(the_title_attribute('echo=0')); //Gets title to be used as a basis for the count
    $wordcount = count( explode( ' ', the_title_attribute( 'echo=0' ) ) );
    $factor = $wordcount / 10;
    return apply_filters( 'mashsb_fake_factor', $factor );
}

/*
 * Sharecount fake number
 * 
 * @since 2.0.9
 * @return int
 * 
 */

function getFakecount() {
    global $mashsb_options;

    
    $fakecount = isset($mashsb_options['fake_count']) && is_numeric ($mashsb_options['fake_count']) ? 
            round( $mashsb_options['fake_count'] * mashsb_get_fake_factor(), 0 ) : 0;
    
    return (int)$fakecount;

}

/*
 * Hide sharecount until number of shares exceed
 * 
 * @since 2.0.7
 * 
 * @param int number of shares
 * @return bool true when shares are hidden
 * 
 */

function mashsb_hide_shares( $sharecount ) {
    global $mashsb_options, $post;

    if( empty( $mashsb_options['hide_sharecount'] ) ) {
        return false;
    }

    $url = get_permalink( isset( $post->ID ) );
    $sharelimit = isset( $mashsb_options['hide_sharecount'] ) ? $mashsb_options['hide_sharecount'] : 0;

    if( $sharecount >= $sharelimit ) {
        return false;
    }
    // Hide share count per default when it is not a valid number
    return true;
}

/* Additional content above share buttons 
 * 
 * @return string $html
 * @scince 2.3.2
 */

function mashsb_content_above() {
    global $mashsb_options;
    $html = !empty( $mashsb_options['content_above'] ) ? '<div class="mashsb_above_buttons">' . mashsb_cleanShortcode('mashshare', $mashsb_options['content_above']) . '</div>' : '';
    return apply_filters( 'mashsb_above_buttons', $html );
}

/* Additional content above share buttons 
 * 
 * @return string $html
 * @scince 2.3.2
 */

function mashsb_content_below() {
    global $mashsb_options;
    $html = !empty( $mashsb_options['content_below'] ) ? '<div class="mashsb_below_buttons">' . mashsb_cleanShortcode('mashshare', $mashsb_options['content_below']) . '</div>' : '';
    return apply_filters( 'mashsb_below_buttons', $html );
}

/**
 * Check if buttons are excluded from a specific post id
 * 
 * @return true if post is excluded
 */
function mashsb_is_excluded() {
    global $post, $mashsb_options;

    if( !isset( $post ) ) {
        return false;
    }

    $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;

    // Load scripts when page is not excluded
    if( strpos( $excluded, ',' ) !== false ) {
        $excluded = explode( ',', $excluded );
        if( in_array( $post->ID, $excluded ) ) {
            mashdebug()->info( "is excluded" );
            return true;
        }
    }
    if( $post->ID == $excluded ) {
        mashdebug()->info( "is single excluded" );
        return true;
    }

    return false;
}


/**
 * Return general post title
 * 
 * @param string $title default post title
 * @global obj $mashsb_meta_tags
 * 
 * @return string the default post title, shortcode title or custom twitter title
 */
function mashsb_get_title() {
    global $post, $mashsb_meta_tags;
    if( is_singular() && method_exists($mashsb_meta_tags, 'get_og_title')) {
        $title = $mashsb_meta_tags->get_og_title();
        $title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
        $title = urlencode( $title );
        $title = str_replace( '#', '%23', $title );
        $title = esc_html( $title );
    } else {
        $title = mashsb_get_document_title();
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
        $title = urlencode($title);
        $title = str_replace('#', '%23', $title);
        $title = esc_html($title);
    }
    return apply_filters( 'mashsb_get_title', $title );
}

/**
 * Return twitter custom title
 * 
 * @global object $mashsb_meta_tags
 * @changed 3.0.0
 * 
 * @return string the custom twitter title
 */
function mashsb_get_twitter_title() {
    global $mashsb_meta_tags;
    // $mashsb_meta_tags is only available on singular pages
    if( is_singular() && method_exists($mashsb_meta_tags, 'get_twitter_title') ) {
        $title = $mashsb_meta_tags->get_twitter_title();
        $title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
        $title = urlencode( $title );
        $title = str_replace( '#', '%23', $title );
        $title = str_replace( '+', '%20', $title );
        $title = str_replace('|','',$title);
        $title = esc_html( $title );
       
    } else {
        // title for non singular pages
        $title = mashsb_get_title();
        $title = str_replace( '+', '%20', $title );
        $title = str_replace('|','',$title);
    }
    return apply_filters('mashsb_twitter_title', $title);
}

/* 
 * Get URL to share
 * 
 * @return url  $string
 * @scince 2.2.8
 */

function mashsb_get_url() {
    global $post;
    
    if( isset($post->ID )) {
        // The permalink for singular pages!
        // Do not check here for is_singular() (like e.g. the sharebar addon does.)
        // Need to check for post id because on category and archiv pages 
        // we want the pageID within the loop instead the first appearing one.
        $url = mashsb_sanitize_url(get_permalink( $post->ID ));
    } else {
         // The main URL
        $url = mashsb_get_main_url();
    }
    
    return apply_filters( 'mashsb_get_url', $url );
}

/* 
 * Get twitter URL to share
 * 
 * @return url  $string
 * @scince 2.2.8
 */

function mashsb_get_twitter_url() {
    if( function_exists( 'mashsb_get_shorturl_singular' ) ) {
        $url = mashsb_get_shorturl_singular( mashsb_get_url() );
    } else if( function_exists( 'mashsuGetShortURL' ) ) { // compatibility mode for MashShare earlier than 3.0
        $get_url = mashsb_get_url();
        $url = mashsuGetShortURL( $get_url );
    } else {
        $url = mashsb_get_url();
    }
    return apply_filters( 'mashsb_get_twitter_url', $url );
}

/**
 * Wrapper for mashsb_get_shorturl_singular()
 * 
 * @param string $url
 * @return string
 */
function mashsb_get_shorturl( $url ) {

    if( !empty( $url ) ) {
        $url = mashsb_get_shorturl_singular( $url );
    } else {
        $url = "";
    }

    return $url;
}


/**
 * Get sanitized twitter handle
 * 
 * @global array $mashsb_options
 * @return mixed string | bool false
 */
function mashsb_get_twitter_username() {
    global $mashsb_options;

    if( empty( $mashsb_options['mashsharer_hashtag'] ) ) {
        return;
    }

    // If plugin is not running on mashshare.net or dev environment replace @mashshare
    if( $_SERVER['HTTP_HOST'] !== 'www.mashshare.net' && $_SERVER['HTTP_HOST'] !== 'src.wordpress-develop.dev' ) {
        //Sanitize it
        $replace_first = str_ireplace( 'mashshare', '', $mashsb_options['mashsharer_hashtag'] );
        $replace_second = str_ireplace( '@', '', $replace_first );
        return $replace_second;
    } else {
        return $mashsb_options['mashsharer_hashtag'];
    }
}

/**
 * Returns document title for the current page.
 *
 * @since 3.0
 *
 * @global int $post Page number of a list of posts.
 *
 * @return string Tag with the document title.
 */
function mashsb_get_document_title() {
    
    /**
     * Filter the document title before it is generated.
     *
     * Passing a non-empty value will short-circuit wp_get_document_title(),
     * returning that value instead.
     *
     * @since 4.4.0
     *
     * @param string $title The document title. Default empty string.
     */
   
   $title = '';

    // If it's a 404 page, use a "Page not found" title.
    if( is_404() ) {
        $title = __( 'Page not found' );

        // If it's a search, use a dynamic search results title.
    } elseif( is_search() ) {
        /* translators: %s: search phrase */
        $title = sprintf( __( 'Search Results for &#8220;%s&#8221;' ), get_search_query() );

        // If on a post type archive, use the post type archive title.
    } elseif( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
        
        // If on a taxonomy archive, use the term title.
    } elseif( is_tax() ) {
        $title = single_term_title( '', false );

        /*
         * If we're on the blog page that is not the homepage or
         * a single post of any post type, use the post title.
         */
    //} elseif( !is_home() || is_singular() ) {
    } elseif( is_singular() ) {
        $title = the_title_attribute('echo=0');

        // If on the front page, use the site title.
    } elseif( is_front_page() ) {
        $title = get_bloginfo( 'name', 'display' );
        
        // If on a category or tag archive, use the term title.   
    } elseif( is_category() || is_tag() ) {
        $title = single_term_title( '', false );

        // If on an author archive, use the author's display name.
    } elseif( is_author() && $author = get_queried_object() ) {
        $title = $author->display_name;

        // If it's a date archive, use the date as the title.
    } elseif( is_year() ) {
        $title = get_the_date( _x( 'Y', 'yearly archives date format' ) );
    } elseif( is_month() ) {
        $title = get_the_date( _x( 'F Y', 'monthly archives date format' ) );
    } elseif( is_day() ) {
        $title = get_the_date();
    }

    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    return $title;
}

/**
 * Append tracking parameter to shared url
 * 
 * @param string $url
 * @return string
 */
function mashsb_append_tracking_param($url, $network = 'mashshare'){
    global $mashsb_options;
    
    if (!isset($mashsb_options['tracking_params'])){
        return $url;
    }
  
    return $url . urlencode('?utm_source=sharebuttons&utm_medium='.$network.'&utm_campaign=mashshare');
}