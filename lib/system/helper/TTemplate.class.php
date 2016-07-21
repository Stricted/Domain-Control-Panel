<?php
namespace dns\system\helper;

trait TTemplate {
	protected $tpl = null;
	
	public function setTPL ($tpl) {
		$this->tpl = $tpl;
	}
}
