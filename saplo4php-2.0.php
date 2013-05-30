<?php

class SaploAPI {

	public static $debug = TRUE;
	private $endpoint = 'https://api.saplo.com/rpc/json';
	private $token;
	private $jsonRequest;

	function __construct($api_key, $secret_key, $endpoint = '') {
		if(!empty($endpoint))
			$this->endpoint = $endpoint;
		$params = array("api_key" => $api_key, "secret_key" => $secret_key);
		$this->accessToken($params);
		$this->text = new Text($this);
		$this->collection = new Collection($this);
		$this->account = new Account($this);
		$this->group = new Group($this);
	}

	function accessToken($params) {
		$response = $this->doRequest("auth.accessToken", $params);
		$object = $this->parseResponse($response, $params);
		$this->token = $object['access_token'];
		return $object;
	}


	/**
	 * Provide your own raw json string.
	 * @param String $json
	 * @return String JSON formatted string
	 */
	public function custom_request($json) {
		return $this->post($json);
	}


	function doRequest($method, $params) {

		if(isset($params['request_id'])) {
			$request_id = $params['request_id'];
			unset($params['request_id']);
		} else 
			$request_id = 0;
			
		if(isset($params['trim']))
			unset($params['trim']);
		
		$request = array( "method" => $method,
						  "params" => $params,
						  "id" => $request_id,
						  "jsonrpc" => "2.0");

		$requestJson = json_encode($request);
		$this->jsonRequest = $requestJson;
		SaploAPI::debug("JSON-Request: ".$requestJson);

		return $this->post($requestJson);

	}

	function post($requestJson) {

		$postUrl = $this->endpoint."?access_token=".$this->token;

		//Get length of post
		$postlength = strlen($requestJson);

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$postUrl);
		curl_setopt($ch,CURLOPT_POST,$postlength);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$requestJson);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);


		$response = curl_exec($ch);

		//close connection
		curl_close($ch);

		return $response;
	}

	function parseResponse($response, $params) {
		$parsed = json_decode($response, true);
		SaploAPI::debug("JSON-Response: ".$response);
		if(isset($parsed['result'])){
			if(isset($params['trim'])) {
				$res = $this->trim($params['trim'], $parsed['result']);
				return $res;				
			} else 
				return $parsed['result'];
		}else if(isset($parsed['error'])) {
			throw new SaploException($parsed['error']['msg'], $parsed['error']['code'], $this->jsonRequest);
		}
	}

	private function trim($param, $array) {
		return $array[$param];
	}
	
	private function setEndpoint($endpoint) {
		$this->endpoint = $endpoint;
	}

	private function getEndpoint() {
		return $this-endpoint;
	}

	public function getAccessToken() {
		return $this->token;
	}

	static function debug($str) {
		if(SaploAPI::$debug)
			echo $str."\n\n";
	}
}


class Account {

	function __construct($api) {
		$this->api = $api;
	}

	function get($params = array()) {
		$response = $this->api->doRequest("account.get",$params);
		return $this->api->parseResponse($response, $params);
	}
	
}
class Collection {

	function __construct($api) {
		$this->api = $api;
	}

	function create($params) {
		$response = $this->api->doRequest("collection.create",$params);
		return $this->api->parseResponse($response, $params);
	}

	function get($params) {
		$response = $this->api->doRequest("collection.get", $params);
		return $this->api->parseResponse($response, $params);
	}

	function update($params) {
		$response = $this->api->doRequest("collection.update",$params);
		return $this->api->parseResponse($response, $params);
	}

	function delete($params) {
		$response = $this->api->doRequest("collection.delete", $params);
		return $this->api->parseResponse($response, $params);
	}

	//Can be called using method name 'list'
	function lst($params = array()) {
		$response = $this->api->doRequest("collection.list", $params);
		return $this->api->parseResponse($response, $params);
	}

	function reset($params) {
		$response = $this->api->doRequest("collection.reset", $params);
		return $this->api->parseResponse($response, $params);
	}

	public function __call($func, $args) {
		switch ($func) {
			case 'list':
				$params = isset($args[0]) ? $args[0]: array();
				$request_id = isset($args[1]) ? $args[1]: 0;
				$trim = isset($args[2]) ? $args[2]: TRUE;
				return $this->lst($params);
				break;
		}
	}
}


class Text {

	function __construct($api) {
		$this->api = $api;
	}

	function create($params) {
		$response = $this->api->doRequest("text.create", $params);
		return $this->api->parseResponse($response, $params);
	}

	function update($params) {
		$response = $this->api->doRequest("text.update", $params);
		return $this->api->parseResponse($response, $params);
	}

	function get($params) {
		$response = $this->api->doRequest("text.get", $params);
		return $this->api->parseResponse($response, $params);
	}

	function delete($params = 0) {
		$response = $this->api->doRequest("text.delete", $params);
		return $this->api->parseResponse($response, $params);
	}

	function tags($params) {
		$response = $this->api->doRequest("text.tags", $params);
		return $this->api->parseResponse($response, $params);
	}

	function related_texts($params) {
		$response = $this->api->doRequest("text.relatedTexts", $params);
		return $this->api->parseResponse($response, $params);
	}

	function related_groups($params) {
		$response = $this->api->doRequest("text.relatedGroups", $params);
		return $this->api->parseResponse($response, $params);
	}
}

class Group {

	function __construct($api) {
		$this->api = $api;
	}

	function create($params) {
		$response = $this->api->doRequest("group.create",$params);
		return $this->api->parseResponse($response, $params);
	}

	function get($params) {
		$response = $this->api->doRequest("group.get",$params);
		return $this->api->parseResponse($response, $params);
	}

	function update($params) {
		$response = $this->api->doRequest("group.update",$params);
		return $this->api->parseResponse($response, $params);
	}

	function reset($params) {
		$response = $this->api->doRequest("group.reset",$params);
		return $this->api->parseResponse($response, $params);
	}
	
	function delete($params) {
		$response = $this->api->doRequest("group.delete",$params);
		return $this->api->parseResponse($response, $params);
	}
	
	//Can also be called using method name 'list'
	function lst($params = array()) {
		$response = $this->api->doRequest("group.list", $params);
		return $this->api->parseResponse($response, $params);
	}

	function add_text($params) {
		$response = $this->api->doRequest("group.addText", $params);
		return $this->api->parseResponse($response);
	}

	function delete_text($params) {
		$response = $this->api->doRequest("group.deleteText", $params);
		return $this->api->parseResponse($response);
	}

	function list_texts($params) {
		$response = $this->api->doRequest("group.listTexts", $params);
		return $this->api->parseResponse($response, $params);
	}

	function related_texts($params) {
		$response = $this->api->doRequest("group.relatedTexts", $params);
		return $this->api->parseResponse($response, $params);
	}

	function related_groups($params) {
		$response = $this->api->doRequest("group.relatedGroups", $params);
		return $this->api->parseResponse($response, $params);
	}

	public function __call($func, $args) {
		switch ($func) {
			case 'list':
				$params = isset($args[0]) ? $args[0]: array();
				$request_id = isset($args[1]) ? $args[1]: 0;
				$trim = isset($args[2]) ? $args[2]: TRUE;
				return $this->lst($params, $trim);
				break;
		}
	}

}

class SaploException extends Exception{

	private $jsonRequest;

	public function __construct($message, $code = 0, $jsonRequest = NULL, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->jsonRequest = $jsonRequest;
		SaploAPI::debug("SaploException: ($code) $message");
	}

	public function getJSONRequest() {
		return $this->jsonRequest;
	}
}
