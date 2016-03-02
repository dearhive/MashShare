<?php

class mashengine {
	private $data;
	private $url;
        private $timeout;
        
	function __construct($url,$timeout=10) {
            $this->url=rawurlencode($url);
            $this->timeout=$timeout;
        }
	
	/* Collect share count from all available networks */
	public function getALLCounts() {
		$this->data = new stdClass;
                $this->data->total = 0;
                $data = $this->getSharesALL();
		return $data;
	}
        
        /* Collect share count from facebook and twitter */
	public function getFBTWCounts() {
		$this->data = new stdClass;
                $this->data->total = 0;
                $data = $this->getSharesFBTW();
		return $data;
	}
	   
        
      /* Build the multi_curl() crawler for facebook and twitter
       * 
       * @returns
       */  
      public function getSharesFBTW() {
          global $mashsb_options;
                $fb_mode = isset($mashsb_options['facebook_count_mode'])  ? $mashsb_options['facebook_count_mode'] : '';  
                $post_data = null;
                //$user_data = null;
                $headers = null;
                
                $options = array(
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE
                //CURLOPT_USERAGENT, 'MashEngine v.1.1',
                );
                
                //$RCX->addRequest($url, $post_data, 'callback_functn', $user_data, $options, $headers);
                
                $RollingCurlX = new RollingCurlX(2);    // max 10 simultaneous downloads
		$RollingCurlX->setOptions($options);
                switch ($fb_mode){
                    case $fb_mode === 'likes':
                        $RollingCurlX->addRequest("https://api.facebook.com/method/links.getStats?format=json&urls=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers);
                        break;
                    case $fb_mode === 'total':    
                        $RollingCurlX->addRequest("https://api.facebook.com/method/links.getStats?format=json&urls=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers);
                        break;
                    default:
                        $RollingCurlX->addRequest("https://api.facebook.com/method/links.getStats?format=json&urls=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers);
                }
                //$RollingCurlX->addRequest("http://urls.api.twitter.com/1/urls/count.json?url=" . $this->url, $post_data, array($this, 'getCount'),  array('twitter'), $headers);
                $RollingCurlX->addRequest("http://public.newsharecounts.com/count.json?url=" . $this->url, $post_data, array($this, 'getCount'),  array('twitter'), $headers);
                $RollingCurlX->execute();
                
		//$data = json_encode($this->data); // This return and json string instead
                $data = $this->data;

		return $data;
	} 
        
      /* Build the multi_curl() crawler for all networks
       * 
       * @returns
       */  
      public function getSharesALL() {
          global $mashsb_options;
                $fb_mode = isset($mashsb_options['facebook_count_mode'])  ? $mashsb_options['facebook_count_mode'] : '';  

                $post_data = null;
                //$user_data = null;
                $headers = null;
                
                $options = array(
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                //CURLOPT_USERAGENT, 'MashEngine v.1.1'
                );
				
                $RollingCurlX = new RollingCurlX(8);    // max 10 simultaneous downloads
		$RollingCurlX->setOptions($options);
                switch ($fb_mode){
                    case $fb_mode === 'likes':
                        $RollingCurlX->addRequest("https://api.facebook.com/method/links.getStats?format=json&urls=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_likes'), $headers);
                        break;
                    case $fb_mode === 'total':    
                        $RollingCurlX->addRequest("https://api.facebook.com/method/links.getStats?format=json&urls=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_total'), $headers);
                        break;
                    default:
                        $RollingCurlX->addRequest("https://api.facebook.com/method/links.getStats?format=json&urls=" . $this->url, $post_data, array($this, 'getCount'), array('facebook_shares'), $headers);
                }
                //$RollingCurlX->addRequest("http://urls.api.twitter.com/1/urls/count.json?url=" . $this->url, $post_data, array($this, 'getCount'),  array('twitter'), $headers);
                $RollingCurlX->addRequest("http://public.newsharecounts.com/count.json?url=" . $this->url, $post_data, array($this, 'getCount'),  array('twitter'), $headers);
                $RollingCurlX->addRequest("https://www.linkedin.com/countserv/count/share?format=json&url=" . $this->url, $post_data, array($this, 'getCount'), array('linkedin'), $headers);
                $RollingCurlX->addRequest("http://www.stumbleupon.com/services/1.01/badge.getinfo?url=" . $this->url, $post_data, array($this, 'getCount'), array('stumbleupon'), $headers);
                $RollingCurlX->addRequest("https://plusone.google.com/_/+1/fastbutton?url=" . $this->url, $post_data, array($this, 'getCount'),  array('google'), $headers);
                $RollingCurlX->addRequest("http://api.pinterest.com/v1/urls/count.json?url=" . $this->url, $post_data, array($this, 'getCount'), array('pinterest'), $headers);
                //$RollingCurlX->addRequest("http://feeds.delicious.com/v2/json/urlinfo/data?url=" . $this->url, $post_data, array($this, 'getCount'),  array('delicious'), $headers);
                //$RollingCurlX->addRequest("http://www.reddit.com/api/info.json?&url=" . $this->url, $post_data, array($this, 'getCount'), array('reddit'), $headers);
                $RollingCurlX->addRequest("https://api.bufferapp.com/1/links/shares.json?url=" . $this->url, $post_data, array($this, 'getCount'), array('buffer'), $headers);
                $RollingCurlX->addRequest("https://vk.com/share.php?act=count&index=1&url=" . $this->url, $post_data, array($this, 'getCount'), array('vk'), $headers);
		$RollingCurlX->execute();
                
                //$data = json_encode($this->data); // This return an json string instead
                $data = $this->data;
                
                // return the total count
		//return $data->shares->total;
		return $data;
	}  
        
      /* Callback function to get share counts 
       * 
       *
       */
        
         function getCount($data, $url, $request_info, $service, $time){
		$count = 0;
		if ($data) {
			switch($service[0]) {
			case "facebook_likes":
				$data = json_decode($data); 
				$count = (is_array($data) ? $data[0]->like_count : $data->like_count);
				break;
                        case "facebook_shares":
				$data = json_decode($data); 
				$count = (is_array($data) ? $data[0]->share_count : $data->share_count);
				break;
                        case "facebook_total":
				$data = json_decode($data); 
				$count = (is_array($data) ? $data[0]->total_count : $data->total_count);
				break;
			case "google":
				preg_match( '/window\.__SSR = {c: ([\d]+)/', $data, $matches );
				if(isset($matches[0])) $count = str_replace( 'window.__SSR = {c: ', '', $matches[0] );
				break;
                        case "pinterest": 
				$data = substr( $data, 13, -1);
			case "linkedin":
			case "twitter":
				$data = json_decode($data);
				$count = isset($data->count) ? $data->count : 0;
				break;
			case "stumbleupon":
				$data = json_decode($data);
                                isset($data->result->views) ? $count = $data->result->views : $count = 0;
				
				break;
			case "delicious":
				$data = json_decode($data);
				$count = !empty($data) ? $data[0]->total_posts : 0;
				break;
			case "reddit":
				$data = json_decode($data);
                                $ups = 0;$downs = 0;
				foreach($data->data->children as $child) {
					$ups+= (int) $child->data->ups;
					$downs+= (int) $child->data->downs;
				}
				$count = $ups - $downs;
				break;
                        case "buffer":
                                $data = json_decode($data);
                                $count = !empty($data) ? $data->shares : 0;
                                //$count = $data->shares;
                                break;
                        case "vk":
                                $data = preg_match('/^VK.Share.count\(\d+,\s+(\d+)\);$/i', $data, $matches);
                                $count = $matches[1];
                                break;
                                default:
                                    // nothing here
			}
                       
			$count = (int) $count;
			/*$this->data->shares->total += $count;
                        $this->data->shares->$service[0] = $count;
                         * */
                        $this->data->total += $count;
                        $this->data->$service[0] = $count;
                        MASHSB()->logger->info('MashEngine - URL: ' . $url . ' ' . $service[0] . ': ' . $count);
                        mashdebug()->info('MashEngine - URL: ' . $url . ' ' . $service[0] . ': ' . $count);
		} 
		return;
        }
}