<?php

/**
 * Scripts
 *
 * @package     MASHSB
 * @subpackage  AMP Functions
 * @copyright   Copyright (c) 2016, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

add_action( 'amp_post_template_css', 'mashsb_amp_load_css', 10 );

/**
 * Check if current page is AMP page
 * 
 * @return boolean
 */
function mashsb_is_amp_page(){
    // Defined in https://wordpress.org/plugins/amp/ is_amp_endpoint()
    
    if (  function_exists('is_amp_endpoint') && is_amp_endpoint()){
        return true;
    }
    return false;
}

/**
 * Load AMP (Accelerated Mobile Pages) CSS
 * 
 * @return string css
 */
function mashsb_amp_load_css() {
    global $mashsb_options;

    $share_color = !empty( $mashsb_options['share_color'] ) ? '.mashsb-count {color:' . $mashsb_options['share_color'] . '}' : '';
    $custom_css = isset( $mashsb_options['custom_css'] ) ? $mashsb_options['custom_css'] : '';
    $amp_css = isset( $mashsb_options['amp_css'] ) ? $mashsb_options['amp_css'] : '';
    
    $css = "@font-face {
  font-family: 'mashsb-font';
  src: url('" . MASHSB_PLUGIN_URL . "assets/css/fonts/mashsb-font.eot?62884501');
  src: url('" . MASHSB_PLUGIN_URL . "assets/css/fonts/mashsb-font.eot?62884501#iefix') format('embedded-opentype'),
       url('" . MASHSB_PLUGIN_URL . "assets/css/fonts/mashsb-font.woff?62884501') format('woff'),
       url('" . MASHSB_PLUGIN_URL . "assets/css/fonts/mashsb-font.ttf?62884501') format('truetype'),
       url('" . MASHSB_PLUGIN_URL . "assets/css/fonts/mashsb-font.svg?62884501#mashsb-font') format('svg');
  font-weight: normal;
  font-style: normal;
}";
    
    // Get default css file
    $css .= file_get_contents( MASHSB_PLUGIN_DIR . '/assets/css/mashsb-amp.css' );
    

    // add custom css
    $css .= $custom_css;

    // add AMP custom css
    $css .= $amp_css;

    // STYLES
    $css .= $share_color;

    if( !empty( $mashsb_options['border_radius'] ) && $mashsb_options['border_radius'] != 'default' ) {
        $css .= '
        [class^="mashicon-"], .onoffswitch-label, .onoffswitch2-label {
            border-radius: ' . $mashsb_options['border_radius'] . 'px;
        }';
    }
    if( !empty( $mashsb_options['mash_style'] ) && $mashsb_options['mash_style'] == 'gradiant' ) {
        $css .= '
    .mashsb-buttons a {
        background-image: -webkit-linear-gradient(bottom,rgba(0, 0, 0, 0.17) 0%,rgba(255, 255, 255, 0.17) 100%);
        background-image: -moz-linear-gradient(bottom,rgba(0, 0, 0, 0.17) 0%,rgba(255, 255, 255, 0.17) 100%);
        background-image: linear-gradient(bottom,rgba(0,0,0,.17) 0%,rgba(255,255,255,.17) 100%);}';
    }
    // Get css for small buttons
    $css .= '[class^="mashicon-"] .text, [class*=" mashicon-"] .text{
        text-indent: -9999px;
        line-height: 0px;
        display: block;
        } 
    [class^="mashicon-"] .text:after, [class*=" mashicon-"] .text:after {
        content: "";
        text-indent: 0;
        font-size:13px;
        display: block;
    }
    [class^="mashicon-"], [class*=" mashicon-"] {
        width:25%;
        text-align: center;
    }
    [class^="mashicon-"] .icon:before, [class*=" mashicon-"] .icon:before {
        float:none;
        margin-right: 0;
    }
    .mashsb-buttons a{
       margin-right: 3px;
       margin-bottom:3px;
       min-width: 0px;
       width: 41px;
    }

    .onoffswitch, 
    .onoffswitch-inner:before, 
    .onoffswitch-inner:after 
    .onoffswitch2,
    .onoffswitch2-inner:before, 
    .onoffswitch2-inner:after  {
        margin-right: 0px;
        width: 41px;
        line-height: 41px;
    }';
    // hide plus and subscribe button 
    // on AMP we disable js
    $css .= '.onoffswitch2, .onoffswitch{display:none}';

    // Hide subscribe button when it's not a link
    $css .= isset( $mashsb_options['subscribe_behavior'] ) && $mashsb_options['subscribe_behavior'] === 'content' ? '.mashicon-subscribe{display:none;}' : '';

    // Make sure the share buttons are not moving under the share count when decreasing width
    $css .= '.mashsb-buttons{display:table;}';

    // Float the second shares box
    $css .= '.secondary-shares{float:left;}';

    // Hide the view count
    $css .= '.mashpv{display:none;}';

    echo $css;
}