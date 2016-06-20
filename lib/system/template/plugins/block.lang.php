<?php
/**
 * Smarty plugin to format text blocks
 *
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2016 Jan Altensen (Stricted)
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
	
	return dns\system\DNS::getLanguageVariable($content);
}
