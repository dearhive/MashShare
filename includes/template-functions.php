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
if ( ! defined( 'ABSPATH' ) ) exit;



/* Load Hooks
 * @since 2.0
 * return void
 */

add_shortcode('mashshare', 'mashshareShortcodeShow');
add_filter('the_content', 'mashshare_filter_content', getExecutionOrder(), 1);
add_filter('widget_text', 'do_shortcode');
add_action('mashshare', 'mashshare');
add_filter('mash_share_title', 'mashsb_get_title', 10, 2);


// uncomment for debugging
//global $wp_filter; 
//print_r($wp_filter['the_content']);

/* Get Execution order of injected Share Buttons in $content 
 *
 * @since 2.0.4
 * @return int
 */

function getExecutionOrder(){
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
 * @deprecated deprecated since version 2.2.8
 */

function mashsbSmoothVelocity($mashsbShareCounts) {
    switch ($mashsbShareCounts) {
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
    if ($mashengine) {
        if(!class_exists('RollingCurlX'))  
        require_once MASHSB_PLUGIN_DIR . 'includes/libraries/RolingCurlX.php';
        if(!class_exists('mashengine'))         
            require_once(MASHSB_PLUGIN_DIR . 'includes/mashengine.php');
        $mashsbSharesObj = new mashengine($url);
        return $mashsbSharesObj;
    } 
        require_once(MASHSB_PLUGIN_DIR . 'includes/sharedcount.class.php');
        $mashsbSharesObj = new mashsbSharedcount($url);
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
    if (class_exists('MashshareNetworks')) {
        $mashsbShareCounts = $mashsbSharesObj->getAllCounts();
        return $mashsbShareCounts;
    } 
        $mashsbShareCounts = $mashsbSharesObj->getFBTWCounts();
        return $mashsbShareCounts;
}

/**
 * Get share count for all pages where $post is empty. E.g. category or blog list pages
 * Uses transient 
 * 
 * @param string $url
 * @param in $cacheexpire
 * @returns integer $shares
 */
/*function mashsbGetNonPostShares($url, $cacheexpire) {
    // Get any existing copy of our transient data
    if (false === ( $non_post_shares = get_transient('non_post_shares') )) {
        // It wasn't there, so regenerate the data and save the transient
        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj($url);
        // Get the share counts
        $mashsbShareCounts = mashsbGetShareMethod($mashsbSharesObj);
        $transient_name = md5($url);
        // Set the transient
        set_transient('$transient_name', $mashsbShareCounts, $cacheexpire);
    } else {
        $shares = get_transient('non_post_shares');
    }
    if (is_numeric($shares)){    
        return $shares;
        mashdebug()->info('Share count where $post is_null(): ' . $shares);
    }
}*/

/*
 * Return the share count
 * 
 * @param string url of the page the share count is collected for
 * @returns int
 */
function getSharedcount($url) {
    global $wpdb, $mashsb_options, $post;
    
    if (is_null($post)) {
    	return apply_filters('filter_get_sharedcount', 0);
    }
    
    isset($mashsb_options['mashsharer_cache']) ? $cacheexpire = $mashsb_options['mashsharer_cache'] : $cacheexpire = 300;
    /* make sure 300sec is default value */
    $cacheexpire < 300 ? $cacheexpire = 300 : $cacheexpire;

    if (isset($mashsb_options['disable_cache'])) {
        $cacheexpire = 5;
    }
    
    /* Bypass next lines and return share count for pages with empty $post object
       share count for pages where $post is empty. E.g. category or blog list pages
       Otherwise share counts are requested with every page load 
     *      */
    /*if (is_null($post)) {
    	return apply_filters('filter_get_sharedcount', mashsbGetNonPostShares($url, $cacheexpire));
    }*/
    
    
    $mashsbNextUpdate = (int) $cacheexpire;
    $mashsbLastUpdated = get_post_meta($post->ID, 'mashsb_timestamp', true);

    if (empty($mashsbLastUpdated)) {
        $mashsbCheckUpdate = true;
        $mashsbLastUpdated = 0;
    }

    if ($mashsbLastUpdated + $mashsbNextUpdate <= time()) {
        mashdebug()->info("First Update - Frequency: " . $mashsbNextUpdate . " Next update: " . date('Y-m-d H:i:s', $mashsbLastUpdated + $mashsbNextUpdate) . " last updated: " . date('Y-m-d H:i:s', $mashsbLastUpdated) . " Current time: " . date('Y-m-d H:i:s', time()));
        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj($url);
        // Get the share counts
        $mashsbShareCounts = mashsbGetShareMethod($mashsbSharesObj);
        //$mashsbShareCounts = new stdClass(); // USE THIS FOR DEBUGGING
        //$mashsbShareCounts->total = 13; // USE THIS FOR DEBUGGING
        $mashsbStoredDBMeta = get_post_meta($post->ID, 'mashsb_shares', true);
        // Write timestamp
        update_post_meta($post->ID, 'mashsb_timestamp', time());

        /* Update post_meta only when API is requested and
         * API share count is greater than real fresh requested share count ->
         * ### This meas there is an error in the API (Failure or hammering any limits, e.g. X-Rate-Limit) ###
         */

        if ($mashsbShareCounts->total >= $mashsbStoredDBMeta) {
            update_post_meta($post->ID, 'mashsb_shares', $mashsbShareCounts->total);
            update_post_meta($post->ID, 'mashsb_jsonshares', json_encode($mashsbShareCounts));
            mashdebug()->info("updated database with share count: " . $mashsbShareCounts->total);
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

function mashsb_subscribe_button(){
        global $mashsb_options;
        if ($mashsb_options['networks'][2]){
            $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"></span><span class="text">' . __('Subscribe', 'mashsb') . '</span></a>';
        } else {
            $subscribebutton = '';    
        }
         return apply_filters('mashsb_filter_subscribe_button', $subscribebutton );
    }
    
    /* Put the Subscribe container under the share buttons
     * @since 2.0.0.
     * @return string
     */
    
    function mashsb_subscribe_content(){
        global $mashsb_options;
        if ($mashsb_options['networks'][2] && $mashsb_options['subscribe_behavior'] === 'content'){ //Subscribe content enabled
            $container = '<div class="mashsb-toggle-container">' . mashsb_cleanShortcode('mashshare', $mashsb_options['subscribe_content']). '</div>';
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
    
    function mashsb_cleanShortcode($code, $content){
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
    
    function roundshares($totalshares){           
         if ($totalshares > 1000000) {
            $totalshares = round($totalshares / 1000000, 1) . 'M';
        } elseif ($totalshares > 1000) {
            $totalshares = round($totalshares / 1000, 1) . 'k';
        }
        return apply_filters('get_rounded_shares', $totalshares);
    }
    
    /* Return the more networks button
     * @since 2.0
     * @return string
     */
    function onOffSwitch(){
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
    function onOffSwitch2(){
        $output = '<div class="onoffswitch2" style="display:none;"></div>';
        return apply_filters('mashsh_onoffswitch2', $output);
    }

    /* Delete all services from array which are not enabled
     * @since 2.0.0
     * @return callback
     */
    function isStatus($var){
        return (!empty($var["status"]));
        }
       



/* Array of all available network share urls
    * 
    * @param string $name id of the network
    * @param string $url to share
    * @param string $title to share
    * @param mixed $customurl boolean | string false default
    * 
    * @since 2.1.3
    * @return string
    */   
        
    function arrNetworks($name) {
        global $mashsb_options, $post, $mashsb_custom_url, $mashsb_custom_text;
        $singular = isset( $mashsb_options['singular'] ) ? $singular = true : $singular = false;     

        $url = $mashsb_custom_url ? $mashsb_custom_url : mashsb_get_url() ;
        $twitter_url = $mashsb_custom_url ? $mashsb_custom_url : mashsb_get_twitter_url();
        $title = $mashsb_custom_text ? $mashsb_custom_text : mashsb_get_title();
        $twitter_title = $mashsb_custom_text ? $mashsb_custom_text : mashsb_get_twitter_title();

        !empty($mashsb_options['mashsharer_hashtag']) ? $via = '&amp;via=' . $mashsb_options['mashsharer_hashtag'] : $via = '';
       
        $networks = apply_filters('mashsb_array_networks', array(
            'facebook' => 'http://www.facebook.com/sharer.php?u=' . $url,
            'twitter' =>  'https://twitter.com/intent/tweet?text=' . $twitter_title . $via . '&amp;url=' . $twitter_url,
            'subscribe' => '#',
            'url' => $url,
            'title' => mashsb_get_title()   
        ));
        
            return isset($networks[$name]) ? $networks[$name] : '';    
        }
        


    /* Returns all available networks
     * 
     * @since 2.0
     * @param string $url to share
     * @param string $title to share
     * @param mixed $customurl boolean | string false default
     * @param string $custom_title a custom title for sharing 
     * @returns string
     */
    function getNetworks() {
        //mashdebug()->timer('getNetworks');
        global $mashsb_options, $enablednetworks;

        $output = '';
        $startsecondaryshares = '';
        $endsecondaryshares = '';
        /* content of 'more services' button */
        $onoffswitch = '';
        /* counter for 'Visible Services' */
        $startcounter = 1;
        $maxcounter = $mashsb_options['visible_services']+1; // plus 1 because our array values start counting from zero
        /* our list of available services, includes the disabled ones! 
         * We have to clean this array first!
         */
        $getnetworks = $mashsb_options['networks'];
        // Delete disabled services from array. Use callback function here. Only once: array_filter is slow. 
        // Use the newly created array and bypass the callback function than
        if (is_array($getnetworks)){
            if (!is_array($enablednetworks)){
                //echo "is not array";
                //var_dump($enablednetworks);
            $enablednetworks = array_filter($getnetworks, 'isStatus');
            }else {
                //echo "is array";
                //var_dump($enablednetworks);
            $enablednetworks = $enablednetworks;    
            }
        }else{
        $enablednetworks = $getnetworks; 
        }

    if (!empty($enablednetworks)) {
        foreach ($enablednetworks as $key => $network):
            if($mashsb_options['visible_services'] !== 'all' && $maxcounter != count($enablednetworks) && $mashsb_options['visible_services'] < count($enablednetworks)){
                if ($startcounter === $maxcounter ){ 
                    $onoffswitch = onOffSwitch();
                    $startsecondaryshares   = '<div class="secondary-shares" style="display:none;">';} else {$onoffswitch = ''; $onoffswitch2 = ''; $startsecondaryshares   = '';}
                if ($startcounter === (count($enablednetworks))){ 
                    $endsecondaryshares     = '</div>'; } else { ;$endsecondaryshares = '';}
                    
                //echo "<h1>Debug: Startcounter " . $startcounter . " Hello: " . $maxcounter+1 .
                //" Debug: Enabled services: " . count($enablednetworks) . "</h1>"; 
            }
            if ($enablednetworks[$key]['name'] !='') {
                /* replace all spaces with $nbsp; This prevents error in css style content: text-intend */
                $name = preg_replace('/\040{1,}/','&nbsp;',$enablednetworks[$key]['name']);
            } else {
                $name = ucfirst($enablednetworks[$key]['id']);
            }
            $enablednetworks[$key]['id'] == 'whatsapp' ? $display = 'display:none;' : $display = ''; // Whatsapp button is made visible via js when opened on mobile devices

            $output .= '<a style="' . $display . '" class="mashicon-' . $enablednetworks[$key]['id'] . '" href="' . arrNetworks($enablednetworks[$key]['id']) . '" target="_blank" rel="nofollow"><span class="icon"></span><span class="text">' . $name . '</span></a>';
            $output .= $onoffswitch;
            $output .= $startsecondaryshares;
            
            $startcounter++;
        endforeach;
        $output .= onOffSwitch2();
        $output .= $endsecondaryshares;
    }
    //mashdebug()->timer('getNetworks', true);
    return apply_filters('return_networks', $output);
    
}

    /* Select Share count from database and returns share buttons and share counts
     * @since 1.0
     * @returns string
     */
    function mashshareShow($atts, $place) {
        mashdebug()->timer('timer');
        $url = mashsb_get_url();
        //$title = mashsb_get_title();
        
        global $wpdb, $mashsb_options, $post;
        !empty($mashsb_options['mashsharer_apikey']) ? $apikey = $mashsb_options['mashsharer_apikey'] : $apikey = '';
        !empty($mashsb_options['sharecount_title']) ? $sharecount_title = $mashsb_options['sharecount_title'] : $sharecount_title = __('SHARES', 'mashsb');
        
            
            if (!isset($mashsb_options['disable_sharecount'])) {
                    /* Get totalshares of the current page */
                    $totalshares = getSharedcount($url);
                    /* Round total shares when enabled */
                    if (isset($mashsb_options['mashsharer_round'])) {
                        $totalshares = roundshares($totalshares);
                    }  
                 $sharecount = '<div class="mashsb-count"><div class="counts mashsbcount">' . $totalshares . '</div><span class="mashsb-sharetext">' . $sharecount_title . '</span></div>';    
             } else {
                 $sharecount = '';
             }
             
                     
                $return = '<aside class="mashsb-container">'
                        . mashsb_content_above().
                    '<div class="mashsb-box">'
                        . apply_filters('mashsb_sharecount_filter', $sharecount) .
                    '<div class="mashsb-buttons">' 
                        . getNetworks() . 
                    '</div></div>
                    <div style="clear:both;"></div>'
                    . mashsb_subscribe_content()
                    . mashsb_content_below() .
                    '</aside>
                        <!-- Share buttons by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
            mashdebug()->timer('timer', true);
            return apply_filters( 'mashsb_output_buttons', $return );
            
    }
    
    
    /* Shortcode function
     * Select Share count from database and returns share buttons and share counts
     * @since 1.0
     * @returns string
     */
    function mashshareShortcodeShow($atts, $place) {
        global $wpdb ,$mashsb_options, $post, $wp, $mashsb_custom_url, $mashsb_custom_text;
        
        //$mainurl = mashsb_get_url();

        !empty($mashsb_options['sharecount_title']) ? $sharecount_title = $mashsb_options['sharecount_title'] : $sharecount_title = __('SHARES', 'mashsb');
        
        $sharecount = '';

        extract(shortcode_atts(array(
            'cache' => '3600',
            'shares' => 'true',
            'buttons' => 'true',
            'align' => 'left',
            'text' => '',
            'url' => ''
                        ), $atts));

            /* Load hashshag*/       
            if ( !empty( $mashsb_options['mashsharer_hashtag'] ) ) {
                $via = '&amp;via=' . $mashsb_options['mashsharer_hashtag'];
            } else {
                $via = '';
            }

            // Define custom url var to share
            $mashsb_custom_url = empty($url) ? false : $url;
            
            // Define custom text to share
            $mashsb_custom_text = empty($text) ? false : $text;
            
            //$sharecount_url = empty($url) ? mashsb_get_url() : $url;
            
             if ($shares != 'false') {
                    /* get totalshares of the current page with sharedcount.com */
                    $totalshares = getSharedcount($mashsb_custom_url);
                    //$totalshares = getSharedcount($mainurl);
                    //$totalshares = $mashsb_custom_url;
                    /* Round total shares when enabled */
                    $roundenabled = isset($mashsb_options['mashsharer_round']) ? $mashsb_options['mashsharer_round'] : null;
                        if ($roundenabled) {
                            $totalshares = roundshares($totalshares);
                        }
                    $sharecount = '<div class="mashsb-count" style="float:' . $align . ';"><div class="counts">' . $totalshares . '</div><span class="mashsb-sharetext">' . $sharecount_title . '</span></div>';    
                    /*If shortcode [mashshare shares="true" onlyshares="true"]
                     * return shares and exit;
                     */
                    if ($shares === "true" && $buttons === 'false'){
                       return $sharecount; 
                    }
                    if ($shares === "false" && $buttons === 'true'){
                       $sharecount = '';
                }  
             }
     
                $return = '<aside class="mashsb-container">'
                    . mashsb_content_above().
                    '<div class="mashsb-box">'
                        . $sharecount .
                    '<div class="mashsb-buttons">' 
                        . getNetworks() . 
                    '</div></div>
                    <div style="clear:both;"></div>'
                    . mashsb_subscribe_content()
                    . mashsb_content_below() .
                    '</aside>
                        <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
        
        // Do not execute filter for excerpts
        //if(in_array('get_the_excerpt', $GLOBALS['wp_current_filter'])) apply_filters( 'mashsb_output_buttons', '' );
            
        return apply_filters( 'mashsb_output_buttons', $return );    
    }
    
    /* Returns active status of Mashshare.
     * Used for scripts.php $hook
     * @since 2.0.3
     * @return bool True if MASHSB is enabled on specific page or post.
     * @TODO: Check if shortcode [mashshare] is used in widget
     */
   
    function mashsbGetActiveStatus(){
       global $mashsb_options, $post;

       $frontpage = isset( $mashsb_options['frontpage'] ) ? $frontpage = 1 : $frontpage = 0;
       $current_post_type = get_post_type();
       $enabled_post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : array();
       $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;
       $singular = isset( $mashsb_options['singular'] ) ? $singular = true : $singular = false;
       $loadall = isset( $mashsb_options['loadall'] ) ? $loadall = true : $loadall = false;
       
       /*if ( is_404() )
           return false;*/
           
       if ($loadall){
           mashdebug()->info("load all mashsb scripts");
           return true;
       }
       
       // Load scripts when shortcode is used
       /* Check if shortcode is used */ 
       if( function_exists('has_shortcode') && is_object($post) && has_shortcode( $post->post_content, 'mashshare' ) ) {
           mashdebug()->info("has_shortcode");
            return true;
       } 
       
       
       // Load scripts when do_action('mashshare') is used
       //if(has_action('mashshare') && mashsb_is_excluded() !== true) {
       /*if(has_action('mashshare')) {
           mashdebug()->info("action1");
           return true;    
       }*/
       
       // Load scripts when do_action('mashsharer') is used
       //if(has_action('mashsharer') && mashsb_is_excluded() !== true) {
       /*if(has_action('mashsharer')) {
           mashdebug()->info("action2");
           return true;    
       }*/ 
       
       // No scripts on non singular page
       if (!is_singular() == 1 && $singular !== true) {
           return false;
       }

        // Load scripts when page is not excluded
        if (strpos($excluded, ',') !== false) {
            //mashdebug()->error("hoo");
            $excluded = explode(',', $excluded);
            if (!in_array($post->ID, $excluded)) {
                return true;
            }
        }
        if ($post->ID == $excluded) {
            return false;
        }
       
       // Load scripts when post_type is defined (for automatic embeding)
       //if ($enabled_post_types && in_array($currentposttype, $enabled_post_types) && mashsb_is_excluded() !== true ) {
       //if ($enabled_post_types == null or in_array($current_post_type, $enabled_post_types)) {
       if (in_array($current_post_type, $enabled_post_types)) {
           mashdebug()->info("100");
           return true;
       }  
       
       /* Check if post types are allowed */
       //mashdebug()->info("var frontpage enabled: " . $frontpage . " is_front_page(): " . is_front_page());
       //if ($enabled_post_types && in_array($currentposttype, $enabled_post_types) && mashsb_is_excluded() !== true) {
       /*if ($enabled_post_types && in_array($current_post_type, $enabled_post_types)) {
           mashdebug()->info("200");
           return true;
       }*/
       
       // No scripts on frontpage when disabled
       //if ($frontpage == 1 && is_front_page() == 1 && mashsb_is_excluded() !== true) {
       if ($frontpage == 1 && is_front_page() == 1) {
           mashdebug()->info("300");
            return true;
       }

    }
    


    
    /* Returns Share buttons on specific positions
     * Uses the_content filter
     * @since 1.0
     * @return string
     */
    function mashshare_filter_content($content){
        global $atts, $mashsb_options, $post, $wp_current_filter, $wp;
        
        // Do not execute filter for excerpts
        //if(in_array('get_the_excerpt', $GLOBALS['wp_current_filter'])) return $content;
        
        /* define some vars here to reduce multiple execution of basic functions */
        /* Use permalink when its not singular page, so on category pages the permalink is used. */
        $url = mashsb_get_url();
        $title = mashsb_get_title();
        /*function_exists('MASHOG') ? $title = MASHOG()->MASHOG_OG_Output->_get_title() : $title = the_title_attribute('echo=0');
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
        $title = urlencode($title);
        $title = str_replace('#' , '%23', $title);
        $title = esc_html($title);*/
        
        $position = !empty($mashsb_options['mashsharer_position']) ? $mashsb_options['mashsharer_position'] : '';
        $enabled_post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : null;
        $current_post_type = get_post_type();
        $frontpage = isset( $mashsb_options['frontpage'] ) ? $mashsb_options['frontpage'] : null;
        $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;
        $singular = isset( $mashsb_options['singular'] ) ? $singular = true : $singular = false;
        
        if (strpos($excluded, ',') !== false) {
             $excluded = explode(',', $excluded);
             if (in_array($post->ID, $excluded)) {
                return $content;
             }  
        }
    
        if ($post->ID == $excluded) {
                return $content;
        }  

        if (!is_singular() == 1 && $singular !== true) {
            return $content;
        }

        if ($frontpage == 0 && is_front_page() == 1) {
            return $content;
        }
        
        if ($enabled_post_types == null or !in_array($current_post_type, $enabled_post_types)) {
            return $content;
        }

        if (in_array('get_the_excerpt', $wp_current_filter)) {
            return $content;
        }
        
        if (is_feed()) {
            return $content;
        }
		
            switch($position){
                case 'manual':
                break;

                case 'both':
                    $content = mashshareShow($atts, '') . $content . mashshareShow($atts, "bottom");
                break;

                case 'before':
                    $content = mashshareShow($atts, '') . $content;
                    
                break;

                case 'after':
                    $content .= mashshareShow($atts, '');
                break;
            }
            return $content;

        }

/* Template function mashshare() 
 * @since 2.0.0
 * @return string
*/ 
function mashshare(){
    global $atts;
    /*global $content;
    global $post;
    global $wp;*/

    /* Use permalink when its not singular page, so on category pages the permalink is used. */
    //is_singular() ? $url = urlencode(home_url( $wp->request )) : $url = urlencode(get_permalink($post->ID));
    //$url = mashsb_get_url();
    //$title = mashsb_get_title(); 
    /*function_exists('MASHOG') ? $title = MASHOG()->MASHOG_OG_Output->_get_title() : $title = the_title_attribute('echo=0');
    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    $title = urlencode($title);
    $title = str_replace('#' , '%23', $title);
    $title = esc_html($title);*/
    echo mashshareShow($atts, '');
}

/* Deprecated: Template function mashsharer()
 * @since 1.0
 * @return string
*/ 
function mashsharer(){
    global $atts;
    /*global $content;
    global $post;
    global $wp;*/
    //is_singular() ? $url = urlencode(home_url( $wp->request )) : $url = urlencode(get_permalink($post->ID));
    //$url = mashsb_get_url();
    //$title = mashsb_get_title();       
    /*function_exists('MASHOG') ? $title = MASHOG()->MASHOG_OG_Output->_get_title() : $title = the_title_attribute('echo=0');
    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    $title = urlencode($title);
    $title = str_replace('#' , '%23', $title);
    $title = esc_html($title);*/
    echo mashshareShow($atts, '');
}




/**
 * Get Thumbnail featured image if existed
 *
 * @since 1.0
 * @param int $postID
 * @return string
 */
function mashsb_get_image($postID){
    mashdebug()->timer('mashsb_get_image');
            global $post;
            if (has_post_thumbnail( $post->ID )) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
				return $image[0];
            	}
    mashdebug()->timer('mashsb_get_image', true);
	}
add_action( 'mashsb_get_image', 'mashsb_get_image' );

/**
 * Get excerpt for Facebook Share
 *
 * @since 1.0
 * @param int $postID
 * @return string
 */
function mashsb_get_excerpt_by_id($post_id){
    mashdebug()->timer('mashsb_get_exerpt');
	$the_post = get_post($post_id); //Gets post ID
	$the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
	$excerpt_length = 35; //Sets excerpt length by word count
	$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
	$words = explode(' ', $the_excerpt, $excerpt_length + 1);
	if(count($words) > $excerpt_length) :
	array_pop($words);
	array_push($words, '…');
	$the_excerpt = implode(' ', $words);
	endif;
	$the_excerpt = '<p>' . $the_excerpt . '</p>';
	return wp_strip_all_tags($the_excerpt);
    mashdebug()->timer('mashsb_get_exerpt', true);
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
    $wordcount = str_word_count(the_title_attribute('echo=0')); //Gets title to be used as a basis for the count
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
    if (isset($mashsb_options['fake_count'])) {
        $fakecountoption = $mashsb_options['fake_count'];
    }
    $fakecount = round($fakecountoption * mashsb_get_fake_factor(), 0);
    //mashdebug()->info("fakecount: " . $fakecount);
    //return apply_filters('filter_get_fakecount', $fakecount);
    return $fakecount;
}

/* Show sharecount only when there is number of x shares. otherwise its hidden via css
 * @return bool
 * @since 2.0.7
 */

function mashsb_hide_shares(){
    global $mashsb_options, $post, $wp;
    $url = get_permalink(isset($post->ID));
    $sharelimit = isset($mashsb_options['hide_sharecount']) ? $mashsb_options['hide_sharecount'] : 0;
   
    if ($sharelimit > 0){
        //mashdebug()->error( "getsharedcount: " . getSharedcount($url) . "sharelimit " . $sharelimit);
        if (getSharedcount($url) > $sharelimit){
            return false;
        }else {
            return true;
        }
    }
    return false;
}

/**
 * Add Custom Styles with WP wp_add_inline_style Method
 *
 * @since 1.0
 * 
 * @return string
 */

function mashsb_styles_method() {
    global $mashsb_options;
    isset($mashsb_options['small_buttons']) ? $smallbuttons = true : $smallbuttons = false;
    
    /* VARS */
    isset($mashsb_options['share_color']) ? $share_color = $mashsb_options['share_color'] : $share_color = '';
    isset($mashsb_options['custom_css']) ? $custom_css = $mashsb_options['custom_css'] : $custom_css = '';
    isset($mashsb_options['button_width']) ? $button_width = $mashsb_options['button_width'] : $button_width = '';
    
    /* STYLES */
    $mashsb_custom_css = "
        .mashsb-count {
        color: {$share_color};
        }"; 
    if ( !empty($mashsb_options['border_radius']) && $mashsb_options['border_radius'] != 'default' ){
    $mashsb_custom_css .= '
        [class^="mashicon-"], .onoffswitch-label, .onoffswitch2-label {
            border-radius: ' . $mashsb_options['border_radius'] . 'px;
        }';   
    }
    if ( !empty($mashsb_options['mash_style']) && $mashsb_options['mash_style']  == 'shadow' ){
    $mashsb_custom_css .= '
        .mashsb-buttons a, .onoffswitch, .onoffswitch2, .onoffswitch-inner:before, .onoffswitch2-inner:before  {
            -webkit-transition: all 0.07s ease-in;
            -moz-transition: all 0.07s ease-in;
            -ms-transition: all 0.07s ease-in;
            -o-transition: all 0.07s ease-in;
            transition: all 0.07s ease-in;
            box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.2),inset 0 -1px 0 0 rgba(0, 0, 0, 0.3);
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);
            border: none;
            -moz-user-select: none;
            -webkit-font-smoothing: subpixel-antialiased;
            -webkit-transition: all linear .25s;
            -moz-transition: all linear .25s;
            -o-transition: all linear .25s;
            -ms-transition: all linear .25s;
            transition: all linear .25s;
        }';   
    }
    if ( !empty($mashsb_options['mash_style']) && $mashsb_options['mash_style']  == 'gradiant' ){
    $mashsb_custom_css .= '
        .mashsb-buttons a  {
            background-image: -webkit-linear-gradient(bottom,rgba(0, 0, 0, 0.17) 0%,rgba(255, 255, 255, 0.17) 100%);
            background-image: -moz-linear-gradient(bottom,rgba(0, 0, 0, 0.17) 0%,rgba(255, 255, 255, 0.17) 100%);
            background-image: linear-gradient(bottom,rgba(0,0,0,.17) 0%,rgba(255,255,255,.17) 100%);
            
        }';   
    }
    if (mashsb_hide_shares() === true){
    $mashsb_custom_css .= ' 
        .mashsb-box .mashsb-count {
            display: none;
        }';   
    }
    
    if ($smallbuttons === true){
    $mashsb_custom_css .= '[class^="mashicon-"] .text, [class*=" mashicon-"] .text{
        text-indent: -9999px !important;
        line-height: 0px;
        display: block;
        } 
    [class^="mashicon-"] .text:after, [class*=" mashicon-"] .text:after {
        content: "" !important;
        text-indent: 0;
        font-size:13px;
        display: block !important;
    }
    [class^="mashicon-"], [class*=" mashicon-"] {
        width:25%;
        text-align: center !important;
    }
    [class^="mashicon-"] .icon:before, [class*=" mashicon-"] .icon:before {
        float:none;
        margin-right: 0;
    }
    .mashsb-buttons a{
       margin-right: 3px;
       margin-bottom:3px;
       min-width: 0;
       width: 41px;
    }

    .onoffswitch, 
    .onoffswitch-inner:before, 
    .onoffswitch-inner:after 
    .onoffswitch2,
    .onoffswitch2-inner:before, 
    .onoffswitch2-inner:after  {
        margin-right: 0px;
        width: 41px;
        line-height: 41px;
    }';   
    } else {
    $mashsb_custom_css .= '
    .mashsb-buttons a {
    min-width: ' . $button_width . 'px;}';
    }
    
    $mashsb_custom_css .= $custom_css;
        // ----------- Hook into existed 'mashsb-style' at /templates/mashsb.min.css -----------
        wp_add_inline_style( 'mashsb-styles', $mashsb_custom_css );
}
add_action( 'wp_enqueue_scripts', 'mashsb_styles_method' );



    /* Additional content above share buttons 
     * 
     * @return string $html
     * @scince 2.3.2
     */
    function mashsb_content_above(){
        global $mashsb_options;
        $html = !empty ($mashsb_options['content_above']) ? '<div class="mashsb_above_buttons">' . $mashsb_options['content_above'] . '</div>' : '';
        return apply_filters( 'mashsb_above_buttons', $html );
    }
    
    /* Additional content above share buttons 
     * 
     * @return string $html
     * @scince 2.3.2
     */
    function mashsb_content_below(){
        global $mashsb_options;
        $html = !empty ($mashsb_options['content_below']) ? '<div class="mashsb_below_buttons">' .$mashsb_options['content_below'] . '</div>' : '';
        return apply_filters( 'mashsb_below_buttons', $html );
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
    $title = str_replace('#' , '%23', $title);
    $title = esc_html($title);
    
    return $title;
}

/**
 * Return twitter custom title
 * 
 * @return string the custom twitter title
 */
function mashsb_get_twitter_title() {
    if (function_exists('MASHOG')) {
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

function mashsb_get_url(){
    global $wp, $post, $numpages;
    if($numpages > 1){ // check if '<!-- nextpage -->' is used
        $url = urlencode(get_permalink($post->ID));
    } elseif (is_singular()){
        $url = urlencode(get_permalink($post->ID));
    }else{
        $url = urlencode(get_permalink($post->ID));
    }
    return apply_filters('mashsb_get_url', $url);
}

/* Get twitter URL to share
 * 
 * @return url  $string
 * @scince 2.2.8
 */

function mashsb_get_twitter_url(){
    global $wp, $post, $numpages; 
       if ( function_exists('mashsuGetShortURL')){
            $url = mashsb_get_url();
            mashsuGetShortURL($url) !== 0 ? $url = mashsuGetShortURL( $url ) : $url = mashsb_get_url();
        } else {
            $url = mashsb_get_url();
        }
    return apply_filters('mashsb_get_twitter_url', $url);
}
