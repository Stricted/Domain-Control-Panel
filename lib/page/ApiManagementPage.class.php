<?php
namespace dns\page;
use dns\system\helper\IDatabase;
use dns\system\helper\TDatabase;
use dns\system\DNS;
use dns\system\User;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
class ApiManagementPage extends AbstractPage implements IDatabase {
	use TDatabase;
	public $activeMenuItem = 'api';
	
	public function prepare() {
		$sql = "SELECT * FROM dns_api WHERE userID = ?";
		$res = $this->db->query($sql, array(DNS::getSession()->userID));
		$row = $this->db->fetch_array($res);
		
		$apiKey = "";
		
		if (isset($row['apiKey'])) {
			$apiKey = $row['apiKey'];
		}
		
		$this->tpl->assign(array("userID" => DNS::getSession()->userID,"apiKey" => $apiKey));
	}
}
