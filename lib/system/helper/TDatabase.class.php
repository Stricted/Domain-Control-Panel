<?php
namespace dns\system\helper;

trait TDatabase {
	protected $db = null;
	
	public function setDB ($database) {
		$this->db = $database;
	}
}
