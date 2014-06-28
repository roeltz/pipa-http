<?php

namespace Pipa\HTTP;
use Pipa\Dispatch\ExpressionRouter;
use Pipa\Dispatch\ViewSelector;
use Pipa\Dispatch\AnnotationOptionExtractor;
use Pipa\Error\ErrorHandler;
use Pipa\Error\HTMLErrorDisplay;

AnnotationOptionExtractor::registerNamespace('Pipa\HTTP\Annotation');
ExpressionRouter::registerExpression(new RoutingExpression, HTTPContext::CONTEXT_ID);
ErrorHandler::addDisplay(new HTMLErrorDisplay, HTTPContext::CONTEXT_ID);
ViewSelector::registerExpression(new ViewSelectionExpression);

Request::registerEntityParser(new EntityParser\JSONEntityParser);
Request::registerEntityParser(new EntityParser\DefaultEntityParser);
