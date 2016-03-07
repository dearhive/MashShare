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
    if(function_exists('curl_init')){
        return true;
    }
    
    return false;
}

