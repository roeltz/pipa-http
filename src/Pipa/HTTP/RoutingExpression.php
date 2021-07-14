<?php

namespace Pipa\HTTP;
use Pipa\Config\Config;
use Pipa\Matcher\Expression;
use Pipa\Matcher\Pattern;
use Pipa\Parser\SymbolMatch;
use Pipa\Parser\Symbol\Regex;
use Pipa\Parser\Symbol\Quantified\ZeroOrOne;
use Pipa\Registry\Registry;

class RoutingExpression extends Expression {
	
	function __construct() {
		parent::__construct(array(
			'https'=>new ZeroOrOne(new Regex('HTTPS\s+', 'i')),
			'method'=>new ZeroOrOne(new Regex('(GET|POST|PUT|DELETE|HEAD|OPTIONS)\s+', 'i')),
			'host'=>new ZeroOrOne(new Regex('\/\/[^\/]+')),
			'path'=>new Regex('\S+')
		));
	}
	
	function toPattern(SymbolMatch $match) {
		$https = $match->value['https']->value ? 1 : array('any'=>true);
		$baseUrl = Config::get("http.base-url");
		$path = $match->value['path']->value;
		
		$path = array('capture'=>$baseUrl.($path == '/' ? '' : $path));

		if ($method = trim($match->value['method']->value->value)) {
			$method = array('capture'=>$method);
		} else {
			$method = array('any'=>true);
		}
		
		if ($host = trim(@$match->value['host']->value->value, '/')) {
			$host = array('capture'=>$host);
		} else {
			$host = array('any'=>true);
		}

		return new Pattern(array(
			'request:https'=>$https,
			'request:method'=>$method,
			'request:host'=>$host,
			'request:path'=>$path
		));
	}
}
