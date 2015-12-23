<?php

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
	
	$url = 'index.php?'.$params['controller'];
	
	if (!empty($params['id'])) {
		if (!empty($params['title'])) {
			$url .= '/'.$params['id'].'-'.$params['title'];
		}
		else {
			$url .= '/'.$params['id'];
		}
	}
	
	if (!empty($content)) {
		if (strpos($content, '&') !== 0) {
			$url .= '&';
		}
		
		$url .= $content;
	}
	
	return @htmlspecialchars($url, ENT_COMPAT, 'UTF-8');;
}