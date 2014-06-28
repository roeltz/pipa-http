<?php

namespace Pipa\HTTP\Locale;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\LocaleExtractor;
use Pipa\Locale\Locale;

class HeaderLocaleExtractor implements LocaleExtractor {
	
	function getLocale(Dispatch $dispatch) {
		if ($header = $dispatch->request->headers['accept-language']) {
			preg_match_all($this->getRegex(), $header, $matches);
			if ($matches)
				return new Locale($matches[0][0]);
		}
	}
	
	private function getRegex() {
		$regex = join("|", Locale::accepted());
		return "/$regex/i";
	}
}
