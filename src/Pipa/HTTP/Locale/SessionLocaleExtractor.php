<?php

namespace Pipa\HTTP\Locale;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\LocaleExtractor;
use Pipa\Locale\Locale;

class SessionLocaleExtractor implements LocaleExtractor {

	protected $callable;

	function __construct(callable $callable) {
		$this->callable = $callable;
	}

	function getLocale(Dispatch $dispatch) {
		$result = call_user_func($this->callable, $dispatch->session);

		if (!($result instanceof Locale)) {
			$result = new Locale($result);
		}

		return $result;
	}
}
