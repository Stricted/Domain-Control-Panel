<?php
namespace dns\system\route;
use Zend\Mvc\Router\Http\Regex as RegexBase;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

class Regex extends RegexBase {
	public function match(Request $request, $pathOffset = null) {
		if (!method_exists($request, 'getPath')) {
			return;
		}
		
		$path = $request->getPath();

		if ($pathOffset !== null) {
			$result = preg_match('(\G' . $this->regex . ')i', $path, $matches, null, $pathOffset);
		}
		else {
			$result = preg_match('(^' . $this->regex . '$)i', $path, $matches);
		}

		if (!$result) {
			return;
		}

		$matchedLength = strlen($matches[0]);

		foreach ($matches as $key => $value) {
			if (is_numeric($key) || is_int($key) || $value === '') {
				unset($matches[$key]);
			}
			else {
				$matches[$key] = rawurldecode($value);
			}
		}

		return new RouteMatch(array_merge($this->defaults, $matches), $matchedLength);
	}
}
