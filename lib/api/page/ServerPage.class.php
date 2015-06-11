<?php
namespace dns\page;
use dns\page\AbstractPage;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class ServerPage extends AbstractPage {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	public function prepare() {
		// todo: user/server seletion
		$key = "";
		if (isset($_REQUEST['key'])) {
			$key = strtoupper(trim($_REQUEST['key']));
		}
		
		if (!defined('DNS_API_KEY') || $key != DNS_API_KEY || empty($key) || !preg_match('/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-[89ab][a-f0-9]{3}\-[a-f0-9]{12}/i', $key)) {
			header('Content-Type: application/json');
			echo json_encode(array("error" => "wrong access key"), JSON_PRETTY_PRINT);
			exit;
		}
		else {
			$data = array();
			
			$sql = "SELECT * FROM dns_soa where active = ?";
			$statement = DNS::getDB()->query($sql, array(1));
			
			while ($zone = DNS::getDB()->fetch_array($statement)) {
				$data[$zone['origin']] = array();
				$data[$zone['origin']]['soa'] = $zone;
				$data[$zone['origin']]['rr'] = array();
				$data[$zone['origin']]['sec'] = array();
				
				/* resource records */
				$sql2 = "SELECT * FROM dns_rr where zone = ? and active = ?";
				$statement2 = DNS::getDB()->query($sql2, array($zone['id'], 1));
				while ($rr = DNS::getDB()->fetch_array($statement2)) {
					$data[$zone['origin']]['rr'][] = $rr;
				}
				
				if (ENABLE_DNSSEC) {
					/* dnssec keys */
					$sql3 = "SELECT * FROM dns_sec where zone = ? and active = ?";
					$statement3 = DNS::getDB()->query($sql3, array($zone['id'], 1));
					while ($sec = DNS::getDB()->fetch_array($statement3)) {
						$data[$zone['origin']]['sec'][] = $sec;
					}
				}
			}

			header('Content-Type: application/json');
			echo json_encode($data, JSON_PRETTY_PRINT);
			exit;
		}
	}
}
