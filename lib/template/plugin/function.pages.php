<?php
use dns\system\DNS;

function smarty_function_pages($tagArgs, $tplObj) {
	// needed params: controller, pageNo, pages
	if (!isset($tagArgs['controller'])) throw new Exception("missing 'controller' argument in pages tag");
	if (!isset($tagArgs['pageNo'])) {
		if (($tagArgs['pageNo'] = $tplObj->smarty->getTemplateVars('pageNo')) === null) {
			throw new Exception("missing 'pageNo' argument in pages tag");
		}
	}
	if (!isset($tagArgs['pages'])) {
		if (($tagArgs['pages'] = $tplObj->smarty->getTemplateVars('pages')) === null) {
			throw new Exception("missing 'pages' argument in pages tag");
		}
	}
	
	$html = '';
	
	if ($tagArgs['pages'] > 1) {
		$link = "index.php?page=".$tagArgs['controller'].(isset($tagArgs['id']) ? "&id=".$tagArgs['id'] : "");
		
		if (!isset($tagArgs['pageNo'])) {
			if (($tagArgs['pageNo'] = $tplObj->smarty->getTemplateVars('pageNo')) === null) {
				$tagArgs['pageNo'] = 0;
			}
		}
		
		// open div and ul
		$html .= "<nav>\n<ul class='pagination'>\n";
		
		// previous page
		$html .= makePreviousLink($link, $tagArgs['pageNo']);
		
		// first page
		$html .= makeLink($link, 1, $tagArgs['pageNo'], $tagArgs['pages']);
		
		// calculate page links
		$maxLinks = 7;
		$linksBeforePage = $tagArgs['pageNo'] - 2;
		if ($linksBeforePage < 0) $linksBeforePage = 0;
		$linksAfterPage = $tagArgs['pages'] - ($tagArgs['pageNo'] + 1);
		if ($linksAfterPage < 0) $linksAfterPage = 0;
		if ($tagArgs['pageNo'] > 1 && $tagArgs['pageNo'] < $tagArgs['pages']) {
			$maxLinks--;
		}
		
		$half = $maxLinks / 2;
		$left = $right = $tagArgs['pageNo'];
		if ($left < 1) $left = 1;
		if ($right < 1) $right = 1;
		if ($right > $tagArgs['pages'] - 1) $right = $tagArgs['pages'] - 1;
		
		if ($linksBeforePage >= $half) {
			$left -= $half;
		}
		else {
			$left -= $linksBeforePage;
			$right += $half - $linksBeforePage;
		}
		
		if ($linksAfterPage >= $half) {
			$right += $half;
		}
		else {
			$right += $linksAfterPage;
			$left -= $half - $linksAfterPage;
		}
		
		$right = intval(ceil($right));
		$left = intval(ceil($left));
		if ($left < 1) $left = 1;
		if ($right > $tagArgs['pages']) $right = $tagArgs['pages'];
		
		// left ... links
		if ($left > 1) {
			if ($left - 1 < 2) {
				$html .= makeLink($link, 2, $tagArgs['pageNo'], $tagArgs['pages']);
			}
			else {
				$html .= '<li class="button jumpTo"><a>&hellip;</a></li>'."\n";
			}
		}
		
		// visible links
		for ($i = $left + 1; $i < $right; $i++) {
			$html .= makeLink($link, $i, $tagArgs['pageNo'], $tagArgs['pages']);
		}
		
		// right ... links
		if ($right < $tagArgs['pages']) {
			if ($tagArgs['pages'] - $right < 2) {
				$html .= makeLink($link, $tagArgs['pages'] - 1, $tagArgs['pageNo'], $tagArgs['pages']);
			}
			else {
				$html .= '<li class="button jumpTo"><a>&hellip;</a></li>'."\n";
			}
		}
		
		// last page
		$html .= makeLink($link, $tagArgs['pages'], $tagArgs['pageNo'], $tagArgs['pages']);
		
		// next page
		$html .= makeNextLink($link, $tagArgs['pageNo'], $tagArgs['pages']);
		
		// close div and ul
		$html .= "</ul></nav>\n";
	}
	
	// assign html output to template var
	if (isset($tagArgs['assign'])) {
		$tplObj->assign($tagArgs['assign'], $html);
	}
	
	return $html;
}

function insertPageNumber($link, $pageNo) {
	$link = $link ."&pageNo=".$pageNo;
	return $link;
}

function makeLink($link, $pageNo, $activePage, $pages) {
	// first page
	if ($activePage != $pageNo) {
		return '<li><a href="'.insertPageNumber($link, $pageNo).'" class="ttips" title="'.DNS::getLanguageVariable('pagination.page', array('page' => $pageNo)).'">'.intval($pageNo).'</a></li>'."\n";
	}
	else {
		return '<li class="active"><a>'.intval($pageNo).'</a></li>'."\n";
	}
}

function makePreviousLink($link, $pageNo) {
	if ($pageNo > 1) {
		return '<li class="skip"><a href="'.insertPageNumber($link, $pageNo - 1).'" title="'.DNS::getLanguageVariable('pagination.previous').'" class="ttips"><span class="fa fa-angle-double-left"></span></a></li>'."\n";
	}
	else {
		return '<li class="skip disabled"><span class="fa fa-angle-double-left disabled"></span></li>'."\n";
	}
}


function makeNextLink($link, $pageNo, $pages) {
	if ($pageNo && $pageNo < $pages) {
		return '<li class="skip"><a href="'.insertPageNumber($link, $pageNo + 1).'" title="'.DNS::getLanguageVariable('pagination.next').'" class="ttips"><span class="fa fa-angle-double-right"></span></a></li>'."\n";
	}
	else {
		return '<li class="skip disabled"><span class="fa fa-angle-double-right disabled"></span></li>'."\n";
	}
}