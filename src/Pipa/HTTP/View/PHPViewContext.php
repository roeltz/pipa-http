<?php

namespace Pipa\HTTP\View;
use Pipa\Config\Config;
use Pipa\Dispatch\Dispatch;
use Pipa\Dispatch\Principal;
use Pipa\Dispatch\Session;

class PHPViewContext {

	public $data;
	public $file;
	public $viewsDir;
	public $options;
	public $dispatch;
	public $user;

	function __construct($file, $viewsDir, Dispatch $dispatch = null, $data = array(), array $options = array()) {
		$this->file = $file;
		$this->viewsDir = $viewsDir;
		$this->dispatch = $dispatch;
		$this->data = $data;
		$this->options = $options;
		$this->user = $dispatch ? $dispatch->session->getPrincipal() : null;
	}

	function render() {
		ob_start();
		if (is_array($this->data) || is_object($this->data)) {
			extract((array) $this->data);
		}
		
		if (@$this->options['view-render-method'] == "eval") {
			eval("?>{$this->file}");
		} else {
			require $this->file;
		}
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
	
	function resolveFile($file) {
		if (preg_match('#^//#', $file)) {
			$file = preg_replace('#^//#', '', $file);
			return "{$this->viewsDir}/$file.php";
		} else {
			return dirname($this->file)."/{$file}.php";
		}
	}
	
	function inline($file) {
		$file = $this->resolveFile($file);
		$context = new PHPViewContext($file, $this->viewsDir, $this->dispatch, $this->data, $this->options);
		echo $context->render();
	}

	function put($file, array $data = array(), array $options = array()) {
		$file = $this->resolveFile($file);
		$context = new PHPViewContext($file, $this->viewsDir, $this->dispatch, $data, array_merge($this->options, $options));
		echo $context->render();
	}

	function sub($action, $data = null) {
		return $this->dispatch->sub($action, $data)->run();
	}

	function localURL($path = "") {
		return 'http'.(@$_SERVER['HTTPS'] ? 's' : '').'://'.$_SERVER['HTTP_HOST'].Config::get("http.base-url").$path;
	}

	function isAllowed($roles = null) {
		if ($this->dispatch->security)
			return $this->dispatch->security->isAllowed($this->user, is_array($roles) ? $roles : func_get_args());
		else
			return true;
	}
	
	function hasRole($role) {
		if ($this->user && $this->user instanceof Principal)
			return in_array($role, $this->user->getPrincipalRoles());
		else
			return false;
	}

	function currentAttr($view, $class = "current") {
		if ($this->options['view'] == $view)
			echo " class=\"$class\"";
	}

	function currentClass($view, $class = "current") {
		if ($this->options['view'] == $view)
			echo " $class";
	}

	function htmlBase($base = null) {
		if (!$base) $base = $this->localURL();
		echo "<base href=\"{$base}\">";
	}

	function htmlSelect($attr, array $options = array(), $value = null, $nullOption = null) {
		$value = \Pipa\to_array($value);
		if (is_string($attr))
			$attr = array('name'=>$attr);
		$attr = join(' ', array_map(function($a, $k){ return $a === true ? "$k" : "$k=\"$a\""; }, $attr, array_keys($attr)));
		$options = join("\n", array_map(function($o, $k) use($value) { $sel = in_array($k, $value) ? ' selected="selected"' : ''; return "<option value=\"$k\"$sel>$o</option>"; }, $options, array_keys($options)));
		if (!is_null($nullOption))
			$options = "<option value=\"\">$nullOption</option>$options";
		echo "<select $attr>\n$options\n</select>";
	}

	function arrayToHtmlOptions(array $array, $value, $text) {
		$options = array();
		foreach($array as $i) {
			$i = (object) $i;
			$options[$i->$value] = $i->$text;
		}
		return $options;
	}

	function htmlCheckbox($attr, $checked) {
		$attr = join(' ', array_map(function($a, $k){ return "$k=\"$a\""; }, $attr, array_keys($attr)));
		$checked = $checked ? "checked=\"checked\"" : "";
		echo "<input type=\"checkbox\" $attr $checked>";
	}
}
