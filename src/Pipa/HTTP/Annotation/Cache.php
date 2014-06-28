<?php

namespace Pipa\HTTP\Annotation;
use Pipa\Dispatch\Annotation\Option;

class Cache extends Option {
	public $name = 'http-cache';
	public $value = "+1 hour";
}
