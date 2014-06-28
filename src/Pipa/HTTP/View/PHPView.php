<?php

namespace Pipa\HTTP\View;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\View;
use Pipa\Config\Config;

class PHPView implements View {

	protected $filters = array();

	function __construct(Filter $filter = null) {
		if ($filter) {
			$this->addFilter($filter);
		}
	}

	function addFilter(Filter $filter) {
		$this->filters[] = $filter;
	}

	function render(Dispatch $dispatch) {
		if ($template = @$dispatch->result->options['template']) {
			$view = $template;
		} else {
			$view = @$dispatch->result->options['view'];
		}

		$viewsDir = isset($dispatch->result->options['views-dir'])
			? $dispatch->result->options['views-dir']
			: Config::get("http.views-dir");
		$file = "{$viewsDir}/{$view}.php";

		if (file_exists($file)) {
			$context = new PHPViewContext($file, $viewsDir, $dispatch, $dispatch->result->data, $dispatch->result->options);
			$buffer = $context->render();
			if ($this->filters) {
				foreach($this->filters as $filter) {
					$buffer = $filter->process($buffer);
				}
			}
			echo $buffer;
		}
	}
}
