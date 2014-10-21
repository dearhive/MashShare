<?php
/**
 * Admin Plugins
 *
 * @package     MASHSB
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Plugins row action links
 *
 * @author Michael Cannon <mc@aihr.us>
 * @since 2.0
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function mashsb_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'options-general.php?page=mashsb-settings' ) . '">' . esc_html__( 'General Settings', 'mashsb' ) . '</a>';
	if ( $file == 'mashsharer/mashshare.php' )
		array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'mashsb_plugin_action_links', 10, 2 );


/**
 * Plugin row meta links
 *
 * @author Michael Cannon <mc@aihr.us>
 * @since 2.0
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function mashsb_plugin_row_meta( $input, $file ) {
	if ( $file != 'mashsharer/mashshare.php' )
		return $input;

	$links = array(
		'<a href="' . admin_url( 'options-general.php?page=mashsb-settings' ) . '">' . esc_html__( 'Getting Started', 'mashsb' ) . '</a>',
		'<a href="https://www.mashshare.net/downloads/">' . esc_html__( 'Add Ons', 'mashsb' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'mashsb_plugin_row_meta', 10, 2 );