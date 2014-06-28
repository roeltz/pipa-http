<?php

namespace Pipa\HTTP\View;

interface Filter {
	function process($buffer);
}
