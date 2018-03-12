<?php

/**
 * Custom HTML field class.
 */
class MASHSB_RWMB_Validate_Og_Field extends MASHSB_RWMB_Field {

    /**
     * Get field HTML
     *
     * @param mixed $meta
     * @param array $field
     *
     * @return string
     */
    static function html( $meta, $field ) {
        global $post;
        $html = !empty( $field['std'] ) ? $field['std'] : '';
        if( !empty( $field['callback'] ) && is_callable( $field['callback'] ) ) {
            $html = call_user_func_array( $field['callback'], array($meta, $field) );
        }
        //return $html;
        //return get_permalink( $post->ID );
        return self::get_fb_debugger_url($post, $field);
    }

    /**
     * Link to the open graph debugger to check if open graph tags are valid
     * 
     * @global array $post
     * @return string
     */
    static function get_fb_debugger_url($post, $field) {
        //var_dump( $post );
        if( isset( $post ) && $post->post_status == "publish" ) {
            $url = get_permalink( $post->ID );
            return '<a href="https://developers.facebook.com/tools/debug/og/object?q=' . $url . '" target="_blank" rel="noopener" class="button-small"> Validate Open Graph data </a>' . $field['helper'];
        } else {
            return '';
        }
    }

}
