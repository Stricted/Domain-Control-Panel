<?php
namespace dns\system\route;
use Zend\Stdlib\Request as BaseRequest;

class Request extends BaseRequest {
	public function getPath() {
		if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
			$queryString = $_SERVER['PATH_INFO'];
		}
		else {
			$queryString = $_SERVER['QUERY_STRING'];
			
			if (strpos($queryString, '&') !== false) {
				$pos = strpos($queryString, '&');
				$queryString = substr($queryString, 0, $pos);
			}
		}
		return $queryString;
	}
}
