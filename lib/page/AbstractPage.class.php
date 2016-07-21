<?php
namespace dns\page;
use dns\system\helper\ITemplate;
use dns\system\helper\TTemplate;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
abstract class AbstractPage implements ITemplate {
	use TTemplate;
	
	public $activeMenuItem = '';
	public $template = "";
		
	public function init() {
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
		
		$this->tpl->assign(array("activeMenuItem" => $this->activeMenuItem));
		$this->tpl->display($this->template);
	}
}
