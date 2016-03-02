<?php
/**
 * Admin Notices
 *
 * @package     MASHSB
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if at least one social network is enabled
 * 
 * @global array $mashsb_options
 * @return boolean false when no network is enabled
 */
function mashsb_check_active_networks(){
    global $mashsb_options;
    
    $networks = isset($mashsb_options['networks']) ? $mashsb_options['networks'] : false;
    
    if ( isset($networks) && is_array($networks) )
    foreach ($networks as $key => $value){
        if ( isset ($networks[$key]['status']) )
            return true;
    }
    
    return false; 
}


/**
 * Admin Messages
 *
 * @since 2.2.3
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_admin_messages() {
	global $mashsb_options;
        
        if (!current_user_can('update_plugins'))
        return;
        
    if ( mashsb_is_admin_page() && !mashsb_check_active_networks() ) {
        echo '<div class="error">';
        echo '<p>'. sprintf( __('No Social Networks enabled. Go to <a href="%s"> Mashshare->Settings->Social Networks</a> and enable at least one Social Network.','mashsb') , admin_url('admin.php?page=mashsb-settings&tab=networks') ) .'</p>';
        echo '</div>';
    }

    $install_date = get_option('mashsb_installDate');
        $display_date = date('Y-m-d h:i:s');
	$datetime1 = new DateTime($install_date);
	$datetime2 = new DateTime($display_date);
	$diff_intrval = round(($datetime2->format('U') - $datetime1->format('U')) / (60*60*24));
        if($diff_intrval >= 7 && get_option('mashsb_RatingDiv')=="no")
    {
	 echo '<div class="mashsb_fivestar" style="box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
    	<p>Awesome, you\'ve been using <strong>Mashshare Social Sharing</strong> for more than 1 week. <br> May i ask you to give it a <strong>5-star rating</strong> on Wordpress? </br>
        This will help to spread its popularity and to make this plugin a better one.
        <br><br>Your help is much appreciated. Thank you very much,<br> ~René Hermenau
        <ul>
            <li><a href="https://wordpress.org/support/view/plugin-reviews/mashsharer" class="thankyou" target="_new" title="Ok, you deserved it" style="font-weight:bold;">Ok, you deserved it</a></li>
            <li><a href="javascript:void(0);" class="mashsbHideRating" title="I already did" style="font-weight:bold;">I already did</a></li>
            <li><a href="javascript:void(0);" class="mashsbHideRating" title="No, not good enough" style="font-weight:bold;">No, not good enough, i do not like to rate it!</a></li>
        </ul>
    </div>
    <script>
    jQuery( document ).ready(function( $ ) {

    jQuery(\'.mashsbHideRating\').click(function(){
        var data={\'action\':\'hideRating\'}
             jQuery.ajax({
        
        url: "'.admin_url( 'admin-ajax.php' ).'",
        type: "post",
        data: data,
        dataType: "json",
        async: !0,
        success: function(e) {
            if (e=="success") {
               jQuery(\'.mashsb_fivestar\').slideUp(\'slow\');
			   
            }
        }
         });
        })
    
    });
    </script>
    ';
    }
    // Disabled since 2.4.7
    //mashsb_update_notices();
}
add_action( 'admin_notices', 'mashsb_admin_messages' );

/**
 * Return update notices
 * @since 2.4.1
 * @deprecated since 2.4.7
 */
function mashsb_update_notices(){
    if ( get_option('mashsb_update_notice') !='no' ) {
    // admin notice after updating Mashshare
    echo '<div class="mashsb_update_notice update-nag">' . __('Mashshare notice: If you are using the php shortcode function <strong>do_shortcode[\'mashshare\'] </strong>and Mashshare styles are not loaded, enable the option <strong><a href="'.admin_url( 'options-general.php?page=mashsb-settings&tab=visual#mashsb_settingslocation_header' ).'">Load JS and CSS all over</a></strong> in Mashshare->settings->Visual->Location & Position', 'mashsb') . 
            '<p><a href="javascript:void(0);" class="mashsb_hide_update" title="I understand" style="text-decoration:none;">I understand! <br>Do not show again this notice</a>'
            . '</div>'
            . '<script>
    jQuery( document ).ready(function( $ ) {
        jQuery(\'.mashsb_hide_update\').click(function(){
            var data={\'action\':\'hide_update\'}
            jQuery.ajax({
                url: "'.admin_url( 'admin-ajax.php' ).'",
                type: "post",
                data: data,
                dataType: "json",
                async: !0,
                success: function(e) {
                    if (e=="success") {
                       jQuery(\'.mashsb_update_notice\').slideUp(\'slow\');	   
                    }
                }
            });
        })
    
    });
    </script>';
    }
}

/* Hide the rating div
 * 
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2.3
 * 
 * @return json string
 * 
 */

function mashsb_HideRatingDiv(){
    update_option('mashsb_RatingDiv','yes');
    echo json_encode(array("success")); exit;
}
add_action('wp_ajax_hideRating','mashsb_HideRatingDiv');

/* Hide the update notice div
 * 
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2015, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4.0
 * 
 * @return json string
 * 
 */
function mashsb_hide_update_div(){
    update_option('mashsb_update_notice','yes');
    echo json_encode(array("success")); exit;
}
add_action('wp_ajax_hide_update','mashsb_hide_update_div');

/**
 * Admin Add-ons Notices
 *
 * @since 1.0
 * @return void
*/
function mashsb_admin_addons_notices() {
	add_settings_error( 'mashsb-notices', 'mashsb-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'mashsb' ), 'error' );
	settings_errors( 'mashsb-notices' );
}

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since 1.8
 * @return void
*/
function mashsb_dismiss_notices() {

	$notice = isset( $_GET['mashsb_notice'] ) ? $_GET['mashsb_notice'] : false;
	if( ! $notice )
		return; // No notice, so get out of here

	update_user_meta( get_current_user_id(), '_mashsb_' . $notice . '_dismissed', 1 );
      
	wp_redirect( esc_url(remove_query_arg( array( 'mashsb_action', 'mashsb_notice' ) ) ) ); exit;

}
add_action( 'mashsb_dismiss_notices', 'mashsb_dismiss_notices' );

/*
 * Show big colored update information below the official update notification in /wp-admin/plugins
 * @since 2.0.8
 * @return void
 * 
 */

function mashsb_in_plugin_update_message( $args ) {
    $transient_name = 'mashsb_upgrade_notice_' . $args['Version'];

    if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

      $response = wp_remote_get( 'https://plugins.svn.wordpress.org/mashsharer/trunk/readme.txt' );

      if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {

        // Output Upgrade Notice
        $matches        = null;
        $regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( MASHSB_VERSION ) . '\s*=|$)~Uis';
        $upgrade_notice = '';

        if ( preg_match( $regexp, $response['body'], $matches ) ) {
          $version        = trim( $matches[1] );
          $notices        = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );
          
          if ( version_compare( MASHSB_VERSION, $version, '<' ) ) {

            $upgrade_notice .= '<div class="mashsb_plugin_upgrade_notice" style="padding:10px;background-color:#58C1FF;color: #FFF;">';

            foreach ( $notices as $index => $line ) {
              $upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}" style="text-decoration:underline;color:#ffffff;">${1}</a>', $line ) );
            }

            $upgrade_notice .= '</div> ';
          }
        }

        set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
      }
    }

    echo wp_kses_post( $upgrade_notice );
  }
 add_action ( 'in_plugin_update_message-mashsharer/mashshare.php', 'mashsb_in_plugin_update_message'  );