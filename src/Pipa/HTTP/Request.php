<?php

namespace Pipa\HTTP;
use Pipa\Dispatch\Request as BaseRequest;

class Request extends BaseRequest {
	
	static protected $entityParsers;
	
	public $https;
	public $method;
	public $host;
	public $path;
	public $headers;
	
	static function registerEntityParser(EntityParser $entityParser) {
		self::$entityParsers[] = $entityParser;
	}
	
	static function fromGlobals() {
		$method = $_SERVER['REQUEST_METHOD'];
		$path = current(explode('?', $_SERVER['REQUEST_URI'], 2));
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		$secure = @$_SERVER['HTTPS'] == "on";
		$headers = array();
		
		foreach(\getallheaders() as $header=>$value) {
			$headers[strtolower($header)] = $value;
		}

		if (strlen($path) > 1) ltrim($path, '/');
		
		foreach(self::$entityParsers as $entityParser) {
			if ($data = $entityParser->parse(@$headers['content-type'])) {
				break;
			}
		}

		return new self($method, $path, $host, $secure, $headers, $data);
	}

	function __construct($method, $path, $host = null, $https = false, $headers = array(), $data = array()) {
		parent::__construct(HTTPContext::CONTEXT_ID, $data);
		$this->method = $method;
		$this->path = $path;
		$this->host = $host;
		$this->https = $https;
		$this->headers = $headers;
	}

	function getAuthorization() {
		if (($header = @$this->headers["authorization"]) && preg_match('#^(\w+)\s+(\S+)#i', $header, $m)) {
			$type = $m[1];
			$credentials = $m[2];

			return (object) [
				"type"=>strtolower($type),
				"credentials"=>$credentials
			];
		} else {
			return false;
		}
	}

	function getComparableState() {
		$state = array(
			'context'=>'http',
			'method'=>$this->method,
			'path'=>$this->path,
			'host'=>$this->host,
			'https'=>$this->https
		);

		foreach($this->headers as $name=>$value) {
			$state["header:$name"] = $value;
		}

		return $state;
	}
	
	function getURL() {
		$qs = $_SERVER['QUERY_STRING'] ? "?{$_SERVER['QUERY_STRING']}" : "";
		return ($this->https ? "https" : "http") . "://{$this->host}{$this->path}$qs";
	}

	function isBearer($token) {
		$auth = $this->getAuthorization();
		return $auth && $auth->type === "bearer" && $auth->credentials === $token;
	}
}
