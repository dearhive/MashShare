<?php

/*
 * Register the meta boxes
 * Used by MASHSB_RWMB class
 * 
 * @package MASHSB
 *
 * @3.0.0
 */

/**
 * Check if meta boxes are shown for a specific user role and 
 * Show meta box when a specific user role is not specified
 * 
 * @global array $mashsb_options
 * @return bool true when meta boxes should should be visible for a specific user role
 */
function mashsb_show_meta_box(){
    global $mashsb_options, $wp_roles;
    
    $visibility = !empty( $mashsb_options['user_roles_for_sharing_options']) ? $mashsb_options['user_roles_for_sharing_options'] : false;
        if($visibility && in_array('disable', $visibility)){
        return false;
    }
    
    // Show meta boxes per default in any case when user roles are not defined
    if(empty($mashsb_options) || !isset($mashsb_options['user_roles_for_sharing_options'])){
        return true;
    }
    
    // Get user roles and plugin settings
    $user = wp_get_current_user();

    // Loop through user roles
    foreach($user->roles as $role) {
        // Rule exists and it is set
        if( isset( $mashsb_options["user_roles_for_sharing_options"] ) && in_array( str_replace( ' ', null, strtolower( $role ) ), $mashsb_options["user_roles_for_sharing_options"] ) ) {
            // Garbage collection
            unset($user);
            return true;
        }
    }
    
    unset ($user);
    return false;
}

add_filter( 'mashsb_rwmb_meta_boxes', 'mashsb_meta_boxes' );
function mashsb_meta_boxes( $meta_boxes ) {
    global $mashsb_options, $post;
    $prefix = 'mashsb_';
    $post_types = isset( $mashsb_options['post_types'] ) ? $mashsb_options['post_types'] : array();
    foreach ( $post_types as $key => $value ):
        $post_type[] = $key;
    endforeach;
    $post_type[] = 'post';
    $post_type[] = 'page';
    //echo "<pre>";
//    echo(var_dump($post_type));
//        echo "</pre>";

    $twitter_handle = isset( $mashsb_options['mashsharer_hashtag'] ) ? $mashsb_options['mashsharer_hashtag'] : '';
    
    
    // Do not show meta boxes
    if( !mashsb_show_meta_box() ) {
        return apply_filters( 'mashsb_meta_box_settings', $meta_boxes, 10, 0 );
    }

    // Setup our meta box using an array
    $meta_boxes[0] = array(
        'id' => 'mashsb_meta',
        'title' => 'MashShare Social Sharing Options',
        'pages' => $post_type,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            // Setup the social media image
            array(
                'name' => '<span class="mashicon mashicon-share"></span> ' . __( 'Social Media Image', 'mashsb' ),
                'desc' => __( 'Optimal size for post shared images on Facebook, Google+ and LinkedIn is 1200px x 630px. Aspect ratio 1.9:1', 'mashsb' ),
                'id' => $prefix . 'og_image',
                'type' => 'image_advanced',
                'clone' => false,
                'max_file_uploads' => 1,
                'class' => 'mashsb-og-image'
            ),
            // Setup the social media title
            array(
                'name' => '<span class="mashicon mashicon-share"> </span> ' . __( 'Social Media Title', 'mashsb' ),
                'desc' => __( 'This title is used by the open graph meta tag og:title and will be used when users share your content on Facebook, LinkedIn, or Google+. Leave this blank to use ', 'mashsb' ) . (mashsb_yoast_active() ? __( 'Yoast Facebook / SEO title', 'mashsb' ) : 'the post title'),
                'id' => $prefix . 'og_title',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-og-title'
            ),
            // Setup the social media description
            array(
                'name' => '<span class="mashicon mashicon-share"></span> ' . __( 'Social Media Description', 'mashsb' ),
                'desc' => __( 'This description is used by the open graph meta tag og:description and will be used when users share your content on Facebook, LinkedIn, and Google Plus. Leave this blank to use ', 'mashsb' ) . (mashsb_yoast_active() ? __( 'Yoast Facebook open graph description or the post excerpt.', 'mashsb' ) : ' the post excerpt.'),
                'id' => $prefix . 'og_description',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-og-desc'
            ),
            array(
                'name' => 'divider',
                'id' => 'divider',
                'type' => 'divider'
            ),
            // Setup the pinterest optimized image
            array(
                'name' => '<span class="mashicon mashicon-pinterest"></span> ' . __( 'Pinterest Image', 'mashsb' ) . '<a class="mashsb-helper" href="#"></a><div class="mashsb-message" style="display: none;">'.sprintf(__('Get the <a href="%s" target="_blank" rel="noopener">Network Add-On</a> to make use of the Pinterest Features','mashsb'),'https://www.mashshare.net/pricing/?utm_source=meta_box&utm_medium=core_plugin&utm_campaign=pinterest_helper').'</div>',
                'desc' => __( 'Pinned images need to be more vertical than horizontal in orientation. Use an aspect ratio of 2:3 to 1:3.5 and a minimum width of 600 pixels. So an image that is 600 pixels wide should be between 900 and 2100 pixels tall.', 'mashsb' ),
                'id' => $prefix . 'pinterest_image',
                'type' => 'image_advanced',
                'clone' => false,
                'max_file_uploads' => 1,
                'class' => 'mashsb-pinterest-image'
            ),
            // Setup the pinterest description
            array(
                'name' => '<span class="mashicon mashicon-pinterest"></span> ' . __('Pinterest Description', 'mashsb' ) . '<a class="mashsb-helper" href="#"></a><div class="mashsb-message" style="display: none;">'.sprintf(__('Get the <a href="%s" target="_blank" rel="noopener">Network Add-On</a> to make use of the Pinterest Features','mashsb'),'https://www.mashshare.net/pricing/?utm_source=meta_box&utm_medium=core_plugin&utm_campaign=pinterest_helper').'</div>',
                'desc' => __( 'Place a customized message that will be used when this post is shared on Pinterest. Leave this blank to use the ', 'mashsb' ) . (mashsb_yoast_active() ? __( 'Yoast SEO title', 'mashsb' ) : __( 'the post title', 'mashsb' )),
                'id' => $prefix . 'pinterest_description',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-pinterest-desc'
            ),
            // Setup the Custom Tweet box
            array(
                'name' => '<span class="mashicon mashicon-twitter"></span> ' . __('Custom Tweet','mashsb'),
                'desc' =>  mashsb_twitter_desc(),
                'id' => $prefix . 'custom_tweet',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-custom-tweet'
            ),
            array(
                'id' => $prefix . 'position',
                'name' => __('Share Button Position','mashsb'),
                'type' => 'select',
                'placeholder' => __('Use Global Setting','mashsb'),
                'before' => '<div style="max-width:250px;float:left;">',
                'after' => '</div>',
                'options' => array(
                    'disable' => __('Disable Automatic Buttons','mashsb'),                      
                    'before' => __('Above Content','mashsb'),
                    'after' => __('Below Content','mashsb'),
                    'both' => __('Above & Below Content','mashsb'),
                    )
            ),
            // Setup the og:type
            array(
                'name' => '<span class="mashicon mashicon-share"> </span> ' . __( 'Open Graph Type', 'mashsb' ),
                'desc' => __( 'This is used by the open graph meta tag og:type. Leave this blank to use the default value "article".  ', 'mashsb' ),
                'id' => $prefix . 'og_type',
                'type' => 'text',
                'clone' => false,
                'class' => 'mashsb-og-type',
                'before' => '<div style="max-width:250px;float:left;">',
                'after' => '</div>',
            ),
            array(
                'helper'=> '<a class="mashsb-helper" href="#" style="margin-left:-4px;"></a><div class="mashsb-message" style="display: none;">'.__('Validate open graph meta tags on your site. Incorrect data can result in wrong share description, title or images and should be fixed! In the facebook debugger click the link "Fetch new scrape information" to purge the facebook cache.','mashsb').'</div>',
                'id' => $prefix . 'validate_og',
                'before' => '<div style="max-width:250px;margin-top:45px;">',
                'after' => '</div>',
                'type' => 'validate_og'
            ),
            
            array(
                'name' => 'divider',
                'id' => 'divider',
                'type' => 'divider'
            ),
            array(
                'id' => $prefix . 'twitter_handle',
                'type' => 'hidden_data',
                'std' => $twitter_handle,
            ),
        )
    );

    return apply_filters( 'mashsb_meta_box_settings', $meta_boxes, 10, 0 );
}

/**
 * Check if Yoast is active
 *
 * @return boolean true when yoast is active
 */
function mashsb_yoast_active() {
    if( defined( 'WPSEO_VERSION' ) ) {
        return true;
    }
}


function mashsb_twitter_desc() {
    $str = "";
    if( mashsb_get_twitter_username() ) {
        $str .= __( 'Based on your username @', 'mashsb' ) . mashsb_get_twitter_username() . __( ' ,the shortened post url and the current content above', 'mashsb' );
    } else {
        $str .= __( 'Based on the shortened post url and the current content above', 'mashsb' );
    }
    $str .= __( ' your tweet has a maximum of 140 characters. ', 'mashsb' );
    if (!mashsb_yoast_active()){
        $str .= __( 'If this is left blank the post title will be used. ', 'mashsb' );
    }else{
        $str .= __( 'If this is left blank the Yoast Twitter Title or post title will be used. ', 'mashsb' );
    }

    return $str;
}
