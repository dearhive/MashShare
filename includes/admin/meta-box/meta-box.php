<?php

/**
 * META BOX Functions
 *
 * @package     MASHSB
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
*/

if ( defined( 'ABSPATH' ) && ! class_exists( 'MASHSB_RWMB_Loader' ) )
{
	require plugin_dir_path( __FILE__ ) . 'inc/loader.php';
	new MASHSB_RWMB_Loader;
}
