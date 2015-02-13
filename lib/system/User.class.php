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
		if (isset($_SESSION['login']) && $_SESSION['login'] == 1) {
			return true;
		}
		
		if (isset($_COOKIE['userID']) && !empty($_COOKIE['userID']) && isset($_COOKIE['cookieHash']) && !empty($_COOKIE['cookieHash'])) {
			return self::cookieLogin($_COOKIE['userID'], $_COOKIE['cookieHash']);
		}
		
		return false;
	}
	
	public static function isAdmin () {
		if (isset($_SESSION['status']) && !empty($_SESSION['status']) && $_SESSION['status'] == 2) {
			return true;
		}
		
		return false;
	}
	
	public static function isReseller () {
		if (self::isAdmin() === true) {
			return true;
		}
		
		if (isset($_SESSION['status']) && !empty($_SESSION['status']) && $_SESSION['status'] === 1) {
			return true;
		}
		
		return false;
	}
	
	public static function cookieLogin ($userID, $hash) {
		$query = DNS::getDB()->query("SELECT * FROM dns_user WHERE SHA1(userID) = ?", array($userID));
		$row = DNS::getDB()->fetch_array($query);
		if (!empty($row)) {
			$sha1UserID = sha1($row["userID"]);
			$sha1Password = sha1($row['password']);
			$sha1CookieHash = sha1($sha1UserID.$sha1Password);
			if ($sha1CookieHash == $hash) {
				$_SESSION['login'] = 1;
				$_SESSION['username'] = $row["username"];
				$_SESSION['userID'] = $row["userID"];
				$_SESSION['status'] = intval($row["status"]);
				return true;
			}
		}
		
		return false;
	}
	
	public static function login ($username, $password, $remember = false) {
		$query = DNS::getDB()->query("SELECT * FROM dns_user WHERE username = ?", array($username));
		$row = DNS::getDB()->fetch_array($query);
		if (!empty($row)) {
			if (crypt(crypt($password, $row['password']), $row['password']) == $row['password']) {
				$_SESSION['login'] = 1;
				$_SESSION['username'] = $row["username"];
				$_SESSION['userID'] = $row["userID"];
				$_SESSION['status'] = intval($row["status"]);
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
	
	public static function logout () {
		$_SESSION = array(); // clear session array before destroy
		
		if (isset($_COOKIE["userID"])) {
			setcookie("userID", '', time() - 3600);
		}
		
		if (isset($_COOKIE["cookieHash"])) {
			setcookie("cookieHash", '', time() - 3600);
		}
		
		session_destroy();
	}
	
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
	
	public static function deleteUser ($userID) {
		DNS::getDB()->query("DELETE FROM dns_user WHERE userID = ?", array($userID));
	}
	
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
	
	public static function getAccessibleDomains ($userID = 0) {
		$data = array();
		
		if ($userID === 0 && self::isLoggedIn()) {
			if (isset($_SESSION['userID'])) {
				$userID = $_SESSION['userID'];
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
