<?php
/**
 * Feedback functions
 *
 * @package     MASHSB
 * @subpackage  Admin/Feedback
 * @copyright   Copyright (c) 2017, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.7
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper method to check if user is in the plugins page.
 *
 * @author René Hermenau
 * @since  3.3.7
 *
 * @return bool
 */
function mashsb_is_plugins_page() {
    global $pagenow;

    return ( 'plugins.php' === $pagenow );
}

/**
 * display deactivation logic on plugins page
 * 
 * @since 3.3.7
 */
function mashsb_add_deactivation_feedback_modal() {

    $screen = get_current_screen();
    if( !is_admin() && !mashsb_is_plugins_page()) {
        return;
    }

    $current_user = wp_get_current_user();
    if( !($current_user instanceof WP_User) ) {
        $email = '';
    } else {
        $email = trim( $current_user->user_email );
    }

    include MASHSB_PLUGIN_DIR . 'includes/admin/views/deactivate-feedback.php';
}

/**
 * send feedback via email
 * 
 * @since 1.4.0
 */
function mashsb_send_feedback() {

    if( isset( $_POST['data'] ) ) {
        parse_str( $_POST['data'], $form );
    }

    $text = '';
    if( isset( $form['mashsb_disable_text'] ) ) {
        $text = implode( "\n\r", $form['mashsb_disable_text'] );
    }

    $headers = array();

    $from = isset( $form['mashsb_disable_from'] ) ? $form['mashsb_disable_from'] : '';
    if( $from ) {
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
    }

    $subject = isset( $form['mashsb_disable_reason'] ) ? $form['mashsb_disable_reason'] : '(no reason given)';

    $success = wp_mail( 'makebetter@mashshare.net', $subject, $text, $headers );

    //error_log(print_r($success, true));
    //error_log($from . $subject . var_dump($form));
    die();
}
add_action( 'wp_ajax_mashsb_send_feedback', 'mashsb_send_feedback' );

