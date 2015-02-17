<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class UserListPage extends AbstractPage {
	/*public $activeMenuItem = 'index';*/
	
	public function prepare() {
		if (User::isLoggedIn() && User::isReseller()) {
			if (User::isAdmin()) {
				$sql = "SELECT * from dns_user";
				$res = DNS::getDB()->query($sql);
			}
			else {
				$sql = "SELECT * from dns_user WHERE reseller = ?";
				$res = DNS::getDB()->query($sql, array($_SESSION['userID']));
			}
			
			$user = array();
			while ($row = DNS::getDB()->fetch_array($res)) {
				$user[] = $row;
			}
			
			DNS::getTPL()->assign(array("user" => $user));
		}
		else {
			throw new \Exeption('Forbidden', 403);
		}
	}
}
