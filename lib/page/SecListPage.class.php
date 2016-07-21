<?php
namespace dns\page;
use dns\system\helper\IDatabase;
use dns\system\helper\TDatabase;
use dns\system\DNS;
use dns\system\User;
use dns\util\DNSSECUtil;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
class SecListPage extends AbstractPage implements IDatabase {
	use TDatabase;
	public $activeMenuItem = 'index';
	
	public function prepare() {
		if (!isset($_GET['id']) || empty($_GET['id']) || !ENABLE_DNSSEC) {
			throw new \Exception('The link you are trying to reach is no longer available or invalid.', 404);
		}
		
		$soaIDs = User::getAccessibleDomains();
		if (!in_array($_GET['id'], $soaIDs)) {
			throw new \Exception('Access denied. You\'re not authorized to view this page.', 403);
		}
		
		$sql = "SELECT * FROM dns_soa WHERE id = ?";
		$res = $this->db->query($sql, array($_GET['id']));
		$soa = $this->db->fetch_array($res);
		
		$records = array();
		$ds = array();
		
		$sql = "SELECT * FROM dns_sec WHERE zone = ?";
		$res = $this->db->query($sql, array($_GET['id']));
		while ($row = $this->db->fetch_array($res)) {
			if ($row['type'] == 'KSK') {
				preg_match("/".$soa['origin']." IN DNSKEY 257 3 ([0-9]+) ([\s\S]+)/i", $row['public'], $match);
				preg_match("/; This is a key-signing key, keyid ([0-9]+), for ".$soa['origin']."/i", $row['public'], $match2);
				if (!empty($match) && !empty($match2)) {
					if ($match[1] == $row['algo']) {
						$ds = DNSSECUtil::calculateDS($soa['origin'], $match[1], $match[2]);
						$ds['algo'] = $match[1];
						$ds['keyid'] = $match2[1];
					}
				}
			}
			$records[] = $row;
		}
		
		$this->tpl->assign(array("records" => $records, "soa" => $soa, 'ds' => $ds));
	}
}
