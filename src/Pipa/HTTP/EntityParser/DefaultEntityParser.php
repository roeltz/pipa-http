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
			$fileValue = null;

			if (is_array($file['name'])) {
				$items = array();
				foreach($file['name'] as $i=>$fileName) {
					if (!$file['error'][$i])
						$items[] = new UploadedFile($fileName, $file['tmp_name'][$i], $file['type'][$i]);
				}
				$fileValue = $items;
			} elseif (!$file['error']) {
				$fileValue = new UploadedFile($file['name'], $file['tmp_name'], $file['type']);
			}

			if ($fileValue !== null) {
				$data[$paramName] = (@$data->$paramName) ? array_merge((array) $data->$paramName, $fileValue) : $fileValue;
			}
		}
		
		return $data;
	}
}
