<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
class LogoutPage extends AbstractPage {
	public function prepare() {
		if (User::isLoggedIn()) {
			User::logout();
			header("Location: ?page=index");
		}
	}
}
