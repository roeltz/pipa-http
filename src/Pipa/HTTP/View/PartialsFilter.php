<?php

namespace Pipa\HTTP\View;

class PartialsFilter implements Filter {

	function process($buffer) {
		return $this->processBuffer($buffer);
	}

	protected function processBuffer($buffer, array &$partials = array(), array &$append = array()) {
		
		$buffer = $this->gather($buffer, $partials, $append);
		$buffer = $this->replace($buffer, $partials, $append);

		return $buffer;		
	}

	protected function gather($buffer, array &$partials, array &$append) {
		
		$hash = crc32($buffer);

		preg_match_all('/<!--\s*([\w-]+)\s+block\s*-->([\s\S]*)<!--\s*end\s+\1\s+block\s*-->/i', $buffer, $blocks);

		foreach($blocks[1] as $i=>$block)
			$partials[$block] = $blocks[2][$i];

		$buffer = preg_replace_callback('/<!--\s*append\s+([\w-]+)\s*-->\s*([\s\S]*?)\s*<!--\s*end\s+append\s+\1\s*-->(?:\s*\n)?/i', function($m) use(&$append){
			$block = $m[1];
			$content = $m[2];
			if (isset($append[$block]))
				$append[$block] .= $content;
			else
				$append[$block] = $content;
		}, $buffer);
		
		$buffer = preg_replace_callback('/<!--\s*([\w-]+)\s+content\s*-->\s*([\s\S]*?)\s*<!--\s*end\s+\1\s+content\s*-->(?:\s*\n)?/i', function($m) use(&$partials){
			$block = $m[1];
			$content = $m[2];
			$content = preg_replace('/<!--\s*parent\s*-->/i', @$partials[$block], $content);
			$partials[$block] = $content;
		}, $buffer);
		
		$newHash = crc32($buffer);

		if ($hash == $newHash)
			return $buffer;

		foreach($append as $block=>$content)
			$partials[$block] = @$content.@$partials[$block];
		
		foreach($partials as $block=>&$content)
			$content = $this->processBuffer($content, $partials, $append);

		return $buffer;
	}
	
	protected function replace($buffer, array &$partials, array &$append) {
			
		$buffer = preg_replace_callback('/<!--\s*([\w-]+)\s+block\s*-->([\s\S]*)<!--\s*end\s+\1\s+block\s*-->(?:\s*\n)?/i', function($m) use(&$partials){
			$block = $m[1];
			return @$partials[$block];
		}, $buffer);

		$buffer = preg_replace_callback('/<!--\s*([\w-]+)\s+placeholder\s*-->/i', function($m) use(&$partials){
			$block = $m[1];
			return @$partials[$block];
		}, $buffer);
		
		return $buffer;
	}
}
