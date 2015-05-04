<?php
namespace dns\system;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class User {
	/**
	 * check if the user is loggedin
	 *
	 * @return	boolean
	 */
	public static function isLoggedIn () {		
		if (DNS::getSession()->login !== null && DNS::getSession()->login == 1) {
			return true;
		}
		
		if (isset($_COOKIE['userID']) && !empty($_COOKIE['userID']) && isset($_COOKIE['cookieHash']) && !empty($_COOKIE['cookieHash'])) {
			return self::cookieLogin($_COOKIE['userID'], $_COOKIE['cookieHash']);
		}
		
		return false;
	}
	
	/**
	 * check if user is an Admin
	 *
	 * @return	boolean
	 */
	public static function isAdmin () {
		if (DNS::getSession()->status !== null && DNS::getSession()->status == 2) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * check if user is an Reseller
	 *
	 * @return	boolean
	 */
	public static function isReseller () {
		if (self::isAdmin() === true) {
			return true;
		}
		
		if (DNS::getSession()->status !== null && DNS::getSession()->status == 1) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * check if user has an login cookie
	 *
	 * @param	integer	$userID
	 * @param	string	$hash
	 * @return	boolean
	 */
	public static function cookieLogin ($userID, $hash) {
		$query = DNS::getDB()->query("SELECT * FROM dns_user WHERE SHA1(userID) = ?", array($userID));
		$row = DNS::getDB()->fetch_array($query);
		if (!empty($row)) {
			$sha1UserID = sha1($row["userID"]);
			$sha1Password = sha1($row['password']);
			$sha1CookieHash = sha1($sha1UserID.$sha1Password);
			if ($sha1CookieHash == $hash) {
				DNS::getSession()->register('login', 1);
				DNS::getSession()->register('username', $row["username"]);
				DNS::getSession()->register('userID', $row["userID"]);
				DNS::getSession()->register('status', intval($row["status"]));
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * login the user
	 *
	 * @param	string	$username
	 * @param	string	$password
	 * @param	boolean	$remember
	 * @return	boolean
	 */
	public static function login ($username, $password, $remember = false) {
		$query = DNS::getDB()->query("SELECT * FROM dns_user WHERE username = ?", array($username));
		$row = DNS::getDB()->fetch_array($query);
		if (!empty($row)) {
			if (crypt(crypt($password, $row['password']), $row['password']) == $row['password']) {
				DNS::getSession()->register('login', 1);
				DNS::getSession()->register('username', $row["username"]);
				DNS::getSession()->register('userID', $row["userID"]);
				DNS::getSession()->register('status', intval($row["status"]));
				
				if ($remember === true) {
					$sha1UserID = sha1($row["userID"]);
					$sha1Password = sha1($row['password']);
					$sha1CookieHash = sha1($sha1UserID.$sha1Password);
					
					setcookie("userID", $sha1UserID, time() + 3600 * 24 * 60); // 60 days
					setcookie("cookieHash", $sha1CookieHash, time() + 3600 * 24 * 60); // 60 days
					
				}
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * log the user out
	 */
	public static function logout () {		
		if (isset($_COOKIE["userID"])) {
			setcookie("userID", '', time() - 3600);
		}
		
		if (isset($_COOKIE["cookieHash"])) {
			setcookie("cookieHash", '', time() - 3600);
		}
		
		DNS::getSession()->destroy();
		session_destroy();
	}
	
	/**
	 * create a new user
	 *
	 * @param	string	$username
	 * @param	string	$email
	 * @param	string	$password
	 * @param	string	$password2
	 * @param	integer	$reseller
	 * @param	integer	$status
	 * @return	boolean
	 */
	public static function createUser ($username, $email, $password, $password2, $reseller = 0, $status = 0) {
		$res = DNS::getDB()->query("SELECT * FROM dns_user WHERE username = ?", array($username));
		$row = DNS::getDB()->fetch_array($res);
		if (!isset($row['username'])) {
			if ($password == $password2) {
				$salt = self::generateSalt();
				$bind = array($username, $email, crypt(crypt($password, $salt), $salt), $reseller, $status);
				DNS::getDB()->query("INSERT INTO dns_user (userID, username, email, password, reseller, status) VALUES (null, ?, ?, ?, ?, ?);", $bind);
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * delete specific user
	 *
	 * @param	integer	$userID
	 */
	public static function deleteUser ($userID) {
		DNS::getDB()->query("DELETE FROM dns_user WHERE userID = ?", array($userID));
	}
	
	/**
	 * change user password
	 *
	 * @param	integer	$userID
	 * @param	string	$oldpassword
	 * @param	string	$newpassword
	 * @param	string	$newpassword2
	 * @return	boolean
	 */
	public static function change_password ($userID, $oldpassword, $newpassword, $newpassword2) {
		$res = DNS::getDB()->query("SELECT * FROM dns_user WHERE userID = ?", array($userID));
		$row = DNS::getDB()->fetch_array($res);
		if ($oldpassword != "" && $newpassword != "" && $newpassword2 != "") {
			if ($newpassword == $newpassword2) {
				if (crypt(crypt($oldpassword, $row['password']), $row['password']) == $row['password']) {
					$salt = self::generateSalt();
					$password = crypt(crypt($newpassword, $salt), $salt);
					DNS::getDB()->query("UPDATE dns_user SET password = ? WHERE userID = ?", array($password, $userID));
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * generate new password salt
	 *
	 * @return	string
	 */
	public static function generateSalt() {
		$blowfishCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
		$maxIndex = strlen($blowfishCharacters) - 1;
		$salt = '';

		for ($i = 0; $i < 22; $i++) {
			$rand = mt_rand(0, $maxIndex);
			$salt .= $blowfishCharacters[$rand];
		}

		return '$2a$08$' . $salt;
	}
	
	/**
	 * get accessible domains for given user
	 *
	 * @param	integer	$userID
	 * @return	array
	 */
	public static function getAccessibleDomains ($userID = 0) {
		$data = array();
		
		if ($userID === 0 && self::isLoggedIn()) {
			if (DNS::getSession()->userID !== null) {
				$userID = DNS::getSession()->userID;
			}
			
			if (self::isAdmin()) {
				$res = DNS::getDB()->query("SELECT * FROM dns_soa");
				while ($row = DNS::getDB()->fetch_array($res)) {
					$data[] = $row['id'];
				}
				
				return $data;
			}
		}
		
		$res = DNS::getDB()->query("SELECT * FROM dns_soa_to_user WHERE userID = ?", array($userID));
		while ($row = DNS::getDB()->fetch_array($res)) {
			$data[] = $row['soaID'];
		}
		
		return $data;
	}
}
