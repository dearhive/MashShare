<?php

/*
 * Register the meta boxes
 * Used by MASHSB_RWMB class
 * 
 * @package MASHSB
 *
 * @2.5.6
 */

add_filter( 'mashsb_rwmb_meta_boxes', 'mashsb_meta_boxes' );

function mashsb_meta_boxes( $meta_boxes ) {
    global $mashsb_options;
    $prefix = 'mashsb_';
    $post_types = isset($mashsb_options['post_types']) ? $mashsb_options['post_types'] : array();
    foreach ( $post_types as $key => $value ):
        $post_type[] = $key;
    endforeach;
    $post_type[] = 'post';
    $post_type[] = 'page';
    $twitter_handle = !empty($mashsb_options['mashsharer_hashtag']) ? $mashsb_options['mashsharer_hashtag'] : '';

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
                'name' => '<span class="mashicon mashicon-share"></span> Social Media Image',
                'desc' => 'Optimal size for post shared images on Facebook, Google+ and LinkedIn is 1200px x 630px. Aspect ratio 1.9:1',
                'id' => $prefix . 'og_image',
                'type' => 'image_advanced',
                'clone' => false,
                'max_file_uploads' => 1,
                'class' => 'mashsb-og-image'
            ),
            // Setup the social media title
            array(
                'name' => '<span class="mashicon mashicon-share"></span> Social Media Title',
                'desc' => 'This title will fill up the open graph meta tag og:title and will be used when users share your content on Facebook, LinkedIn, or Google+. Leave this blank to use the post title.',
                'id' => $prefix . 'og_title',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-og-title'
            ),
            // Setup the social media description
            array(
                'name' => '<span class="mashicon mashicon-share"></span> Social Media Description',
                'desc' => 'This description will fill up the open graph meta tag og:description and will be used when users share your content on Facebook, LinkedIn, and Google Plus.',
                'id' => $prefix . 'og_description',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-og-desc'
            ),

            // Setup the pinterest optimized image
            array(
                'name' => '<span class="mashicon mashicon-pinterest"></span> Pinterest Image',
                'desc' => 'Pinned images need to be more vertical than horizontal in orientation. Use an aspect ratio of 2:3 to 1:3.5 and a minimum width of 600 pixels. So an image that is 600 pixels wide should be between 900 and 2100 pixels tall.',
                'id' => $prefix . 'pinterest-image',
                'type' => 'image_advanced',
                'clone' => false,
                'max_file_uploads' => 1,
                'class' => 'mashsb-pinterest-image'
            ),
            // Setup the pinterest description
            array(
                'name' => '<span class="mashicon mashicon-pinterest"></span> Pinterest Description',
                'desc' => 'Place a customized message that will be used when this post is shared on Pinterest. Leave this blank to use the post title.',
                'id' => $prefix . 'pinterest_description',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-pinterest-desc'
            ),
            // Setup the Custom Tweet box
            array(
                'name' => '<span class="mashicon mashicon-twitter"></span> Custom Tweet',
                'desc' => 'If this is left blank the post title will be used. ' . ($twitter_handle ? 'Based on your username (via ' . $twitter_handle . '), <span class="tweetLinkSection">a link being added,</span> and the current content above' : '<span ="tweetLinkSection">Based on a link being added, and</span> the current content above') . ', your tweet has <span class="counterNumber">140</span> characters remaining.',
                'id' => $prefix . 'custom_tweet',
                'type' => 'textarea',
                'clone' => false,
                'class' => 'mashsb-custom-tweet'
            ),
            array(
                'name' => 'divider',
                'id' => 'divider',
                'type' => 'divider'
            ),
        )
    );
    
    return apply_filters('mashsb_meta_box_settings', $meta_boxes, 10, 0);
}
