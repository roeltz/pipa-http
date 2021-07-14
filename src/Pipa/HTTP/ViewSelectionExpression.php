<?php

namespace Pipa\HTTP;
use Pipa\Matcher\Expression;
use Pipa\Matcher\Pattern;
use Pipa\Parser\Match;
use Pipa\Parser\Symbol\Alternative;
use Pipa\Parser\Symbol\NonTerminal;
use Pipa\Parser\Symbol\Regex;
use Pipa\Parser\Symbol\Literal;
use Pipa\Parser\Symbol\Quantified\ZeroOrOne;

class ViewSelectionExpression extends Expression {
	
	function __construct() {
		parent::__construct(array('alternative'=>new Alternative(array(
			'default'=>new Regex('default', 'i'),
			'extension'=>new NonTerminal(array(
				'prefix'=>new Literal('.'),
				'extension'=>new Regex('[a-z0-9]+', 'i')
			)),
			'accept'=>new NonTerminal(array(
				'prefix'=>new Regex('accept\s+', 'i'),
				'regex'=>new Regex('.+')
			)),
			'option'=>new NonTerminal(array(
				'prefix'=>new Regex('option\s+', 'i'),
				'option'=>new Regex('[^\s]+'),
				'ws'=>new Regex('\s+'),
				'value'=>new Regex('.+')
			))
		))));
	}
	
	function toPattern(Match $match) {
		$alt = $match->value['alternative'];
		switch($alt->name) {
			case 'default':
				return new Pattern(array());
			case 'extension':
				return new Pattern(array('request:path'=>array('regex'=>'/\.'.preg_quote($alt->value['extension']->value).'$/i')));
			case 'accept':
				return new Pattern(array('request:header:accept'=>array('regex'=>'/'.$alt->value['regex']->value.'/i')));
			case 'option':
				$value = $alt->value['value']->value;
				if ($value == "*") $value = array("regex"=>'#^.+$#');
				return new Pattern(array("result:option:{$alt->value['option']->value}"=>$value));
		}
	}
}
