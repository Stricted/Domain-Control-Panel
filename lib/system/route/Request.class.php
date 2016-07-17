<?php
namespace dns\system\route;
use Zend\Stdlib\Request as BaseRequest;

class Request extends BaseRequest {
	public function getUri() {
		return $this;
	}
	
	public function getPath() {
		$queryString = $_SERVER['QUERY_STRING'];
		
		if (strpos($queryString, '&') !== false) {
			$pos = strpos($queryString, '&');
			$queryString = substr($queryString, 0, $pos);
		}
		echo $queryString;
		return $queryString;
	}
}
