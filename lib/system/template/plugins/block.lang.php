<?php
/**
 * Smarty plugin to format text blocks
 *
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2015 Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     Smarty
 * @subpackage  PluginsBlock
 */

/**
 * Smarty {lang}{/lang} block plugin
 */
function smarty_block_lang($params, $content, $template, &$repeat) {
	if ($content === null || empty($content)) {
		return;
	}
	
	$lang = $template->smarty->getTemplateVars('language');
	
	if ($lang === null) {
		return $content;
	}
	
	$content = str_replace(array("'", '"'), "", $content);
	
	if (isset($lang[$content])) {
		if (strpos($lang[$content], $template->smarty->left_delimiter) !== false && strpos($lang[$content], $template->smarty->right_delimiter) !== false) {
			$data = str_replace("\$", '$', $lang[$content]);
			
			$dir = $template->smarty->getTemplateDir();
			
			if (is_array($dir)) {
				$dir = $dir[0];
			}
			
			$filename = "lang.".$lang['languageCode'].".".$content.".tpl";
			if (file_exists($dir.$filename)) {
				$mtime = filemtime($dir.$filename);
				$maxLifetime = 3600;
				
				if ($mtime === false || ($maxLifetime > 0 && (time() - $mtime) > $maxLifetime)) {
					@unlink($dir.$filename);
				}
			}
			
			if (!file_exists($dir.$filename)) {
				$h = fopen($dir.$filename, "a+");
				fwrite($h, $lang[$content]);
				fclose($h);
			}
			
			return $template->smarty->fetch($filename);
		}
		
		return $lang[$content];
	}
	
	return $content;
}
