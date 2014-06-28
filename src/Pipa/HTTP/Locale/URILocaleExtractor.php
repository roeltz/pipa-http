<?php

namespace Pipa\HTTP\Locale;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\LocaleExtractor;
use Pipa\Locale\Locale;

class URILocaleExtractor implements LocaleExtractor {
	const MODE_SUBDOMAIN = "subdomain";
	const MODE_PATH = "path";
	
	protected $mode;
	
	function __construct($mode) {
		$this->mode = $mode;
	}
		
	function getLocale(Dispatch $dispatch) {
		preg_match_all($this->getRegex(), $dispatch->request->uri, $matches);
		if (@$matches[1][0]) {
			return new Locale($matches[1][0]);
		}
	}
	
	private function getRegex() {
		$regex = join("|", Locale::accepted());
		switch($this->mode) {
			case self::MODE_SUBDOMAIN:
				$regex = "//($regex)\\.";
				break;
			case self::MODE_PATH:
				$regex = "/($regex)/";
				break;
		}
		return "#$regex#";
	}
}
