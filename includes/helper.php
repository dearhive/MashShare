<?php

/**
 * Echo string when debug mode is enabled
 * 
 * @param type $string
 */
function mashecho($string){
    if(MASHSB_DEBUG){
        echo $string;
    }
}

/**
 * Check if curl is installed
 * 
 * @return boolean true when it is installed
 */
function mashsb_curl_installed(){
    if(function_exists('curl_init') && function_exists('curl_multi_init') && function_exists('curl_multi_exec') ){
        return true;
    }
    
    return false;
}

/*function mashsb_is_amp_endpoint(){
    if (  function_exists( 'is_amp_endpoint' )){
        return is_amp_endpoint();
    }
}*/


/**
 * Remove http(s) on WP site info
 * 
 * @param type $string
 * @return type
 */
function mashsb_replace_http($string){
    if (empty($string)){
        return $string;
    }
    
    $a = str_replace('https://', '', $string);
    return str_replace('http://', '', $string);
}

function mashsb_share_buttons(){
    $content = '<li><a class="mashicon-facebook" target="_blank" href="https://www.facebook.com/sharer.php?u=https%3A%2F%2Fwww.mashshare.net%2F&display=popup&ref=plugin&src=like&app_id=449277011881884"><span class="icon"></span><span class="text">Share it</span></a></li>'.
               '<li><a class="mashicon-twitter" target="_blank" href="https://twitter.com/intent/tweet?hashtags=mashshare%2C&original_referer=http%3A%2F%2Fsrc.wordpress-develop.dev%2Fwp-admin%2Fadmin.php%3Fpage%3Dmashsb-settings%26tab%3Dgeneral&ref_src=twsrc%5Etfw&related=mashshare&text=I%20use%20MashShare%20- incredible%20great%20social%20media%20tool%20on%20my%20site%20'. mashsb_replace_http(get_bloginfo('wpurl')).'&tw_p=tweetbutton&url=https%3A%2F%2Fwww.mashshare.net%2F"><span class="icon"></span><span class="text">Tweet #mashshare</span></a></li>' .
               '<li><a class="mashicon-twitter" target="_blank" href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Fsrc.wordpress-develop.dev%2Fwp-admin%2Fadmin.php%3Fpage%3Dmashsb-settings%26tab%3Dgeneral&ref_src=twsrc%5Etfw&region=follow_link&screen_name=mashshare&tw_p=followbutton"><span class="icon"></span><span class="text">Follow @mashshare</span></a></li>';
               //'<li><a class="mashicon-twitter" target="_blank" href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Fsrc.wordpress-develop.dev%2Fwp-admin%2Fadmin.php%3Fpage%3Dmashsb-settings%26tab%3Dgeneral&ref_src=twsrc%5Etfw&region=follow_link&screen_name=renehermenau&tw_p=followbutton"><span class="icon"></span><span class="text">Follow @renehermenau</span></a></li>';
    return $content;
}

