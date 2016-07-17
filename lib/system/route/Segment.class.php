<?php
namespace dns\system\route;
use Zend\Mvc\Router\Exception\RuntimeException;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\Segment as SegmentBase;
use Zend\Stdlib\RequestInterface as Request;

class Segment extends SegmentBase {
	public function match(Request $request, $pathOffset = null, array $options = []) {
		if (!method_exists($request, 'getPath')) {
			return;
		}
		
		$path = $request->getPath();
		
		$regex = $this->regex;
		
		if ($this->translationKeys) {
			if (!isset($options['translator']) || !$options['translator'] instanceof Translator) {
				throw new RuntimeException('No translator provided');
			}
			
			$translator = $options['translator'];
			$textDomain = (isset($options['text_domain']) ? $options['text_domain'] : 'default');
			$locale     = (isset($options['locale']) ? $options['locale'] : null);
			
			foreach ($this->translationKeys as $key) {
				$regex = str_replace('#' . $key . '#', $translator->translate($key, $textDomain, $locale), $regex);
			}
		}
		
		if ($pathOffset !== null) {
			$result = preg_match('(\G' . $regex . ')i', $path, $matches, null, $pathOffset);
		}
		else {
			$result = preg_match('(^' . $regex . '$)i', $path, $matches);
		}
		
		if (!$result) {
			return;
		}
		
		$matchedLength = strlen($matches[0]);
		$params        = [];
		
		foreach ($this->paramMap as $index => $name) {
			if (isset($matches[$index]) && $matches[$index] !== '') {
				$params[$name] = $this->decode($matches[$index]);
			}
		}
		
		return new RouteMatch(array_merge($this->defaults, $params), $matchedLength);
	}
}
