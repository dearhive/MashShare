<?php
/**
 * Register Settings
 *
 * @package     MASHSB
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @return mixed
 */
function mashsb_get_option( $key = '', $default = false ) {
    global $mashsb_options;
    $value = !empty( $mashsb_options[$key] ) ? $mashsb_options[$key] : $default;
    $value = apply_filters( 'mashsb_get_option', $value, $key, $default );
    return apply_filters( 'mashsb_get_option_' . $key, $value, $key, $default );
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array MASHSB settings
 */
function mashsb_get_settings() {
    $settings = get_option( 'mashsb_settings' );


    if( empty( $settings ) ) {
        // Update old settings with new single option

        $general_settings = is_array( get_option( 'mashsb_settings_general' ) ) ? get_option( 'mashsb_settings_general' ) : array();
        $visual_settings = is_array( get_option( 'mashsb_settings_visual' ) ) ? get_option( 'mashsb_settings_visual' ) : array();
        $networks = is_array( get_option( 'mashsb_settings_networks' ) ) ? get_option( 'mashsb_settings_networks' ) : array();
        $ext_settings = is_array( get_option( 'mashsb_settings_extensions' ) ) ? get_option( 'mashsb_settings_extensions' ) : array();
        $license_settings = is_array( get_option( 'mashsb_settings_licenses' ) ) ? get_option( 'mashsb_settings_licenses' ) : array();
        $addons_settings = is_array( get_option( 'mashsb_settings_addons' ) ) ? get_option( 'mashsb_settings_addons' ) : array();

        $settings = array_merge( $general_settings, $visual_settings, $networks, $ext_settings, $license_settings, $addons_settings );

        update_option( 'mashsb_settings', $settings );
    }
    return apply_filters( 'mashsb_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
 */
function mashsb_register_settings() {

    if( false == get_option( 'mashsb_settings' ) ) {
        add_option( 'mashsb_settings' );
    }

    foreach ( mashsb_get_registered_settings() as $tab => $settings ) {

        add_settings_section(
            'mashsb_settings_' . $tab, __return_null(), '__return_false', 'mashsb_settings_' . $tab
        );

        foreach ( $settings as $option ) {

            $name = isset( $option['name'] ) ? $option['name'] : '';

            add_settings_field(
                'mashsb_settings[' . $option['id'] . ']', $name, function_exists( 'mashsb_' . $option['type'] . '_callback' ) ? 'mashsb_' . $option['type'] . '_callback' : 'mashsb_missing_callback', 'mashsb_settings_' . $tab, 'mashsb_settings_' . $tab, array(
                    'id' => isset( $option['id'] ) ? $option['id'] : null,
                    'desc' => !empty( $option['desc'] ) ? $option['desc'] : '',
                    'name' => isset( $option['name'] ) ? $option['name'] : null,
                    'section' => $tab,
                    'size' => isset( $option['size'] ) ? $option['size'] : null,
                    'options' => isset( $option['options'] ) ? $option['options'] : '',
                    'std' => isset( $option['std'] ) ? $option['std'] : '',
                    'textarea_rows' => isset( $option['textarea_rows'] ) ? $option['textarea_rows'] : ''
                )
            );
        }
    }

    // Creates our settings in the options table
    register_setting( 'mashsb_settings', 'mashsb_settings', 'mashsb_settings_sanitize' );
}

add_action( 'admin_init', 'mashsb_register_settings' );


/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
 */
function mashsb_get_registered_settings() {
    
    /**
     * 'Whitelisted' MASHSB settings, filters are provided for each settings
     * section to allow extensions and other plugins to add their own settings
     */
    $mashsb_settings = array(
        /** General Settings */
        'general' => apply_filters( 'mashsb_settings_general', array(
                'general_header' => array(
                    'id' => 'general_header',
                    'name' => '<strong>' . __( 'General', 'mashsb' ) . '</strong>',
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'header'
                ),
                'mashsb_sharemethod' => array(
                    'id' => 'mashsb_sharemethod',
                    'name' => __( 'Share Count', 'mashsb' ),
                    //'desc' => __( '- <i>MashEngine</i> collects shares by direct request to social networks.<br><br><i>SharedCount.com</i> is a third party service free for up to 10.000 daily requests. It collects shares for Facebook, Pinterest, Stumbleupon. (For GDPR compliance you should select the sharedcount.com service.)<br><br>Twitter count is aggreagated via <a href="https://twitcount.com" target="_blank" rel="external nofollow">https://twitcount.com</a>. You must sign up with your Twitter account for this free service to get the twitter share count. Visit the site http://twitcount.com, fill in your website domain and click on <i>Sign up</i>. <br><br><strong>Note: You need <a href="https://mashshare.net/downloads/mashshare-social-networks-addon/" target="_blank">MashShare Social Network Add-On</a> for enabling Twitter count.</strong>', 'mashsb' ),
                    'desc' => __( '- <i>SharedCount.com</i> is a third party service free for up to 10.000 daily requests. It collects shares for Facebook.<br><br>Twitter count is aggreagated via <a href="https://opensharecount.com" target="_blank" rel="external nofollow">https://opensharecount.com</a>. You must sign up with your Twitter account for this free service to get the twitter share count. Visit the site https://opensharecount.com, fill in your website domain and click on <i>Sign up</i>. <br><br><strong>Note: You need <a href="https://mashshare.net/downloads/mashshare-social-networks-addon/" target="_blank">MashShare Social Network Add-On</a> for enabling Twitter count.</strong>', 'mashsb' ),
                    'type' => 'select',
                    'options' => array(
                        //'mashengine' => 'MashEngine',
                        'sharedcount' => 'Sharedcount.com'
                    )
                ),
                'mashsharer_apikey' => array(
                    'id' => 'mashsharer_apikey',
                    'name' => __( 'Sharedcount.com API Key', 'mashsb' ),
                    'desc' => __( 'This is needed to use the sharedcount.com Share Count method. Get it at <a href="https://admin.sharedcount.com/admin/signup.php?utm_campaign=settings&utm_medium=plugin&utm_source=mashshare" target="_blank">SharedCount.com</a> for 10.000 free daily requests.', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'medium'
                ),
//                'mashsharer_sharecount_domain' => array(
//                    'id' => 'mashsharer_sharecount_domain',
//                    'name' => __( 'Sharedcount.com endpint', 'mashsb' ),
//                    'desc' => __( 'The SharedCount Domain your API key is configured to query. For example, free.sharedcount.com. This may update automatically if configured incorrectly.', 'mashsb' ),
//                    'type' => 'text',
//                    'size' => 'medium',
//                    'std' => 'https://api.sharedcount.com'
//                ),
                'caching_method' => array(
                    'id' => 'caching_method',
                    'name' => __( 'Caching Method', 'mashsb' ),
                    'desc' => sprintf(__( '<strong>Async Cache Refresh</strong> <br/>Refreshes the cache asyncronously in the background. <br><br>- New posts are updated at each hour. <br>- Posts older than 3 weeks are updated every 4 hours<br>- Post older than 2 months are updated every 12 hours<br><br> <strong>Refresh while loading</strong> <br/> Rebuilds expired cache while page is loaded after cache expiration time. <br><br><strong>If shares are not updating</strong> or site is heavy cached activate <i>Refresh while loading!</i> <br/>That\'s the default method.<br>Shares still not shown? <a href="%1s" target="_blank">Read this first!</a>', 'mashsb' ), 'http://docs.mashshare.net/article/4-try-this-first-before-troubleshooting'),
                    'type' => 'select',
                    'options' => array(
                        'async_cache' => 'Async Cache Refresh',
                        'refresh_loading' => 'Refresh while loading'
                    )
                ),
                'mashsharer_cache' => array(
                    'id' => 'mashsharer_cache',
                    'name' => __( 'Cache expiration', 'mashsb' ),
                    'desc' => __( 'Shares are counted for posts after a certain time and counts are not updated immediately. Sharedcount.com uses his own cache (30 - 60min). <p><strong>Default: </strong>5 min. <strong>Recommended: </strong>30min and more', 'mashsb' ),
                    'type' => 'select',
                    'options' => mashsb_get_expiretimes()
                ),
                'facebook_count' => array(
                    'id' => 'facebook_count_mode',
                    'name' => __( 'Facebook Count', 'mashsb' ),
                    'desc' => __( 'Get the Facebook total count including "likes" and "shares" or get only the pure share count', 'mashsb' ),
                    'type' => 'select',
                    'options' => array(
                        'shares' => 'Shares',
                        //'likes' => 'Likes', not used any longer
                        'total' => 'Shares + Comments'
                    )
                ),
                'cumulate_http_https' => array(
                    'id' => 'cumulate_http_https',
                    'name' => __( 'Cumulate Http(s) Shares', 'mashsb' ),
                    'desc' => __( 'Activate this if you want facebook shares to be cumulated for https and http scheme. If you switched your site to from http to https this is needed to not loose any previous shares which are cumulated earlier for the non ssl version of your site. If you are not missing any shares do not activate this option.', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'fake_count' => array(
                    'id' => 'fake_count',
                    'name' => __( 'Fake Share Count', 'mashsb' ),
                    'desc' => __( 'This number will be aggregated to all your share counts and is multiplied with a post specific factor. (Number of words of post title divided with 10).', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'medium'
                ),
                'disable_sharecount' => array(
                    'id' => 'disable_sharecount',
                    'name' => __( 'Disable Sharecount', 'mashsb' ),
                    'desc' => __( 'Use this if share should not be counted. Default: false', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'hide_sharecount' => array(
                    'id' => 'hide_sharecount',
                    'name' => __( 'Hide Sharecount', 'mashsb' ),
                    'desc' => __( '<strong>Optional:</strong> If you fill in any number here, the shares for a specific post are not shown until the share count of this number is reached.', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'small'
                ),
                'execution_order' => array(
                    'id' => 'execution_order',
                    'name' => __( 'Execution Order', 'mashsb' ),
                    'desc' => __( 'If you use other content plugins you can define here the execution order. Lower numbers mean earlier execution. E.g. Say "0" and Mashshare is executed before any other plugin (When the other plugin is not overwriting our execution order). Default is "1000"', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'small',
                    'std' => 1000
                ),
                'load_scripts_footer' => array(
                    'id' => 'load_scripts_footer',
                    'name' => __( 'JavaScript in Footer', 'mashsb' ),
                    'desc' => __( 'Enable this to load all *.js files into footer. Make sure your theme uses the wp_footer() template tag in the appropriate place. Default: Disabled', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'loadall' => array(
                    'id' => 'loadall',
                    'name' => __( 'JS & CSS Everywhere', 'mashsb' ),
                    'desc' => __( 'Enable this option if you are using </br> <strong>&lt;?php echo do_shortcode("[mashshare]"); ?&gt;</strong> to make sure that all css and js files are loaded. If Top or Bottom automatic position is used you can deactivate this option to allow conditional loading so MashShare\'s JS and CSS files are loaded only on pages where MashShare is used.', 'mashsb' ),
                    'type' => 'checkbox',
                    'std' => 'false'
                ),
                'twitter_popup' => array(
                    'id' => 'twitter_popup',
                    'name' => __( 'Twitter Popup disabled', 'mashsb' ),
                    'desc' => __( 'Check this box if your twitter popup is openening twice. This happens sometimes when you are using any third party twitter plugin or the twitter SDK on your website.', 'mashsb' ),
                    'type' => 'checkbox',
                    'std' => '0'
                ),
                'uninstall_on_delete' => array(
                    'id' => 'uninstall_on_delete',
                    'name' => __( 'Remove Data on Uninstall?', 'mashsb' ),
                    'desc' => __( 'Check this box if you would like Mashshare to completely remove all of its data when the plugin is deleted.', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'allow_tracking' => array(
                    'id' => 'allow_tracking',
                    'name' => __( 'Allow Usage Tracking', 'mashsb' ),
                    'desc' => sprintf( __( 'Allow Mashshare to track plugin usage? Opt-in to tracking and our newsletter and immediately be emailed a <strong>20%% discount to the Mashshare shop</strong>, valid towards the <a href="%s" target="_blank" rel="noopener">purchase of Add-Ons</a>. No sensitive data is tracked.', 'mashsb' ), 'https://www.mashshare.net/add-ons/?utm_source=' . substr( md5( get_bloginfo( 'name' ) ), 0, 10 ) . '&utm_medium=admin&utm_term=setting&utm_campaign=MASHSBUsageTracking' ),
                    'type' => 'checkbox'
                ),
                'is_main_query' => array(
                    'id' => 'is_main_query',
                    'name' => __( 'Hide Buttons in Widgets (is_main_query)', 'mashsb' ),
                    'desc' => __( 'If Share Buttons are shown in widgets enable this option. For devs: This uses the is_main_query condition. ' ) ,
                    'type' => 'checkbox'
                ),
                "user_roles_for_sharing_options" => array(
                    "id"            => "user_roles_for_sharing_options",
                    "name"          => __("Show Share Options Meta Box User Roles", "mashsb"),
                    "desc"          => __("Show the MashShare Share Options Meta Box on the page editor for certain user roles only. If nothing is set meta box is shown to all user roles.", "mashsb"),
                    "type"          => "multiselect",
                    "options"       => array_merge(array('disable' => 'Disable Share Options Meta Box') , mashsb_get_user_roles()),
                    "placeholder"   => __("Select User Roles", "mashsb"),
                    "std"           => __("All Roles", "mashsb"),
                ),
                'services_header' => array(
                    'id' => 'services_header',
                    'name' => '<strong>' . __( 'Networks', 'mashsb' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
//                array(
//                'id' => 'fb_access_token_new',
//                'name' => __( 'Facebook User Access Token', 'mashsb' ),
//                'desc' => sprintf( __( 'Optional: Use this to make up to 200 calls per hour to facebook api. <a href="%s" target="_blank">Read here</a> how to get the access token. If your access token is not working just leave this field empty. Shares are still counted.', 'mashsb' ), 'http://docs.mashshare.net/article/132-how-to-create-a-facebook-access-token' ),
//                'type' => 'fboauth',
//                'size' => 'large'
//                ),
                array(
                    'id' => 'fb_publisher_url',
                    'name' => __( 'Facebook page url', 'mashsb' ),
                    'desc' => __( 'Optional: The url of the main facebook account connected with this site', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'large'
                ),
//                array(
//                    'id' => 'fb_app_id',
//                    'name' => __( 'Facebook App ID', 'mashsb' ),
//                    'desc' => sprintf( __( 'Optional and not needed for basic share buttons. But required by some MashShare Add-Ons. <a href="%1s" target="_blank">Create a App ID now</a>.', 'mashsb' ), 'https://developers.facebook.com/docs/apps/register' ),
//                    'type' => 'text',
//                    'size' => 'medium'
//                ),
//                array(
//                    'id' => 'fb_app_secret',
//                    'name' => __( 'Facebook App Secret', 'mashsb' ),
//                    'desc' => sprintf( __( 'Required for getting accurate facebook share numbers. Where do i find the facebook APP Secret?', 'mashsb' ), 'https://developers.facebook.com/docs/apps/register' ),
//                    'type' => 'text',
//                    'size' => 'medium'
//                ),
                'mashsharer_hashtag' => array(
                    'id' => 'mashsharer_hashtag',
                    'name' => __( 'Twitter Username', 'mashsb' ),
                    'desc' => __( '<strong>Optional:</strong> Using your twitter username results in via @username', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'medium'
                ),
                'twitter_card' => array(
                    'id' => 'twitter_card',
                    'name' => __( 'Twitter Card', 'mashsb' ),
                    'desc' => __( 'If this is activated MashShare is creating new tags in head of your site for twitter card data and populates them with data coming from the MashShare Twitter Meta Box from the post editing screen or in case you are using Yoast these fields will be populated with the Yoast Twitter Card Data.<br><br>
So the MashShare twitter card tags will be containing the same social meta data that YOAST would be supplying on your site. So you can use that feature parallel to the Yoast twitter card integration and you do not need to deactivate it even when you prefer to use the Yoast Twitter Card editor.', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'open_graph' => array(
                    'id' => 'open_graph',
                    'name' => __( 'Open Graph Meta Tags', 'mashsb' ),
                    'desc' => __( 'If this is activated MashShare is creating new tags in head of your site for open graph data and populates them with data coming from the MashShare Open Graph Meta Box from the post editing screen or in case you are using Yoast these fields will be populated with the Yoast Open Graph Data.<br><br>
So the MashShare open graph data will be containing the same social meta data that YOAST would be supplying on your site. So you can use that feature parallel to the Yoast open graph integration and you do not need to deactivate it even when you prefer to use the Yoast Open Graph editor.', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'visible_services' => array(
                    'id' => 'visible_services',
                    'name' => __( 'Large Buttons', 'mashsb' ),
                    'desc' => __( 'Specify how many services and social networks are visible before the "Plus" Button is shown. This buttons turn into large prominent buttons.', 'mashsb' ),
                    'type' => 'select',
                    'options' => numberServices()
                ),
                'networks' => array(
                    'id' => 'networks',
                    'name' => __( 'Social Networks', 'mashsb' ),
                    'desc' => __( 'Use Drag and drop for sorting. Enable the ones that should be visible. Activate<br>more networks than number of "Large Buttons" and [+] PLUS button will be<br> added automatically.', 'mashsb' ),
                    'type' => 'networks',
                    'options' => mashsb_get_networks_list()
                ),
                /*'networks' => array(
                    'id' => 'networks',
                    'name' => '<strong>' . __( 'Services', 'mashsb' ) . '</strong>',
                    'desc' => __( 'Drag and drop the Share Buttons to sort them and specify which ones should be enabled. <br>If you enable more networks than "Large Buttons", the plus sign is automatically added <br>to the last visible large share buttons', 'mashsb' ),
                    'type' => 'networks',
                    'options' => mashsb_get_networks_list()
                ),*/
                /*'services_header' => array(
                    'id' => 'services_header',
                    'name' => __( 'Social Networks', 'mashsb' ),
                    'desc' => '',
                    'type' => 'header'
                ),*/
                /*'visible_services' => array(
                    'id' => 'visible_services',
                    'name' => __( 'Large Share Buttons', 'mashsb' ),
                    'desc' => __( 'Specify how many services and social networks are visible before the "Plus" Button is shown. These buttons turn into large prominent buttons.', 'mashsb' ),
                    'type' => 'select',
                    'options' => numberServices()
                ),*/
                
//                array(
//                    'id' => 'shorturl_type',
//                    'name' => __( 'Enable on', 'mashsb' ),
//                    'desc' => __( 'You can choose multiple networks where short url\'s should be used.', 'mashsb' ),
//                    'type' => 'multiselect',
//                    'placeholder' => 'Select the networks',
//                    'options' => array(
//                        'twitter' => 'Twitter',
//                        'facebook' => 'Facebook',
//                        'default' => 'All Networks'
//                    ),
//                    'std' => 'All networks'
//                ),
                'style_header' => array(
                    'id' => 'style_header',
                    'name' => '<strong>' . __( 'Visual', 'mashsb' ) . '</strong>',
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'header'
                ),
                'share_headline' => array(
                    'id' => 'share_headline',
                    'name' => __( 'Shares', 'mashsb' ),
                    'type' => 'headline'
                ),
                'mashsharer_round' => array(
                    'id' => 'mashsharer_round',
                    'name' => __( 'Round up Shares', 'mashsb' ),
                    'desc' => __( 'Share counts greater than 1.000 will be shown as 1k. Greater than 1 Million as 1M', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'animate_shares' => array(
                    'id' => 'animate_shares',
                    'name' => __( 'Animate Shares', 'mashsb' ),
                    'desc' => __( 'Count up the shares on page loading with a nice looking animation effect. This only works on singular pages and not with shortcodes generated buttons.', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'sharecount_title' => array(
                    'id' => 'sharecount_title',
                    'name' => __( 'Share Count Label', 'mashsb' ),
                    'desc' => __( 'Change the text of the Share count title. <strong>Default:</strong> SHARES', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'medium',
                    'std' => 'SHARES'
                ),
                'share_color' => array(
                    'id' => 'share_color',
                    'name' => __( 'Share Count Color', 'mashsb' ),
                    'desc' => __( 'Choose color of the share number in hex format, e.g. #7FC04C: ', 'mashsb' ),
                    'type' => 'color_select',
                    'size' => 'medium',
                    'std' => '#cccccc'
                ),
                'button_headline' => array(
                    'id' => 'button_headline',
                    'name' => __( 'Buttons', 'mashsb' ),
                    'type' => 'headline'
                ),
                #######################

                'buttons_size' => array(
                    'id' => 'buttons_size',
                    'name' => __( 'Buttons Size', 'mashsb' ),
                    'desc' => __('', 'mashsb'),
                    'type' => 'select',
                    'options' => array(
                        'mash-large' => 'Large',
                        'mash-medium' => 'Medium',
                        'mash-small' => 'Small'
                    ),
                    'std' => 'Large'
                ),
                'responsive_buttons' => array(
                    'id' => 'responsive_buttons',
                    'name' => __( 'Full Responsive Buttons', 'mashsb' ),
                    'desc' => __( 'Get full width buttons on large devices and small buttons on mobile devices. Deactivate to specify manually a fixed button width.', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                array(
                    'id' => 'button_width',
                    'name' => __( 'Button Width', 'mashpv' ),
                    'desc' => __( 'Minimum with of the large share buttons in pixels', 'mashpv' ),
                    'type' => 'number',
                    'size' => 'normal',
                    'std' => '177'
                ),
                'button_margin' => array(
                    'id' => 'button_margin',
                    'name' => __( 'Button Margin', 'mashsb' ),
                    'desc' => __('Decide if there is a small gap between the buttons or not', 'mashsb'),
                    'type' => 'checkbox',
                ),
                'border_radius' => array(
                    'id' => 'border_radius',
                    'name' => __( 'Border Radius', 'mashsb' ),
                    'desc' => __( 'Specify the border radius of all buttons in pixel. A border radius of 20px results in circle buttons. Default value is zero.', 'mashsb' ),
                    'type' => 'select',
                    'options' => array(
                        0 => 0,
                        1 => 1,
                        2 => 2,
                        3 => 3,
                        4 => 4,
                        5 => 5,
                        6 => 6,
                        7 => 7,
                        8 => 8,
                        9 => 9,
                        10 => 10,
                        11 => 11,
                        12 => 12,
                        13 => 13,
                        14 => 14,
                        15 => 15,
                        16 => 16,
                        17 => 17,
                        18 => 18,
                        19 => 19,
                        20 => 20,
                        'default' => 'default'
                    ),
                    'std' => 'default'
                ),
                'mash_style' => array(
                    'id' => 'mash_style',
                    'name' => __( 'Share Button Style', 'mashsb' ),
                    'desc' => __( 'Change visual appearance of the share buttons.', 'mashsb' ),
                    'type' => 'select',
                    'options' => array(
                        'shadow' => 'Shadowed',
                        'gradiant' => 'Gradient',
                        'default' => 'Flat'
                    ),
                    'std' => 'default'
                ),
                'small_buttons' => array(
                    'id' => 'small_buttons',
                    'name' => __( 'Small Share Buttons', 'mashsb' ),
                    'desc' => __( 'All buttons will be shown as pure small icons without any text on desktop and mobile devices all the time.<br><strong>Note:</strong>Disable this if you want the buttons full width on desktop devices and small on mobile devices.', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'text_align_center' => array(
                    'id' => 'text_align_center',
                    'name' => __( 'Text Align Center', 'mashsb' ),
                    'desc' => __( 'Buttons Text labels and social icons will be aligned in center of the buttons', 'mashsb' ),
                    'type' => 'checkbox'
                ),
                /*'image_share' => array(
                    'id' => 'image_share',
                    'name' => __( 'Share buttons on image hover', 'mashsb' ),
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'checkbox'
                ),*/
                'subscribe_behavior' => array(
                    'id' => 'subscribe_behavior',
                    'name' => __( 'Subscribe Button', 'mashsb' ),
                    'desc' => __( 'Specify if the subscribe button is opening a content box below the button or if the button is linked to the "subscribe url" below.', 'mashsb' ),
                    'type' => 'select',
                    'options' => array(
                        'content' => 'Open content box',
                        'link' => 'Open Subscribe Link'
                    ),
                    'std' => 'content'
                ),
                'subscribe_link' => array(
                    'id' => 'subscribe_link',
                    'name' => __( 'Subscribe URL', 'mashsb' ),
                    'desc' => __( 'Link the Subscribe button to this URL. This can be the url to your subscribe page, facebook fanpage, RSS feed etc. e.g. http://yoursite.com/subscribe', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std' => ''
                ),
                'additional_content' => array(
                    'id' => 'additional_content',
                    'name' => __( 'Additional Content', 'mashsb' ),
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'add_content',
                    'options' => array(
                        'box1' => array(
                            'id' => 'content_above',
                            'name' => __( 'Content Above', 'mashsb' ),
                            'desc' => __( 'Content appearing above share buttons. Use HTML, formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'mashsb' ),
                            'type' => 'textarea',
                            'textarea_rows' => '3',
                            'size' => 15
                        ),
                        'box2' => array(
                            'id' => 'content_below',
                            'name' => __( 'Content Below', 'mashsb' ),
                            'desc' => __( 'Content appearing below share buttons.  Use HTML, formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'mashsb' ),
                            'type' => 'textarea',
                            'textarea_rows' => '3',
                            'size' => 15
                        ),
                        'box3' => array(
                            'id' => 'subscribe_content',
                            'name' => __( 'Subscribe content', 'mashsb' ),
                            'desc' => __( 'Define the content of the opening toggle subscribe window here. Use formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'mashsb' ),
                            'type' => 'textarea',
                            'textarea_rows' => '3',
                            'size' => 15
                        )
                    )
                ),
                'additional_css' => array(
                    'id' => 'additional_css',
                    'name' => __( 'Custom Styles', 'mashsb' ),
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'add_content',
                    'options' => array(
                        'box1' => array(
                            'id' => 'custom_css',
                            'name' => __( 'General CSS', 'mashsb' ),
                            'desc' => __( 'This css is loaded on all pages where the Mashshare buttons are enabled and it\'s loaded as an additonal inline css on your site', 'mashsb' ),
                            'type' => 'textarea',
                            'textarea_rows' => '3',
                            'size' => 15
                        ),
                        'box2' => array(
                            'id' => 'amp_css',
                            'name' => __( 'AMP CSS', 'mashsb' ),
                            'desc' => sprintf( __( 'This CSS is loaded only on AMP Project pages like yourwebsite.com/amp. <strong>Note: </strong> You need the WordPress <a href="%s" target="_blank">AMP Plugin</a> installed.', 'mashsb' ), 'https://wordpress.org/plugins/amp/' ),
                            'type' => 'textarea',
                            'textarea_rows' => '3',
                            'size' => 15
                        ),
                    )
                ),

                'location_header' => array(
                    'id' => 'location_header',
                    'name' => '<strong>' . __( 'Position', 'mashsb' ) . '</strong>',
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'header'
                ),
                'mashsharer_position' => array(
                    'id' => 'mashsharer_position',
                    'name' => __( 'Position', 'mashsb' ),
                    'desc' => __( 'Position of Share Buttons. If this is set to <i>manual</i> use the shortcode function [mashshare] or use php code <br>&lt;?php echo do_shortcode("[mashshare]"); ?&gt; in template files. </p>You must activate the option "<strong>Load JS and CSS all over</strong>" if you experience issues with do_shortcode() and the buttons are not shown as expected. See all <a href="https://www.mashshare.net/faq/#Shortcodes" target="_blank">available shortcodes</a>.', 'mashsb' ),
                    'type' => 'select',
                    'options' => array(
                        'before' => __( 'Top', 'mashsb' ),
                        'after' => __( 'Bottom', 'mashsb' ),
                        'both' => __( 'Top and Bottom', 'mashsb' ),
                        'manual' => __( 'Manual', 'mashsb' )
                    )
                ),
                'post_types' => array(
                    'id' => 'post_types',
                    'name' => __( 'Post Types', 'mashsb' ),
                    'desc' => __( 'Select on which post_types the share buttons appear. These values will be ignored when "manual" position is selected.', 'mashsb' ),
                    'type' => 'posttypes'
                ),
                'excluded_from' => array(
                    'id' => 'excluded_from',
                    'name' => __( 'Exclude from post id', 'mashsb' ),
                    'desc' => __( 'Exclude share buttons from a list of post ids. Put in the post id separated by a comma, e.g. 23, 63, 114 ', 'mashsb' ),
                    'type' => 'text',
                    'size' => 'medium'
                ),
                'singular' => array(
                    'id' => 'singular',
                    'name' => __( 'Categories', 'mashsb' ),
                    'desc' => __( 'Enable this checkbox to enable Mashshare on categories with multiple blogposts. <br><strong>Note: </strong> Post_types: "Post" must be enabled.', 'mashsb' ),
                    'type' => 'checkbox',
                    'std' => '0'
                ),
                'frontpage' => array(
                    'id' => 'frontpage',
                    'name' => __( 'Frontpage', 'mashsb' ),
                    'desc' => __( 'Enable share buttons on frontpage', 'mashsb' ),
                    'type' => 'checkbox'
                ),
            array(
                    'id' => 'shorturl_header',
                    'name' => '<strong>' . __( 'Short URLs', 'mashsb' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header',
                    'size' => 'regular'
                ),
                array(
                    'id' => 'bitly_access_token',
                    'name' => __( 'Bitly access token', 'mashsb' ),
                    'desc' => sprintf(__( 'If you like to use bitly.com shortener get a free bitly access token <a href="%s" target="_blank">here</a>. This turn urls into a format: http://bit.ly/cXnjsh. ', 'mashsb' ), 'https://bitly.com/a/oauth_apps'),
                    'type' => 'text',
                    'size' => 'large'
                ),
//                array(
//                    'id' => 'google_app_id',
//                    'name' => __( 'Google API Key (goo.gl)', 'mashsb' ),
//                    'desc' => sprintf(__( 'If you like to use goo.gl shortener get a free Google API key <a href="%s" target="_blank">here</a>. This turn urls into a format: http://goo.gl/cXnjsh. ' . mashsb_check_google_apikey(), 'mashsb' ),'https://console.developers.google.com/'),
//                    'type' => 'text',
//                    'size' => 'large'
//                ),
                array(
                    'id' => 'mashsu_methods',
                    'name' => __( 'Shorturl method', 'mashsb' ),
                    'desc' => sprintf(__('Bitly generated shortlinks will be converted to the url format: <i>http://bit.ly/1PPg9D9</i><br><br>Goo.gl generated urls look like: <br><i>http://goo.gl/vSJwUV</i><br><br>Using WP Shortlinks converts twitter links into:<br> <i>%s ?p=101</i>', 'mashsb'), get_site_url() ),
                    'type' => 'select',
                    'options' => array(
                        'wpshortlinks' => 'WP Short links',
                        'bitly' => 'Bitly',
                        'disabled' => 'Short URLs Disabled',
                    )
                ),
                array(
                    'id' => 'shorturl_explanation',
                    'name' => __( 'Important: Read this!', 'mashsb' ),
                    'desc' => __('<strong>The post short url is NOT generated immediatly after first page load!</strong>  Background processing can take up to 1 hour for new posts and 4 - 12 hours for old posts.','mashsb'),
                    'type' => 'renderhr',
                    'size' => 'large'
                ),
                'debug_header' => array(
                    'id' => 'debug_header',
                    'name' => '<strong>' . __( 'Debug', 'mashsb' ) . '</strong>',
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'header'
                ),
                array(
                    'id' => 'disable_cache',
                    'name' => __( 'Disable Cache', 'mashsb' ),
                    'desc' => __( '<strong>Note: </strong>Use this only for testing to see if shares are counted! Your page loading performance will drop. Works only when sharecount is enabled.<br>' . mashsb_cache_status(), 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'delete_cache_objects' => array(
                    'id' => 'delete_cache_objects',
                    'name' => __( 'Attention: Purge DB Cache', 'mashsb' ),
                    'desc' => __( '<strong>Caution </strong>Use this as last resort. This will delete all your share counts stored in mashshare post_meta objects and it takes hours to get them back. Usually this option is not needed! <br>' . mashsb_delete_cache_objects(), 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'debug_mode' => array(
                    'id' => 'debug_mode',
                    'name' => __( 'Debug mode', 'mashsb' ),
                    'desc' => __( '<strong>Note: </strong> Check this box before you get in contact with our support team. This allows us to check publically hidden debug messages on your website. Do not forget to disable it thereafter! Enable this also to write daily sorted log files of requested share counts to folder <strong>/wp-content/plugins/mashsharer/logs</strong>. Please send us this files when you notice a wrong share count.' . mashsb_log_permissions(), 'mashsb' ),
                    'type' => 'checkbox'
                ),
                'fb_debug' => array(
                    'id' => 'fb_debug',
                    'name' => __( '', 'mashsb' ),
                    'desc' => '',
                    'type' => 'ratelimit'
                ),
            )
        ),
        'licenses' => apply_filters( 'mashsb_settings_licenses', array(
                'licenses_header' => array(
                    'id' => 'licenses_header',
                    'name' => __( 'Activate your Add-Ons', 'mashsb' ),
                    'desc' => mashsb_check_active_addons() ? __('Activate your license key to get important security and feature updates for your Add-On!','mashsb') : sprintf(__('No Add-Ons are active or installed! <a href="%s" target="blank">See all Add-Ons</a>','mashsb'), 'https://www.mashshare.net/add-ons/?utm_source=insideplugin&utm_medium=userwebsite&utm_content=see_all_add_ons&utm_campaign=freeplugin'),
                    'type' => 'header'
                ),)
        ),
        'extensions' => apply_filters( 'mashsb_settings_extension', array()),
        'addons' => apply_filters( 'mashsb_settings_addons', array(
                'addons' => array(
                    'id' => 'addons',
                    'name' => __( '', 'mashsb' ),
                    'desc' => __( '', 'mashsb' ),
                    'type' => 'addons'
                ),
            )
        )
    );

    return $mashsb_settings;
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.0
 *
 * @param array $input The value input in the field
 *
 * @return string $input Sanitized value
 */
function mashsb_settings_sanitize( $input = array() ) {

    global $mashsb_options;

    if( empty( $_POST['_wp_http_referer'] ) ) {
        return $input;
    }

    parse_str( $_POST['_wp_http_referer'], $referrer );

    $settings = mashsb_get_registered_settings();
    $tab = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

    $input = $input ? $input : array();
    $input = apply_filters( 'mashsb_settings_' . $tab . '_sanitize', $input );

    // Loop through each setting being saved and pass it through a sanitization filter
    foreach ( $input as $key => $value ) {

        // Get the setting type (checkbox, select, etc)
        $type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

        if( $type ) {
            // Field type specific filter
            $input[$key] = apply_filters( 'mashsb_settings_sanitize_' . $type, $value, $key );
        }

        // General filter
        $input[$key] = apply_filters( 'mashsb_settings_sanitize', $value, $key );
    }

    // Loop through the whitelist and unset any that are empty for the tab being saved
    if( !empty( $settings[$tab] ) ) {
        foreach ( $settings[$tab] as $key => $value ) {

            // settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
            if( is_numeric( $key ) ) {
                $key = $value['id'];
            }

            if( empty( $input[$key] ) ) {
                unset( $mashsb_options[$key] );
            }
        }
    }

    // Merge our new settings with the existing
    $output = array_merge( $mashsb_options, $input );

    add_settings_error( 'mashsb-notices', '', __( 'Settings updated.', 'mashsb' ), 'updated' );

    return $output;
}

/**
 * Sanitize text fields
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function mashsb_sanitize_text_field( $input ) {
    return trim( $input );
}
add_filter( 'mashsb_settings_sanitize_text', 'mashsb_sanitize_text_field' );

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @return string $input Sanitizied value
 */
function mashsb_get_settings_tabs() {

    $settings = mashsb_get_registered_settings();

    $tabs = array();
    $tabs['general'] = __( 'Settings', 'mashsb' );

    if( !empty( $settings['visual'] ) ) {
        $tabs['visual'] = __( 'Visual', 'mashsb' );
    }

    if( !empty( $settings['networks'] ) ) {
        $tabs['networks'] = __( 'Social Networks', 'mashsb' );
    }

    if( !empty( $settings['extensions'] ) ) {
        $tabs['extensions'] = __( 'Add-On Settings', 'mashsb' );
    }

    if( !empty( $settings['licenses'] ) ) {
        $tabs['licenses'] = __( 'Licenses', 'mashsb' );
    }
    if (false === mashsb_hide_addons()){
    $tabs['addons'] = __( 'Get More Add-Ons', 'mashsb' );
    }
    
    //$tabs['misc']      = __( 'Misc', 'mashsb' );

    return apply_filters( 'mashsb_settings_tabs', $tabs );
}

/*
 * Retrieve a list of possible expire cache times
 *
 * @since  2.0.0
 *
 */

function mashsb_get_expiretimes() {
    /* Defaults */
    $times = array(
        '300' => 'in 5 minutes',
        '600' => 'in 10 minutes',
        '1800' => 'in 30 minutes',
        '3600' => 'in 1 hour',
        '21600' => 'in 6 hours',
        '43200' => 'in 12 hours',
        '86400' => 'in 24 hours'
    );
    return $times;
}

/**
 * Retrieve array of  social networks Facebook / Twitter / Subscribe
 *
 * @since 2.0.0
 *
 * @return array Defined social networks
 */
function mashsb_get_networks_list() {

    $networks = get_option( 'mashsb_networks' );
    return apply_filters( 'mashsb_get_networks_list', $networks );
}

/**
 * Page Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function mashsb_headline_callback( $args ) {
    echo '&nbsp';
}
/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function mashsb_header_callback( $args ) {
    echo '&nbsp';
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */

function mashsb_checkbox_callback( $args ) {
    global $mashsb_options;

    $checked = isset( $mashsb_options[$args['id']] ) ? checked( 1, $mashsb_options[$args['id']], false ) : '';
    $html = '<div class="mashsb-admin-onoffswitch">';
    $html .= '<input type="checkbox" class="mashsb-admin-onoffswitch-checkbox" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
    $html .= '<label class="mashsb-admin-onoffswitch-label" for="mashsb_settings[' . $args['id'] . ']">'
        . '<span class="mashsb-admin-onoffswitch-inner"></span>'
        . '<span class="mashsb-admin-onoffswitch-switch"></span>'
        . '</label>';
    $html .= '</div>';

    echo $html;
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_multicheck_callback( $args ) {
    global $mashsb_options;

    if( !empty( $args['options'] ) ) {
        foreach ( $args['options'] as $key => $option ):
            if( isset( $mashsb_options[$args['id']][$key] ) ) {
                $enabled = $option;
            } else {
                $enabled = NULL;
            }
            echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . ']" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
            echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
        endforeach;
        echo '<p class="description mashsb_hidden">' . $args['desc'] . '</p>';
    }
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_radio_callback( $args ) {
    global $mashsb_options;

    foreach ( $args['options'] as $key => $option ) :
        $checked = false;

        if( isset( $mashsb_options[$args['id']] ) && $mashsb_options[$args['id']] == $key )
            $checked = true;
        elseif( isset( $args['std'] ) && $args['std'] == $key && !isset( $mashsb_options[$args['id']] ) )
            $checked = true;

        echo '<input name="mashsb_settings[' . $args['id'] . ']"" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
        echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
    endforeach;

    echo '<p class="description mashsb_hidden">' . $args['desc'] . '</p>';
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_text_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<label class="mashsb_hidden" class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_number_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $max = isset( $args['max'] ) ? $args['max'] : 999999;
    $min = isset( $args['min'] ) ? $args['min'] : 0;
    $step = isset( $args['step'] ) ? $args['step'] : 1;

    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_textarea_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : '40';
    $html = '<textarea class="large-text mashsb-textarea" cols="50" rows="' . $size . '" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}
/**
 * Custom CSS Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @deprecated 3.3.6
 * @return void
 */
//function mashsb_customcss_callback( $args ) {
//    global $mashsb_options;
//
//    if( isset( $mashsb_options[$args['id']] ) )
//        $value = $mashsb_options[$args['id']];
//    else
//        $value = isset( $args['std'] ) ? $args['std'] : '';
//
//    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : '40';
//    $html = '<textarea class="large-text mashsb-textarea" cols="50" rows="' . $size . '" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']">' . esc_textarea( $value ) . '</textarea>';
//    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';
//
//    echo $html;
//}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_password_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="password" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
    $html .= '<label for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function mashsb_missing_callback( $args ) {
    printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'mashsb' ), $args['id'] );
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return string
 */
function mashsb_select_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $html = '<select id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']"/>';

    foreach ( $args['options'] as $option => $name ) :
        $selected = selected( $option, $value, false );
        $html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
    endforeach;

    $html .= '</select>';
    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Multi Select Callback
 *
 * @since 3.0.0
 * @param array $args Arguments passed by the settings
 * @global $mashsb_options Array of all the MASHSB Options
 * @return string $output dropdown
 */
function mashsb_multiselect_callback( $args = array() ) {
    global $mashsb_options;

    $selected = isset($mashsb_options[$args['id']]) ? $mashsb_options[$args['id']] : '';
    $checked = '';
    
    $html = '<select name="mashsb_settings[' . $args['id'] . '][]" data-placeholder="" style="width:350px;" multiple tabindex="4" class="mashsb-select mashsb-chosen-select">';
    $i = 0;
    foreach ( $args['options'] as $key => $value ) :
        if( is_array($selected)){
            $checked = selected( true, in_array( $key, $selected ), false );
        }
        $html .= '<option value="' . $key . '" ' . $checked . '>' . $value . '</option>';
    endforeach;
    $html .= '</select>';
    echo $html;
}




/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 2.1.2
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */

function mashsb_color_select_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $html = '<strong>#:</strong><input type="text" style="max-width:80px;border:1px solid #' . esc_attr( stripslashes( $value ) ) . ';border-right:20px solid #' . esc_attr( stripslashes( $value ) ) . ';" id="mashsb_settings[' . $args['id'] . ']" class="medium-text ' . $args['id'] . ' mashsb-color-box" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';

    $html .= '</select>';
    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @global $wp_version WordPress Version
 */
function mashsb_rich_editor_callback( $args ) {
    global $mashsb_options, $wp_version;
    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    if( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
        ob_start();
        wp_editor( stripslashes( $value ), 'mashsb_settings_' . $args['id'], array('textarea_name' => 'mashsb_settings[' . $args['id'] . ']', 'textarea_rows' => $args['textarea_rows']) );
        $html = ob_get_clean();
    } else {
        $html = '<textarea class="large-text mashsb-richeditor" rows="10" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    }

    $html .= '<br/><label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_upload_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . $size . '-text mashsb_upload_field" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<span>&nbsp;<input type="button" class="mashsb_settings_upload_button button-secondary" value="' . __( 'Upload File', 'mashsb' ) . '"/></span>';
    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_color_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $default = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="mashsb-color-picker" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
//if( !function_exists( 'mashsb_license_key_callback' ) ) {
//
//    function mashsb_license_key_callback( $args ) {
//        global $mashsb_options;
//
//        if( isset( $mashsb_options[$args['id']] ) )
//            $value = $mashsb_options[$args['id']];
//        else
//            $value = isset( $args['std'] ) ? $args['std'] : '';
//
//        $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
//        $html = '<input type="text" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
//
//        if( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
//            $html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License', 'mashsb' ) . '"/>';
//            $html .= '<span style="font-weight:bold;color:green;"> License key activated! </span> <p style="color:green;font-size:13px;"> You´ll get updates for this Add-On automatically!</p>';
//        } else {
//            $html .= '<span style="color:red;"> License key not activated!</span style=""><p style="font-size:13px;font-weight:bold;">You´ll get no important security and feature updates for this Add-On!</p>';
//        }
//        $html .= '<label for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';
//
//        wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );
//
//        echo $html;
//    }
//
//}

/**
 * Registers the license field callback for MashShare Add-Ons
 *
 * @since 3.0.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB options
 * @return void
 */
if ( ! function_exists( 'mashsb_license_key_callback' ) ) {
    function mashsb_license_key_callback( $args ) {
        global $mashsb_options;
        
        $class = '';

        $messages = array();
        $license  = get_option( $args['options']['is_valid_license_option'] );


        if( isset( $mashsb_options[$args['id']] ) ) {
            $value = $mashsb_options[$args['id']];
        } else {
            $value = isset( $args['std'] ) ? $args['std'] : '';
        }

        if( ! empty( $license ) && is_object( $license ) ) {

            // activate_license 'invalid' on anything other than valid, so if there was an error capture it
            if ( false === $license->success ) {

                switch( $license->error ) {

                    case 'expired' :

                        $class = 'error';
                        $messages[] = sprintf(
                            __( 'Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'mashsb' ),
                            date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                            'https://www.mashshare.net/checkout/?edd_license_key=' . $value . '&utm_campaign=notice&utm_source=license_tab&utm_medium=admin&utm_content=license-expired'
                        );

                        $license_status = 'mashsb-license-' . $class . '-notice';

                        break;

                    case 'missing' :

                        $class = 'error';
                        $messages[] = sprintf(
                            __( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'mashsb' ),
                            'https://www.mashshare.net/your-account?utm_source=licenses-tab&utm_medium=admin&utm_content=invalid-license&utm_campaign=notice'
                        );

                        $license_status = 'mashsb-license-' . $class . '-notice';

                        break;

                    case 'invalid' :
                    case 'site_inactive' :

                        $class = 'error';
                        $messages[] = sprintf(
                            __( 'Your %s is not active for this URL. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'easy-digital-downloads' ),
                            $args['name'],
                            'https://www.mashshare.net/your-account?utm_campaign=notice&utm_source=licenses-tab&utm_medium=admin&utm_content=invalid-license'
                        );

                        $license_status = 'mashsb-license-' . $class . '-notice';

                        break;

                    case 'item_name_mismatch' :

                        $class = 'error';
                        $messages[] = sprintf( __( 'This is not a %s.', 'mashsb' ), $args['name'] );

                        $license_status = 'mashsb-license-' . $class . '-notice';

                        break;

                    case 'no_activations_left':

                        $class = 'error';
                        $messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'mashsb' ), 'https://www.mashshare.net/your-account?utm_campaign=notice&utm_source=licenses-tab&utm_medium=admin&utm_content=invalid-license' );

                        $license_status = 'mashsb-license-' . $class . '-notice';

                        break;

                }

            } else {

                switch( $license->license ) {

                    case 'valid' :
                    default:

                        $class = 'valid';

                        $now        = current_time( 'timestamp' );
                        $expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

                        if( 'lifetime' === $license->expires ) {

                            $messages[] = __( 'License key never expires.', 'mashsb' );

                            $license_status = 'mashsb-license-lifetime-notice';

                        } elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

                            $messages[] = sprintf(
                                __( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank" title="Renew license">Renew your license key</a>.', 'mashsb' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                                'https://www.mashshare.net/checkout/?edd_license_key=' . $value . '&utm_campaign=notice&utm_source=licenses-tab&utm_medium=admin'
                            );

                            $license_status = 'mashsb-license-expires-soon-notice';

                        } else {

                            $messages[] = sprintf(
                                __( 'Your license key expires on %s.', 'mashsb' ),
                                date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
                            );

                            $license_status = 'mashsb-license-expiration-date-notice';

                        }

                        break;

                }

            }

        } else {
            $license_status = null;
        }

        $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
        $html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="mashsb_settings[' . mashsb_sanitize_key( $args['id'] ) . ']" name="mashsb_settings[' . mashsb_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

        if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
            $html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'mashsb' ) . '"/>';
        }

        $html .= '<label for="mashsb_settings[' . mashsb_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

        if ( ! empty( $messages ) ) {
            foreach( $messages as $message ) {

                $html .= '<div class="mashsb-license-data mashsb-license-' . $class . '">';
                $html .= '<p>' . $message . '</p>';
                $html .= '</div>';

            }
        }

        wp_nonce_field( mashsb_sanitize_key( $args['id'] ) . '-nonce', mashsb_sanitize_key( $args['id'] ) . '-nonce' );

        if ( isset( $license_status ) ) {
            echo '<div class="' . $license_status . '">' . $html . '</div>';
        } else {
            echo '<div class="mashsb-license-null">' . $html . '</div>';
        }
    }
}

/**
 * Networks Callback / Facebook, Twitter and Subscribe default
 *
 * Renders network order table. Uses separate option field 'mashsb_networks
 *
 * @since 2.0.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the mashsb Options
 * @return void
 */
function mashsb_networks_callback( $args ) {
    global $mashsb_options;
    /* Array in $mashsb_option['networks']

      array(
      0 => array (
      'status' => '1',
      'name' => 'Share on Facebook',
      'name2' => 'Share'
      ),
      1 => array (
      'status' => '1',
      'name' => 'Tweet on Twitter',
      'name2' => 'Twitter'
      ),
      2 => array (
      'status' => '1',
      'name' => 'Subscribe',
      'name2' => 'Subscribe'
      )
      )
     */

    ob_start();
    ?>
    <p class="mashsb_description"><?php echo $args['desc']; ?></p>
    <table id="mashsb_network_list" class="wp-list-table fixed posts">
    <thead>
    <tr>
        <th scope="col" class='mashsb-network-col' style="padding: 2px 0px 10px 0px"><?php _e( 'Social Network', 'mashsb' ); ?></th>
        <th scope="col" class='mashsb-status-col' style="padding: 2px 0px 10px 10px"><?php _e( 'Status', 'mashsb' ); ?></th>
        <th scope="col" class='mashsb-label-col' style="padding: 2px 0px 10px 10px"><?php _e( 'Custom Label', 'mashsb' ); ?></th>
    </tr>
    </thead>
    <?php
    if( !empty( $args['options'] ) ) {
        foreach ( $args['options'] as $key => $option ):
            echo '<tr id="mashsb_list_' . $key . '" class="mashsb_list_item">';
            if( isset( $mashsb_options[$args['id']][$key]['status'] ) ) {
                $enabled = 1;
            } else {
                $enabled = NULL;
            }
            if( isset( $mashsb_options[$args['id']][$key]['name'] ) ) {
                $name = $mashsb_options[$args['id']][$key]['name'];
            } else {
                $name = NULL;
            }
            
            if ($option === 'Flipboard'){ // Darn you multi color flipboard svg icon.
            echo '<td class="mashicon-' . strtolower( $option ) . '"><div class="icon"><span class="mash-path1"></span><span class="mash-path2"></span><span class="mash-path3"></span><span class="mash-path4"></span></div><span class="text">' . $option . '</span></td>';
            } else {
            echo '<td class="mashicon-' . strtolower( $option ) . '"><span class="icon"></span><span class="text">' . $option . '</span></td>';    
            }
            echo '<td><input type="hidden" name="mashsb_settings[' . $args['id'] . '][' . $key . '][id]" id="mashsb_settings[' . $args['id'] . '][' . $key . '][id]" value="' . strtolower( $option ) . '">';
            echo '<div class="mashsb-admin-onoffswitch">';
            echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . '][status]" class="mashsb-admin-onoffswitch-checkbox" id="mashsb_settings[' . $args['id'] . '][' . $key . '][status]" type="checkbox" value="1" ' . checked( 1, $enabled, false ) . '/>';
            echo '<label class="mashsb-admin-onoffswitch-label" for="mashsb_settings[' . $args['id'] . '][' . $key . '][status]">'
                . '<span class="mashsb-admin-onoffswitch-inner"></span>'
                . '<span class="mashsb-admin-onoffswitch-switch"></span>'
                . '</label>';
            echo '</div>';
            echo '<td><input type="text" class="medium-text" id="mashsb_settings[' . $args['id'] . '][' . $key . '][name]" name="mashsb_settings[' . $args['id'] . '][' . $key . '][name]" value="' . $name . '"/>';
            echo '</tr>';
        endforeach;
    }
    echo '</table>';
    echo ob_get_clean();
}



/**
 * Registers the Add-Ons field callback for Mashshare Add-Ons
 *
 * @since 2.0.5
 * @param array $args Arguments passed by the setting
 * @return html
 */
function mashsb_addons_callback( $args ) {
    $html = mashsb_add_ons_page();
    echo $html;
}

/**
 * Registers the image upload field
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_upload_image_callback( $args ) {
    global $mashsb_options;

    if( isset( $mashsb_options[$args['id']] ) )
        $value = $mashsb_options[$args['id']];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . $size . '-text ' . $args['id'] . '" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

    $html .= '<input type="submit" class="button-secondary mashsb_upload_image" name="' . $args['id'] . '_upload" value="' . __( 'Select Image', 'mashsb' ) . '"/>';

    $html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

    echo $html;
}

/*
 * Post Types Callback
 *
 * Adds a multiple choice drop box
 * for selecting where Mashshare should be enabled
 *
 * @since 2.0.9
 * @param array $args Arguments passed by the setting
 * @return void
 *
 */

function mashsb_posttypes_callback( $args ) {
    global $mashsb_options;
    $posttypes = get_post_types();

    //if ( ! empty( $args['options'] ) ) {
    if( !empty( $posttypes ) ) {
        //foreach( $args['options'] as $key => $option ):
        foreach ( $posttypes as $key => $option ):
            if( isset( $mashsb_options[$args['id']][$key] ) ) {
                $enabled = $option;
            } else {
                $enabled = NULL;
            }
            echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . ']" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
            echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
        endforeach;
        echo '<p class="description mashsb_hidden">' . $args['desc'] . '</p>';
    }
}

/*
 * Note Callback
 *
 * Show a note
 *
 * @since 2.2.8
 * @param array $args Arguments passed by the setting
 * @return void
 *
 */

function mashsb_note_callback( $args ) {
    global $mashsb_options;
    //$html = !empty($args['desc']) ? $args['desc'] : '';
    $html = '';
    echo $html;
}

/**
 * Additional content Callback
 * Adds several content text boxes selectable via jQuery easytabs()
 *
 * @param array $args
 * @return string $html
 * @scince 2.3.2
 */
function mashsb_add_content_callback( $args ) {
    global $mashsb_options;

    $html = '<div id="mashtabcontainer" class="tabcontent_container"><ul class="mashtabs" style="width:99%;max-width:500px;">';
    foreach ( $args['options'] as $option => $name ) :
        $html .= '<li class="mashtab" style="float:left;margin-right:4px;"><a href="#' . $name['id'] . '">' . $name['name'] . '</a></li>';
    endforeach;
    $html .= '</ul>';
    $html .= '<div class="mashtab-container">';
    foreach ( $args['options'] as $option => $name ) :
        $value = isset( $mashsb_options[$name['id']] ) ? $mashsb_options[$name['id']] : '';
        $textarea = '<textarea class="large-text mashsb-textarea" cols="50" rows="15" id="mashsb_settings[' . $name['id'] . ']" name="mashsb_settings[' . $name['id'] . ']">' . esc_textarea( $value ) . '</textarea>';
        $html .= '<div id="' . $name['id'] . '" style="max-width:500px;"><span style="padding-top:60px;display:block;">' . $name['desc'] . '</span><br>' . $textarea . '</div>';
    endforeach;
    $html .= '</div>';
    $html .= '</div>';
    echo $html;
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function mashsb_hook_callback( $args ) {
    do_action( 'mashsb_' . $args['id'] );
}

/**
 * Custom Callback for rendering a <hr> line in the settings
 *
 * @since 2.4.7
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the Mashshare Options
 * @return void

 */
if( !function_exists( 'mashsb_renderhr_callback' ) ) {

    function mashsb_renderhr_callback( $args ) {
        $html = '';
        echo $html;
    }

}

/**
 * Set manage_options as the cap required to save MASHSB settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function mashsb_set_settings_cap() {
    return 'manage_options';
}

add_filter( 'option_page_capability_mashsb_settings', 'mashsb_set_settings_cap' );


/* returns array with amount of available services
 * @since 2.0
 * @return array
 */

function numberServices() {
    $number = 1;
    $array = array();
    while ( $number <= count( mashsb_get_networks_list() ) ) {
        $array[] = $number++;
    }
    $array['all'] = __( 'All Services' );
    return apply_filters( 'mashsb_return_services', $array );
}

/* Purge the Mashshare
 * database MASHSB_TABLE
 *
 * @since 2.0.4
 * @return string
 */

function mashsb_delete_cache_objects() {
    global $mashsb_options, $wpdb;
    if( isset( $mashsb_options['delete_cache_objects'] ) ) {
        delete_post_meta_by_key( 'mashsb_timestamp' );
        delete_post_meta_by_key( 'mashsb_shares' );
        delete_post_meta_by_key( 'mashsb_jsonshares' );
        return ' <strong style="color:red;">' . __( 'DB cache deleted! Do not forget to uncheck this box for performance increase after doing the job.', 'mashsb' ) . '</strong> ';
    }
}

/* 
 * Check Cache Status if enabled or disabled
 *
 * @since 2.0.4
 * @return string
 */

function mashsb_cache_status() {
    global $mashsb_options;
    if( isset( $mashsb_options['disable_cache'] ) ) {
        return ' <strong style="color:red;">' . __( 'Transient Cache disabled! Enable it for performance increase.', 'mashsb' ) . '</strong> ';
    }
}

/**
 * Check if cache is deactivated
 * 
 * @global $mashsb_options $mashsb_options
 * @return boolean
 */
function mashsb_is_deactivated_cache() {
    global $mashsb_options;
    if( isset( $mashsb_options['disable_cache'] ) ) {
        return true;
    }
    return false;
}

/**
 * Check if cache gets deleted
 * 
 * @global $mashsb_options $mashsb_options
 * @return boolean
 */
function mashsb_is_deleted_cache() {
    global $mashsb_options;
    if( isset( $mashsb_options['delete_cache_objects'] ) ) {
        return true;
    }
    return false;
}

/* Permission check if logfile is writable
 *
 * @since 2.0.6
 * @return string
 */

function mashsb_log_permissions() {
    global $mashsb_options;
    if( !MASHSB()->logger->checkDir() ) {
        return '<br><strong style="color:red;">' . __( 'Log file directory not writable! Set FTP permission to 755 or 777 for /wp-content/plugins/mashsharer/logs/', 'mashsb' ) . '</strong> <br> Read here more about <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">file permissions</a> ';
    }
}

/**
 * Sanitizes a string key for MASHSB Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
 *
 * @since  2.5.5
 * @param  string $key String key
 * @return string Sanitized key
 */
function mashsb_sanitize_key( $key ) {
    $raw_key = $key;
    $key = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );
    /**
     * Filter a sanitized key string.
     *
     * @since 2.5.8
     * @param string $key     Sanitized key.
     * @param string $raw_key The key prior to sanitization.
     */
    return apply_filters( 'mashsb_sanitize_key', $key, $raw_key );
}


function mashsb_return_self($content = array()){
    return $content;
}

/**
 * Check if MashShare Add-Ons are installed and active
 *
 * @return boolean true when active
 */
function mashsb_check_active_addons(){

    $content = apply_filters('mashsb_settings_licenses', array());
    if (count($content) > 0){
        return true;
    }
}

/**
 * 
 * Get user roles with capability 'edit_posts'
 * 
 * @global array $wp_roles
 * @return array
 */
function mashsb_get_user_roles() {
    global $wp_roles;
    $roles = array();

    foreach ( $wp_roles->roles as $role ) {
        if( isset( $role["capabilities"]["edit_posts"] ) && $role["capabilities"]["edit_posts"] === true ) {
            $value = str_replace( ' ', null, strtolower( $role["name"] ) );
            $roles[$value] = $role["name"];
        }
    }
    return $roles;
}

/**
 * Render Button for oauth authentication and access token generation
 * @global $mashsb_options $mashsb_options
 * @param type $args
 */
function mashsb_fboauth_callback( $args ) {
    global $mashsb_options;
    
    if( isset( $mashsb_options[$args['id']] ) ){
        $value = $mashsb_options[$args['id']];
    }else{        
        $value = isset( $args['std'] ) ? $args['std'] : '';
    }
    // Change expiration date
    if( isset( $mashsb_options['expire_'.$args['id']] ) ){
        $expire = $mashsb_options['expire_'.$args['id']];
    }else{        
        $expire = '';
    }
    
    $button_label = __('Verify Access Token', 'mashsb');

    $html = '<a href="#" id="mashsb_verify_fbtoken" class="button button-primary">'.$button_label.'</a>';
    $html .= '&nbsp; <input type="text" class="medium-text" style="width:333px;" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '&nbsp; <input type="hidden" class="medium-text" id="mashsb_settings[expire_' . $args['id'] . ']" name="mashsb_settings[expire_' . $args['id'] . ']" value="' . esc_attr( stripslashes( $expire ) ) . '"/>';
    $html .= '<div class="token_status">'
            . '<span id="mashsb_expire_token_status"></span>'
            . '<span id="mashsb_token_notice"></span>'
            . '</div>';
    
echo $html;
    
}
//function mashsb_fboauth_callback( $args ) {
//    global $mashsb_options;
//    
//    if( isset( $mashsb_options[$args['id']] ) ){
//        $value = $mashsb_options[$args['id']];
//    }else{        
//        $value = isset( $args['std'] ) ? $args['std'] : '';
//    }
//    // Change expiration date
//    if( isset( $mashsb_options['expire_'.$args['id']] ) ){
//        $expire = $mashsb_options['expire_'.$args['id']];
//    }else{        
//        $expire = '';
//    }
//    
//    $button_label = empty($mashsb_options[$args['id']]) ? __('Get Access Token | Facebook Login', 'mashsb') : __('Renew Access Token', 'mashsb');
//
//    $auth_url = 'https://www.mashshare.net/oauth/login.html'; // production
//
//    $html = '<a href="'.$auth_url.'" id="mashsb_fb_auth" class="button button-primary">'.$button_label.'</a>';
//    //$html .= empty($mashsb_options[$args['id']]) ? $verify_button : '';
//    $html .= '&nbsp; <input type="text" class="medium-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
//    $html .= '&nbsp; <input type="hidden" class="medium-text" id="mashsb_settings[expire_' . $args['id'] . ']" name="mashsb_settings[expire_' . $args['id'] . ']" value="' . esc_attr( stripslashes( $expire ) ) . '"/>';
//    $html .= '<div class="token_status">'
//            . '<span id="mashsb_expire_token_status"></span>'
//            . '<span id="mashsb_token_notice"></span>'
//            . '</div>';
//    
//echo $html;
//    
//}

/**
 * Test facebook api and check if site is rate limited
 * 
 * @global array $mashsb_options
 * @return string
 */
function mashsb_ratelimit_callback() {
        global $mashsb_options;


        if( !mashsb_is_admin_page() || !isset( $mashsb_options['debug_mode'] ) || !mashsb_curl_installed() ) {
            return '';
        }
        // Test open facebook api endpoint
        $url = 'http://graph.facebook.com/?id=http://www.google.com';
        $curl_handle = curl_init();
        curl_setopt( $curl_handle, CURLOPT_URL, $url );
        curl_setopt( $curl_handle, CURLOPT_CONNECTTIMEOUT, 2 );
        curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, 1 );
        $buffer = curl_exec( $curl_handle );
        curl_close( $curl_handle );
        echo '<div style="min-width:500px;"><strong>Testing facebook public API <br><br>Result for google.com: </strong></div>';
        if( empty( $buffer ) ) {
            print "Nothing returned from url.<p>";
        } else {
            print '<div style="max-width:200px;">' . $buffer . '</div>';
        }
        
        if(empty($mashsb_options['fb_access_token_new'])){
            return;
        }
        
        // Test facebook api with access token
        $url = 'https://graph.facebook.com/v2.7/?id=http://www.google.com&access_token=' . $mashsb_options['fb_access_token_new'];
        $curl_handle = curl_init();
        curl_setopt( $curl_handle, CURLOPT_URL, $url );
        curl_setopt( $curl_handle, CURLOPT_CONNECTTIMEOUT, 2 );
        curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, 1 );
        $buffer = curl_exec( $curl_handle );
        curl_close( $curl_handle );
        echo '<br><strong>Testing facebook API <br>with access token<br><br>Result for google.com: </strong>';
        if( empty( $buffer ) ) {
            print "Nothing returned from url.<p>";
        } else {
            print '<div style="max-width:200px;">' . $buffer . '</div>';
        }
        
        
    }

/**
 * Helper function to determine if adverts and add-on ressources are hidden
 * 
 * @return bool
 */
function mashsb_hide_addons(){
    return apply_filters('mashsb_hide_addons', false);
}

/**
 * outout debug vars
 * @global array $mashsb_options
 */
function mashsb_get_debug_settings(){
   global $mashsb_options;
   if(isset($mashsb_options['debug_mode'])){
      echo '<div style="clear:both;">';
      var_dump($mashsb_options);
      echo 'Installed Networks:<br>';
      var_dump(get_option('mashsb_networks'));
      echo '</div>';
   }
}
