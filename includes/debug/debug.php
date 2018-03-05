<?php
    /**
     * Output debug notices in footer
     * @global type $mashsb_options
     */
    function mashsbOutputDebug() {
        global $mashsb_options, $mashsb_debug;
        
            if (empty($mashsb_debug)){
                return '';
            }

        if (current_user_can('install_plugins') && isset($mashsb_options['debug_mode'])) {
            echo '<div class="mash-debug" style="display:block;z-index:250000;font-size:12px;text-align:center;">';
            echo  'MashShare Debug Mode.<br><br>';
            foreach ($mashsb_debug as $key => $value){
                //echo $key . ' ' . date( 'H:m:s.u', time()). ' ' . $value . '<br />';
                echo $value . '<br />';
            }
            echo '</div>';
        }
    }
    add_action('wp_footer', 'mashsbOutputDebug', 100);
    
    
    
