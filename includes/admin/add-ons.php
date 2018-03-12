<?php
/**
 * Admin Add-ons
 *
 * @package     MASHSB
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2014, Rene Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons
 *
 * Renders the add-ons content.
 *
 * @since 1.1.8
 * @return void
 */
function mashsb_add_ons_page() {
	ob_start(); ?>
	<div class="wrap" id="mashsb-add-ons">
		<h2>
			<?php _e( 'Add Ons for Mashshare', 'mashsb' ); ?>
			&nbsp;&mdash;&nbsp;<a href="https://www.mashshare.net" class="button-primary" title="<?php _e( 'Visit Website', 'mashsb' ); ?>" target="_blank" rel="noopener"><?php _e( 'See Details', 'mashsb' ); ?></a>
		</h2>
		<p><?php _e( 'These add-ons extend the functionality of MashShare.', 'mashsb' ); ?></p>
		<?php echo mashsb_add_ons_get_feed(); ?>
	</div>
	<?php
	echo ob_get_clean();
}

/**
 * Add-ons Get Feed
 *
 * Gets the add-ons page feed.
 *
 * @since 1.1.8
 * @return void
 */
function mashsb_add_ons_get_feed() {
	if ( false === ( $cache = get_transient( 'mashshare_add_ons_feed' ) ) ) {
		$feed = wp_remote_get( 'https://www.mashshare.net/?feed=addons', array( 'sslverify' => false ) );
		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'mashshare_add_ons_feed', $cache, 3600 );
			}
		} else {
			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the Mashshare addon list from the server. Please try again later.', 'mashsb' ) . '
                                   <br>Visit instead the Mashshare Addon Website <a href="https://www.mashshare.net" class="button-primary" title="Mashshare Add ons" target="_blank" rel="noopener"> Get Add-Ons  </a></div>';
		}
	}
	return $cache;
}