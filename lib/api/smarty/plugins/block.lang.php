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
	if (is_null($content) || empty($content)) {
		return;
	}
	
	$lang = $template->smarty->getTemplateVars('language');
	
	if (is_null($lang)) {
		return $content;
	}
	
	$content = str_replace(array("'", '"'), "", $content);
	
	if (isset($lang[$content])) {
		if (strpos($lang[$content], $template->smarty->left_delimiter) !== false && strpos($lang[$content], $template->smarty->right_delimiter) !== false) {
			$data = str_replace("\$", '$', $lang[$content]);
			$_template = new $template->smarty->template_class('eval:'.$data, $template->smarty, $template);
			return $_template->fetch();
		}
		
		return $lang[$content];
	}
	
	return $content;
}
