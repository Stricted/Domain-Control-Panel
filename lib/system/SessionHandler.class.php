<?php
namespace dns\system;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2015 Jan Altensen (Stricted)
 */
class SessionHandler {
	private $sessionID = null;
	
	private $sessionData = array();
	
	public function __construct () {
		$this->init();
	}
	
	public function init() {
		if ($this->sessionID === null) {
			$this->sessionID = session_id();
		}
		
		// load session data from database and check if the data is expired
		if (!$this->exists()) {
			$sql = "INSERT INTO dns_session (id, sessionID, expire, sessionData) VALUES (NULL, ?, ?, ?)";
			DNS::getDB()->query($sql, array($this->sessionID, time() + 3600 * 24, ''));
		}
		
		/* load data from database */
		$sql ="SELECT * FROM dns_session where sessionID = ?";
		$res = DNS::getDB()->query($sql, array($this->sessionID));
		$data = DNS::getDB()->fetch_array($res);
		if (isset($data['sessionData']) && !empty($data['sessionData'])) {
			$this->sessionData = json_decode($data['sessionData'], true);
		}
	}
	
	private function exists() {
		$sql = "SELECT * FROM dns_session where sessionID = ?";
		$res = DNS::getDB()->query($sql, array($this->sessionID));
		$data = DNS::getDB()->fetch_array($res);
		if (isset($data['sessionID']) && !empty($data['sessionID'])) {
			if ($data['expire'] < time()) {
				$this->destroy();
				return false;
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Provides access to session data.
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */
	public function __get($key) {
		return $this->getVar($key);
	}
	
	public function getVar($key) {
		if (isset($this->sessionData[$key])) {
			return $this->sessionData[$key];
		}
		
		return null;
	}
	
	/**
	 * Registers a session variable.
	 * 
	 * @param	string		$key
	 * @param	string		$value
	 */
	public function register($key, $value) {
		$this->sessionData[$key] = $value;
		
		
		$data = json_encode($this->sessionData);
		$sql = "UPDATE dns_session SET sessionData = ?, expire = ? WHERE sessionID = ?";
		DNS::getDB()->query($sql, array($data, time() + 3600 * 24, $this->sessionID));
	}
	
	public function __set($key, $value) {
		$this->register($key, $value);
	}
	
	public function destroy() {
		$this->sessionData = array();
		
		$sql = "DELETE FROM dns_session WHERE sessionID = ?";
		DNS::getDB()->query($sql, array($this->sessionID));
	}
	
	public function update($key, $value) {
		$this->register($key, $value);
	}
}
