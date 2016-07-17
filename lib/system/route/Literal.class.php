<?php
namespace dns\system\route;
use Zend\Mvc\Router\Http\Literal as LiteralBase;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

class Literal extends LiteralBase {
	public function match(Request $request, $pathOffset = null) {
		if (!method_exists($request, 'getUri')) {
			return;
		}

		$uri  = $request->getUri();
		$path = $uri->getPath();

		if ($pathOffset !== null) {
			if ($pathOffset >= 0 && strlen($path) >= $pathOffset && !empty($this->route)) {
				if (strpos($path, $this->route, $pathOffset) === $pathOffset) {
					return new RouteMatch($this->defaults, strlen($this->route));
				}
			}

			return;
		}

		if (mb_strtolower($path) === mb_strtolower($this->route)) {
			return new RouteMatch($this->defaults, strlen($this->route));
		}

		return;
	}
}
