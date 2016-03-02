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

