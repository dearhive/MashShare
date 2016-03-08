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
if ( !defined( 'ABSPATH' ) ) exit;


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
	$value = ! empty( $mashsb_options[ $key ] ) ? $mashsb_options[ $key ] : $default;
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

		$general_settings = is_array( get_option( 'mashsb_settings_general' ) )    ? get_option( 'mashsb_settings_general' )  	: array();
                $visual_settings = is_array( get_option( 'mashsb_settings_visual' ) )   ? get_option( 'mashsb_settings_visual' )   : array();
                $networks = is_array( get_option( 'mashsb_settings_networks' ) )   ? get_option( 'mashsb_settings_networks' )   : array();
		$ext_settings     = is_array( get_option( 'mashsb_settings_extensions' ) ) ? get_option( 'mashsb_settings_extensions' )	: array();
		$license_settings = is_array( get_option( 'mashsb_settings_licenses' ) )   ? get_option( 'mashsb_settings_licenses' )   : array();
                $addons_settings = is_array( get_option( 'mashsb_settings_addons' ) )   ? get_option( 'mashsb_settings_addons' )   : array();
                
		$settings = array_merge( $general_settings, $visual_settings, $networks, $ext_settings, $license_settings, $addons_settings);

		update_option( 'mashsb_settings', $settings);
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

	if ( false == get_option( 'mashsb_settings' ) ) {
		add_option( 'mashsb_settings' );
	}

	foreach( mashsb_get_registered_settings() as $tab => $settings ) {

		add_settings_section(
			'mashsb_settings_' . $tab,
			__return_null(),
			'__return_false',
			'mashsb_settings_' . $tab
		);

		foreach ( $settings as $option ) {

			$name = isset( $option['name'] ) ? $option['name'] : '';

			add_settings_field(
				'mashsb_settings[' . $option['id'] . ']',
				$name,
				function_exists( 'mashsb_' . $option['type'] . '_callback' ) ? 'mashsb_' . $option['type'] . '_callback' : 'mashsb_missing_callback',
				'mashsb_settings_' . $tab,
				'mashsb_settings_' . $tab,
				array(
					'id'      => isset( $option['id'] ) ? $option['id'] : null,
					'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
					'name'    => isset( $option['name'] ) ? $option['name'] : null,
					'section' => $tab,
					'size'    => isset( $option['size'] ) ? $option['size'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'std'     => isset( $option['std'] ) ? $option['std'] : '',
                                        'textarea_rows' => isset( $option['textarea_rows']) ? $option['textarea_rows'] : ''
				)
			);
		}

	}

	// Creates our settings in the options table
	register_setting( 'mashsb_settings', 'mashsb_settings', 'mashsb_settings_sanitize' );

}
add_action('admin_init', 'mashsb_register_settings');

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
		'general' => apply_filters( 'mashsb_settings_general',
			array(
                                'general_header' => array(
					'id' => 'general_header',
					'name' => '<strong>' . __( 'General settings', 'mashsb' ) . '</strong>',
					'desc' => __( ' ', 'mashsb' ),
					'type' => 'header'
				),
                                'mashsb_sharemethod' => array(
					'id' => 'mashsb_sharemethod',
					'name' =>  __( 'Share counts', 'mashsb' ),
					'desc' => __('<i>MashEngine</i> collects shares by calling social networks directly from your server. All shares are cached and stored in your database. <p> If you notice performance issues choose the classical <i>Sharedcount.com</i>. This needs an API key and is limited to 10.000 free requests daily but it is a little bit faster on requesting. After caching there is no performance benefit compared to MashEngine! Only mashEngine is able to collect twitter tweets.<p> <strong>MashEngine collects: </strong> Facebook, Twitter, LinkedIn, Google+, Pinterest, Stumbleupon, Buffer, VK. <strong>Default:</strong> MashEngine<p>Twitter count is powered by <a href="http://newsharecounts.com" target="_blank" rel="external nofollow">newsharecounts.com</a> You have to sign up with your Twitter account for this free service to get the twitter share count. Just visit the website, fill in your website domain and click on <i>Sign in with Twitter</i>. Thats it, nothing more to do!', 'mashsb'),
					'type' => 'select',
					'options' => array(
                                            'mashengine' => 'MashEngine (including twitter count)',
                                            'sharedcount' => 'Sharedcount.com (no twitter count)'
                                        )
     
				),
				
				'mashsharer_apikey' => array(
					'id' => 'mashsharer_apikey',
					'name' => __( 'Sharedcount.com API Key', 'mashsb' ),
					'desc' => __( 'Get it at <a href="https://www.sharedcount.com" target="_blank">SharedCount.com</a> for 10.000 free daily requests.', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium'
				),
				'mashsharer_sharecount_domain' => array(
					'id' => 'mashsharer_sharecount_domain',
					'name' => __( 'Sharedcount.com endpint', 'mashsb' ),
					'desc' => __( 'The SharedCount Domain your API key is configured to query. For example, free.sharedcount.com. This may update automatically if configured incorrectly.', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium',
					'std'  => 'free.sharedcount.com'
				),
                                'mashsharer_cache' => array(
					'id' => 'mashsharer_cache',
					'name' =>  __( 'Cache expiration', 'mashsb' ),
					'desc' => __('Shares are counted for every post after this time. Notice that Sharedcount.com uses his own cache (30 - 60min) so share count does not update immediately. Make sure to increase this value especially when you use MashEngine! Otherwise it could happen that some networks block your requests due to hammering their rate limits. <p><strong>Default: </strong>5 min. <strong>Recommended: </strong>30min and more', 'mashsb'),
					'type' => 'select',
					'options' => mashsb_get_expiretimes()
				),
                                'disable_sharecount' => array(
					'id' => 'disable_sharecount',
					'name' => __( 'Disable Sharecount', 'mashsb' ),
					'desc' => __( 'Use this when curl() is not supported on your server or share counts should not counted. This mode does not call the database and no SQL queries are generated. (Only less performance benefit. All db requests are cached) Default: false', 'mashsb' ),
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
                                        'std'  => 1000
				),
                                'fake_count' => array(
					'id' => 'fake_count',
					'name' => __( 'Fake Share counts', 'mashsb' ),
					'desc' => __( 'This number will be aggregated to all your share counts and is multiplied with a post specific factor. (Number of post title words divided with 10).', 'mashsb' ),
					'type' => 'text',
                                        'size' => 'medium'
				),
                                'load_scripts_footer' => array(
					'id' => 'load_scripts_footer',
					'name' => __( 'JS Load Order', 'mashsb' ),
					'desc' => __( 'Enable this to load all *.js files into footer. Make sure your theme uses the wp_footer() template tag in the appropriate place. Default: Disabled', 'mashsb' ),
					'type' => 'checkbox'
				),
                                'facebook_count' => array(
					'id' => 'facebook_count_mode',
					'name' => __( 'Facebook Count', 'mashsb' ),
					'desc' => __( 'Get the Facebook total count including "likes" and "shares" or get only the pure share count', 'mashsb' ),
					'type' => 'select',
                                        'options' => array(
                                            'shares' => 'Shares',
                                            'likes' => 'Likes',
                                            'total' => 'Total: likes + shares + comments'
                                            
                                        )
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
					'desc' => sprintf( __( 'Allow Mashshare to track plugin usage? Opt-in to tracking and our newsletter and immediately be emailed a <strong>10%% discount to the Mashshare shop</strong>, valid towards the <a href="%s" target="_blank">purchase of Add-Ons</a>. No sensitive data is tracked.', 'mashsb' ), 'https://www.mashshare.net/add-ons/?utm_source=' . substr( md5( get_bloginfo( 'name' ) ), 0, 10 ) . '&utm_medium=admin&utm_term=setting&utm_campaign=MASHSBUsageTracking') ,
					'type' => 'checkbox'
				),
                                'debug_header' => array(
					'id' => 'debug_header',
					'name' => '<strong>' . __( 'Debug', 'mashsb' ) . '</strong>',
					'desc' => __( ' ', 'mashsb' ),
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
					'desc' => __( '<strong>Note: </strong>Use this with caution only when you think your share counts are totally wrong. <strong>This will delete all your twitter counts. They can not be restored!</strong> Checking this and using the save button will delete all stored mashshare post_meta objects.<br>' . mashsb_delete_cache_objects(), 'mashsb' ),
					'type' => 'checkbox'
				),
                                
                                'debug_mode' => array(
					'id' => 'debug_mode',
					'name' => __( 'Debug mode', 'mashsb' ),
					'desc' => __( '<strong>Note: </strong> Check this box before you get in contact with our support team. This allows us to check publically hidden debug messages on your website. Do not forget to disable it thereafter! Enable this also to write daily sorted log files of requested share counts to folder <strong>/wp-content/plugins/mashsharer/logs</strong>. Please send us this files when you notice a wrong share count.' . mashsb_log_permissions(), 'mashsb' ),
					'type' => 'checkbox'
				)
                                
			)
		),
                'visual' => apply_filters('mashsb_settings_visual',
			array(
                            'style_header' => array(
					'id' => 'style_header',
					'name' => '<strong>' . __( 'Customize', 'mashsb' ) . '</strong>',
					'desc' => __( ' ', 'mashsb' ),
					'type' => 'header'
                                ),
				'mashsharer_round' => array(
					'id' => 'mashsharer_round',
					'name' => __( 'Round Shares', 'mashsb' ),
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
					'name' => __( 'Share count title', 'mashsb' ),
					'desc' => __( 'Change the text of the Share count title. <strong>Default:</strong> SHARES', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium',
                                        'std' => 'SHARES'
				),
				'mashsharer_hashtag' => array(
					'id' => 'mashsharer_hashtag',
					'name' => __( 'Twitter handle', 'mashsb' ),
					'desc' => __( '<strong>Optional:</strong> Using your twitter username, e.g. \'Mashshare\' results in via @Mashshare', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium'
				),
                                /*'share_color' => array(
					'id' => 'share_color',
					'name' => __( 'Share count color', 'mashsb' ),
					'desc' => __( 'Choose color of the share number in hex format, e.g. #7FC04C: ', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium',
                                        'std' => '#cccccc'
				),*/
                                'share_color' => array(
					'id' => 'share_color',
					'name' => __( 'Share count color', 'mashsb' ),
					'desc' => __( 'Choose color of the share number in hex format, e.g. #7FC04C: ', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium',
                                        'std' => '#cccccc'
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
                                array(
                                        'id' => 'button_width',
                                        'name' => __( 'Button width', 'mashpv' ),
                                        'desc' => __( 'Minimum with of the large share buttons in pixels', 'mashpv' ),
                                        'type' => 'number',
                                        'size' => 'normal',
                                        'std' => '177'
                                ), 
                                'mash_style' => array(
					'id' => 'mash_style',
					'name' => __( 'Share button style', 'mashsb' ),
					'desc' => __( 'Change visual appearance of the share buttons.', 'mashsb' ),
					'type' => 'select',
                                        'options' => array(
						'shadow' => 'Shadowed buttons',
                                                'gradiant' => 'Gradient colored buttons',
                                                'default' => 'Clean buttons - no effects'
					),
                                        'std' => 'default'
					
				),
                                'small_buttons' => array(
					'id' => 'small_buttons',
					'name' => __( 'Use small buttons', 'mashsb' ),
					'desc' => __( 'All buttons will be shown as pure small icons without any text on desktop and mobile devices all the time.<br><strong>Note:</strong> Disable this when you use the <a href="https://www.mashshare.net/downloads/mashshare-responsive/" target="_blank">responsive Add-On</a>', 'mashsb' ),
					'type' => 'checkbox'
				),
                                'subscribe_behavior' => array(
					'id' => 'subscribe_behavior',
					'name' => __( 'Subscribe button', 'mashsb' ),
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
                                /*'subscribe_content' => array(
					'id' => 'subscribe_content',
					'name' => __( 'Subscribe content', 'mashsb' ),
					'desc' => __( '<br>Define the content of the opening toggle subscribe window here. Use formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'mashsb' ),
					'type' => 'textarea',
					'textarea_rows' => '3',
                                        'size' => 15
				),*/
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
                                                'desc' => sprintf(__( 'This CSS is loaded only on AMP Project pages like yourwebsite.com/amp. <strong>Note: </strong> You need the WordPress <a href="%s" target="_blank">AMP Plugin</a> installed.', 'mashsb' ), 'https://wordpress.org/plugins/amp/'),
                                                'type' => 'textarea',
                                                'textarea_rows' => '3',
                                                'size' => 15
                                                ),
                                        )
				), 
                                /*'custom_css' => array(
					'id' => 'custom_css',
					'name' => __( 'Custom CSS', 'mashsb' ),
					'desc' => __( '<br>Use Mashshare custom styles here', 'mashsb' ),
					'type' => 'textarea',
					'size' => 15
                                        
				),*/
                                'location_header' => array(
					'id' => 'location_header',
					'name' => '<strong>' . __( 'Location & Position', 'mashsb' ) . '</strong>',
					'desc' => __( ' ', 'mashsb' ),
					'type' => 'header'
                                ),
                                'mashsharer_position' => array(
					'id' => 'mashsharer_position',
					'name' => __( 'Position', 'mashsb' ),
					'desc' => __( 'Position of Share Buttons. If this is set to <i>manual</i> use the shortcode function [mashshare] or use php code <br>&lt;?php echo do_shortcode("[mashshare]"); ?&gt; in template files. </p>You must activate the option "<strong>Load JS and CSS all over</strong>" if you experience issues with do_shortcode() and the buttons are not shown as expected. List of <a href="https://www.mashshare.net/faq/#Shortcodes" target="_blank">available shortcodes</a>', 'mashsb' ),
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
					'desc' => __( 'Select on which post_types the share buttons appear. This values will be ignored when position is specified "manual".', 'mashsb' ),
					'type' => 'posttypes'
				),
                                'loadall' => array(
					'id' => 'loadall',
					'name' => __( '<strong>IMPORTANT:</strong> Load JS and CSS all over', 'mashsb' ),
					'desc' => __( 'Enable this option if you are using </br> <strong>&lt;?php echo do_shortcode("[mashshare]"); ?&gt;</strong>.', 'mashsb' ),
					'type' => 'checkbox',
                                        'std' => 'false'
				),
                                'excluded_from' => array(
					'id' => 'excluded_from',
					'name' => __( 'Exclude from', 'mashsb' ),
					'desc' => __( 'Exclude share buttons from a list of post ids. Put in the post id separated by a comma, e.g. 23, 63, 114 ', 'mashsb' ),
					'type' => 'text',
                                        'size' => 'medium'
				),
                                'singular' => array(
					'id' => 'singular',
					'name' => __( 'Categories', 'mashsb' ),
					'desc' => __('Enable this checkbox to enable Mashshare on categories with multiple blogposts. <br><strong>Note: </strong> Post_types: "Post" must be enabled.','mashsb'),
					'type' => 'checkbox',
                                        'std' => '0'
				),
				'frontpage' => array(
					'id' => 'frontpage',
					'name' => __( 'Frontpage', 'mashsb' ),
					'desc' => __('Enable share buttons on frontpage','mashsb'),
					'type' => 'checkbox'
				),
                                /*'current_url' => array(
					'id' => 'current_url',
					'name' => __( 'Current Page URL', 'mashsb' ),
					'desc' => __('Force sharing the current page on non singular pages like categories with multiple blogposts','mashsb'),
					'type' => 'checkbox'
				),*/
                                'twitter_popup' => array(
					'id' => 'twitter_popup',
					'name' => __( 'Twitter Popup disable', 'mashsb' ),
					'desc' => __('Check this box if your twitter popup is openening twice. This happens sometimes when you are using any third party twitter plugin or the twitter SDK on your website.','mashsb'),
					'type' => 'checkbox',
                                        'std' => '0'
                                    
				),
                                /*'mashsb_shortcode_info' => array(
					'id' => 'mashsb_shortcode_info',
					'name' => __( 'Note:', 'mashsb' ),
					'desc' => __('Using the shortcode <strong>[mashshare]</strong> forces loading of dependacy scripts and styles on specific pages. It is overwriting any other location setting.','mashsb'),
					'type' => 'note',
                                        'label_for' => 'test'
                                    
				),*/
                                
                        )
		),
                 'networks' => apply_filters( 'mashsb_settings_networks',
                         array(
                                'services_header' => array(
					'id' => 'services_header',
					'name' => __( 'Select available networks', 'mashsb' ),
					'desc' => '',
					'type' => 'header'
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
					'name' => '<strong>' . __( 'Services', 'mashsb' ) . '</strong>',
					'desc' => __('Drag and drop the Share Buttons to sort them and specify which ones should be enabled. If you enable more networks than "Large Buttons", the plus sign is automatically added to the last visible large share button.s','mashsb'),
					'type' => 'networks',
                                        'options' => mashsb_get_networks_list()
                                 )
                         )
                ),
		'licenses' => apply_filters('mashsb_settings_licenses',
			array('licenses_header' => array(
					'id' => 'licenses_header',
					'name' => __( 'Activate your Add-Ons', 'mashsb' ),
					'desc' => '',
					'type' => 'header'
				),)
		),
                'extensions' => apply_filters('mashsb_settings_extension',
			array()
		),
                'addons' => apply_filters('mashsb_settings_addons',
			array(
                                'addons' => array(
					'id' => 'addons',
					'name' => __( '', 'mashsb' ),
					'desc' => __( '', 'mashsb' ),
					'type' => 'addons'
				)
                            //mashsb_addons_callback()
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

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = mashsb_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();
	$input = apply_filters( 'mashsb_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[$key] = apply_filters( 'mashsb_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[$key] = apply_filters( 'mashsb_settings_sanitize', $value, $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	if ( ! empty( $settings[$tab] ) ) {
		foreach ( $settings[$tab] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[$key] ) ) {
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
 * DEPRECATED Misc Settings Sanitization
 *
 * @since 1.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
/*function mashsb_settings_sanitize_misc( $input ) {

	global $mashsb_options;*/

	/*if( mashsb_get_file_download_method() != $input['download_method'] || ! mashsb_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		mashsb_create_protection_files( true, $input['download_method'] );
	}*/

	/*if( ! empty( $input['enable_sequential'] ) && ! mashsb_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		MASHSB()->session->set( 'upgrade_sequential', '1' );

	}*/

	/*return $input;
}
add_filter( 'mashsb_settings_misc_sanitize', 'mashsb_settings_sanitize_misc' );
         * */

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
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function mashsb_get_settings_tabs() {

	$settings = mashsb_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'mashsb' );

        if( ! empty( $settings['visual'] ) ) {
		$tabs['visual'] = __( 'Visual', 'mashsb' );
	} 
        
        if( ! empty( $settings['networks'] ) ) {
		$tabs['networks'] = __( 'Social Networks', 'mashsb' );
	}  
        
	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Add-On Settings', 'mashsb' );
	}
	
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'mashsb' );
	}
        $tabs['addons'] = __( 'Get More Add-Ons', 'mashsb' );

	//$tabs['misc']      = __( 'Misc', 'mashsb' );

	return apply_filters( 'mashsb_settings_tabs', $tabs );
}

       /*
	* Retrieve a list of possible expire cache times
	*
	* @since  2.0.0
	* @change 
	*
	* @param  array  $methods  Array mit verfügbaren Arten
	*/

        function mashsb_get_expiretimes()
	{
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

        $networks = get_option('mashsb_networks');
	return apply_filters( 'mashsb_get_networks_list', $networks );
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
	//echo '<hr/>';
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

	$checked = isset( $mashsb_options[ $args[ 'id' ] ] ) ? checked( 1, $mashsb_options[ $args[ 'id' ] ], false ) : '';
	$html = '<input type="checkbox" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
			if( isset( $mashsb_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . ']" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
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

		if ( isset( $mashsb_options[ $args['id'] ] ) && $mashsb_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $mashsb_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="mashsb_settings[' . $args['id'] . ']"" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	endforeach;

	echo '<p class="description mashsb_hidden">' . $args['desc'] . '</p>';
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_gateways_callback( $args ) {
	global $mashsb_options;

	foreach ( $args['options'] as $key => $option ) :
		if ( isset( $mashsb_options['gateways'][ $key ] ) )
			$enabled = '1';
		else
			$enabled = null;

		echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . ']"" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
	endforeach;
}

/**
 * Dropdown Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_gateway_select_callback($args) {
	global $mashsb_options;

	echo '<select name="mashsb_settings[' . $args['id'] . ']"" id="mashsb_settings[' . $args['id'] . ']">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $mashsb_options[ $args['id'] ] ) ? selected( $key, $mashsb_options[$args['id']], false ) : '';
		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	echo '</select>';
	echo '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
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

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label class="mashsb_hidden" class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : '40';
	$html = '<textarea class="large-text mashsb-textarea" cols="50" rows="' . $size . '" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

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

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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
function mashsb_missing_callback($args) {
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
 * @return void
 */
function mashsb_select_callback($args) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $name ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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
/*function mashsb_color_select_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}*/

function mashsb_color_select_callback( $args ) {
	global $mashsb_options;
        
        if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<strong>#:</strong><input type="text" style="max-width:80px;border:1px solid #' . esc_attr( stripslashes( $value ) ) . ';border-right:20px solid #' . esc_attr( stripslashes( $value ) ) . ';" id="mashsb_settings[' . $args['id'] . ']" class="medium-text ' . $args['id'] . '" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';

	$html .= '</select>';
	$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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
	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor( stripslashes( $value ), 'mashsb_settings_' . $args['id'], array( 'textarea_name' => 'mashsb_settings[' . $args['id'] . ']', 'textarea_rows' => $args['textarea_rows'] ) );
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text mashsb-richeditor" rows="10" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[$args['id']];
	else
		$value = isset($args['std']) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text mashsb_upload_field" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="mashsb_settings_upload_button button-secondary" value="' . __( 'Upload File', 'mashsb' ) . '"/></span>';
	$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="mashsb-color-picker" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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
if ( ! function_exists( 'mashsb_license_key_callback' ) ) {
	function mashsb_license_key_callback( $args ) {
		global $mashsb_options;

		if ( isset( $mashsb_options[ $args['id'] ] ) )
			$value = $mashsb_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'mashsb' ) . '"/>';
                        $html .= '<span style="font-weight:bold;color:green;"> License key activated! </span> <p style="color:green;font-size:13px;"> You´ll get updates for this Add-On automatically!</p>';
                } else {
                    $html .= '<span style="color:red;"> License key not activated!</span style=""><p style="font-size:13px;font-weight:bold;">You´ll get no important security and feature updates for this Add-On!</p>';
                }
		$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

                wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );
                
		echo $html;
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
        * 
        *                                   array(
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
                                                    'name' => 'Subscribe to us',
                                                    'name2' => 'Subscribe'
                                                )
                                            )
        */

       ob_start();
        ?>
        <p class="description"><?php echo $args['desc']; ?></p>
        <table id="mashsb_network_list" class="wp-list-table fixed posts">
		<thead>
			<tr>
				<th scope="col" style="padding: 15px 10px;"><?php _e( 'Social Networks', 'mashsb' ); ?></th>
                                <th scope="col" style="padding: 15px 10px;"><?php _e( 'Enable', 'mashsb' ); ?></th>
                                <th scope="col" style="padding: 15px 10px;"><?php _e( 'Custom name', 'mashsb' ); ?></th>
			</tr>
		</thead>        
        <?php

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
                        echo '<tr id="mashsb_list_' . $key . '" class="mashsb_list_item">';
			if( isset( $mashsb_options[$args['id']][$key]['status'] ) ) { $enabled = 1; } else { $enabled = NULL; }
                        if( isset( $mashsb_options[$args['id']][$key]['name'] ) ) { $name = $mashsb_options[$args['id']][$key]['name']; } else { $name = NULL; }

                        echo '<td class="mashicon-' . strtolower($option) . '"><span class="icon"></span><span class="text">' . $option . '</span></td>
                        <td><input type="hidden" name="mashsb_settings[' . $args['id'] . '][' . $key . '][id]" id="mashsb_settings[' . $args['id'] . '][' . $key . '][id]" value="' . strtolower($option) .'"><input name="mashsb_settings[' . $args['id'] . '][' . $key . '][status]" id="mashsb_settings[' . $args['id'] . '][' . $key . '][status]" type="checkbox" value="1" ' . checked(1, $enabled, false) . '/><td>
                        <input type="text" class="medium-text" id="mashsb_settings[' . $args['id'] . '][' . $key . '][name]" name="mashsb_settings[' . $args['id'] . '][' . $key . '][name]" value="' . $name .'"/>
                        </tr>';
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

		if ( isset( $mashsb_options[ $args['id'] ] ) )
			$value = $mashsb_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text ' . $args['id'] . '" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	
		$html .= '<input type="submit" class="button-secondary mashsb_upload_image" name="' . $args['id'] . '_upload" value="' . __( 'Select Image',  'mashsb' ) . '"/>';
		
		$html .= '<label class="mashsb_hidden" for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

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

function mashsb_posttypes_callback ($args){
  global $mashsb_options;
  $posttypes = get_post_types();

  //if ( ! empty( $args['options'] ) ) {
  if ( ! empty( $posttypes ) ) {
		//foreach( $args['options'] as $key => $option ):
                foreach( $posttypes as $key => $option ):
			if( isset( $mashsb_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . ']" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
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

function mashsb_note_callback ($args){
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

function mashsb_add_content_callback($args){
    	global $mashsb_options;

        $html = '<div id="mashtabcontainer" class="tabcontent_container"><ul class="mashtabs" style="width:99%;max-width:500px;">';
            foreach ( $args['options'] as $option => $name ) :
                    $html .= '<li class="mashtab" style="float:left;margin-right:4px;"><a href="#'.$name['id'].'">'.$name['name'].'</a></li>';
            endforeach;
        $html .= '</ul>';
        $html .= '<div class="mashtab-container">';
            foreach ( $args['options'] as $option => $name ) :
                    $value = isset($mashsb_options[$name['id']]) ? $mashsb_options[ $name['id']] : '';
                    $textarea = '<textarea class="large-text mashsb-textarea" cols="50" rows="15" id="mashsb_settings['. $name['id'] .']" name="mashsb_settings['.$name['id'].']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
                    $html .= '<div id="'.$name['id'].'" style="max-width:500px;"><span style="padding-top:60px;display:block;">' . $name['desc'] . '</span><br>' . $textarea . '</div>';
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
if ( !function_exists('mashsb_renderhr_callback') ) {
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

function numberServices(){
    $number = 1;
    $array = array();
    while ($number <= count(mashsb_get_networks_list())){
        $array[] = $number++; 

    }
    $array['all'] = __('All Services');
    return apply_filters('mashsb_return_services', $array);
}

/* Purge the Mashshare 
 * database MASHSB_TABLE
 * 
 * @since 2.0.4
 * @return string
 */

function mashsb_delete_cache_objects(){
    global $mashsb_options, $wpdb;
    if (isset($mashsb_options['delete_cache_objects'])){
        //$sql = "TRUNCATE TABLE " . MASHSB_TABLE;
        //require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        //$wpdb->query($sql);
        delete_post_meta_by_key( 'mashsb_timestamp' );
        delete_post_meta_by_key( 'mashsb_shares' ); 
        delete_post_meta_by_key( 'mashsb_jsonshares' );
        return ' <strong style="color:red;">' . __('DB cache deleted! Do not forget to uncheck this box for performance increase after doing the job.', 'mashsb') . '</strong> ';
    }
}

/* returns Cache Status if enabled or disabled
 *
 * @since 2.0.4
 * @return string
 */

function mashsb_cache_status(){
    global $mashsb_options;
    if (isset($mashsb_options['disable_cache'])){
        return ' <strong style="color:red;">' . __('Transient Cache disabled! Enable it for performance increase.' , 'mashsb') . '</strong> ';
    }
}

/* Permission check if logfile is writable
 *
 * @since 2.0.6
 * @return string
 */

function mashsb_log_permissions(){
    global $mashsb_options;
    if (!MASHSB()->logger->checkDir() ){
        return '<br><strong style="color:red;">' . __('Log file directory not writable! Set FTP permission to 755 or 777 for /wp-content/plugins/mashsharer/logs/', 'mashsb') . '</strong> <br> Read here more about <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">file permissions</a> ';
    }
}
