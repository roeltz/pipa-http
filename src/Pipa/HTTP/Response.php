<?php

namespace Pipa\HTTP;
use DateTime;
use DateTimeZone;
use Pipa\Config\Config;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\Response as BaseResponse;
use Pipa\Dispatch\View;
use Pipa\Registry\Registry;

class Response extends BaseResponse {

	public $headers = array();
	public $statusCode = 200;
	public $responseBody;

	function allowOrigin($origin) {
		$this->setHeader('Access-Control-Allow-Origin', $origin);
	}

	function noCache() {
		$this->setHeader('Expires', 'Tue, 01 Jan 2000 00:00:00 GMT');
		$this->setHeader('Last-Modified', gmdate("D, d M Y H:i:s").' GMT');
		$this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
	}

	function setAsDownload($contentType, $filename) {
		$this->setContentType($contentType);
		$this->setHeader('Content-Disposition', "attachment; filename=$filename");
	}
	
	function setContent($content, $type = null) {
		$this->responseBody = $content;
		if (!is_null($type)) {
			$this->setContentType($type);
		}
	}

	function setContentType($contentType) {
		$this->setHeader('Content-Type', $contentType);
	}

	function setExpiration($expression) {
		$date = new DateTime($expression, new DateTimeZone('UTC'));
		$this->setHeader('Expires', gmdate("D, d M Y H:i:s", $date->getTimestamp()));
	}

	function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	function setStatusCode($code) {
		$this->statusCode = $code;
	}

	function setScriptCall($method, $argument) {
		$this->responseBody = "<script>$method(".json_encode($argument).");</script>";
	}

	function setScriptRedirection($uri, $frame = "top") {
		$this->responseBody = "<script>{$frame}.location = ".json_encode($uri).";</script>";
	}

	function redirect($location) {
		$this->setHeader('Location', $location);
	}

	function redirectLocal($path = "") {
		$baseUrl = Config::get("http.base-url");
		$this->redirect("$baseUrl$path");
	}

	function render(Dispatch $dispatch) {
		if ($this->responseBody) {
			$buffer = $this->responseBody;
		} else {
			ob_start();
			parent::render($dispatch);
			$buffer = ob_get_contents();
			ob_end_clean();
		}

		$this->setOptions($dispatch->result->options);
		header("{$_SERVER['SERVER_PROTOCOL']} {$this->statusCode}");
		foreach($this->headers as $name=>$value) {
			header("$name: $value");
		}

		echo $buffer;
	}

	protected function setOptions(array $options) {
		if ($origin = @$options['http-allow-origin']) {
			$this->allowOrigin($origin);
		}
		if ($contentType = @$options['http-content-type']) {
			$this->setContentType($contentType);
		}
		if ($filename = @$options['http-download']) {
			$contentType = isset($options['http-content-type']) ? $options['http-content-type'] : 'application/octet-stream';
			$this->setAsDownload($contentType, $filename);
		}
		if ($header = @$options['http-header']) {
			list($name, $value) = preg_split('/\s*:\s*/', $header);
			$this->setHeader($name, $value);
		}
		if ($location = @$options['http-redirect']) {
			$this->redirect($location);
		}
		if ($path = @$options['http-redirect-local']) {
			$this->redirectLocal($path);
		}
		if ($statusCode = @$options['http-status-code']) {
			$this->setStatusCode($statusCode);
		}
	}
}
