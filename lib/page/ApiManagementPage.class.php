<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class ApiManagementPage extends AbstractPage {
	public $activeMenuItem = 'api';
	
	public function prepare() {
		$sql = "SELECT * FROM dns_api WHERE userID = ?";
		$res = DNS::getDB()->query($sql, array(DNS::getSession()->userID));
		$row = DNS::getDB()->fetch_array($res);
		
		$apiKey = "";
		
		if (isset($row['apiKey'])) {
			$apiKey = $row['apiKey'];
		}
		
		DNS::getTPL()->assign(array("userID" => DNS::getSession()->userID,"apiKey" => $apiKey));
	}
}
