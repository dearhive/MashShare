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
 