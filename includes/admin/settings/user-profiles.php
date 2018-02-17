<?php

// Queue up our profile field functions
add_action( 'show_user_profile', 'mashsb_render_user_profiles' );
add_action( 'edit_user_profile', 'mashsb_render_user_profiles' );
add_action( 'personal_options_update', 'mashsb_save_user_profiles' );
add_action( 'edit_user_profile_update', 'mashsb_save_user_profiles' );

/**
 * Render the user profile settings
 * 
 * @param array $user
 * @return string html
 */
function mashsb_render_user_profiles( $user ) {
    
    $html = '<h3>' . __( 'MashShare Social Media Integration', 'mashsb' ) . '</h3>' .
            '<table class="form-table">' .
            '<tr>' .
            '<th><label for="twitter">' . __( 'Twitter Username', 'mashsb' ) . '</label></th>' .
            '<td>' .
            '<input type="text" name="mashsb_twitter_handle" id="mashsb_twitter_handle" value="' . esc_attr( get_the_author_meta( 'mashsb_twitter_handle', $user->ID ) ) . '" class="regular-text" />' .
            '<br /><span class="description">' . __( 'Your Twitter username (without the @ symbol)', 'mashsb' ) . '</span>' .
            '</tr>' .
            '<th><label for="mashsb_fb_author_url">' . __( 'Facebook Author URL', 'mashsb' ) . '</label></th>' .
            '<td>' .
            '<input type="text" name="mashsb_fb_author_url" id="mashsb_fb_author_url" value="' . esc_attr( get_the_author_meta( 'mashsb_fb_author_url', $user->ID ) ) . '" class="regular-text" />' .
            '<br /><span class="description">' . __( 'URL to your Facebok profile.', 'mashsb' ) . '</span>' .
            '</td>' .
            '</tr>' .
            '</table>';
    
    if( mashsb_show_meta_box() ){
        echo $html;
    }
}

/**
 * Save user profile
 * 
 * @param type $user_id
 * @return boolean
 */
function mashsb_save_user_profiles( $user_id ) {

    if( !current_user_can( 'edit_user', $user_id ) )
        return false;

    update_user_meta( $user_id, 'mashsb_twitter_handle', $_POST['mashsb_twitter_handle'] );
    update_user_meta( $user_id, 'mashsb_fb_author_url', $_POST['mashsb_fb_author_url'] );
}
