<?php
namespace dns\system\helper;

trait TDatabase {
	private $db = null;
	
	public function setDB ($database) {
		$this->db = $database;
	}
}
