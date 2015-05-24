<?php
namespace dns\system;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2015 Jan Altensen (Stricted)
 */
class SessionHandler {
	/**
	 * session id
	 *
	 * @var	integer
	 */
	private $sessionID = null;
	
	/**
	 * session data
	 *
	 * @var	array
	 */
	private $sessionData = array();
	
	/**
	 * initial session system
	 */
	public function __construct () {
		if ($this->sessionID === null) {
			$this->sessionID = session_id();
		}
		
		/* delete expired sessions */
		$sql = "DELETE FROM dns_session WHERE expire < ?";
		DNS::getDB()->query($sql, array(time()));
		
		/* load data from database */
		$sql ="SELECT * FROM dns_session where sessionID = ?";
		$res = DNS::getDB()->query($sql, array($this->sessionID));
		$data = DNS::getDB()->fetch_array($res);
		if (isset($data['sessionID']) && !empty($data['sessionID'])) {
			if (isset($data['sessionData']) && !empty($data['sessionData'])) {
				$this->sessionData = json_decode($data['sessionData'], true);
			}
		}
		else {
			$sql = "INSERT INTO dns_session (id, sessionID, expire, sessionData) VALUES (NULL, ?, ?, ?)";
			DNS::getDB()->query($sql, array($this->sessionID, time() + 3600 * 24, ''));
		}
	}
	
	/**
	 * Checks if the active user has the given permission
	 *
	 * @return	boolean
	 */
	public function checkPermission($permission) {
		
		/* get permissionID */
		$sql = "SELECT * FROM dns_permissions where permission = ?";
		$res = DNS::getDB()->query($sql, array($permission));
		$data = DNS::getDB()->fetch_array($res);
		
		/* get permission from user */
		$sql = "SELECT * FROM dns_permissions_to_user where userID = ? and permissionID = ?";
		$res = DNS::getDB()->query($sql, array($this->userID, $data['id']));
		$row = DNS::getDB()->fetch_array($res);
		
		if (isset($row['permission']) && $row['permission'] == $permission) {
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
	
	/**
	 * Provides access to session data.
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */
	public function getVar($key) {
		if (isset($this->sessionData[$key])) {
			return $this->sessionData[$key];
		}
		
		return null;
	}
	
	/**
	 * Unsets a session variable.
	 * 
	 * @param	string		$key
	 */
	public function unregister($key) {
		if (isset($this->sessionData[$key])) {
			unset($this->sessionData[$key]);
		}
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
	
	/**
	 * Registers a session variable.
	 * 
	 * @param	string		$key
	 * @param	string		$value
	 */
	public function __set($key, $value) {
		$this->register($key, $value);
	}
	
	/**
	 * destroy the session
	 */
	public function destroy() {
		$this->sessionData = array();
		
		$sql = "DELETE FROM dns_session WHERE sessionID = ?";
		DNS::getDB()->query($sql, array($this->sessionID));
	}
	
	/**
	 * Registers a session variable.
	 * 
	 * @param	string		$key
	 * @param	string		$value
	 */
	public function update($key, $value) {
		$this->register($key, $value);
	}
}
