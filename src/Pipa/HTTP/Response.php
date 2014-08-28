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
	public $overrides = array();
	public $statusCode = 200;
	public $responseBody;

	function allowOrigin($origin) {
		$this->setHeader('Access-Control-Allow-Origin', $origin);
		$this->overrides[] = 'http-allow-origin';
	}

	function noCache() {
		$this->setHeader('Expires', 'Tue, 01 Jan 2000 00:00:00 GMT');
		$this->setHeader('Last-Modified', gmdate("D, d M Y H:i:s").' GMT');
		$this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
		$this->overrides[] = 'http-no-cache';
	}

	function setAsDownload($contentType, $filename) {
		$this->setContentType($contentType);
		$this->setHeader('Content-Disposition', "attachment; filename=$filename");
		$this->overrides[] = 'http-content-type';
		$this->overrides[] = 'http-download';
	}
	
	function setContent($content, $type = null) {
		$this->responseBody = $content;
		if (!is_null($type)) {
			$this->setContentType($type);
		}
	}

	function setContentType($contentType) {
		$this->setHeader('Content-Type', $contentType);
		$this->overrides[] = 'http-content-type';
	}

	function setExpiration($expression) {
		$date = new DateTime($expression, new DateTimeZone('UTC'));
		$this->setHeader('Expires', gmdate("D, d M Y H:i:s", $date->getTimestamp()));
		$this->overrides[] = 'http-no-cache';
	}

	function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	function setStatusCode($code) {
		$this->statusCode = $code;
		$this->overrides[] = 'http-status-code';
	}

	function setScriptCall($method, $argument) {
		$this->responseBody = "<script>$method(".json_encode($argument).");</script>";
	}

	function setScriptRedirection($uri, $frame = "top") {
		$this->responseBody = "<script>{$frame}.location = ".json_encode($uri).";</script>";
	}

	function redirect($location) {
		$this->setHeader('Location', $location);
		$this->overrides[] = 'http-redirect';
	}

	function redirectLocal($path = "") {
		$baseUrl = Config::get("http.base-url");
		$this->redirect("$baseUrl$path");
	}

	function render(Dispatch $dispatch) {
		if ($this->responseBody) {
			$buffer = $this->responseBody;
		} else {
			$this->startBuffer();
			parent::render($dispatch);
			$buffer = $this->endBuffer();
		}		

		$this->outputHeaders($dispatch);

		echo $buffer;
	}
	
	function outputHeaders(Dispatch $dispatch) {
		$this->setOptions($dispatch->result->options);
		header("{$_SERVER['SERVER_PROTOCOL']} {$this->statusCode}");
		foreach($this->headers as $name=>$value) {
			header("$name: $value");
		}
	}

	function startBuffer() {
		ob_start();
	}
	
	function endBuffer() {
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	protected function setOptions(array $options) {
		if (!in_array('http-allow-origin', $this->overrides) && ($origin = @$options['http-allow-origin'])) {
			$this->allowOrigin($origin);
		}
		if (!in_array('http-content-type', $this->overrides) && ($contentType = @$options['http-content-type'])) {
			$this->setContentType($contentType);
		}
		if (!in_array('http-download', $this->overrides) && ($filename = @$options['http-download'])) {
			$contentType = isset($options['http-content-type']) ? $options['http-content-type'] : 'application/octet-stream';
			$this->setAsDownload($contentType, $filename);
		}
		if ($header = @$options['http-header']) {
			list($name, $value) = preg_split('/\s*:\s*/', $header);
			$this->setHeader($name, $value);
		}
		if (!in_array('http-redirect', $this->overrides) && ($location = @$options['http-redirect'])) {
			$this->redirect($location);
		}
		if (!in_array('http-redirect', $this->overrides) && ($path = @$options['http-redirect-local'])) {
			$this->redirectLocal($path);
		}
		if (!in_array('http-status-code', $this->overrides) && ($statusCode = @$options['http-status-code'])) {
			$this->setStatusCode($statusCode);
		}
	}
}
