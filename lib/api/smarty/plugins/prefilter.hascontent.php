<?php
/**
 * Template prefiler plugin which allows inserting code dynamically upon the contents
 * of 'content'.
 * 
 * Usage:
 * 	{hascontent}
 * 	<ul>
 * 		{content}
 * 			{if $foo}<li>bar</li>{/if}
 * 		{/content}
 * 	</ul>
 * 	{hascontentelse}
 * 		<p>baz</p>
 * 	{/hascontent}
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */

function smarty_prefilter_hascontent($source, &$smarty) {
	$ldq = preg_quote($smarty->left_delimiter, '~');
	$rdq = preg_quote($smarty->right_delimiter, '~');
	
	$source = preg_replace_callback("~{$ldq}hascontent( assign='(?P<assign>.*)')?{$rdq}(?P<before>.*){$ldq}content{$rdq}(?P<content>.*){$ldq}\/content{$rdq}(?P<after>.*)({$ldq}hascontentelse{$rdq}(?P<else>.*))?{$ldq}\/hascontent{$rdq}~sU", function ($matches) {
		$beforeContent = $matches['before'];
		$content = $matches['content'];
		$afterContent = $matches['after'];
		$elseContent = (isset($matches['else'])) ? $matches['else'] : '';
		$assignContent = (isset($matches['assign']) && !empty($matches['assign'])) ? $matches['assign'] : '';
		$variable = 'hascontent_' . sha1(time());
		
		$newContent = '{capture assign='.$variable.'}'.$content.'{/capture}'."\n";
		$newContent .= '{assign var='.$variable.' value=$'.$variable.'|trim}'."\n";
		
		if ($assignContent) $newContent .= '{capture assign='.$assignContent.'}'."\n";
		$newContent .= '{if $'.$variable.'}'.$beforeContent.'{$'.$variable.'}'."\n".$afterContent;
		
		if (!empty($elseContent)) {
			$newContent .= '{else}'.$elseContent."\n";
		}
		
		$newContent .= '{/if}'."\n";
		
		if ($assignContent) $newContent .= "{/capture}\n{\$".$assignContent."}\n";
		
		return $newContent;
	}, $source);
	
	return $source;
}
