<?php
    /**
     * Output debug notices in footer
     * @global type $mashsb_options
     */
    function mashsbOutputDebug() {
        global $mashsb_options, $mashsb_error;
        
            if (empty($mashsb_error)){
                return '';
            }

        if (current_user_can('install_plugins') && isset($mashsb_options['debug_mode'])) {
            echo '<div class="mash-debug" style="display:block;z-index:250000;font-size:11px;text-align:center;">';
            foreach ($mashsb_error as $key => $value){
                echo $key . ' ' . date( 'H:m:s.u', time()). ' ' . $value . '<br />';
            }
            echo '</div>';
        }
    }
    add_action('wp_footer', 'mashsbOutputDebug', 100);
    
    
    
