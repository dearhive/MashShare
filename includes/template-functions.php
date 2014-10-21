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
add_filter('the_content', 'mashshare_filter_content', getExecutionOrder());
add_filter('widget_text', 'do_shortcode');
add_action('mashshare', 'mashshare');

/* Get Execution order of injected Share Buttons in $content 
 * Set global var $enablescripts to determine if js and css must be loaded in frontend
 * 
 * @since 2.0.4
 * @return int
 */

function getExecutionOrder(){
    global $mashsb_options, $enablescripts;
    $priority = mashsb_get_option('execution_order');
    if (is_int($priority)){
        return $priority;
    }
    /* return priority*/
    return 1000;
}
    
    /* Creates some shares for older posts which has been already 
     * shared dozens of times
     * This smooths the Velocity Graph Add-On if available
     * 
     * @since 2.0.9
     * @return int
     */
function mashsbSmoothVelocity ($mashsbShareCounts) {
        switch ($mashsbShareCounts) {
                    case $mashsbShareCounts >= 1000:
                        $mashsbShareCountArr = array(100,170,276,329,486,583,635,736,875, $mashsbShareCounts);
                        return $mashsbShareCountArr;
                        break;
                    case $mashsbShareCounts >= 600:
                        $mashsbShareCountArr = array(75,99,165,274,384,485,573, $mashsbShareCounts);
                        return $mashsbShareCountArr;
                        break;
                    case $mashsbShareCounts >= 400:
                        $mashsbShareCountArr = array(25,73,157,274,384,399, $mashsbShareCounts);
                        return $mashsbShareCountArr;
                        break;
                    case $mashsbShareCounts >= 200:
                        $mashsbShareCountArr = array(52,88,130,176,199, $mashsbShareCounts);
                        return $mashsbShareCountArr;
                        break;
                    case $mashsbShareCounts >= 100:
                        $mashsbShareCountArr = array(23,54,76,87,99, $mashsbShareCounts);
                        return $mashsbShareCountArr;
                        break;
                    case $mashsbShareCounts >= 60:
                        $mashsbShareCountArr = array(2,10,14,18,27,33,45,57, $mashsbShareCounts);
                        return $mashsbShareCountArr;
                        break;
                    case $mashsbShareCounts >= 20:
                        $mashsbShareCountArr = array(2,5,7,9,9, 10,11, 13, 15, 20, $mashsbShareCounts);
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

/* Get the ShareObject depending if Mashshare Server Add-On
 * is enabled or native API calls are used.
 * 
 * @since 2.0.9
 * @return object
 */

function mashsbGetShareObj($url) {
    if (class_exists('mashserver')) {
        //require_once(MASHSB_PLUGIN_DIR . 'includes/mashserver.php');
        $mashsbSharesObj = new shareCount($url);
        $mashsbSharesObj = new mashsbSharedcount($url);
        return $mashsbSharesObj;
    } 
        require_once(MASHSB_PLUGIN_DIR . 'includes/sharedcount.class.php');
        $mashsbSharesObj = new mashsbSharedcount($url);
        return $mashsbSharesObj;   
}

/* Get the correct share method depening if mashshare networks is enabled
 * 
 * @since 2.0.9
 * @return var
 * 
 */

/* Get the sharecounts from sharedcount.com or direct API calls.
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

function getSharedcount($url) {
    global $wpdb, $mashsb_options, $post;
    $cacheexpire = $mashsb_options['mashsharer_cache'];
    /* make sure 300sec is default value */
    $cacheexpire < 300 ? $cacheexpire = 300 : $cacheexpire;

    if (isset($mashsb_options['disable_cache'])) {
        $cacheexpire = 5;
    }
    $mashsbNextUpdate = (int) $cacheexpire;
    $mashsbLastUpdated = get_post_meta($post->ID, 'mashsb_timestamp', true);
    if (empty($mashsbLastUpdated)) {
        $mashsbCheckUpdate = true;
        $mashsbLastUpdated = 0;
    }
    if ($mashsbLastUpdated < (time() - $mashsbNextUpdate)) {
        mashdebug()->info( " Update frequency " . $mashsbNextUpdate . " last updated: " . $mashsbLastUpdated . "time: " . time());
        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj($url);
        // Get the share counts
        $mashsbShareCounts = mashsbGetShareMethod($mashsbSharesObj);
        
        //$mashsbShareCounts['total'] = 11; // USE THIS FOR DEBUGGING
        
        if (isset($mashsbCheckUpdate)) {
            mashdebug()->info("First Update");
            //Some fake shares to create smooth step values for older posts which 
            //did not use Mashshare before (important for velocity graph Add-On) 
            $mashsbShareCountArr = mashsbSmoothVelocity($mashsbShareCounts['total']);
            
             if (empty($mashsbShareCountArr)){
             $mashsbShareCountArr = array(0);}
             $mashsbStoredDBMeta = 0;
        } else {
            $mashsbStoredDBMeta = get_post_meta($post->ID, 'mashsb_shares', true);
            $mashsbStoredDBMetaArr = explode(",", $mashsbStoredDBMeta);
            if (count($mashsbStoredDBMetaArr) >= 40) {
                array_shift($mashsbStoredDBMetaArr);
                array_push($mashsbStoredDBMetaArr, $mashsbShareCounts['total']);
            } else {
                array_push($mashsbStoredDBMetaArr, $mashsbShareCounts['total']);
            }
            $mashsbShareCountArr = $mashsbStoredDBMetaArr;
        }
        
        /* Update post_meta only when API requested and
         * API share count is not smaller than current sharecount 
         * This would mean there was an error in the API (Failure or hammering any limits like X-Rate-Limit)
         */
        
        $lastDBStoredShareArray = explode(",", $mashsbStoredDBMeta);

        if ($mashsbShareCounts['total'] >= end((array_values($lastDBStoredShareArray)))) {
            
            mashdebug()->info("update database with sharedcount: " . $mashsbShareCounts['total']);
            
            $mashsbShareCountArr = implode(",", $mashsbShareCountArr);
            update_post_meta($post->ID, 'mashsb_shares', $mashsbShareCountArr);
            update_post_meta($post->ID, 'mashsb_timestamp', time());
            /* return counts from getAllCounts() when they are updated in DB */
            return apply_filters('filter_get_sharedcount', $mashsbShareCounts['total'] + getFakecount());
        }
        /* return previous counts from DB Cache | this happens when API has a hiccup and does not return any results as expected*/
        return apply_filters('filter_get_sharedcount', end((array_values($lastDBStoredShareArray))) + getFakecount());
    } else {
        /* return counts from post_meta cache | This is regular cached result */
        $cachedCountsArr = explode(",", get_post_meta($post->ID, 'mashsb_shares', true));
        $cachedCounts = end((array_values($cachedCountsArr))) + getFakecount();
        return apply_filters('filter_get_sharedcount', $cachedCounts);
    }
}
    

    
    function mashsb_subscribe_button(){
        global $mashsb_options;
        if ($mashsb_options['networks'][2]){
            // DEPRECATED todo: remove in later version $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"></span><span class="text">' . __('Subscribe', 'mashsb') . '</span></a>' . $addons;
        $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"></span><span class="text">' . __('Subscribe', 'mashsb') . '</span></a>';
        } else {
            $subscribebutton = '';    
        }
         return apply_filters('mashsb_filter_subscribe_button', $subscribebutton );
    }
    //add_filter('mashsb_output_networks', 'mashsb_subscribe_button');
    
    /* Put the Subscribe container under the share buttons
     * @since 2.0.0.
     * @return string
     */
    
    function mashsb_subscribe_content(){
        global $mashsb_options;
        if ($mashsb_options['networks'][2] && $mashsb_options['subscribe_behavior'] === 'content'){ //Subscribe content enabled
            $container = '<div class="mashsb-toggle-container" id="mashsb-toggle">' . mashsb_cleanShortcode('mashshare', $mashsb_options['subscribe_content']). '</div>';
        } else {
            $container = '';    
        }
         return apply_filters('mashsb_toggle_container', $container);
    }
    //add_filter('mashsb_output_buttons', 'mashsb_subscribe_content');
    
    
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
        $output = '<div class="onoffswitch">
                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" checked="">
                        <label class="onoffswitch-label" for="myonoffswitch">
                        <div class="onoffswitch-inner"></div>
                        </label>
                        </div>';
        return apply_filters('mashsh_onoffswitch', $output);
    }
    
    /* Return the second more networks button after 
     * last hidden additional service. initial status: hidden
     * Gets visible with click on 'plus'
     * @since 2.0
     * @return string
     */
    function onOffSwitch2(){
        $output = '<div class="onoffswitch2" style="display:none;">
                        <input type="checkbox" name="onoffswitch2" class="onoffswitch2-checkbox" id="myonoffswitch2" checked="">
                        <label class="onoffswitch2-label" for="myonoffswitch2">
                        <div class="onoffswitch2-inner"></div>
                        </label>
                        </div>';
        return apply_filters('mashsh_onoffswitch2', $output);
    }

    /* Delete all services from array which are not enabled
     * @since 2.0.0
     * @return callback
     */
    function isStatus($var){
        return (!empty($var["status"]));
        }
    
    /* Returns all available networks
     * @since 2.0
     * @returns string
     */
    function getNetworks() {
        
        //mashdebug()->timer('getNetworks');
        global $mashsb_options;
        $output = '';
        $startsecondaryshares = '';
        $endsecondaryshares = '';
        /* var for more services button */
        $onoffswitch = '';
        /* counter for 'Visible Services' */
        $startcounter = 1;
        $maxcounter = $mashsb_options['visible_services']+1; // plus 1 because our array values start counting from zero
        /* our list of available services, includes disabled ones! 
         * We have to clean this array first!
         */
        $getnetworks = $mashsb_options['networks'];
        /* Delete disabled services from array. Use callback function here */
        if (is_array($getnetworks)){
        $enablednetworks = array_filter($getnetworks, 'isStatus');
        }else{
        $enablednetworks = $getnetworks; 
        }
        //var_dump($enablednetworks);
        //echo "max: " . $maxcounter;
    if (!empty($enablednetworks)) {
        foreach ($enablednetworks as $key => $network):
            if($mashsb_options['visible_services'] !== 'all' && $maxcounter != count($enablednetworks) && $mashsb_options['visible_services'] < count($enablednetworks)){
                //if ($startcounter > $maxcounter){$hiddenclass = 'mashsb-hide';} else {$hiddenclass = '';}
                if ($startcounter === $maxcounter ){ 
                    $onoffswitch = onOffSwitch();
                    //$onoffswitch2 = onOffSwitch2();
                    $startsecondaryshares   = '<div class="secondary-shares" style="display:none;">';} else {$onoffswitch = ''; $onoffswitch2 = ''; $startsecondaryshares   = '';}
                if ($startcounter === (count($enablednetworks))){ 
                    //$onoffswitch2 = onOffSwitch2();
                    $endsecondaryshares     = '</div>'; } else { ;$endsecondaryshares = '';}
                    
                //echo " Debug: Startcounter " . $startcounter . " Hello: " . $maxcounter+1 .
                 //" Debug: Enabled services: " . count($enablednetworks) . "<br>"; 
            }
            if ($enablednetworks[$key]['name'] !='') {
                /* replace all spaces with $nbsp; prevents css content: error on text-intend */
                $name = preg_replace('/\040{1,}/','&nbsp;',$enablednetworks[$key]['name']);
            } else {
                $name = ucfirst($enablednetworks[$key]['id']);
            }
            
            $output .= '<a class="mashicon-' . $enablednetworks[$key]['id'] . '" href="javascript:void(0);"><span class="icon"></span><span class="text">' . $name . '</span></a>';
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
        global $wpdb, $mashsb_options, $post, $title, $url;
       
            /* Load hashshag*/       
            if ($mashsb_options['mashsharer_hashtag'] != '') {
                $hashtag = '&via=' . $mashsb_options['mashsharer_hashtag'];
            } else {
                $hashtag = '';
            }
            
            if (!isset($mashsb_options['disable_sharecount'])) {
                    /* Get totalshares of the current page */
                    $totalshares = getSharedcount($url);
                    /* Round total shares when enabled */
                    if (isset($mashsb_options['mashsharer_round'])) {
                        $totalshares = roundshares($totalshares);
                    }  
                 $sharecount = '<div class="mashsb-count"><div class="counts" id="mashsbcount">' . $totalshares . '</div><span class="mashsb-sharetext">' . __('SHARES', 'mashsb') . '</span></div>';    
             } else {
                 $sharecount = '';
             }
                     
                $return = '
                    <aside class="mashsb-container">
                    <div class="mashsb-box">'
                        . apply_filters('mashsb_sharecount_filter', $sharecount) .
                    '<div class="mashsb-buttons">' 
                        . getNetworks() . 
                        //'<a class="mashicon-facebook" href="javascript:void(0);"><span class="icon"></span><span class="text">' . __('Share&nbsp;on&nbsp;Facebook', 'mashsb') . '</span></a><a class="mashicon-twitter" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Tweet&nbsp;on&nbsp;Twitter', 'mashsb') . '</span></a><a class="mashicon-google" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Google+', 'mashsb') . '</span></a>' . mashsb_subscribe_button() .                     
                    '</div></div>
                    <div style="clear:both;"></div>'
                    . mashsb_subscribe_content() .
                    '</aside>
                        <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
            mashdebug()->timer('timer', true);
            return apply_filters( 'mashsb_output_buttons', $return );
    }
    
    /* Shortcode function
     * Select Share count from database and returns share buttons and share counts
     * @since 1.0
     * @returns string
     */
    function mashshareShortcodeShow($atts, $place) {
        global $wpdb ,$mashsb_options, $post;
        $url = get_permalink($post->ID);
        $title = addslashes(the_title_attribute('echo=0'));
        $sharecount = '';

        extract(shortcode_atts(array(
            'cache' => '3600',
            'shares' => 'true',
            'buttons' => 'true',
            'align' => 'left'
                        ), $atts));

            /* Load hashshag*/       
            if ($mashsb_options['mashsharer_hashtag'] != '') {
                $hashtag = '&via=' . $mashsb_options['mashsharer_hashtag'];
            } else {
                $hashtag = '';
            }

            
             if ($shares != 'false') {
                    /* gettotalshares of the current page with sharedcount.com */
                    $totalshares = getSharedcount($url);
                    /* Round total shares when enabled */
                    $roundenabled = isset($mashsb_options['mashsharer_round']) ? $mashsb_options['mashsharer_round'] : null;
                        if ($roundenabled) {
                            $totalshares = roundshares($totalshares);
                        }
                    $sharecount = '<div class="mashsb-count" style="float:' . $align . ';"><div class="counts">' . $totalshares . '</div><span class="mashsb-sharetext">' . __('SHARES', 'mashsb') . '</span></div>';    
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
             
                        
                $return = '
                    <aside class="mashsb-container">
                    <div class="mashsb-box">'
                        . $sharecount .
                    '<div class="mashsb-buttons">' 
                        . getNetworks() . 
                        //'<a class="mashicon-facebook" href="javascript:void(0);"><span class="icon"></span><span class="text">' . __('Share&nbsp;on&nbsp;Facebook', 'mashsb') . '</span></a><a class="mashicon-twitter" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Tweet&nbsp;on&nbsp;Twitter', 'mashsb') . '</span></a><a class="mashicon-google" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Google+', 'mashsb') . '</span></a>' . mashsb_subscribe_button() .                     
                    '</div></div>
                    <div style="clear:both;"></div>'
                    . mashsb_subscribe_content() .
                    '</aside>
                        <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
          if (is_singular() == 1){
            return apply_filters( 'mashsb_output_buttons', $return );  
          }
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
       $enabled_post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : null;
       $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;

       // No scripts on non singular page
       if (!is_singular() == 1) {
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
       if ($enabled_post_types == null or in_array($current_post_type, $enabled_post_types)) {
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
       // Load scripts when shortcode is used
       /* Check if shortcode is used */ 
       if( has_shortcode( $post->post_content, 'mashshare' ) ) {
           mashdebug()->info("400");
            return true;
       } 
       
       // Load scripts when do_action('mashshare') is used
       //if(has_action('mashshare') && mashsb_is_excluded() !== true) {
       if(has_action('mashshare')) {
           mashdebug()->info("action1");
           //return true;    
       }
       
       // Load scripts when do_action('mashsharer') is used
       //if(has_action('mashsharer') && mashsb_is_excluded() !== true) {
       if(has_action('mashsharer')) {
           mashdebug()->info("action2");
           //return true;    
       } 
    }
    


    
    /* Returns Share buttons on specific positions
     * Uses the_content filter
     * @since 1.0
     * @return string
     */
    function mashshare_filter_content($content){
         
        global $atts, $mashsb_options, $url, $title, $post;
        global $wp_current_filter;
        
        /* define some vars here to reduce multiple execution of some basic functions */
        $url = get_permalink($post->ID);
        $title = addslashes(the_title_attribute('echo=0'));
        $position = $mashsb_options['mashsharer_position'];
        $enabled_post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : null;
        $current_post_type = get_post_type();
        $frontpage = isset( $mashsb_options['frontpage'] ) ? $mashsb_options['frontpage'] : null;
        $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;
        
        if (strpos($excluded, ',') !== false) {
             $excluded = explode(',', $excluded);
             if (in_array($post->ID, $excluded)) {
                return $content;
             }  
        }
    
        if ($post->ID == $excluded) {
                return $content;
        }  

        if (!is_singular() == 1) {
            /* disabled to show mashshare on non singualar pages to do: allow mashshare on this pages
               @TODO: Hardcode the share links into php source href instead using only js 
            */
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
                    $content = mashshareShow($atts, '') . $content . mashshareShow($atts, "bottom", $url, $title);
                break;

                case 'before':
                    $content = mashshareShow($atts, '', $url, $title) . $content;
                    
                break;

                case 'after':
                    $content .= mashshareShow($atts, '', $url, $title);
                break;
            }
            return $content;

        }


/* Deprecated: Template function mashsharer()
 * @since 1.0
 * @return string
*/ 
function mashsharer(){
    global $content;
    global $atts;
    global $url;
    global $title;
    global $post;
    $url = get_permalink($post->ID);
    $title = addslashes(the_title_attribute('echo=0'));
    echo mashshareShow($atts, '', $url, $title);
}

/* Template function mashshare() 
 * @since 2.0.0
 * @return string
*/ 
function mashshare(){
    global $content;
    global $atts;
    global $url;
    global $title;
    global $post;
    $url = get_permalink($post->ID);
    $title = addslashes(the_title_attribute('echo=0'));
    echo mashshareShow($atts, '', $url, $title);
}


/**
 * Get Thumbnail image if existed
 *
 * @since 1.0
 * @param int $postID
 * @return void
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
 * @return void
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
    global $mashsb_options;
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
    global $mashsb_options, $post;
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
    /*trailingslashit( plugins_url(). '/mashshare-likeaftershare/templates/'    ) . $file;
    wp_enqueue_style(
		'custom-style',
		plugins_url() . '/mashshare-likeaftershare/templates/'
	);*/
    
    /* VARS */
    $share_color = $mashsb_options['share_color'];
    $custom_css = $mashsb_options['custom_css'];
    
    /* STYLES */
    $mashsb_custom_css = "
        .mashsb-count {
        color: {$share_color};
       
        }"; 
    if ($mashsb_options['border_radius']  != 'default'){
    $mashsb_custom_css .= '
        [class^="mashicon-"], .onoffswitch-label, .onoffswitch2-label {
            border-radius: ' . $mashsb_options['border_radius'] . 'px;
        }';   
    }
    if ($mashsb_options['mash_style']  == 'shadow'){
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
    if ($mashsb_options['mash_style']  == 'gradiant'){
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
    
    $mashsb_custom_css .= $custom_css;
        // ----------- Hook into existed 'mashsb-style' at /templates/mashsb.min.css -----------
        wp_add_inline_style( 'mashsb-styles', $mashsb_custom_css );
}
add_action( 'wp_enqueue_scripts', 'mashsb_styles_method' );
