<?php
/**
 * Front-end Actions
 *
 * @package     MASHSB
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hooks MASHSB actions, when present in the $_GET superglobal. Every mashsb_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
function mashsb_get_actions() {
	if ( isset( $_GET['mashsb_action'] ) ) {
		do_action( 'mashsb_' . $_GET['mashsb_action'], $_GET );
	}
}
add_action( 'init', 'mashsb_get_actions' );

/**
 * Hooks MASHSB actions, when present in the $_POST superglobal. Every mashsb_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
function mashsb_post_actions() {
	if ( isset( $_POST['mashsb_action'] ) ) {
		do_action( 'mashsb_' . $_POST['mashsb_action'], $_POST );
	}
}
add_action( 'init', 'mashsb_post_actions' );

/**
 * Force cache refresh via GET REQUEST
 * 
 * @global array $mashsb_options
 * @return boolean true for cache refresh
 */
function mashsb_force_cache_refresh() {
    global $mashsb_options;
    
    // Needed for testing (phpunit)
    if (MASHSB_DEBUG || isset( $mashsb_options['disable_cache'] ) ){
        mashsb()->logger->info('mashsb_force_cache_refresh() -> Debug mode enabled');
        return true;
    }
    
    $caching_method = !empty($mashsb_options['caching_method']) ? $mashsb_options['caching_method'] : 'refresh_loading';
    
    // Old method and less performant - Cache is rebuild during pageload
    if($caching_method == 'refresh_loading'){
        if (mashsb_is_cache_refresh()){
            return true;
        }
    }
    
    // New method - Cache will be rebuild after complete pageloading and will be initiated via ajax.
    if( isset( $_GET['mashsb-refresh'] ) && $caching_method == 'async_cache' ) {
        MASHSB()->logger->info('Force Cache Refresh');
        return true;
    }
    
    return false;
}
//add_action( 'init', 'mashsb_force_cache_refresh' );
add_action( 'wp_ajax_mashsb_force_cache_refresh', 'mashsb_force_cache_refresh' );
add_action( 'wp_ajax_nopriv_mashsb_force_cache_refresh', 'mashsb_force_cache_refresh' );