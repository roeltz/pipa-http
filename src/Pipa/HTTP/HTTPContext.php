<?php

namespace Pipa\HTTP;
use Pipa\Dispatch\Context;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\Router;
use Pipa\Dispatch\View;
use Pipa\Error\ErrorHandler;

class HTTPContext extends Context {

	const CONTEXT_ID = 'http';

	function dispatch(Router $router, View $view, Request $request = null, Response $response = null) {
		ErrorHandler::setContext(self::CONTEXT_ID);

		if (!$request)
			$request = Request::fromGlobals();

		if (!$response)
			$response = new Response();

		$dispatch = new Dispatch(
			$this,
			$router,
			$request,
			$response,
			$view
		);
		return $dispatch->run();
	}
}
