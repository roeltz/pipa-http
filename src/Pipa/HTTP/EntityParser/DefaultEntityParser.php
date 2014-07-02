<?php

namespace Pipa\HTTP\EntityParser;
use Pipa\HTTP\EntityParser;
use Pipa\HTTP\UploadedFile;

class DefaultEntityParser implements EntityParser {
	
	function parse($contentType) {
		$data = $_REQUEST;
		
		foreach($data as $paramName=>&$value) {
			if (empty($value) && $value !== "0")
				$value = null;
		}

		foreach($_FILES as $paramName=>$file) {
			$value = null;

			if (is_array($file['name'])) {
				$items = array();
				foreach($file['name'] as $i=>$fileName) {
					if (!$file['error'][$i])
						$items[] = new UploadedFile($fileName, $file['tmp_name'][$i], $file['type'][$i]);
				}
				$value = $items;
			} elseif (!$file['error']) {
				$value = new UploadedFile($file['name'], $file['tmp_name'], $file['type']);
			}

			if ($value !== null) {
				$data[$paramName] = (@$data->$paramName) ? array_merge((array) $data->$paramName, $value) : $value;
			}
		}
		
		return $data;
	}
}
