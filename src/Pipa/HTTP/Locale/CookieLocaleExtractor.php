<?php

namespace Pipa\HTTP\Locale;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\LocaleExtractor;
use Pipa\Locale\Locale;

class CookieLocaleExtractor implements LocaleExtractor {
	
	protected $cookieName;
	
	function __construct($cookieName = "locale") {
		$this->cookieName = $cookieName;
	}
	
	function getLocale(Dispatch $dispatch) {
		if ($cookie = @$_COOKIE[$this->cookieName]) {
			if (in_array($cookie, Locale::accepted())) {
				return new Locale($cookie);
			}
		}
	}
}
