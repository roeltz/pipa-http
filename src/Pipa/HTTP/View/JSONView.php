<?php

namespace Pipa\HTTP\View;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\View;
use Pipa\Registry\Registry;

class JSONView implements View {

	function render(Dispatch $dispatch) {
		$data = \Pipa\object_remove_recursion($dispatch->result->data);
		$json = @json_encode($data);
		if (isset($dispatch->request->data['callback'])) {
			$json = "{$dispatch->request->data['callback']}({$json});";
		}
		$dispatch->response->setContentType("application/json; charset=utf-8");
		echo $json;
	}
}

