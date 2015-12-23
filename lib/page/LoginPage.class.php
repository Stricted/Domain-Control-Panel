<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class LoginPage extends AbstractPage {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	public function prepare() {
		if (isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password'])) {
			if (!empty($_POST['submit']) && !empty($_POST['username']) && !empty($_POST['password'])) {
				$remember = false;
				if (isset($_POST['remember']) && !empty($_POST['remember'])) {
					$remember = true;
				}
				
				User::login(trim($_POST['username']), trim($_POST['password']), $remember);
				header("Location: index.php?index");
			}
		}
	}
}
