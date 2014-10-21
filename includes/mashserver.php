<?php
/**
 * Mashshare ShareCount Server
 *
 * @package     MASHSERV
 * @copyright   Copyright (c) 2014, René Hermenau
 * @since       1.0.0
 */

class mashserver {
private $url,$timeout;
function __construct($url,$timeout=10) {
$this->url=rawurlencode($url);
$this->timeout=$timeout;
}

function getFBTWCounts(){

	$ret = array('shares'=>array(),'total'=>0);
	$ret['shares']['fb'] = $this->get_fb();
	$ret['shares']['tw'] = $this->get_tweets();

	foreach ($ret['shares'] as $sbserv => $sbsharecount) $ret['total'] += (int)$sbsharecount;
        mashdebug()->error("direct API call getFBTWCounts: " . $ret['total']);
	return $ret;

}

function getAllCounts(){

	$ret = array('shares'=>array(),'total'=>0);
	$ret['shares']['fb'] = $this->get_fb();
	$ret['shares']['tw'] = $this->get_tweets();
	$ret['shares']['gp'] = $this->get_plusones();
	$ret['shares']['li'] = $this->get_linkedin();
	$ret['shares']['st'] = $this->get_stumble();
	$ret['shares']['pin'] = $this->get_pinterest();
	$ret['shares']['del'] = $this->get_delicious();

	foreach ($ret['shares'] as $sbserv => $sbsharecount) $ret['total'] += (int)$sbsharecount;
        mashdebug()->error("direct API call getAllCounts: " . $ret['total']);
	return $ret;
}

function get_tweets() {
	
	$json_string = $this->retrieveURL('http://urls.api.twitter.com/1/urls/count.json?url=' . $this->url);
	
	if (!empty($json_string)) { 
		$json = json_decode($json_string, true);
		return isset($json['count'])?intval($json['count']):0;
	} 

	return 0;
}
function get_linkedin() {

	$json_string = $this->retrieveURL("http://www.linkedin.com/countserv/count/share?url=$this->url&format=json");
	
	if (!empty($json_string)) { 
		$json = json_decode($json_string, true);
		return isset($json['count'])?intval($json['count']):0;
	} 

	return 0;
}
function get_fb() {
	
	$json_string = $this->retrieveURL('http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls='.$this->url);
	
	if (!empty($json_string)) { 
		$json = json_decode($json_string, true);
		return isset($json[0]['total_count'])?intval($json[0]['total_count']):0;
	} 

	return 0;
}
function get_plusones()  {

	
	
	try {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"'.rawurldecode($this->url).'","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		$curl_results = curl_exec ($curl);
		curl_close ($curl);
		$json = json_decode($curl_results, true);
		return isset($json[0]['result']['metadata']['globalCounts']['count'])?intval( $json[0]['result']['metadata']['globalCounts']['count'] ):0;
	
	} catch (Exception $e){
		return 0;
	}

	return 0;
}
function get_stumble() {

	$json_string = $this->retrieveURL('http://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$this->url);
	
	if (!empty($json_string)) { 
		$json = json_decode($json_string, true);
		return isset($json['result']['views'])?intval($json['result']['views']):0;
	} 

	return 0;
}
function get_delicious() {
	
	$json_string = $this->retrieveURL('http://feeds.delicious.com/v2/json/urlinfo/data?url='.$this->url);
	
	if (!empty($json_string)) { 
		$json = json_decode($json_string, true);
		return isset($json[0]['total_posts'])?intval($json[0]['total_posts']):0;
	}	

	return 0;
}
function get_pinterest() {
	
	$return_data = $this->retrieveURL('http://api.pinterest.com/v1/urls/count.json?url='.$this->url);
	
	if (!empty($return_data)) { 
		$json_string = preg_replace('/^receiveCount\((.*)\)$/', "\\1", $return_data);
		$json = json_decode($json_string, true);
	
		return isset($json['count'])?intval($json['count']):0;
	}

	return 0;
}


private function retrieveURL($u){
	
	try {
		if( function_exists('curl_init') ) { 
				$ch = curl_init($u);
				curl_setopt($ch, CURLOPT_URL, $u);
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				curl_setopt($ch, CURLOPT_FAILONERROR, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
				$ret = curl_exec($ch);
				curl_close($ch);
		} else $ret = file_get_contents($u);
	} catch (Exception $e){
		$ret = false;	
	}
	return $ret;
	
}

}
?>