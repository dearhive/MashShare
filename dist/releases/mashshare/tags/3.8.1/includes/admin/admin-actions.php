<?php
/**
 * Admin Actions
 *
 * @package     MASHSB
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all MASHSB actions sent via POST and GET by looking for the 'mashsb-action'
 * request and running do_action() to call the function
 *
 * @since 1.0
 * @return void
 */
function mashsb_process_actions() {
	if ( isset( $_POST['mashsb-action'] ) ) {
		do_action( 'mashsb_' . $_POST['mashsb-action'], $_POST );
	}

	if ( isset( $_GET['mashsb-action'] ) ) {
		do_action( 'mashsb_' . $_GET['mashsb-action'], $_GET );
	}
}
add_action( 'admin_init', 'mashsb_process_actions' );

/**
 * Arrange order of social network array when it is draged and droped
 * 
 * @global array $mashsb_options
 */
function mashsb_save_order(){
        global $mashsb_options;
        // Get all settings
        
        $current_list = get_option('mashsb_networks');
        $new_order = $_POST['mashsb_list'];
        $new_list = array();
        
        /* First write the sort order */
        foreach ($new_order as $n){
            if (isset($current_list[$n])){
                $new_list[$n] = $current_list[$n];
                
            }
        }
        /* Update sort order of networks */
        update_option('mashsb_networks', $new_list);
        die();
}
add_action ('wp_ajax_mashsb_update_order', 'mashsb_save_order');

/**
 * Purge the MashShare Cache
 * 
 * @global array $post
 * @return bool false
 */
function mashsb_purge_cache(){
    global $post;
    
        
    // Stop WP from update data on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
        return;
    }
    
    if (!isset($post)){
        return;
    }

    
    update_post_meta($post->ID, 'mashsb_timestamp', '');
}
add_action('save_post', 'mashsb_purge_cache' );
