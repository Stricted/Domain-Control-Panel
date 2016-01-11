<?php
namespace dns\page;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
abstract class AbstractPage {
	public $activeMenuItem = '';
	public $template = "";
	
	public final function __construct() {
		$this->prepare();
		$this->show();
	}
	
	abstract public function prepare();
	
	public final function show() {
		if (empty($this->template) || $this->template == "") {
			$classParts = explode('\\', get_class($this));
			$className = str_replace('Page', '', array_pop($classParts));
			$this->template = lcfirst($className).".tpl";
		}
		else {
			if (substr($this->template, -4) != ".tpl") {
				$this->template = $this->template.".tpl";
			}
		}
		
		DNS::getTPL()->assign(array("activeMenuItem" => $this->activeMenuItem));
		DNS::getTPL()->display($this->template);
	}
}
