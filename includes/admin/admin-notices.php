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
if( !defined( 'ABSPATH' ) )
    exit;

/**
 * Check if at least one social network is enabled
 * 
 * @global array $mashsb_options
 * @return boolean false when no network is enabled
 */
function mashsb_check_active_networks() {
    global $mashsb_options;

    $networks = isset( $mashsb_options['networks'] ) ? $mashsb_options['networks'] : false;

    if( isset( $networks ) && is_array( $networks ) )
        foreach ( $networks as $key => $value ) {
            if( isset( $networks[$key]['status'] ) )
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

    if( !current_user_can( 'update_plugins' ) ){
        return;
    }
    
    mashsb_show_update_notice_gdpr();
    
    mashsb_show_new_fb_api();
    
    // Rate Limit warning
//    if( mashsb_is_admin_page() && mashsb_rate_limit_exceeded() ) {
//        echo '<div class="error">';
//        echo '<p>' . sprintf(__('Your website exceeded the Facebook rate limit. Share count requests to Facebook and other networks will be delayed for 60min and the Share Count will not grow during this time. If you get this notice often consider to change <strong>MashShare Caching Method</strong> to <a href="%s">Refresh while Loading</a> and use a higher cache expiration. MashShare tries again to get shares in ' . mashsbGetRemainingRateLimitTime() , 'mashsb'), admin_url() . 'admin.php?page=mashsb-settings#mashsb_settingsgeneral_header', admin_url() . 'admin.php?page=mashsb-settings#mashsb_settingsservices_header') . '</p>';
//        echo '</div>';
//    }
    // Access Token expired
//    if( mashsb_is_invalid_fb_api_key() ) {
//        echo '<div class="error">';
//        echo '<p>' . sprintf(__('<strong>Error: </strong>'.mashsb_is_invalid_fb_api_key().' <br> Your <strong>Facebook Access Token</strong> has been expired or is invalid. Remove the invalid access token from <a href="%s">MashShare->Settings->Networks</a> or generate a new one. Your MashShare Facebook Shares will not be refreshed any longer. <a href="%s" target="_blank">Read here</a> how to renew the Facebook access token. Fix it and press the button: | <a href="%s" class="button">CHECK AGAIN</a>', 'mashsb'), admin_url() . 'admin.php?page=mashsb-settings#mashsb_settingsservices_header', 'http://docs.mashshare.net/article/132-how-to-create-a-facebook-access-token', admin_url() . 'admin.php?mashsb_action=check_access_token&page=mashsb-settings') . '</p>';
//        echo '</div>';
//    }
    
    // Cache warning
    if( mashsb_is_deactivated_cache() ) {
        echo '<div class="error">';
        echo '<p>' . sprintf(__('Attention: The Mashshare Cache is deactivated. <a href="%s">Activate it</a> or share count requests to social networks will be rate limited.', 'mashsb'), admin_url() . 'admin.php?page=mashsb-settings#mashsb_settingsdebug_header') . '</p>';
        echo '</div>';
    }
    // Cache warning
    if( mashsb_is_deleted_cache() ) {
        echo '<div class="error">';
        echo '<p>' . sprintf(__('Attention: The Mashshare Cache is permanetely purged. <a href="%s">Fix this</a> or share count requests to social networks will be rate limited.', 'mashsb'), admin_url() . 'admin.php?page=mashsb-settings#mashsb_settingsdebug_header') . '</p>';
        echo '</div>';
    }
    
    //mashsb_update_notice_101();
    
    if( mashsb_is_admin_page() && !mashsb_curl_installed() ) {
        echo '<div class="error">';
        echo '<p>' . sprintf(__('MashShare needs the PHP extension cURL and curl_multi_init() which is not installed on your server. Please <a href="%s" target="_blank" rel="noopener">install and activate</a> it to be able to collect share count of your posts.', 'mashsb'), 'https://www.google.com/search?btnG=1&pws=0&q=enable+curl+on+php') . '</p>';
        echo '</div>';
    }

    // notice no Networks enabled    
    if( mashsb_is_admin_page() && !mashsb_check_active_networks() ) {
        echo '<div class="error">';
        echo '<p>' . sprintf( __( 'No Social Networks enabled. Go to <a href="%s"> Mashshare->Settings->Social Networks</a> and enable at least one Social Network.', 'mashsb' ), admin_url( 'admin.php?page=mashsb-settings&tab=networks#mashsb_settingsservices_header' ) ) . '</p>';
        echo '</div>';
    }
    // Share bar add-on notice    
    if( mashsb_is_admin_page() && mashsb_incorrect_sharebar_version() ) { 
        echo '<div class="error">';
        echo '<p>' . sprintf( __( 'Your Sharebar Add-On version is not using new short url mechanism of MashShare 3.X. Please <a href="%s" target="blank"> update the Sharebar Add-On</a> to at least version 1.2.5. if you want to make sure that twitter short urls will not stop working in one of the next updates. This requires a valid license of the Sharebar Add-On', 'mashsb' ), 'https://www.mashshare.net/downloads/sticky-sharebar/?utm_source=insideplugin&utm_medium=userwebsite&utm_content=update_sharebar&utm_campaign=freeplugin' ) . '</p>';
        echo '</div>';
    }
    // Floating Sidebar add-on notice    
    if( mashsb_is_admin_page() && mashsb_incorrect_sidebar_version() ) {
        echo '<div class="error">';
        echo '<p>' . sprintf( __( 'Your Floating Sidebar Add-On version is not using new short url mechanism of MashShare 3.X. Please <a href="%s" target="blank"> update the Floating Sidebar Add-On</a> to at least version 1.2.6. if you want to make sure that twitter short urls will not stop working in one of the next updates. This requires a valid license of the Floating Sidebar Add-On', 'mashsb' ), 'https://www.mashshare.net/downloads/floating-sidebar/?utm_source=insideplugin&utm_medium=userwebsite&utm_content=update_sharebar&utm_campaign=freeplugin' ) . '</p>';
        echo '</div>';
    }
    // Check google API key  
//    if( mashsb_is_admin_page() && ( mashsb_check_google_apikey() && isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'google' ) ) {
//        echo '<div class="error">';
//        echo '<p>' . sprintf( __( 'Google API key is invalid. Go to <a href="%s"><i>Mashshare->Settings->Short URL Integration</i></a> and check the Google API key.', 'mashsb' ), admin_url( 'admin.php?page=mashsb-settings#mashsb_settingsshorturl_header' ) ) . '</p>';
//        echo '</div>';
//    }
    // Check Bitly API key  
    if( mashsb_is_admin_page() && (false === mashsb_check_bitly_apikey() && isset( $mashsb_options['mashsu_methods'] ) && $mashsb_options['mashsu_methods'] === 'bitly' ) ) {
        echo '<div class="error">';
        echo '<p>' . sprintf( __( 'Bitly Access Token is invalid or bitly.com endpoint can not be reached. Go to <a href="%s"><i>Mashshare->Settings->Short URL Integration</i></a> and check the Bitly API key.', 'mashsb' ), admin_url( 'admin.php?page=mashsb-settings#mashsb_settingsshorturl_header' ) ) . '</p>';
        echo '</div>';
    }
    // Notice MashShare Open Graph Add-On installed and activated
    if( class_exists( 'MashshareOpenGraph' ) ) {
        echo '<div class="error">';
        echo '<p>' . sprintf( __( '<strong>Important:</strong> Deactivate the MashShare Open Graph Add-On. It is not longer needed and having it activated leads to duplicate open graph tags on your site. Go to <a href="%s"> Plugin Settings</a> ', 'mashsb' ), admin_url( 'plugins.php' ) ) . '</p>';
        echo '</div>';
    }
    // Notice MashShare ShortURL Add-On installed and activated
    if( class_exists( 'MashshareShorturls' ) ) {
        echo '<div class="error">';
        echo '<p>' . sprintf( __( '<strong>Important:</strong> Deactivate the MashShare Shorturls Add-On. It is not longer needed and already built in MashShare. Deactivate it from <a href="%s"> Plugin Settings</a> ', 'mashsb' ), admin_url( 'plugins.php' ) ) . '</p>';
        echo '</div>';
    }
    // Share count is deactivated when permalinks are not used
    if( mashsb_is_admin_page() && !mashsb_is_enabled_permalinks() ) {
        echo '<div class="error">';
        echo '<p>' . sprintf( __( '<strong>No Share Count aggregation possible!</strong> <a href="%s">Permalinks</a> must be enabled to count shares. Share count is deactivated until you have fixed this.', 'mashsb' ), admin_url( 'options-permalink.php' ) ) . '</p>';
        echo '</div>';
    }
    
    // Show save notice
    if( isset( $_GET['mashsb-message'] ) ) {
        switch ( $_GET['mashsb-message'] ) {
            case 'settings-imported' :
                echo '<div class="updated">';
                echo '<p>' . __( 'The settings have been imported', 'mashsb' ) . '</p>';
                echo '</div>';
                break;
        }
    }


    // Please rate us
    $install_date = get_option( 'mashsb_installDate' );
    $display_date = date( 'Y-m-d h:i:s' );
    $datetime1 = new DateTime( $install_date );
    $datetime2 = new DateTime( $display_date );
    $diff_intrval = round( ($datetime2->format( 'U' ) - $datetime1->format( 'U' )) / (60 * 60 * 24) );
    if( $diff_intrval >= 7 && get_option( 'mashsb_RatingDiv' ) == "no" ) {
        echo '<div class="mashsb_fivestar update-nag" style="box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
    	<p>Awesome, you\'ve been using <strong>Mashshare Social Sharing Plugin</strong> for more than 1 week. <br> May i ask you to give it a <strong>5-star rating</strong> on Wordpress? </br>
        This will help to spread its popularity and to make this plugin a better one.
        <br><br>Your help is much appreciated. Thank you very much,<br> ~René Hermenau
        <ul>
            <li class="float:left"><a href="https://wordpress.org/support/plugin/mashsharer/reviews/?filter=5#new-post" class="thankyou button button-primary" target="_new" title=Yes, MashShare Increased My Shares" style="color: #ffffff;-webkit-box-shadow: 0 1px 0 #256e34;box-shadow: 0 1px 0 #256e34;font-weight: normal;float:left;margin-right:10px;">I Like MashShare - It Increased My Shares</a></li>
            <li><a href="javascript:void(0);" class="mashsbHideRating button" title="I already did" style="">I already rated it</a></li>
            <li><a href="javascript:void(0);" class="mashsbHideRating" title="No, not good enough" style="">No, not good enough, i do not like to rate it!</a></li>
        </ul>
    </div>
    <script>
    jQuery( document ).ready(function( $ ) {

    jQuery(\'.mashsbHideRating\').click(function(){
        var data={\'action\':\'hideRating\'}
             jQuery.ajax({
        
        url: "' . admin_url( 'admin-ajax.php' ) . '",
        type: "post",
        data: data,
        dataType: "json",
        async: !0,
        success: function(e) {
            if (e=="success") {
               jQuery(\'.mashsb_fivestar\').slideUp(\'fast\');
			   
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
 * Check if sharebar add-on version is fully supported
 * 
 * @return boolean true if incorrect
 */
function mashsb_incorrect_sharebar_version() {
    if( defined( 'MASHBAR_VERSION' ) ) {
        return version_compare( MASHBAR_VERSION, '1.2.5', '<' );
    } else {
        return false;
    }
}
/**
 * Check if sharebar add-on version is fully supported
 * 
 * @return boolean true if incorrect
 */
function mashsb_incorrect_sidebar_version() {
    if( defined( 'MASHFS_VERSION' ) ) {
        return version_compare(MASHFS_VERSION, '1.1.6', '<');
    } else {
        return false;
    }
}

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

function mashsb_hide_update_notice() {
    if( !empty( $_POST['action'] ) && $_POST['action'] === 'mashsb_hide_notice' && !empty( $_POST['id'] ) ) {
        //echo $_POST['action'] . '_' . $_POST['id'];
        update_option( 'mashsb_update_notice_' . $_POST['id'], 'no' );
        $result = array('success');
        echo json_encode( $result );
        exit;
    }
}

add_action( 'wp_ajax_mashsb_hide_notice', 'mashsb_hide_update_notice' );

/**
 * Return update notice 101
 * @since 3.2.0
 */
function mashsb_update_notice_101() {
    
        if( !mashsb_is_admin_page() ) {
            return false;
        }
    
    $notice_id = '101'; //unique id of our notice
    $message = sprintf(__( 'Admin notices are pain but read this one or you will miss how to fix your facebook share counts in MashShare: <p><strong style="font-weight:bold;">Go to <a href="%1s">Settings->Networks</a> and request your access token via facebook login - That\'s all. '
            . '<a href="#" id="mashsb_notice_101_resp"> Whats also new? </a> </strong>'
                . '<div style="display:none;" id="mashsb_notice_101_more">'
                . '<ul style="font-weight:600;">'
                . '<li>- Full Width Responsive Buttons (Enable them from <a href="%2s">Visual Setting</a>)<li>'
                . '<li>- Most Shared Posts Widget incl. Thumbnails</li>'
                . '<li>- Cumulate Http(s) Shares - Move your site to ssl without loosing shares</li>'
                . '</div>'
            , 'mashsb' ), 
            admin_url() . 'admin.php?page=mashsb-settings#mashsb_settingsservices_header',
            admin_url() . 'admin.php?page=mashsb-settings#mashsb_settingsstyle_header'
            );
      
        if( get_option( 'mashsb_update_notice_' . $notice_id ) === 'yes' ) {
  
        // admin notice after updating Mashshare
        echo '<div class="mashsb_update_notice_'. $notice_id .' update-nag">' . $message . 
        '<p><a href="javascript:void(0);" class="mashsb_hide_'. $notice_id .'" title="I got it" style="text-decoration:none;">- Ok, Do Not Show Again</a></a>'
        . '</div>'
        . '<script>
    jQuery( document ).ready(function( $ ) {
        jQuery(\'.mashsb_hide_'. $notice_id .'\').click(function(){
            var data={
            \'action\':\'mashsb_hide_notice\',
            \'id\':\'101\',
            }
            jQuery.ajax({
                url: "' . admin_url( 'admin-ajax.php' ) . '",
                type: "post",
                data: data,
                dataType: "json",
                async: !0,
                success: function(e) {
                    if (e=="success") {
                       jQuery(\'.mashsb_update_notice_'. $notice_id .'\').hide();	   
                    }
                }
            });
        })
        jQuery(\'#mashsb_notice_101_resp\').click(function(e){
        e.preventDefault();
            jQuery(\'#mashsb_notice_101_more\').show()
        });
        
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

function mashsb_HideRatingDiv() {
    update_option( 'mashsb_RatingDiv', 'yes' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_hideRating', 'mashsb_HideRatingDiv' );

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
    if( !$notice )
        return; // No notice, so get out of here

    update_user_meta( get_current_user_id(), '_mashsb_' . $notice . '_dismissed', 1 );

    wp_redirect( esc_url( remove_query_arg( array('mashsb_action', 'mashsb_notice') ) ) );
    exit;
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

    if( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

        $response = wp_remote_get( 'https://plugins.svn.wordpress.org/mashsharer/trunk/readme.txt' );

        if( !is_wp_error( $response ) && !empty( $response['body'] ) ) {

            // Output Upgrade Notice
            $matches = null;
            $regexp = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( MASHSB_VERSION ) . '\s*=|$)~Uis';
            $upgrade_notice = '';

            if( preg_match( $regexp, $response['body'], $matches ) ) {
                $version = trim( $matches[1] );
                $notices = ( array ) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

                if( version_compare( MASHSB_VERSION, $version, '<' ) ) {

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

add_action( 'in_plugin_update_message-mashsharer/mashshare.php', 'mashsb_in_plugin_update_message' );

/**
 * Get remaining time in seconds of the rate limit transient
 * @return type
 */
function mashsbGetRemainingRateLimitTime() {
    $trans_time = get_transient( 'timeout_mashsb_rate_limit' );

    if( false !== $trans_time ) {
        $rest = abs(time() - $trans_time);
        
        if ($rest < 60){
            return $rest . ' seconds.';
        } else {
            $minutes = floor($rest / 60) . ' minutes.';
            return $minutes;
        }
    }
    return 0 . 'seconds';
}

/**
 * Get the status of the FB api key
 * @global array $mashsb_options
 * @return mixed boolean | string false if fb api key is valid. String if api key is invalid
 */
function mashsb_is_invalid_fb_api_key(){
    global $mashsb_options;
    
    if (empty($mashsb_options['fb_access_token_new'])){
        return false;
    }
    
    $status = get_option('mashsb_valid_fb_api_key');
    if (false === $status || 'success' === $status){
        return false;
    } else {
        return $status;
    }
} 


/**
 * Return update notice for gdpr compliance
 * @since 3.5.3.0
 */
function mashsb_show_update_notice_gdpr() {
    
    
    $message = sprintf(__( '<h2 style="color:white;">MashShare GDPR Compliance</h2>'
            . 'MashShare uses new sharedcount.com integration to be GDPR compliant. <br>Activate sharedcount.com at <a href="'.admin_url().'admin.php?page=mashsb-settings#mashsb_settingsgeneral_header" style="color:white;">MashShare > Settings > General > Share Count</a><br><br>For collecting Twitter shares get the <a href="https://mashshare.net/downloads/mashshare-social-networks-addon/?utm_source=wp-admin&utm_medium=gdpr-notice&utm_campaign=gdpr-notice" target="_blank">Social Network Add-On</a>'
            , 'mashsb' ), 
            admin_url() . 'admin.php?page=mashsb-settings'
            );
      
        if( get_option( 'mashsb_show_update_notice_gdpr1' ) === 'no' ) {
           return false;
        }
  
        // admin notice after updating Mashshare
        echo '<div class="mashsb-notice-gdpr mashsb_update_notice_gdpr update-nag" style="background-color: #00abed;color: white;padding: 20px;margin-top: 20px;border: 3px solid white;">' . $message . 
        '<p><a href="'.admin_url().'admin.php?page=mashsb-settings&mashsb-action=hide_gdpr_notice" class="mashsb_hide_gdpr" title="I got it" style="text-decoration:none;color:white;">- I Understand! Do Not Show This Message Again -</a></a>'.
            '</div>';
       
    
}

/**
 * Hide GDPR notice
 * 
 * @global array $mashsb_options
 */
function mashsb_hide_gdpr_notice(){
        global $mashsb_options;
        // Get all settings
        update_option( 'mashsb_show_update_notice_gdpr1', 'no' );
}
add_action ('mashsb_hide_gdpr_notice', 'mashsb_hide_gdpr_notice');



/**
 * Show notice for new FB API
 * @since 3.5.3.0
 */
function mashsb_show_new_fb_api() {
    
    
    $message = sprintf(__( '<h2 style="color:white;">MashShare: Facebook API Changes</h2>'
            . 'Facebook shut down its old API endpoint so you need to switch over to sharedcount.com integration to get the latest Facebook share count. <br>Add sharedcount.com API key at <a href="'.admin_url().'admin.php?page=mashsb-settings#mashsb_settingsgeneral_header" style="color:white;">MashShare > Settings > General > Share Count</a><br>'
            , 'mashsb' ), 
            admin_url() . 'admin.php?page=mashsb-settings'
            );
      
        if( get_option( 'mashsb_show_new_fb_api' ) === 'no' ) {
           return false;
        }
  
        // admin notice after updating Mashshare
        echo '<div class="mashsb-notice-gdpr mashsb_update_notice_gdpr update-nag" style="background-color: red;color: white;padding: 20px;margin-top: 20px;border: 3px solid white;">' . $message . 
        '<p><a href="'.admin_url().'admin.php?page=mashsb-settings&mashsb-action=hide_fb_api_notice" class="mashsb_hide_fb_api" title="I got it" style="text-decoration:none;color:white;">- I Understand! Do Not Show This Message Again -</a></a>'.
            '</div>';
       
    
}

/**
 * Hide FB API notice
 * 
 * @global array $mashsb_options
 */
function mashsb_hide_fb_api_notice(){
        global $mashsb_options;
        // Get all settings
        update_option( 'mashsb_show_new_fb_api', 'no' );
}
add_action ('mashsb_hide_fb_api_notice', 'mashsb_hide_fb_api_notice');


