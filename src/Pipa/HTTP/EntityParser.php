<?php

namespace Pipa\HTTP;

interface EntityParser {
	function parse($contentType);
}
