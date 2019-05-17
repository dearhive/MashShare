<?php

/**
 * Sharecount functions
 * Get the share count from the service sharedcount.com
 *
 * @package     MASHSB
 * @subpackage  Functions/sharedcount
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.9
 */
class mashsbSharedcount {

    private $url, $timeout;
    private $http_scheme_url;
    private $https_scheme_url;

    function __construct( $url, $timeout = 10, $apikey = '' ) {
        global $mashsb_options;
        
        // Uncomment for testing
        //$url = 'https://google.com';

        // remove http and https
        $url_host_path = preg_replace( "(^https?://)", "", $url );

        // build new urls
        $this->http_scheme_url  = rawurlencode( 'http://' . $url_host_path );
        $this->https_scheme_url = rawurlencode( 'https://' . $url_host_path );

        $this->url     = rawurlencode( $url );
        $this->timeout = $timeout;
        $this->apikey  = trim( $apikey );
    }

    /**
     * Collect shares for facebook and twitter (Twitter not supported any longer)
     * @global array $mashsb_options
     * @return type
     */
    public function getFBTWCounts() {
        global $mashsb_options;

        isset( $mashsb_options['facebook_count_mode'] ) ? $fb_mode = $mashsb_options['facebook_count_mode'] : $fb_mode = '';

        $sharecounts = $this->get_sharedcount();

        if( !$sharecounts ) {
            $this->sharecount        = new stdClass;
            $this->sharecount->total = 0;
            return $this->sharecount;
        }
        $counts = array('shares' => array(), 'total' => 0);
        switch ( $fb_mode ) {
            case $fb_mode === 'likes':
                $counts['shares']['fb']       = $sharecounts['Facebook']['like_count'];
                $counts['shares']['fb_https'] = $sharecounts['https']['Facebook']['like_count'];

                break;
            case $fb_mode === 'total':
                $counts['shares']['fb']       = $sharecounts['Facebook']['total_count'];
                $counts['shares']['fb_https'] = $sharecounts['https']['Facebook']['total_count'];
                break;
            default:
                $counts['shares']['fb']       = $sharecounts['Facebook']['share_count'];
                $counts['shares']['fb_https'] = $sharecounts['https']['Facebook']['share_count'];
        }
        $counts['shares']['tw'] = $sharecounts['Twitter'];



        foreach ( $counts['shares'] as $mashsbcounts => $sharecount ) {
            $counts['total'] += ( int ) $sharecount;
        }

        mashdebug()->error( "sharedcount.com getFBTWCounts: " . $counts['total'] );

        $totalArr  = array('total' => $counts['total']);
        $objMerged = ( object ) array_merge( ( array ) $sharecounts, ( array ) $totalArr );
        return $objMerged;
    }

    /* Only used when mashshare-networks is enabled */

    function getAllCounts() {
        global $mashsb_options;

        isset( $mashsb_options['facebook_count_mode'] ) ? $fb_mode = $mashsb_options['facebook_count_mode'] : $fb_mode = '';

        $sharecounts = $this->get_sharedcount();
        if( !$sharecounts ) {
            $this->sharecount        = new stdClass;
            $this->sharecount->total = 0;
            return $this->sharecount;
        }

        $counts = array('shares' => array(), 'total' => 0);
        switch ( $fb_mode ) {
            case $fb_mode === 'likes':
                $counts['shares']['fb']       = $sharecounts['Facebook']['like_count'];
                $counts['shares']['fb_https'] = $sharecounts['https']['Facebook']['like_count'];
                break;
            case $fb_mode === 'total':
                $counts['shares']['fb']       = $sharecounts['Facebook']['total_count'];
                $counts['shares']['fb_https'] = $sharecounts['https']['Facebook']['total_count'];
                break;
            default:
                $counts['shares']['fb']       = $sharecounts['Facebook']['share_count'];
                $counts['shares']['fb_https'] = isset($sharecounts['https']['Facebook']['share_count']) ? $sharecounts['https']['Facebook']['share_count'] : '';
        }
        isset( $sharecounts['Twitter'] ) ? $counts['shares']['tw']        = $sharecounts['Twitter'] : $counts['shares']['tw']        = 0;
        isset( $sharecounts['GooglePlusOne'] ) ? $counts['shares']['gp']        = $sharecounts['GooglePlusOne'] : $counts['shares']['gp']        = 0;
        isset( $sharecounts['LinkedIn'] ) ? $counts['shares']['li']        = $sharecounts['LinkedIn'] : $counts['shares']['li']        = 0;
        isset( $sharecounts['StumbleUpon'] ) ? $counts['shares']['st']        = $sharecounts['StumbleUpon'] : $counts['shares']['st']        = 0;
        isset( $sharecounts['Pinterest'] ) ? $counts['shares']['pin']       = $sharecounts['Pinterest'] : $counts['shares']['pin']       = 0;
        isset( $sharecounts['https']['Pinterest'] ) ? $counts['shares']['pin_https'] = $sharecounts['https']['Pinterest'] : $counts['shares']['pin_https'] = 0;



        $total = 0;
        foreach ( $counts['shares'] as $totalcount ) {
            $total += ( int ) $totalcount;
        }
        $totalArr  = array('total' => $total);
        $objMerged = ( object ) array_merge( ( array ) $sharecounts, ( array ) $totalArr );
        mashdebug()->info( "sharedcount.com getAllCounts: " . $counts['total'] );
        return $objMerged;
    }

    /**
     * 
     * @global array $mashsb_options
     * @param type $domain
     * @return int
     */
//    function update_sharedcount_domain( $domain = false ) {
//        global $mashsb_options;
//        if( !$domain ) {
//            try {
//                $domain_obj = $this->_curl( 'http://' . $mashsb_options["mashsharer_sharecount_domain"] . "/account?apikey=" . $this->apikey );
//                $domain     = $domain_obj["domain"];
//            } catch ( Exception $e ) {
//                mashdebug()->error( "error: " . $domain_obj );
//                return 0;
//            }
//        }
//        $mashsb_options["mashsharer_sharecount_domain"] = $domain;
//        update_option( 'mashsb_settings', $mashsb_options );
//        return 1;
//    }

    private function _curl( $url ) {
        $curl         = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 5 );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 5 ); //timeout in seconds
        $curl_results = curl_exec( $curl );
        curl_close( $curl );
        return json_decode( $curl_results, true );
    }

    /**
     * Get share count from sharedcount.com API
     * @global array $mashsb_options
     * @return mixed array | int
     */
    function get_sharedcount() {

        global $mashsb_options;

        if( empty( $this->apikey ) ) {
            return 0; //quit early if there's no API key.
        }

        try {
            $counts      = array();
            $httpsShares = array();

            if( isset( $mashsb_options['cumulate_http_https'] ) ) {
                $counts      = $this->_curl( 'https://api.sharedcount.com/v1.0/?url=' . $this->http_scheme_url . "&apikey=" . $this->apikey );
                $httpsShares = $this->_curl( 'https://api.sharedcount.com/v1.0/?url=' . $this->https_scheme_url . "&apikey=" . $this->apikey );
            } else {
                $counts = $this->_curl( 'https://api.sharedcount.com/v1.0/?url=' . $this->url . "&apikey=" . $this->apikey );
            }

            if( isset( $counts["Error"] ) && isset( $counts['Domain'] ) && $counts["Type"] === "domain_apikey_mismatch" ) {
                return 0;
            } else if( isset( $counts["Error"] ) && isset( $counts['Type'] ) && $counts['Type'] === 'invalid_api_key' ) {
                return 0;
            }

            $tweets = $this->getTwitterShares();
            
            $counts = is_array( $counts ) ? array_merge( $counts, array('Twitter' => $tweets) ) : array('Twitter' => $tweets);

            $counts['https'] = $httpsShares;

            return $counts;
        } catch ( Exception $e ) {
            mashdebug()->error( "error: " . $e );
            MASHSB()->logger->info( 'ERROR: Curl()' . $e );
            return 0;
        }
        mashdebug()->error( "error2: " . $counts );
        MASHSB()->logger->info( 'ERROR 2: Curl()' . $counts );
        return 0;
    }

    /**
     * Get twitter tweet count if social network add-on is installed
     * @return int
     */
    private function getTwitterShares() {

        if( class_exists( 'mashnetTwitter' ) ) {
            $twitter = new mashnetTwitter( $this->url );
            
            $tweets = $twitter->getTwitterShares();
            
            return empty( $tweets ) ? 0 : $tweets;
        }
        return 0;
    }

}

?>