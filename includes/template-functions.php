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

/* Get mashsbShareObject 
 * depending if MashEngine or sharedcount.com is used
 * 
 * @since 2.0.9
 * @return object
 * @changed 2.2.7
 */

function mashsbGetShareObj( $url ) {
    global $mashsb_options;
    $mashengine = isset( $mashsb_options['mashsb_sharemethod'] ) && $mashsb_options['mashsb_sharemethod'] === 'mashengine' ? true : false;
    if( $mashengine ) {
        if( !class_exists( 'RollingCurlX' ) )
            require_once MASHSB_PLUGIN_DIR . 'includes/libraries/RolingCurlX.php';
        if( !class_exists( 'mashengine' ) )
            require_once(MASHSB_PLUGIN_DIR . 'includes/mashengine.php');
        mashdebug()->error( 'mashsbGetShareObj() url: ' . $url );
        $mashsbSharesObj = new mashengine( $url );
        return $mashsbSharesObj;
    }
    require_once(MASHSB_PLUGIN_DIR . 'includes/sharedcount.class.php');
    $apikey = isset( $mashsb_options['mashsharer_apikey'] ) ? $mashsb_options['mashsharer_apikey'] : '';
    $mashsbSharesObj = new mashsbSharedcount( $url, 10, $apikey );
    return $mashsbSharesObj;
}

/*
 * Get the correct share method depending if mashshare networks is enabled
 * 
 * @since 2.0.9
 * @return var
 * 
 */

/* Get the sharecounts from sharedcount.com or MashEngine
 * Creates the share count cache using post_meta db fields.
 * 
 * @since 2.0.9
 * @returns int
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
    global $mashsb_options;

    // Expiration
    $expiration = mashsb_get_expiration();
    
    // Remove variables, parameters and trailingslash
    $url_clean = mashsb_sanitize_url( $url );

    // Get any existing copy of our transient data and fill the cache
    if( mashsb_force_cache_refresh() ) {

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
        // Get shares from transient cache
        
        $shares = get_transient( 'mashcount_' . md5( $url_clean ) );

        if( isset( $shares ) && is_numeric( $shares ) ) {
            MASHSB()->logger->info( 'mashsbGetNonPostShares() get shares from get_transient. URL: ' . $url_clean . ' SHARES: ' . $shares );
            return $shares + getFakecount();
        } else {
            return 0 + getFakecount(); // we need a result
        }
    }
}

/*
 * Return the share count
 * 
 * @param string url of the page the share count is collected for
 * @returns int
 */

function getSharedcount( $url ) {
    //global $mashsb_options, $post;
    global $mashsb_options, $post, $mashsb_sharecount; // todo test a global share count var if it reduces the amount of requests

//    // Return global share count variable to prevent multiple execution
    if (isset($mashsb_sharecount) && !mashsb_is_cache_refresh() ){
        return $mashsb_sharecount[$url] + getFakecount();
    }
   
    
    // Remove mashsb-refresh query parameter
    $url = mashsb_sanitize_url($url);
    
    /*
     * Deactivate share count on:
     * - 404 pages
     * - search page
     * - empty url
     * - disabled permalinks
     * - admin pages
     */
    if( is_404() || is_search() || empty($url) || is_admin() || !mashsb_is_enabled_permalinks() ) {
        return apply_filters( 'filter_get_sharedcount', 0 );
    }

    // if it's a crawl bot only serve non calculated numbers to save load
    if( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'] ) ) {
        return apply_filters( 'filter_get_sharedcount', 100 );
    }

    /* 
     * Return share count on non singular pages when url is defined
       Possible: Category, blog list pages, non singular() pages. This store the shares in transients with mashsbGetNonPostShares();
     */

//    if( !is_singular() ) {
//        // global sharecount
//        $shares = mashsbGetNonPostShares( mashsb_get_main_url() ) + getFakecount();
//        $mashsb_sharecount = array($url => $shares);
//        echo "url2: " . $mashsb_sharecount[$url];
//        return apply_filters( 'filter_get_sharedcount', $shares );
//    }
    if( !empty( $url ) && is_null( $post ) ) {
      return apply_filters( 'filter_get_sharedcount', mashsbGetNonPostShares( $url ) );
    }

    /*
     * Important: This runs on non singular pages 
     * and prevents php from crashing and looping 
     * because of timeouts and no results query
     */
    /*if( !is_null( $post ) ) {
        return apply_filters( 'filter_get_sharedcount', get_post_meta( $post->ID, 'mashsb_shares', true ) + getFakecount() );
    }*/

    /*
     * Refresh Cache
     */
    if( mashsb_force_cache_refresh() && is_singular() ) {
        
        // free some memory
        unset ( $mashsb_sharecount[$url] );
        
        // Write timestamp (Use this on top of this condition. If this is not on top following return statements will be skipped and ignored - possible bug?)
        update_post_meta( $post->ID, 'mashsb_timestamp', time() );

        MASHSB()->logger->info( 'Refresh Cache: Update Timestamp: ' . time() );

        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj( $url );
        // Get the share count Method
        $mashsbShareCounts = mashsbGetShareMethod( $mashsbSharesObj );
        // Get stored share count
        $mashsbStoredShareCount = get_post_meta( $post->ID, 'mashsb_shares', true );

        // Create global sharecount
        $mashsb_sharecount = array($url => $mashsbShareCounts->total);
        /*
         * Update post_meta only when API is requested and
         * API share count is greater than real fresh requested share count ->
         */

        if( $mashsbShareCounts->total >= $mashsbStoredShareCount ) {
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
        $cachedCountsMeta = get_post_meta( $post->ID, 'mashsb_shares', true );
        $cachedCounts = $cachedCountsMeta + getFakecount();
        MASHSB()->logger->info( 'Cached Results: ' . $cachedCounts . ' url:' . $url );
        return apply_filters( 'filter_get_sharedcount', $cachedCounts );
    }
}

function mashsb_subscribe_button() {
    global $mashsb_options;
    if( $mashsb_options['networks'][2] ) {
        $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"></span><span class="text">' . __( 'Subscribe', 'mashsb' ) . '</span></a>';
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

function onOffSwitch() {
    $output = '<div class="onoffswitch"></div>';
    return apply_filters( 'mashsh_onoffswitch', $output );
}

/* Return the second more networks button after 
 * last hidden additional service. initial status: hidden
 * Become visible with click on plus icon
 * 
 * @since 2.0
 * @return string
 */

function onOffSwitch2() {
    $output = '<div class="onoffswitch2" style="display:none;"></div>';
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
    //global $mashsb_options, $post, $mashsb_custom_url, $mashsb_custom_text;
    global $mashsb_custom_url, $mashsb_custom_text, $mashsb_twitter_url;

    if( $is_shortcode ) {
        $url = !empty( $mashsb_custom_url ) ? $mashsb_custom_url : mashsb_get_url();
        $title = !empty( $mashsb_custom_text ) ? $mashsb_custom_text : mashsb_get_title();
        $twitter_title = !empty( $mashsb_custom_text ) ? $mashsb_custom_text : mashsb_get_twitter_title();
        //$twitter_url = !empty( $mashsb_custom_url ) ? mashsb_get_shorturl( $mashsb_custom_url ) : mashsb_get_twitter_url();
    }
    if( !$is_shortcode ) {
        $url = mashsb_get_url();
        $title = mashsb_get_title();
        $twitter_title = mashsb_get_twitter_title();
        //$twitter_url = mashsb_get_twitter_url();
    }

    $via = mashsb_get_twitter_username() ? '&via=' . mashsb_get_twitter_username() : '';
    
    $networks_arr = array(
        'facebook' => 'http://www.facebook.com/sharer.php?u=' . $url,
        'twitter' => 'https://twitter.com/intent/tweet?text=' . $twitter_title . '&url=' . $mashsb_twitter_url . $via,
        'subscribe' => '#',
        'url' => $url,
        'title' => $title
    );

    $networks = apply_filters( 'mashsb_array_networks', $networks_arr );
    return isset( $networks[$name] ) ? $networks[$name] : '';
}

/* Returns all available networks
 * 
 * @since 2.0
 * @param bool true when used from shortcode [mashshare]
 * @param int number of visible networks
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

    /* visible services from shortcode attribute */
    $maxcounter = ($services === 0) ? $maxcounter : $services;

    /* our list of available services, includes the disabled ones! 
     * We have to clean this array first!
     */
    $getnetworks = isset( $mashsb_options['networks'] ) ? $mashsb_options['networks'] : '';

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

    if( !empty( $enablednetworks ) ) {
        foreach ( $enablednetworks as $key => $network ):
            if( $maxcounter !== 'all' && $maxcounter < count( $enablednetworks ) ) { // $maxcounter + 1 for correct comparision with count()
                if( $startcounter == $maxcounter ) {
                    $onoffswitch = onOffSwitch();
                    $startsecondaryshares = '<div class="secondary-shares" style="display:none;">';
                } else {
                    $onoffswitch = '';
                    $onoffswitch2 = '';
                    $startsecondaryshares = '';
                }
                if( $startcounter === (count( $enablednetworks )) ) {
                    $endsecondaryshares = '</div>';
                } else {
                    ;
                    $endsecondaryshares = '';
                }
            }
            if( $enablednetworks[$key]['name'] != '' ) {
                /* replace all spaces with $nbsp; This prevents error in css style content: text-intend */
                $name = preg_replace( '/\040{1,}/', '&nbsp;', $enablednetworks[$key]['name'] );
            } else {
                $name = ucfirst( $enablednetworks[$key]['id'] );
            }
            $enablednetworks[$key]['id'] == 'whatsapp' ? $display = 'display:none;' : $display = ''; // Whatsapp button is made visible via js when opened on mobile devices

            $output .= '<a style="' . $display . '" class="mashicon-' . $enablednetworks[$key]['id'] . '" href="' . arrNetworks( $enablednetworks[$key]['id'], $is_shortcode ) . '" target="_blank" rel="nofollow"><span class="icon"></span><span class="text">' . $name . '</span></a>';
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
 * Render template
 * Returns share buttons and share count
 * 
 * @since 1.0
 * @returns string html
 */

function mashshareShow() {

    $return = '<aside class="mashsb-container mashsb-main">'
            . mashsb_content_above() .
            '<div class="mashsb-box">'
            . apply_filters( 'mashsb_sharecount_filter', mashsb_render_sharecounts() ) .
            '<div class="mashsb-buttons">'
            . mashsb_getNetworks() .
            '</div></div>
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
function mashsb_render_sharecounts( $customurl = '', $align = 'left' ) {
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

    $html = '<div class="mashsb-count" style="float:' . $align . ';"><div class="counts mashsbcount">' . $sharecount . '</div><span class="mashsb-sharetext">' . $sharetitle . '</span></div>';
    return $html;
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

    //!empty( $mashsb_options['sharecount_title'] ) ? $sharecount_title = $mashsb_options['sharecount_title'] : $sharecount_title = __( 'SHARES', 'mashsb' );
    //!empty($mashsb_options['visible_services']) ? $visible_services = $mashsb_options['visible_services'] : $visible_services = 1;
    //$sharecount_title = !empty( $mashsb_options['sharecount_title'] ) ? $mashsb_options['sharecount_title'] : __( 'SHARES', 'mashsb' );

    $services = !empty( $mashsb_options['visible_services'] ) ? $mashsb_options['visible_services'] : 1;
    $visible_services = ($services === 'all') ? 'all' : ($services + 1); // plus 1 to get networks correct counted (array's starting counting from zero)

    $sharecount = '';

    //Filter shortcode args to add an option for developers to change (add) some args
    apply_filters( 'mashsb_shortcode_atts', $args );

    extract( shortcode_atts( array(
        'cache' => '3600',
        'shares' => 'true',
        'buttons' => 'true',
        'services' => $visible_services + 1, //default is by admin option - plus 1 because array starts counting from zero
        'align' => 'left',
        'text' => '', // $text
        'url' => '' // $url
                    ), $args ) );

    // Define custom url var to share
    $mashsb_custom_url = empty( $url ) ? mashsb_get_url() : $url;

    // Define custom text to share
    $mashsb_custom_text = empty( $text ) ? mashsb_get_title() : $text;

    if( $shares != 'false' ) {
        $sharecount = mashsb_render_sharecounts( $mashsb_custom_url, $align );
        // shortcode [mashshare shares="true" buttons="false"] 
        if( $shares === "true" && $buttons === 'false' ) {
            return $sharecount;
        }
    }

    $return = '<aside class="mashsb-container mashsb-main">'
            . mashsb_content_above() .
            '<div class="mashsb-box">'
            . $sharecount .
            '<div class="mashsb-buttons">'
            . mashsb_getNetworks( true, $services ) .
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

/* Returns Share buttons on specific positions
 * Uses the_content filter
 * @since 1.0
 * @return string
 */

function mashshare_filter_content( $content ) {
    global $mashsb_options, $post, $wp_current_filter, $wp;

    $position = !empty( $mashsb_options['mashsharer_position'] ) ? $mashsb_options['mashsharer_position'] : '';
    $enabled_post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : null;
    $current_post_type = get_post_type();
    $frontpage = isset( $mashsb_options['frontpage'] ) ? true : false;
    $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;
    $singular = isset( $mashsb_options['singular'] ) ? $singular = true : $singular = false;

    if( !is_main_query() ) {
        mashdebug()->info( "is_main_query()" );
        return $content;
    }

    if( mashsb_is_excluded() )
        return $content;

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
    $mashsb_instance = mashshareShow();
    switch ( $position ) {
        case 'manual':
            break;

        case 'both':
            $content = $mashsb_instance . $content . $mashsb_instance;
            break;

        case 'before':
            $content = $mashsb_instance . $mashsb_instance;
            break;

        case 'after':
            $content .= $mashsb_instance;
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
        return $image[0];
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
    global $mashsb_options, $wp;
    $fakecountoption = 0;
    if( isset( $mashsb_options['fake_count'] ) ) {
        $fakecountoption = $mashsb_options['fake_count'];
    }
    $fakecount = round( $fakecountoption * mashsb_get_fake_factor(), 0 );
    return $fakecount;
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
    $html = !empty( $mashsb_options['content_above'] ) ? '<div class="mashsb_above_buttons">' . $mashsb_options['content_above'] . '</div>' : '';
    return apply_filters( 'mashsb_above_buttons', $html );
}

/* Additional content above share buttons 
 * 
 * @return string $html
 * @scince 2.3.2
 */

function mashsb_content_below() {
    global $mashsb_options;
    $html = !empty( $mashsb_options['content_below'] ) ? '<div class="mashsb_below_buttons">' . $mashsb_options['content_below'] . '</div>' : '';
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
        //mashdebug()->error("hoo");
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

    //mashdebug()->info( "is not excluded" );
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
    if( is_singular() ) {
        $title = $mashsb_meta_tags->get_og_title();
    } else if( !empty( $post->ID ) ) {
        $title = get_the_title( $post->ID );
        $title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
        $title = urlencode( $title );
        $title = str_replace( '#', '%23', $title );
        $title = esc_html( $title );
    } else {
        $title = mashsb_get_document_title();
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
    if( is_singular() ) {
        $title = $mashsb_meta_tags->get_twitter_title();
    } else {
        // title for non singular pages
        $title = mashsb_get_title();
        $title = str_replace( '+', '%20', $title );
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
    global $wp, $post;

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

//function mashsb_get_twitter_url() {
//    if( function_exists( 'mashsuGetShortURL' ) ) {
//        //mashsuGetShortURL($url) !== 0 ? $url = mashsuGetShortURL( $url ) : $url = mashsb_get_url();
//        $get_url = mashsb_get_url();
//        $url = mashsuGetShortURL( $get_url );
//    } else {
//        $url = mashsb_get_url();
//    }
//    return apply_filters( 'mashsb_get_twitter_url', $url );
//}
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
 * @global int $page  Page number of a single post.
 * @global int $paged Page number of a list of posts.
 *
 * @return string Tag with the document title.
 */
function mashsb_get_document_title() {
    // wp_get_document_title() exist since WP 4.4
    if( function_exists( 'wp_get_document_title' ) ) {
        return wp_get_document_title();
    }

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
    $title = apply_filters( 'pre_get_document_title', '' );
    if( !empty( $title ) ) {
        return $title;
    }

    global $page, $paged;

    $title = array(
        'title' => '',
    );

    // If it's a 404 page, use a "Page not found" title.
    if( is_404() ) {
        $title['title'] = __( 'Page not found' );

        // If it's a search, use a dynamic search results title.
    } elseif( is_search() ) {
        /* translators: %s: search phrase */
        $title['title'] = sprintf( __( 'Search Results for &#8220;%s&#8221;' ), get_search_query() );

        // If on the front page, use the site title.
    } elseif( is_front_page() ) {
        $title['title'] = get_bloginfo( 'name', 'display' );

        // If on a post type archive, use the post type archive title.
    } elseif( is_post_type_archive() ) {
        $title['title'] = post_type_archive_title( '', false );

        // If on a taxonomy archive, use the term title.
    } elseif( is_tax() ) {
        $title['title'] = single_term_title( '', false );

        /*
         * If we're on the blog page that is not the homepage or
         * a single post of any post type, use the post title.
         */
    } elseif( is_home() || is_singular() ) {
        $title['title'] = single_post_title( '', false );

        // If on a category or tag archive, use the term title.
    } elseif( is_category() || is_tag() ) {
        $title['title'] = single_term_title( '', false );

        // If on an author archive, use the author's display name.
    } elseif( is_author() && $author = get_queried_object() ) {
        $title['title'] = $author->display_name;

        // If it's a date archive, use the date as the title.
    } elseif( is_year() ) {
        $title['title'] = get_the_date( _x( 'Y', 'yearly archives date format' ) );
    } elseif( is_month() ) {
        $title['title'] = get_the_date( _x( 'F Y', 'monthly archives date format' ) );
    } elseif( is_day() ) {
        $title['title'] = get_the_date();
    }

    // Add a page number if necessary.
    if( ( $paged >= 2 || $page >= 2 ) && !is_404() ) {
        $title['page'] = sprintf( __( 'Page %s' ), max( $paged, $page ) );
    }

    // Append the description or site title to give context.
    if( is_front_page() ) {
        $title['tagline'] = get_bloginfo( 'description', 'display' );
    } else {
        $title['site'] = get_bloginfo( 'name', 'display' );
    }

    /**
     * Filter the separator for the document title.
     *
     * @since 4.4.0
     *
     * @param string $sep Document title separator. Default '-'.
     */
    $sep = apply_filters( 'document_title_separator', '-' );

    /**
     * Filter the parts of the document title.
     *
     * @since 4.4.0
     *
     * @param array $title {
     *     The document title parts.
     *
     *     @type string $title   Title of the viewed page.
     *     @type string $page    Optional. Page number if paginated.
     *     @type string $tagline Optional. Site description when on home page.
     *     @type string $site    Optional. Site title when not on home page.
     * }
     */
    $title = apply_filters( 'document_title_parts', $title );

    $title = implode( " $sep ", array_filter( $title ) );
    $title = wptexturize( $title );
    $title = convert_chars( $title );
    $title = esc_html( $title );
    $title = capital_P_dangit( $title );

    return $title;
}
