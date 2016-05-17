<?php

require_once ("GsScraper2.php");
require_once ("curl.php");

abstract class AbstractWebGsScraper2 extends GsScraper2 {

	/**
	 * @var Curl
	 */
	protected $curl = null;
	
	protected $username = null;
	protected $password = null;
	
	protected $session_cookie;
	
	protected $url = null;

	public function __construct($url, $username = "", $password = "") {
		$this->curl = new Curl();
		
		$this->setAuthentication($username, $password);
		$this->setUrl($url);
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function setAuthentication($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	
	public function setSessionCookie($cookie) {
		$this->session_cookie = $cookie;
		$this->curl->setHeader("Cookie", $cookie);
	}
	

}
