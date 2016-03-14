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
if ( !defined('ABSPATH') )
    exit;


/* Load Hooks
 * @since 2.0
 * return void
 */

add_shortcode('mashshare', 'mashshareShortcodeShow');
add_filter('the_content', 'mashshare_filter_content', getExecutionOrder(), 1);
add_filter('widget_text', 'do_shortcode');
add_action('mashshare', 'mashshare');
add_filter('mash_share_title', 'mashsb_get_title', 10, 2);


/* Get Execution order of injected Share Buttons in $content 
 *
 * @since 2.0.4
 * @return int
 */

function getExecutionOrder() {
    global $mashsb_options;
    isset($mashsb_options['execution_order']) && is_numeric($mashsb_options['execution_order']) ? $priority = trim($mashsb_options['execution_order']) : $priority = 1000;
    return $priority;
}

/* Creates some shares for older posts which has been already 
 * shared dozens of times
 * This smooths the Velocity Graph Add-On if available
 * 
 * @since 2.0.9
 * @return int
 * 
 * @deprecated deprecated since version 2.2.8
 */

function mashsbSmoothVelocity($mashsbShareCounts) {
    switch ( $mashsbShareCounts ) {
        case $mashsbShareCounts >= 1000:
            $mashsbShareCountArr = array(100, 170, 276, 329, 486, 583, 635, 736, 875, $mashsbShareCounts);
            return $mashsbShareCountArr;
            break;
        case $mashsbShareCounts >= 600:
            $mashsbShareCountArr = array(75, 99, 165, 274, 384, 485, 573, $mashsbShareCounts);
            return $mashsbShareCountArr;
            break;
        case $mashsbShareCounts >= 400:
            $mashsbShareCountArr = array(25, 73, 157, 274, 384, 399, $mashsbShareCounts);
            return $mashsbShareCountArr;
            break;
        case $mashsbShareCounts >= 200:
            $mashsbShareCountArr = array(52, 88, 130, 176, 199, $mashsbShareCounts);
            return $mashsbShareCountArr;
            break;
        case $mashsbShareCounts >= 100:
            $mashsbShareCountArr = array(23, 54, 76, 87, 99, $mashsbShareCounts);
            return $mashsbShareCountArr;
            break;
        case $mashsbShareCounts >= 60:
            $mashsbShareCountArr = array(2, 10, 14, 18, 27, 33, 45, 57, $mashsbShareCounts);
            return $mashsbShareCountArr;
            break;
        case $mashsbShareCounts >= 20:
            $mashsbShareCountArr = array(2, 5, 7, 9, 9, 10, 11, 13, 15, 20, $mashsbShareCounts);
            return $mashsbShareCountArr;
            break;
        case $mashsbShareCounts == 0:
            $mashsbShareCountArr = array(0);
            return $mashsbShareCountArr;
            break;
        default:
            $mashsbShareCountArr = array(0);
            return $mashsbShareCountArr;
    }
}

/* Get mashsbShareObject 
 * depending if MashEngine or sharedcount.com is used
 * 
 * @since 2.0.9
 * @return object
 * @changed 2.2.7
 */

function mashsbGetShareObj($url) {
    global $mashsb_options;
    $mashengine = isset($mashsb_options['mashsb_sharemethod']) && $mashsb_options['mashsb_sharemethod'] === 'mashengine' ? true : false;
    if ( $mashengine ) {
        if ( !class_exists('RollingCurlX') )
            require_once MASHSB_PLUGIN_DIR . 'includes/libraries/RolingCurlX.php';
        if ( !class_exists('mashengine') )
            require_once(MASHSB_PLUGIN_DIR . 'includes/mashengine.php');
        mashdebug()->error('mashsbGetShareObj() url: ' . $url);
        $mashsbSharesObj = new mashengine($url);
        return $mashsbSharesObj;
    }
    require_once(MASHSB_PLUGIN_DIR . 'includes/sharedcount.class.php');
    $apikey = isset($mashsb_options['mashsharer_apikey']) ? $mashsb_options['mashsharer_apikey'] : '';
    $mashsbSharesObj = new mashsbSharedcount($url, 10, $apikey);
    return $mashsbSharesObj;
}

/* Get the correct share method depending if mashshare networks is enabled
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

function mashsbGetShareMethod($mashsbSharesObj) {
    if ( class_exists('MashshareNetworks') ) {
        $mashsbShareCounts = $mashsbSharesObj->getAllCounts();
        return $mashsbShareCounts;
    }
    $mashsbShareCounts = $mashsbSharesObj->getFBTWCounts();
    return $mashsbShareCounts;
}

/**
 * Get share count for all pages where $post is empty or a custom url is used E.g. category or blog list pages or in shortcodes
 * Uses transients 
 * 
 * @param string $url
 *  
 * @returns integer $shares
 */
function mashsbGetNonPostShares($url) {

    isset($mashsb_options['mashsharer_cache']) ? $cacheexpire = $mashsb_options['mashsharer_cache'] : $cacheexpire = 300;
    /* make sure 300sec is default value */
    $cacheexpire < 300 ? $cacheexpire = 300 : $cacheexpire;
    
    if ( MASHSB_DEBUG ){
        $cacheexpire = 10;
    }

    if ( isset($mashsb_options['disable_cache']) ) {
        //$cacheexpire = 2;
        delete_transient('mashcount_' . md5($url));
    }

    // Get any existing copy of our transient data
    if ( false === get_transient('mashcount_' . md5($url)) ) {

        // It wasn't there, so regenerate the data and save the transient
        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj($url);

        // Get the share counts object
        $mashsbShareCounts = mashsbGetShareMethod($mashsbSharesObj);

        // Set the transient
        set_transient('mashcount_' . md5($url), $mashsbShareCounts->total, $cacheexpire);
        mashdebug('mashsbGetNonPostShares set_transient:' . $mashsbShareCounts->total);
        return $mashsbShareCounts->total + getFakecount();
    } else {
        $shares = get_transient('mashcount_' . md5($url));
        if ( isset($shares) && is_numeric($shares) ) {
            mashdebug()->info('mashsbGetNonPostShares() get_transient: ' . $shares);
            return $shares + getFakecount();
        } else {
            return 0 + getFakecount(); // we need a result
        }
    }
}

/*
 * Return the share count
 * 
 * @param1 string url of the page the share count is collected for
 * @returns int
 */

function getSharedcount($url) {
    global $mashsb_options, $post;

    isset($mashsb_options['mashsharer_cache']) ? $cacheexpire = $mashsb_options['mashsharer_cache'] : $cacheexpire = 300;
    /* make sure 300sec is default value */
    $cacheexpire < 300 ? $cacheexpire = 300 : $cacheexpire;
    
    if ( MASHSB_DEBUG ){
        $cacheexpire = 10;
    }

    /* Bypass next lines and return share count for pages with empty $post object
      Category, blog list pages, non singular() pages. Store the shares in transients with mashsbGetNonPostShares();
     */
    if ( !empty($url) && is_null($post) ) {
        return mashsbGetNonPostShares($url);
    }

    /*
     * Important: This runs on non singular pages and prevents php crashes and loops without results
     */
    if ( empty($url) && !is_null($post) ){
        return get_post_meta($post->ID, 'mashsb_shares', true) + getFakecount();
    }
    
    $mashsbNextUpdate = ( int ) $cacheexpire;
    $mashsbLastUpdated = get_post_meta($post->ID, 'mashsb_timestamp', true);


    if ( empty($mashsbLastUpdated) ) {
        $mashsbLastUpdated = 0;
    }

    if ( $mashsbLastUpdated + $mashsbNextUpdate <= time() ) {
        // Write timestamp (Use this on top of this conditional. If this is not on top following return statements will be skipped and ignored - possible bug?)
        update_post_meta($post->ID, 'mashsb_timestamp', time());
        
        //update_post_meta($post->ID, 'mashsb_timestamp', time());
        mashdebug()->info("First Update - Frequency: " . $mashsbNextUpdate . " Next update: " . date('Y-m-d H:i:s', $mashsbLastUpdated + $mashsbNextUpdate) . " last updated: " . date('Y-m-d H:i:s', $mashsbLastUpdated) . " Current time: " . date('Y-m-d H:i:s', time()));

        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj($url);
        // Get the share counts
        $mashsbShareCounts = mashsbGetShareMethod($mashsbSharesObj);
        //$mashsbShareCounts = new stdClass(); // USE THIS FOR DEBUGGING
        //$mashsbShareCounts->total = 13; // USE THIS FOR DEBUGGING
        $mashsbStoredDBMeta = get_post_meta($post->ID, 'mashsb_shares', true);

        /*
         * Update post_meta only when API is requested and
         * API share count is greater than real fresh requested share count ->
         */

        if ( $mashsbShareCounts->total >= $mashsbStoredDBMeta ) {
            update_post_meta($post->ID, 'mashsb_shares', $mashsbShareCounts->total);
            update_post_meta($post->ID, 'mashsb_jsonshares', json_encode($mashsbShareCounts));
            mashdebug()->info("updated database with share count: " . $mashsbShareCounts->total);
            mashdebug()->info("fake count: " . getFakecount());
            /* return counts from getAllCounts() after DB update */
            return apply_filters('filter_get_sharedcount', $mashsbShareCounts->total + getFakecount());
        }
        /* return previous counts from DB Cache | this happens when API has a hiccup and does not return any results as expected */
        return apply_filters('filter_get_sharedcount', $mashsbStoredDBMeta + getFakecount());
    } else {
        /* return counts from post_meta plus fake count | This is regular cached result */
        $cachedCountsMeta = get_post_meta($post->ID, 'mashsb_shares', true);
        $cachedCounts = $cachedCountsMeta + getFakecount();
        mashdebug()->info("Cached result - Frequency: " . $mashsbNextUpdate . " Next update: " . date('Y-m-d H:i:s', $mashsbLastUpdated + $mashsbNextUpdate) . " last updated: " . date('Y-m-d H:i:s', $mashsbLastUpdated) . " Current time: " . date('Y-m-d H:i:s', time()));
        return apply_filters('filter_get_sharedcount', $cachedCounts);
    }
}

function mashsb_subscribe_button() {
    global $mashsb_options;
    if ( $mashsb_options['networks'][2] ) {
        $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"></span><span class="text">' . __('Subscribe', 'mashsb') . '</span></a>';
    } else {
        $subscribebutton = '';
    }
    return apply_filters('mashsb_filter_subscribe_button', $subscribebutton);
}

/* Put the Subscribe container under the share buttons
 * @since 2.0.0.
 * @return string
 */

function mashsb_subscribe_content() {
    global $mashsb_options;
    if ( isset($mashsb_options['networks'][2]) && isset($mashsb_options['subscribe_behavior']) && $mashsb_options['subscribe_behavior'] === 'content' ) { //Subscribe content enabled
        $container = '<div class="mashsb-toggle-container">' . mashsb_cleanShortcode('mashshare', $mashsb_options['subscribe_content']) . '</div>';
    } else {
        $container = '';
    }
    return apply_filters('mashsb_toggle_container', $container);
}

/* Check if [mashshare] shortcode is used in subscribe field and deletes it
 * Prevents infinte loop
 * 
 * @since 2.0.9
 * @return string / shortcodes parsed
 */

function mashsb_cleanShortcode($code, $content) {
    global $shortcode_tags;
    $stack = $shortcode_tags;
    $shortcode_tags = array($code => 1);
    $content = strip_shortcodes($content);
    $shortcode_tags = $stack;

    return do_shortcode($content);
}

/* Round the totalshares
 * 
 * @since 1.0
 * @return string
 */

function roundshares($totalshares) {
    if ( $totalshares > 1000000 ) {
        $totalshares = round($totalshares / 1000000, 1) . 'M';
    } elseif ( $totalshares > 1000 ) {
        $totalshares = round($totalshares / 1000, 1) . 'k';
    }
    return apply_filters('get_rounded_shares', $totalshares);
}

/* Return the more networks button
 * @since 2.0
 * @return string
 */

function onOffSwitch() {
    $output = '<div class="onoffswitch"></div>';
    return apply_filters('mashsh_onoffswitch', $output);
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
    return apply_filters('mashsh_onoffswitch2', $output);
}

/* Delete all services from array which are not enabled
 * @since 2.0.0
 * @return callback
 */

function isStatus($var) {
    return (!empty($var["status"]));
}

/* Array of all available network share urls
 * 
 * @param string $name id of the network
 * @param bool $is_shortcode true when function is used in shortcode [mashshare]
 * 
 * @since 2.1.3
 * @return string
 */

function arrNetworks($name, $is_shortcode) {
    global $mashsb_options, $post, $mashsb_custom_url, $mashsb_custom_text;
    $singular = isset($mashsb_options['singular']) ? $singular = true : $singular = false;

    if ( $is_shortcode ) {
        $url = !empty($mashsb_custom_url) ? $mashsb_custom_url : mashsb_get_url();
        $title = !empty($mashsb_custom_text) ? $mashsb_custom_text : mashsb_get_title();
        $twitter_title = !empty($mashsb_custom_text) ? $mashsb_custom_text : mashsb_get_twitter_title();
        $twitter_url = !empty($mashsb_custom_url) ? mashsb_get_shorturl($mashsb_custom_url) : mashsb_get_twitter_url();
    }
    if ( !$is_shortcode ) {
        $url = mashsb_get_url();
        $title = mashsb_get_title();
        $twitter_title = mashsb_get_twitter_title();
        $twitter_url = mashsb_get_twitter_url();
    }

    !empty($mashsb_options['mashsharer_hashtag']) ? $via = '&via=' . $mashsb_options['mashsharer_hashtag'] : $via = '';

    $networks = apply_filters('mashsb_array_networks', array(
        'facebook' => 'http://www.facebook.com/sharer.php?u=' . $url,
        'twitter' => 'https://twitter.com/intent/tweet?text=' . $twitter_title . '&url=' . $twitter_url . $via,
        'subscribe' => '#',
        'url' => $url,
        'title' => $title
    ));
    return isset($networks[$name]) ? $networks[$name] : '';
}

/* Returns all available networks
 * 
 * @since 2.0
 * @param bool true when used from shortcode [mashshare]
 */

function getNetworks($is_shortcode = false) {
    global $mashsb_options, $enablednetworks;

    $output = '';
    $startsecondaryshares = '';
    $endsecondaryshares = '';
    /* content of 'more services' button */
    $onoffswitch = '';
    /* counter for 'Visible Services' */
    $startcounter = 1;
    $maxcounter = isset($mashsb_options['visible_services']) ? $mashsb_options['visible_services'] + 1 : 0; // plus 1 because our array values start counting from zero
    /* our list of available services, includes the disabled ones! 
     * We have to clean this array first!
     */
    $getnetworks = isset($mashsb_options['networks']) ? $mashsb_options['networks'] : '';
    // Delete disabled services from array. Use callback function here. Only once: array_filter is slow. 
    // Use the newly created array and bypass the callback function than
    if ( is_array($getnetworks) ) {
        if ( !is_array($enablednetworks) ) {
            $enablednetworks = array_filter($getnetworks, 'isStatus');
        } else {
            $enablednetworks = $enablednetworks;
        }
    } else {
        $enablednetworks = $getnetworks;
    }

    if ( !empty($enablednetworks) ) {
        foreach ( $enablednetworks as $key => $network ):
            if ( $mashsb_options['visible_services'] !== 'all' && $maxcounter != count($enablednetworks) && $mashsb_options['visible_services'] < count($enablednetworks) ) {
                if ( $startcounter === $maxcounter ) {
                    $onoffswitch = onOffSwitch();
                    $startsecondaryshares = '<div class="secondary-shares" style="display:none;">';
                } else {
                    $onoffswitch = '';
                    $onoffswitch2 = '';
                    $startsecondaryshares = '';
                }
                if ( $startcounter === (count($enablednetworks)) ) {
                    $endsecondaryshares = '</div>';
                } else {
                    ;
                    $endsecondaryshares = '';
                }

                //echo "<h1>Debug: Startcounter " . $startcounter . " Hello: " . $maxcounter+1 .
                //" Debug: Enabled services: " . count($enablednetworks) . "</h1>"; 
            }
            if ( $enablednetworks[$key]['name'] != '' ) {
                /* replace all spaces with $nbsp; This prevents error in css style content: text-intend */
                $name = preg_replace('/\040{1,}/', '&nbsp;', $enablednetworks[$key]['name']);
            } else {
                $name = ucfirst($enablednetworks[$key]['id']);
            }
            $enablednetworks[$key]['id'] == 'whatsapp' ? $display = 'display:none;' : $display = ''; // Whatsapp button is made visible via js when opened on mobile devices

            $output .= '<a style="' . $display . '" class="mashicon-' . $enablednetworks[$key]['id'] . '" href="' . arrNetworks($enablednetworks[$key]['id'], $is_shortcode) . '" target="_blank" rel="nofollow"><span class="icon"></span><span class="text">' . $name . '</span></a>';
            $output .= $onoffswitch;
            $output .= $startsecondaryshares;

            $startcounter++;
        endforeach;
        $output .= onOffSwitch2();
        $output .= $endsecondaryshares;
    }

    return apply_filters('return_networks', $output);
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
            . apply_filters('mashsb_sharecount_filter', mashsb_render_sharecounts()) .
            '<div class="mashsb-buttons">'
            . getNetworks() .
            '</div></div>
                    <div style="clear:both;"></div>'
            . mashsb_subscribe_content()
            . mashsb_content_below() .
            '</aside>
            <!-- Share buttons by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
    return apply_filters('mashsb_output_buttons', $return);
}

/**
 * Render the sharecount template
 * 
 * @param string $customurl default empty
 * @param string alignment default left
 * @return string html
 */
function mashsb_render_sharecounts($customurl = '', $align='left'){
    global $mashsb_options;
    
    if ( isset( $mashsb_options['disable_sharecount'] ) || !mashsb_curl_installed() ) {
        return;
    }

    $url = empty($customurl) ? mashsb_get_url() : $customurl;
    $sharetitle = isset($mashsb_options['sharecount_title']) ? $mashsb_options['sharecount_title'] :  __('SHARES', 'mashsb');
    // If $url is valid wordpress url store and return share count from getSharedcount() else with mashsbGetNonPostShares()
    if ( $url == mashsb_get_url() ) {
        $shares = getSharedcount($url);
        $sharecount = isset($mashsb_options['mashsharer_round']) ? roundshares($shares) : getSharedcount($url);
    } else {
        $shares = mashsbGetNonPostShares($url);
        $sharecount = isset($mashsb_options['mashsharer_round']) ? roundshares($shares) : mashsbGetNonPostShares($url);
    }
    // do not show shares after x shares
    if( mashsb_hide_shares($shares) ){
        return;
    }

    $html = '<div class="mashsb-count" style="float:' . $align . ';"><div class="counts mashsbcount">' . $sharecount . '</div><span class="mashsb-sharetext">' . $sharetitle . '</span></div>';
    //$html .= 'Debug: mashsb_get_url():' . mashsb_get_url() . '</br>customurl:' . $customurl;
    return $html;
}

/* Shortcode function
 * Select Share count from database and returns share buttons and share counts
 * @since 1.0
 * @returns string
 */

function mashshareShortcodeShow($args) {
    global $wpdb, $mashsb_options, $post, $wp, $mashsb_custom_url, $mashsb_custom_text;

    !empty($mashsb_options['sharecount_title']) ? $sharecount_title = $mashsb_options['sharecount_title'] : $sharecount_title = __('SHARES', 'mashsb');

    $sharecount = '';

    extract(shortcode_atts(array(
        'cache' => '3600',
        'shares' => 'true',
        'buttons' => 'true',
        'align' => 'left',
        'text' => '', // $text
        'url' => '' // $url
                    ), $args));

    // Define custom url var to share
    $mashsb_custom_url = empty($url) ? mashsb_get_url() : $url;

    // Define custom text to share
    $mashsb_custom_text = empty($text) ? mashsb_get_title() : $text;

    if ( $shares != 'false' ) {
        $sharecount = mashsb_render_sharecounts($mashsb_custom_url, $align); 
        // shortcode [mashshare shares="true" buttons="false"] 
        if ( $shares === "true" && $buttons === 'false' ) {
            return $sharecount;
        }
    }

    $return = '<aside class="mashsb-container mashsb-main">'
            . mashsb_content_above() .
            '<div class="mashsb-box">'
            . $sharecount .
            '<div class="mashsb-buttons">'
            . getNetworks(true) .
            '</div></div>
                    <div style="clear:both;"></div>'
            . mashsb_subscribe_content()
            . mashsb_content_below() .
            '</aside>
            <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';

    return apply_filters('mashsb_output_buttons', $return);
}


/* Returns active status of Mashshare.
 * Used for scripts.php $hook
 * @since 2.0.3
 * @return bool True if MASHSB is enabled on specific page or post.
 * @TODO: Check if shortcode [mashshare] is used in widget
 */

function mashsbGetActiveStatus() {
    global $mashsb_options, $post;

    $frontpage = isset($mashsb_options['frontpage']) ? true : false;
    $current_post_type = get_post_type();
    $enabled_post_types = isset($mashsb_options['post_types']) ? $mashsb_options['post_types'] : array();
    $singular = isset($mashsb_options['singular']) ? true : false;
    $loadall = isset($mashsb_options['loadall']) ? $loadall = true : $loadall = false;


    if ( mashsb_is_excluded() ) {
        mashdebug()->info("mashsb_is_excluded()");
        return apply_filters('mashsb_active', false);
    }

    if ( $loadall ) {
        mashdebug()->info("load all mashsb scripts");
        return apply_filters('mashsb_active', true);
    }

    // Load on frontpage
    if ( $frontpage === true && is_front_page() ) {
        mashdebug()->info("allow frontpage and is frontpage");
        return apply_filters('mashsb_active', true);
    }

    // Load scripts when shortcode is used
    /* Check if shortcode is used */
    if ( function_exists('has_shortcode') && is_object($post) && has_shortcode($post->post_content, 'mashshare') ) {
        mashdebug()->info("has_shortcode");
        return apply_filters('mashsb_active', true);
    }

    // No scripts on non singular page
    if ( !is_singular() && !$singular ) {
        mashdebug()->info("No scripts on non singular page");
        return apply_filters('mashsb_active', false);
    }


    // Load scripts when post_type is defined (for automatic embeding)
    if ( in_array($current_post_type, $enabled_post_types) ) {
        mashdebug()->info("automatic post_type enabled");
        return apply_filters('mashsb_active', true);
    }

    mashdebug()->info("mashsbGetActiveStatus false");
    return apply_filters('mashsb_active', false);
}

/* Returns Share buttons on specific positions
 * Uses the_content filter
 * @since 1.0
 * @return string
 */

function mashshare_filter_content($content) {
    global $mashsb_options, $post, $wp_current_filter, $wp;

    $position = !empty($mashsb_options['mashsharer_position']) ? $mashsb_options['mashsharer_position'] : '';
    $enabled_post_types = isset($mashsb_options['post_types']) ? $mashsb_options['post_types'] : null;
    $current_post_type = get_post_type();
    $frontpage = isset($mashsb_options['frontpage']) ? true : false;
    $excluded = isset($mashsb_options['excluded_from']) ? $mashsb_options['excluded_from'] : null;
    $singular = isset($mashsb_options['singular']) ? $singular = true : $singular = false;

    if ( mashsb_is_excluded() )
        return $content;

    if ( $frontpage == false && is_front_page() ) {
        return $content;
    }

    if ( !is_singular() == 1 && $singular !== true ) {
        return $content;
    }

    if ( $enabled_post_types == null or ! in_array($current_post_type, $enabled_post_types) ) {
        return $content;
    }

    if ( in_array('get_the_excerpt', $wp_current_filter) ) {
        return $content;
    }

    switch ( $position ) {
        case 'manual':
            break;

        case 'both':
            $content = mashshareShow() . $content . mashshareShow();
            break;

        case 'before':
            $content = mashshareShow() . $content;
            break;

        case 'after':
            $content .= mashshareShow();
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
function mashsb_get_image($postID) {
    global $post;

    if ( !isset($post) ) {
        return '';
    }

    if ( has_post_thumbnail($post->ID) ) {
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
        return $image[0];
    }
}
add_action('mashsb_get_image', 'mashsb_get_image');

/**
 * Get excerpt for Facebook Share
 *
 * @since 1.0
 * @param int $postID
 * @return string
 */
function mashsb_get_excerpt_by_id($post_id) {
    mashdebug()->timer('mashsb_get_exerpt');
    $the_post = get_post($post_id); //Gets post ID
    $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
    $excerpt_length = 35; //Sets excerpt length by word count
    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
    $words = explode(' ', $the_excerpt, $excerpt_length + 1);
    if ( count($words) > $excerpt_length ) :
        array_pop($words);
        array_push($words, '…');
        $the_excerpt = implode(' ', $words);
    endif;
    $the_excerpt = '<p>' . $the_excerpt . '</p>';
    return wp_strip_all_tags($the_excerpt);
    mashdebug()->timer('mashsb_get_exerpt', true);
}

add_action('mashsb_get_excerpt_by_id', 'mashsb_get_excerpt_by_id');

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
    $wordcount = count(explode(' ', the_title_attribute('echo=0')));
    $factor = $wordcount / 10;
    return apply_filters('mashsb_fake_factor', $factor);
}

/* Sharecount fake number
 * @return int
 * @since 2.0.9
 * 
 */

function getFakecount() {
    global $mashsb_options, $wp;
    $fakecountoption = 0;
    if ( isset($mashsb_options['fake_count']) ) {
        $fakecountoption = $mashsb_options['fake_count'];
    }
    $fakecount = round($fakecountoption * mashsb_get_fake_factor(), 0);
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
    $html = !empty($mashsb_options['content_above']) ? '<div class="mashsb_above_buttons">' . $mashsb_options['content_above'] . '</div>' : '';
    return apply_filters('mashsb_above_buttons', $html);
}

/* Additional content above share buttons 
 * 
 * @return string $html
 * @scince 2.3.2
 */

function mashsb_content_below() {
    global $mashsb_options;
    $html = !empty($mashsb_options['content_below']) ? '<div class="mashsb_below_buttons">' . $mashsb_options['content_below'] . '</div>' : '';
    return apply_filters('mashsb_below_buttons', $html);
}

/**
 * Return general post title
 * 
 * @param string $title default post title
 * @return string the default post title, shortcode title or custom twitter title
 */
function mashsb_get_title() {
    function_exists('MASHOG') ? $title = MASHOG()->MASHOG_OG_Output->_get_title() : $title = the_title_attribute('echo=0');
    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    $title = urlencode($title);
    $title = str_replace('#', '%23', $title);
    $title = esc_html($title);

    return $title;
}

/**
 * Return twitter custom title
 * 
 * @return string the custom twitter title
 */
function mashsb_get_twitter_title() {
    if ( function_exists('MASHOG') ) {
        $title = MASHOG()->MASHOG_OG_Output->_get_tw_title();
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
        $title = urlencode($title);
        $title = str_replace('#', '%23', $title);
        $title = esc_html($title);
        $title = str_replace('+', '%20', $title);
    } else {
        $title = mashsb_get_title();
        $title = str_replace('+', '%20', $title);
    }
    return $title;
}

/* Get URL to share
 * 
 * @return url  $string
 * @scince 2.2.8
 */

function mashsb_get_url() {
    global $wp, $post, $numpages;
    if ( $numpages > 1 ) { // check if '<!-- nextpage -->' is used
        $url = get_permalink($post->ID);
    } elseif ( is_singular() ) {
        $url = get_permalink($post->ID);
    } else {
        $url = get_permalink($post->ID);
    }
    return apply_filters('mashsb_get_url', $url);
}

/* Get twitter URL to share
 * 
 * @return url  $string
 * @scince 2.2.8
 */

function mashsb_get_twitter_url() {
    if ( function_exists('mashsuGetShortURL') ) {
        $url = mashsb_get_url();
        
        //mashsuGetShortURL($url) !== 0 ? $url = mashsuGetShortURL( $url ) : $url = mashsb_get_url();
        $url = mashsuGetShortURL($url);
    } else {
        $url = mashsb_get_url();
    }
    return apply_filters('mashsb_get_twitter_url', $url);
}

/**
 * Wrapper for mashsuGetShortURL() which exists in shorturl addon
 * 
 * @param string $url
 * @return string
 */
function mashsb_get_shorturl($url) {
    if ( function_exists('mashsuGetShortURL') )
        return mashsuGetShortURL($url);

    return $url;
}

/**
 * Check if buttons are excluded from a specific post id
 * 
 * @return true if post is excluded
 */
function mashsb_is_excluded() {
    global $post, $mashsb_options;

    if ( !isset($post) ) {
        return false;
    }

    $excluded = isset($mashsb_options['excluded_from']) ? $mashsb_options['excluded_from'] : null;

    // Load scripts when page is not excluded
    if ( strpos($excluded, ',') !== false ) {
        //mashdebug()->error("hoo");
        $excluded = explode(',', $excluded);
        if ( in_array($post->ID, $excluded) ) {
            mashdebug()->info("is excluded");
            return true;
        }
    }
    if ( $post->ID == $excluded ) {
        mashdebug()->info("is single excluded");
        return true;
    }

    mashdebug()->info("is not excluded");
    return false;
}

/**
 * Get mashsb cache expiration time
 * 
 * @return int
 */
function mashsb_get_cache_expiration() {
    isset($mashsb_options['mashsharer_cache']) ? $cacheexpire = $mashsb_options['mashsharer_cache'] : $cacheexpire = 300;
    /* make sure 300sec is default value */
    $cacheexpire < 300 ? $cacheexpire = 300 : $cacheexpire;

    if ( isset($mashsb_options['disable_cache']) ) {
        $cacheexpire = 2;
    }

    return $cacheexpire;
}
