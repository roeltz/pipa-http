<?php

namespace Pipa\HTTP\EntityParser;
use Pipa\HTTP\EntityParser;

class JSONEntityParser implements EntityParser {
	
	function parse($contentType) {
		if (strpos($contentType, 'application/json') !== false) {
			$json = @json_decode(file_get_contents("php://input"));
			return array_merge($_REQUEST, (array) $json);
		}
	}
}