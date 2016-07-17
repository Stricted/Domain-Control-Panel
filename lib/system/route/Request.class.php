<?php
namespace dns\system\route;
use Zend\Stdlib\Request as BaseRequest;

class Request extends BaseRequest {
	public function getUri() {
		return $this;
	}
	
	public function getPath() {
		return $_SERVER['QUERY_STRING'];
	}
}
