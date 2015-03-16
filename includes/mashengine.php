<?php

class mashengine {
	private $config;
	private $data;
	private $url;
	private $format;
	private $callback;
	private $cache;
	private $cache_directory;
	private $cache_time;
        private $timeout;
        
	function __construct($url,$timeout=10) {
            $this->url=rawurlencode($url);
            $this->timeout=$timeout;
        }
	
	
	public function get() {
		$this->data                = new stdClass;
		$this->data->url           = $this->url;
		$this->data->shares        = new stdClass;
		$this->data->shares->total = 0;
                $data['total'] = $this->getShares();
		return $data;
	}
	      
        
      /* Build the multi_curl() crawler
       * 
       * @returns
       */  
      public function getShares() {
                $post_data = null;
                //$user_data = null;
                $headers = null;
                
                /*$options = array(
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_USERAGENT, 'Mashengine v.1.1',
                );*/
                //$options = array(); 
                $RollingCurlX = new RollingCurlX(7);    // max 10 simultaneous downloads
		$RollingCurlX->addRequest("https://api.facebook.com/method/links.getStats?format=json&urls=" . $this->url, $post_data, array($this, 'getCount'), array('facebook'), $headers);
                $RollingCurlX->addRequest("http://urls.api.twitter.com/1/urls/count.json?url=" . $this->url, $post_data, array($this, 'getCount'),  array('twitter'), $headers);
                $RollingCurlX->addRequest("https://www.linkedin.com/countserv/count/share?format=json&url=" . $this->url, $post_data, array($this, 'getCount'), array('linkedin'), $headers);
                $RollingCurlX->addRequest("http://www.stumbleupon.com/services/1.01/badge.getinfo?url=" . $this->url, $post_data, array($this, 'getCount'), array('stumbleupon'), $headers);
                $RollingCurlX->addRequest("https://plusone.google.com/_/+1/fastbutton?url=" . $this->url, $post_data, array($this, 'getCount'),  array('google'), $headers);
                $RollingCurlX->addRequest("http://api.pinterest.com/v1/urls/count.json?url=" . $this->url, $post_data, array($this, 'getCount'), array('pinterest'), $headers);
                $RollingCurlX->addRequest("http://feeds.delicious.com/v2/json/urlinfo/data?url=" . $this->url, $post_data, array($this, 'getCount'),  array('delicious'), $headers);
                $RollingCurlX->addRequest("http://www.reddit.com/api/info.json?&url=" . $this->url, $post_data, array($this, 'getCount'), array('reddit'), $headers);
                $RollingCurlX->addRequest("https://api.bufferapp.com/1/links/shares.json?url=" . $this->url, $post_data, array($this, 'getCount'), array('buffer'), $headers);
                $RollingCurlX->addRequest("https://vk.com/share.php?act=count&index=1&url=" . $this->url, $post_data, array($this, 'getCount'), array('buffer'), $headers);
                $RollingCurlX->execute();
                
		//$data = json_encode($this->data); // This return and json string instead
                $data = $this->data;
                
                // return the total count
		return $data->shares->total;
		//return $data;
	}  
        
      /* Callback function to get share counts 
       * 
       *
       */
        
         function getCount($data, $url, $request_info, $service, $time){
		$count = 0;
		if ($data) {
			switch($service[0]) {
			case "facebook":
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
				$count = $data->count;
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
				foreach($data->data->children as $child) {
					$ups+= (int) $child->data->ups;
					$downs+= (int) $child->data->downs;
				}
				$count = $ups - $downs;
				break;
                        case "buffer":
                                $data = json_decode($data);
                                $count = $data->shares;
                                break;
                        case "vk":
                                $data = preg_match('/^VK.Share.count\(\d+,\s+(\d+)\);$/i', $data, $matches);
                                $count = $matches[1];
                                break;
                                default:
				// kill the script if trying to fetch from a provider that doesn't exist
				//die("Error: Service not found");
			}
                       
			$count = (int) $count;
			$this->data->shares->total += $count;
                        //$this->data->shares->$service[0] = $count;
                        MASHSB()->logger->info('URL: ' . $url . ' ' . $service[0] . ': ' . $count);
		} 
		return;
        }
        
        /*

         *          */
        private function getDataNew(){
            return $this->getShares();
        }
       
	
	// Get data and return it. If cache is active check for cached data and create it if unsuccessful.
	private function getData() {
            //if($this->cache) $key = md5($this->url) . '.' . ($this->format == 'jsonp' ? 'json' : $this->format);
       
		switch($this->cache) {
			case 'memcache':
				if(!function_exists('memcache_connect')) 
				{ 
					die('Memcache isn\'t installed'); 
				} 
				else
				{
					$memcache = new Memcache;
					$memcache->addServer($this->config->cache_server, $this->config->cache_port, $this->config->cache_persistent);
					if(!$memcache->connect($this->config->cache_server, $this->config->cache_port)) 
					{ 
						die('Couldn\'t connect to Memcache host'); 
					} 
					$data = $memcache->get($key);
					if ($data === false) {
						$data = $this->getShares();
						$memcache->set($key, $data, $this->cache_time);
					}
				}
				break;
                            case 'apc':
                                    $data = $this->getDataGlobalQuota();
				break;
			case 'file': 
				$data = $this->getCacheFile($key);
				break;
			default:
				//$data = $this->getShares();
                                $data = $this->getShares();
		}
		// if the format is JSONP wrap in callback function
		if($this->format == 'jsonp') $data = $this->callback . '(' . $data . ')';
		
		return $data;
	}
        
        /* Get data - use users daily quota
         * 
         * @return boolean  True if not rate limited
         * 
         */
        
        private function getDataDailyQuota() {
            // set APC key as users public IP
            $key = $_SERVER['REMOTE_ADDR'];
                if (APC::exists($key)) {
                    $data = apc_fetch($key);
                    // check whether IP is over API limit
                    if (APC::fetch($key) >= $this->apiLimit) {
                        // set rate limit headers
                        $this->headers($this->apiLimit, 0, APC::ttl($key, 'time'));
                        exit('Rate limit exceeded');
                    }
                } else {
                    // create a new key
                    APC::store($key, 0, $this->apiTTL);
                    return true;
                }
                // increase API calls by 1, and set headers
                $this->headers($this->apiLimit, ($this->apiLimit - APC::inc($key)), APC::ttl($key, 'time'));
        }
        
        /* Get data - use Global TTL Cache
         * 
         * @return $data API count
         */
        
        private function getDataGlobalQuota() {
            if($this->cache) $key = md5($this->url) . '.' . ($this->format == 'jsonp' ? 'json' : $this->format);
                if (APC::exists($key)) {
                      $data = apc_fetch($key);
                    //$data = APC::fetch($key);
                    // Run this and returns userdata when $cache_time is expired
                    $this->getDataDailyQuota();
                    return $data;
                } else {
                    $data = $this->getShares();
                    apc_store($key, $data, $this->cache_time);
                    //APC::store($key, $data, $this->cache_time);
                    //APC::store($keyGlobal, 0, $this->cache_time);
                    $this->getDataDailyQuota();
                    return $data;
                }
         }
        
        
	
	// get cache file - create if doesn't exist
	private function getCacheFile($key) {
		if (!file_exists($this->cache_directory)) {
			mkdir($this->cache_directory, 0777, true);
		}
		$file = $this->cache_directory . $key;
		$file_created = ((@file_exists($file))) ? @filemtime($file) : 0;
		@clearstatcache();
		if (time() - $this->cache_time < $file_created) {
			return file_get_contents($file);
		}
		$data = $this->getShares();
		$fp = @fopen($file, 'w'); 
		@fwrite($fp, $data);
		@fclose($fp);
		return $data;
	}
        
        
	
	// Delete expired file cache. Use "kill" parameter to also flush the memory and delete all cache files.
	public function cleanCache($kill = null) {
		// flush memcache
		if($kill) {
			switch($this->cache) {
				case 'memcache':
					$memcache = new Memcache;
					$memcache->flush();
					break;
				case 'apc':
					apc_clear_cache();
					apc_clear_cache('user');
					apc_clear_cache('opcode');
					break;
			}
		}
		// delete cache files
		if ($handle = @opendir($this->cache_directory)) {
			while (false !== ($file = @readdir($handle))) {
				if ($file != '.' and $file != '..') {
					$file_created = ((@file_exists($file))) ? @filemtime($file) : 0;
					if (time() - $this->cache_time < $file_created or $kill) {
						echo $file . ' deleted.<br>';
						@unlink($this->cache_directory . '/' . $file);
					}
				}
			}
			@closedir($handle);
		}
	}
        

        /**
         * Check APC for rate limit
         *
         * @param  integer $limit  API call limit
         * @param  integer $ttl    API TTL
         * @return boolean         True if not rate limited
         */
        public function rateLimit($limit, $ttl)
        {
            // set APC key as users public IP
            $key = $_SERVER['REMOTE_ADDR'];
            //echo "key: " . $key;
            // check if IP exists
            if (APC::exists($key)) {
                // check whether IP is over API limit
                if (APC::fetch($key) >= $limit) {
                    // set rate limit headers
                    $this->headers($limit, 0, APC::ttl($key, 'time'));
                    exit('Rate limit exceeded');
                }
            } else {
                // create a new key
                APC::store($key, 0, $ttl);
            }
            // increase API calls by 1, and set headers
            $this->headers($limit, ($limit - APC::inc($key)), APC::ttl($key, 'time'));
            return true;
        }
        /**
         * Set daily rate limit headers
         *
         * @param  string $limit   API limit
         * @param  string $remain  API calls remaining
         * @param  string $reset   Timestamp of expiry
         */
        public function headers($limit, $remain, $reset)
        {
            header('X-RateLimit-Limit: '     . $limit);
            header('X-RateLimit-Remaining: ' . $remain);
            header('X-RateLimit-Reset: '     . $reset);
        }
        
	
	// output share counts as XML
	// functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/
	public static function generateValidXmlFromObj(stdClass $obj, $node_block='nodes', $node_name='node') {
		$arr = get_object_vars($obj);
		return self::generateValidXmlFromArray($arr, $node_block, $node_name);
	}

	public static function generateValidXmlFromArray($array, $node_block='nodes', $node_name='node') {
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>';
		$xml .= '<' . $node_block . '>';
		$xml .= self::generateXmlFromArray($array, $node_name);
		$xml .= '</' . $node_block . '>';
		return $xml;
	}

	private static function generateXmlFromArray($array, $node_name) {
		$xml = '';
		if (is_array($array) || is_object($array)) {
			foreach ($array as $key=>$value) {
				if (is_numeric($key)) {
					$key = $node_name;
				}
				$xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
			}
		} else {
			$xml = htmlspecialchars($array, ENT_QUOTES);
		}
		return $xml;
	}
        
        /* Create a json file with userdata API Keys and whitelisted domains
         * 
         * @return false
         */
        
        public function createAPIkeys(){
                $arrKeys = array(
               '7652345623488234' => array(
                        'apikey' => '7652345623488234',
                        'white' => array('46.59.204.94', '127.0.0.1')
                ),
                '862374708627838' =>array(
                        'apikey' => '862374708627838',
                        'white' => array('46.59.204.98', '46.59.204.95')
                )
            );
            // Write json file
            $writeKeys = file_put_contents("apikeys.json",json_encode($arrKeys));
        }
        
        /* Returns all available API Keys and whitelisted domains
         * Stores them in APC
         * 
         * @return boolean  true when API key is in_array of $_POST['apikey']
         */
        
        public function getAPIkeys(){
            $key = md5('apikeys.json');
                if (APC::exists($key)) {
                      $data = apc_fetch($key);
                    // Run this and returns userdata when userdataTTL is expired
                } else {
                    // get json data from file - todo build mysql data storage here
                    $data = json_decode(file_get_contents('apikeys.json'), true);
                    apc_store($key, $data, $this->userdataTTL);
                }
                return $data;
            }
            
            
            
            /* Find API key and user values in array
             * 
             * @param $needle  search this string
             * @param $haystack  search needle in this array 
             * @return boolean
             */
            public function in_array_r($needle, $haystack, $strict = false) {
                foreach ($haystack as $item) {
                    if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                        return true;
                    }
                }
            return false;
            }
            
            /* Check if API access is allowed
             * 
             * @param $string   API Key
             * @return boolean  True when valid apikey ip
             */
            public function accessAuthority() {
                if (isset($_GET['apikey'])) {
                    return $this->in_array_r($_GET['apikey'], $this->getAPIkeys()) ? 
                            true : 
                            false;
                            //'api key found. IP whitelisted: ' . $userdata[$_GET['apikey']]['domain1'] . ' ' . $userdata[$_GET['apikey']]['domain1'] :
                            //'apikey not found. IP whitelisted: ' . $userdata[$_GET['apikey']]['domain1'] . ' ' . $userdata[$_GET['apikey']]['domain1'];
                } 
                return false; // no api key
            }
            
            /* Check if request ip is in whitelisted domains
             * 
             * @return boolean True when whitelisted or no ip´s exist in array
             */
            
            public function accessAuthorityHost(){
                if ($this->accessAuthority()){
                    $apidata = $this->getAPIkeys();
                    if (array_key_exists('white', $apidata[$_GET['apikey']])){
                        $remoteIP = $_SERVER['REMOTE_ADDR'];
                        $arrWhitelisted = $apidata[$_GET['apikey']]['white'];
                        return $this->in_array_r($remoteIP, $arrWhitelisted) ? true : false;
                    }
                    return true; //return true when no ip´s are whitelisted
                }
            }

}