<?php
/**
 * Admin Dashboard
 *
 * @package     MASHSB
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2016, Rene Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add new columns in posts dashboard
 * 
 * @return string
 */
function mashsb_create_share_columns($col) {
	$col['mashsb_shares'] = 'Share Count';
	return $col;
}
add_filter('manage_posts_columns', 'mashsb_create_share_columns');

/**
 * Get share count in post columns
 * 
 * @param array $col
 * @param int $post_id
 * @retrun int
 */
function mashsb_get_shares($col, $post_id) {
	if ($col == 'mashsb_shares') {
		$shares = get_post_meta($post_id,'mashsb_shares',true);
		echo (int)$shares;
	}
}
add_action('manage_posts_custom_column', 'mashsb_get_shares', 10, 2);
/**
 * Make share count columns sortable
 * 
 * @param array $col
 * @return string
 */
// Make the column Sortable
function mashsb_share_column_sortable( $col ) {
	$col['mashsb_shares'] = 'Share Count';
	return $col;
}
add_filter('manage_edit-post_sortable_columns', 'mashsb_share_column_sortable');


/**
 * Change columns get_posts() query
 * 
 * @param type $query
 * @return void
 */
function mashsb_sort_shares_by( $query ) {
    if( ! is_admin() ){
        return false;
    }
 
    $orderby = $query->get( 'orderby');
 
    if( 'Share Count' == $orderby ) {
        $query->set('meta_key','mashsb_shares');
        $query->set('orderby','meta_value_num');
    }
}
add_action( 'pre_get_posts', 'mashsb_sort_shares_by' );


