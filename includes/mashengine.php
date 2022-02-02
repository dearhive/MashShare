<?php

class mashengine {

   private $data;
   private $url;
   private $timeout;
   private $http_scheme_url;
   private $https_scheme_url;
   private $debug_notices;

   function __construct( $url, $timeout = 10 ) {
      // remove http and https
      $url_host_path = preg_replace( "(^https?://)", "", $url );
      // build new urls
      $this->http_scheme_url = rawurlencode( 'http://' . $url_host_path );
      $this->https_scheme_url = rawurlencode( 'https://' . $url_host_path );

      $this->timeout = $timeout;
      $this->url = rawurlencode( $url ); // Original URL
      //$this->url ='https://google.com';
   }

   /* Collect share count from all available networks */

   public function getALLCounts() {
      $this->data = new stdClass;
      $this->data->total = 0;
      $this->data->error = '';

      $data = $this->getSharesALL();
      return $data;
   }

   /* Collect share count from facebook and twitter */

   public function getFBTWCounts() {
      $this->data = new stdClass;
      $this->data->total = 0;
      $this->data->error = '';

      $data = $this->getSharesFBTW();
      return $data;
   }

   /* Build the multi_curl() crawler for facebook and twitter
    * 
    * @returns
    */

   public function getSharesFBTW() {
      global $mashsb_options;

      $fb_mode = isset( $mashsb_options['facebook_count_mode'] ) ? $mashsb_options['facebook_count_mode'] : 'total';

      $post_data = null;
      $headers = null;

//      $options = array(
//          CURLOPT_SSL_VERIFYPEER => FALSE,
//          CURLOPT_SSL_VERIFYHOST => FALSE
//      );
      
      $options = array();

      $RollingCurlX = new RollingCurlX( 4 );    // max 10 simultaneous downloads
      $RollingCurlX->setOptions( $options );

      switch ( $fb_mode ) {
         case $fb_mode === 'likes':
            //if( empty( $mashsb_options['fb_access_token_new'] ) ) {
            if( isset( $mashsb_options['cumulate_http_https'] ) ) {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
            } else {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
            }
            //} 
//                else {
//                    if( isset( $mashsb_options['cumulate_http_https'] ) ) {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->http_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->https_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
//                    } else {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
//                    }
//                }
            break;
         case $fb_mode === 'total':
            //if( empty( $mashsb_options['fb_access_token_new'] ) ) {
            if( isset( $mashsb_options['cumulate_http_https'] ) ) {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
            } else {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
            }
//                } else {
//                    if( isset( $mashsb_options['cumulate_http_https'] ) ) {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->http_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->https_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
//                    } else {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
//                    }
//                }
            break;
         default:
            //if( empty( $mashsb_options['fb_access_token_new'] ) ) {
            if( isset( $mashsb_options['cumulate_http_https'] ) ) {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
            } else {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
            }
//                } else {
//                    if( isset( $mashsb_options['cumulate_http_https'] ) ) {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->http_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->https_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
//                    } else {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
//                    }
//                }
      }
      if( isset( $mashsb_options['cumulate_http_https'] ) ) {
         if( class_exists( 'mashnetTwitter' ) ) {
            $RollingCurlX->addRequest( "https://counts.twitcount.com/counts.php?url=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('twitter'), $headers );
            $RollingCurlX->addRequest( "https://counts.twitcount.com/counts.php?url=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('twitter'), $headers );
         }
      } else {
         if( class_exists( 'mashnetTwitter' ) ) {
            $RollingCurlX->addRequest( "https://counts.twitcount.com/counts.php?url=" . $this->url, $post_data, array($this, 'getCount'), array('twitter'), $headers );
         }
      }

      // Fire and forget
      $RollingCurlX->execute();

      $data = $this->data;

      return $data;
   }

   /* Build the multi_curl() crawler for all networks
    * 
    * @returns
    */

   public function getSharesALL() {
      global $mashsb_options;

      $fb_mode = isset( $mashsb_options['facebook_count_mode'] ) ? $mashsb_options['facebook_count_mode'] : 'total';
      $post_data = null;
      $headers = null;

//      $options = array(
//          CURLOPT_SSL_VERIFYPEER => FALSE,
//          CURLOPT_SSL_VERIFYHOST => FALSE
//      );
      
      $options = array();

      $RollingCurlX = new RollingCurlX( 10 );    // max 10 simultaneous downloads
      $RollingCurlX->setOptions( $options );
      switch ( $fb_mode ) {
         case $fb_mode === 'likes':
            //if( empty( $mashsb_options['fb_access_token_new'] ) ) {
            if( isset( $mashsb_options['cumulate_http_https'] ) ) {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
            } else {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
            }
//                } else {
//                    if( isset( $mashsb_options['cumulate_http_https'] ) ) {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->http_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->https_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
//                    } else {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_likes'), $headers );
//                    }
//                }
            break;
         case $fb_mode === 'total':
            //if( empty( $mashsb_options['fb_access_token_new'] ) ) {
            if( isset( $mashsb_options['cumulate_http_https'] ) ) {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
            } else {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
            }
//                } else {
//                    if( isset( $mashsb_options['cumulate_http_https'] ) ) {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->http_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->https_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
//                    } else {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_total'), $headers );
//                    }
//                }
            break;
         default:
            //if( empty( $mashsb_options['fb_access_token_new'] ) ) {
            if( isset( $mashsb_options['cumulate_http_https'] ) ) {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
            } else {
               $RollingCurlX->addRequest( "http://graph.facebook.com/?id=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
            }
//                } else {
//                    if( isset( $mashsb_options['cumulate_http_https'] ) ) {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->http_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->https_scheme_url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
//                    } else {
//                        $RollingCurlX->addRequest( "https://graph.facebook.com/v2.7/?id=" . $this->url . '&access_token=' . sanitize_text_field( $mashsb_options['fb_access_token_new'] ), $post_data, array($this, 'getCount'), array('facebook_shares'), $headers );
//                    }
//                }
      }
      if( isset( $mashsb_options['cumulate_http_https'] ) ) {
         if( class_exists( 'mashnetTwitter' ) ) {
            $RollingCurlX->addRequest( "https://counts.twitcount.com/counts.php?url=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('twitter'), $headers );
            $RollingCurlX->addRequest( "https://counts.twitcount.com/counts.php?url=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('twitter'), $headers );
         }
         $RollingCurlX->addRequest( "https://api.pinterest.com/v1/urls/count.json?url=" . $this->http_scheme_url, $post_data, array($this, 'getCount'), array('pinterest'), $headers );
         $RollingCurlX->addRequest( "https://api.pinterest.com/v1/urls/count.json?url=" . $this->https_scheme_url, $post_data, array($this, 'getCount'), array('pinterest'), $headers );
      } else {
         if( class_exists( 'mashnetTwitter' ) ) {
            $RollingCurlX->addRequest( "https://counts.twitcount.com/counts.php?url=" . $this->url, $post_data, array($this, 'getCount'), array('twitter'), $headers );
         }
         $RollingCurlX->addRequest( "https://api.pinterest.com/v1/urls/count.json?url=" . $this->url, $post_data, array($this, 'getCount'), array('pinterest'), $headers );
      }

      $RollingCurlX->addRequest( "http://www.stumbleupon.com/services/1.01/badge.getinfo?url=" . $this->url, $post_data, array($this, 'getCount'), array('stumbleupon'), $headers );
      $RollingCurlX->addRequest( "https://api.bufferapp.com/1/links/shares.json?url=" . $this->url, $post_data, array($this, 'getCount'), array('buffer'), $headers );
      $RollingCurlX->addRequest( "https://vk.com/share.php?act=count&index=1&url=" . $this->url, $post_data, array($this, 'getCount'), array('vk'), $headers );

      $RollingCurlX->execute();

      $data = $this->data;

      // return the total count
      return $data;
   }

   /*
    * Callback function to get share counts 
    */

   function getCount( $data, $url, $request_info, $service, $time ) {
      global $mashsb_debug;
      $count = 0;
      $error = '';

      if( $data ) {
         switch ( $service[0] ) {
            // not used any longer. Keep it here for compatibility reasons and return share count
            case "facebook_likes":
               $data = json_decode( $data, true );
               $count = isset( $data['share']['share_count'] ) || array_key_exists( 'share_count', $data ) ? $data['share']['share_count'] : 0;
               if( isset( $data['error'] ) ) {                        // Probably rate limit exceed
                  $this->setRateLimitTransient();
               }
               break;
            case "facebook_shares":
               $data = json_decode( $data, true ); // return assoc array
               $count = isset( $data['share']['share_count'] ) || array_key_exists( 'share_count', $data ) ? $data['share']['share_count'] : 0;
               if( isset( $data['error'] ) ) {
                  // Probably rate limit exceed
                  $this->setRateLimitTransient();
               }
               break;
            case "facebook_total":
               $data = json_decode( $data, true );
               $share_count = isset( $data['share']['share_count'] ) || array_key_exists( 'share_count', $data ) ? $data['share']['share_count'] : 0;
               $comment_count = isset( $data['share']['comment_count'] ) || array_key_exists( 'comment_count', $data ) ? $data['share']['comment_count'] : 0;
               $count = $share_count + $comment_count;
               if( isset( $data['error'] ) ) {
                  // Probably rate limit exceed
                  $error = array('facebook_error' => $data['error']);
                  $this->setRateLimitTransient();
               }
               break;
            case "google":
               preg_match( "#window\.__SSR = {c: ([\d]+)#", $data, $matches );
               if( isset( $matches[0] ) )
                  $count = str_replace( 'window.__SSR = {c: ', '', $matches[0] );
               break;
            case "pinterest":
               $data = substr( $data, 13, -1 );
            case "linkedin":
            case "twitter":
               $data = json_decode( $data );
               $count = isset( $data->count ) ? $data->count : 0;
               break;
            case "stumbleupon":
               $data = json_decode( $data );
               isset( $data->result->views ) ? $count = $data->result->views : $count = 0;

               break;
            case "delicious":
               $data = json_decode( $data );
               $count = !empty( $data ) ? $data[0]->total_posts : 0;
               break;
            case "reddit":
               $data = json_decode( $data );
               $ups = 0;
               $downs = 0;
               foreach ( $data->data->children as $child ) {
                  $ups+= ( int ) $child->data->ups;
                  $downs+= ( int ) $child->data->downs;
               }
               $count = $ups - $downs;
               break;
            case "buffer":
               $data = json_decode( $data );
               $count = !empty( $data ) ? $data->shares : 0;
               //$count = $data->shares;
               break;
            case "vk":
               $data = preg_match( '/^VK.Share.count\(\d+,\s+(\d+)\);$/i', $data, $matches );
               $count = $matches[1];
               break;
            default:
            // nothing here
         }

         $count = ( int ) $count;

         $this->data->error = $error;
         $this->data->total += $count;
         $this->data->{$service[0]} = $count;

         MASHSB()->logger->info( 'MashEngine - URL: ' . $url . ' ' . $service[0] . ': ' . $count );
         mashdebug()->info( 'MashEngine - URL: ' . $url . ' ' . $service[0] . ': ' . $count );
         //echo 'MashEngine - URL: ' . $url . ' ' . $service[0] . ': ' . $count . '<br>';
         $this->debug_notices[] = 'MashEngine - URL: ' . $url . ' ' . $service[0] . ': ' . $count;
         $mashsb_debug[] = 'MashEngine - URL: ' . $url . ' ' . $service[0] . ': ' . $count;

         add_action( 'wp_footer', array($this, 'outputDebug'), 1000 );
      }
      return;
   }

   public function outputDebug() {
      global $mashsb_options;

      if( current_user_can( 'install_plugins' ) && isset( $mashsb_options['debug_mode'] ) ) {
         echo '<div class="mash-debug" style="display:block;z-index:250000;font-size:11px;text-align:center;">';
         $this->debug_notices[] = (false === get_transient( 'timeout_mashsb_rate_limit' ) ? '' : 'FB rate limit active. Shares will be collected again in ' . $this->getRemainingRateLimitTime() . 'min.');
         //$this->debug_notices[] = 'MashShare Cache will be refreshed in ' . $time. 'min';
         var_dump( $this->debug_notices );
         echo '</div>';
      }
   }

   public function setRateLimitTransient() {
      set_transient( 'mashsb_rate_limit', 'true', 60 * 60 );

      MASHSB()->logger->info( 'Error: Facebook Rate Limit hit' );
      $this->debug_notices[] = 'Error: Requests to Facebook hit Rate Limit. Delaying requests for 60min';
      add_action( 'wp_footer', array($this, 'outputDebug'), 1000 );
   }

   public function getRemainingRateLimitTime() {
      $trans_time = get_transient( 'timeout_mashsb_rate_limit' );

      if( false !== $trans_time ) {
         return time() - $trans_time;
      }
      return time();
   }

   /**
    * Get twitter tweet count if social network add-on is installed
    * @return int
    */
   private function getTwitterShares() {
      if( class_exists( 'mashnetTwitter' ) ) {
         $twitter = new mashnetTwitter( $this->url );
         return $twitter->getTwitterShares();
      }
      return 0;
   }

}
