<?php

/**
 * Scripts
 *
 * @package     MASHSB
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

add_action( 'admin_enqueue_scripts', 'mashsb_load_admin_scripts', 100 );
add_action( 'wp_enqueue_scripts', 'mashsb_load_scripts', 10 );
add_action( 'wp_enqueue_scripts', 'mashsb_register_styles', 10 );
add_action( 'wp_enqueue_scripts', 'mashsb_load_inline_styles', 10 );
add_action( 'admin_enqueue_scripts', 'mashsb_load_plugins_admin_scripts', 10 );

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @global $mashsb_options
 * @global $post
 * @return void
 * @param string $hook Page hook
 */
function mashsb_load_scripts( $hook ) {
   
   if (is_admin()){
      return false;
   }
   
   
    global $mashsb_options, $post, $mashsb_sharecount;
    if( !apply_filters( 'mashsb_load_scripts', mashsbGetActiveStatus(), $hook ) ) {
        mashdebug()->info( "mashsb_load_script not active" );
        return;
    }

    //$url = mashsb_get_url();
    //$url = mashsb_get_main_url();
    $url = get_permalink();
    $title = urlencode( html_entity_decode( the_title_attribute( 'echo=0' ), ENT_COMPAT, 'UTF-8' ) );
    $title = str_replace( '#', '%23', $title );
    $titleclean = esc_html( $title );
    $image = "";
    $desc = "";

    if ( isset($post->ID) ){
    $image = mashsb_get_image( $post->ID );
    $desc = mashsb_get_excerpt_by_id( $post->ID );
    }
    // Rest API Not used any longer
    //$restapi = mashsb_allow_rest_api() ? "1" : "0";
    
    /* Load hashshags */
    $hashtag = !empty( $mashsb_options['mashsharer_hashtag'] ) ? $mashsb_options['mashsharer_hashtag'] : '';

    $js_dir = MASHSB_PLUGIN_URL . 'assets/js/';
    // Use minified libraries if Mashshare debug mode is turned off
    $suffix = ( mashsbIsDebugMode() ) ? '' : '.min';

    isset( $mashsb_options['load_scripts_footer'] ) ? $in_footer = true : $in_footer = false;
    
    wp_enqueue_script( 'mashsb', $js_dir . 'mashsb' . $suffix . '.js', array('jquery'), MASHSB_VERSION, $in_footer );
    
    $status = apply_filters('mashsbStatus', false);
        
    $refresh = mashsb_is_async_cache_refresh() ? 1 : 0;
    //$refresh = 0;
    
    wp_localize_script( 'mashsb', 'mashsb', array(
        'shares' => isset($post->ID) ? mashsb_get_total_shares_post_meta($post->ID) + (int)getFakecount() : false,
        'round_shares' => isset( $mashsb_options['mashsharer_round'] ),
        /* Do not animate shares on blog posts. The share count would be wrong there and performance bad */
        'animate_shares' => isset( $mashsb_options['animate_shares'] ) && is_singular() ? 1 : 0,
        'dynamic_buttons' => isset( $mashsb_options['dynamic_button_resize'] ) ? 1 : 0,
        'share_url' => $url,
        'title' => $titleclean,
        'image' => $image,
        'desc' => $desc,
        'hashtag' => $hashtag,
        'subscribe' => !empty( $mashsb_options['subscribe_behavior'] ) && $mashsb_options['subscribe_behavior'] === 'content' ? 'content' : 'link',
        'subscribe_url' => isset( $mashsb_options['subscribe_link'] ) ? $mashsb_options['subscribe_link'] : '',
        'activestatus' => mashsbGetActiveStatus(),
        'singular' => is_singular() ? 1 : 0,
        'twitter_popup' => isset( $mashsb_options['twitter_popup'] ) ? 0 : 1,
        //'restapi' => $restapi
        'refresh' => $refresh,
        'nonce' => wp_create_nonce( "mashsb-nonce" ),
        'postid' => isset($post->ID) && is_singular() ? $post->ID : false,
        'servertime' => time(),
        'ajaxurl' => admin_url('admin-ajax.php')
        ) );
    
}


/**
 * Register CSS Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @global $mashsb_options
 * @return void
 */
function mashsb_register_styles( $hook ) {
    if( !apply_filters( 'mashsb_register_styles', mashsbGetActiveStatus(), $hook ) ) {
        return;
    }
    global $mashsb_options;

    if( isset( $mashsb_options['disable_styles'] ) ) {
        return;
    }

    // Use minified libraries if Mashshare debug mode is turned off
    $suffix = ( mashsbIsDebugMode() ) ? '' : '.min';
    $file = 'mashsb' . $suffix . '.css';

    $url = MASHSB_PLUGIN_URL . 'assets/css/' . $file;
    wp_enqueue_style( 'mashsb-styles', $url, array(), MASHSB_VERSION );
}

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return string custom css into
 */
function mashsb_load_admin_scripts( $hook ) {
    if( !apply_filters( 'mashsb_load_admin_scripts', mashsb_is_admin_page(), $hook ) ) {
        return;
    }
    global $mashsb_options;

    $js_dir = MASHSB_PLUGIN_URL . 'assets/js/';
    $css_dir = MASHSB_PLUGIN_URL . 'assets/css/';

    // Use minified libraries if Mashshare debug mode is turned off
    $suffix = ( mashsbIsDebugMode() ) ? '' : '.min';
    
    wp_enqueue_script( 'mashsb-admin-scripts', $js_dir . 'mashsb-admin' . $suffix . '.js', array('jquery'), MASHSB_VERSION, false );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'media-upload' ); //Provides all the functions needed to upload, validate and give format to files.
    wp_enqueue_script( 'thickbox' ); //Responsible for managing the modal window.
    wp_enqueue_style( 'thickbox' ); //Provides the styles needed for this window.
    wp_enqueue_style( 'mashsb-admin', $css_dir . 'mashsb-admin' . $suffix . '.css', MASHSB_VERSION );
    wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), MASHSB_VERSION );
    wp_enqueue_style( 'jquery-chosen' );

    wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $suffix . '.js', array('jquery'), MASHSB_VERSION );
    wp_enqueue_script( 'jquery-chosen' );
}


/**
 * Load Admin Scripts available on plugins page 
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function mashsb_load_plugins_admin_scripts( $hook ) {
    if( !apply_filters( 'mashsb_load_plugins_admin_scripts', mashsb_is_plugins_page(), $hook ) ) {
        return;
    }

    $js_dir = MASHSB_PLUGIN_URL . 'assets/js/';
    $css_dir = MASHSB_PLUGIN_URL . 'assets/css/';

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( mashsbIsDebugMode() ) ? '' : '.min';

    wp_enqueue_script( 'mashsb-plugins-admin-scripts', $js_dir . 'mashsb-plugins-admin' . $suffix . '.js', array('jquery'), MASHSB_VERSION, false );
    wp_enqueue_style( 'mashsb-plugins-admin', $css_dir . 'mashsb-plugins-admin' . $suffix . '.css', MASHSB_VERSION );   
}

/**
 * Get Share Count Color incl. compatibility mode for earlier version
 * 
 * @global $mashsb_options $mashsb_options
 * @return string
 */
function mashsb_get_share_color(){
    global $mashsb_options;
    // Compatibility mode. Early values were stored including #
    // New values are stored without #
    
    $value = !empty($mashsb_options['share_color']) ? $mashsb_options['share_color'] : '';
    return str_replace('#', '', $value); 
}

/**
 * Add Custom Styles with WP wp_add_inline_style Method
 *
 * @since 1.0
 * 
 * @return string
 */
function mashsb_load_inline_styles() {
    global $mashsb_options;

    /* VARS */
    
    $is_share_color = mashsb_get_share_color();
    $share_color = !empty( $is_share_color ) ? '.mashsb-count {color:#' . $is_share_color . ';}' : '';
    isset( $mashsb_options['custom_css'] ) ? $custom_css = $mashsb_options['custom_css'] : $custom_css = '';
    isset( $mashsb_options['small_buttons'] ) ? $smallbuttons = true : $smallbuttons = false;
    $button_width = isset( $mashsb_options['button_width'] ) ? $mashsb_options['button_width'] : null;

    /* STYLES */
    $mashsb_custom_css = $share_color;
    
    if( !empty( $mashsb_options['border_radius'] ) && $mashsb_options['border_radius'] != 'default' ) {
        $mashsb_custom_css .= '
        [class^="mashicon-"], .onoffswitch-label, .onoffswitch2-label, .onoffswitch {
            border-radius: ' . $mashsb_options['border_radius'] . 'px;
        }';
    }
    if( !empty( $mashsb_options['mash_style'] ) && $mashsb_options['mash_style'] == 'gradiant' ) {
        $mashsb_custom_css .= '.mashsb-buttons a {
        background-image: -webkit-linear-gradient(bottom,rgba(0, 0, 0, 0.17) 0%,rgba(255, 255, 255, 0.17) 100%);
        background-image: -moz-linear-gradient(bottom,rgba(0, 0, 0, 0.17) 0%,rgba(255, 255, 255, 0.17) 100%);
        background-image: linear-gradient(bottom,rgba(0,0,0,.17) 0%,rgba(255,255,255,.17) 100%);}';
    }
    if( $smallbuttons === true ) {
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
        // need this to make sure the min-width value is not overwriting the responsive add-on settings if available
        //if ($button_width && !mashsb_is_active_responsive_addon() ){
        //$mashsb_custom_css .= '.mashsb-buttons a {min-width: ' . $button_width . 'px}';
        //}
        if( $button_width ) {
            $mashsb_custom_css .= '@media only screen and (min-width:568px){.mashsb-buttons a {min-width: ' . $button_width . 'px;}}';
        }
    }

    $mashsb_custom_css .= $custom_css;

    wp_add_inline_style( 'mashsb-styles', $mashsb_custom_css );
}

/*
 * Check if debug mode is enabled
 * 
 * @since 2.2.7
 * @return bool true if Mashshare debug mode is on
 */

function mashsbIsDebugMode() {
    global $mashsb_options;

    $debug_mode = isset( $mashsb_options['debug_mode'] ) ? true : false;
    return $debug_mode;
}

/**
 * Check if responsive add-on is installed and activated
 * 
 * @return true if add-on is installed
 */
function mashsb_is_active_responsive_addon() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if( is_plugin_active( 'mashshare-responsive/mashshare-responsive.php' ) ) {
        return true;
    }
    return false;
}

