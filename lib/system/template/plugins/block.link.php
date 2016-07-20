<?php
use dns\system\RequestHandler;

function smarty_block_link($params, $content, $template, &$repeat) {
	if ($repeat) {
		return;
	}
	
	if (!array_key_exists('controller', $params)) {
		$params['controller'] = null;
	}
	
	if (!array_key_exists('id', $params)) {
		$params['id'] = null;
	}
	
	if (!array_key_exists('title', $params)) {
		$params['title'] = null;
	}
	
	return RequestHandler::getInstance()->getLink($params, $content);
}
