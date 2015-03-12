<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class DomainAddPage extends AbstractPage {
	public $activeMenuItem = 'add';
	
	public function prepare() {
		if (User::isReseller() === false) {
			throw new \Exeption('Forbidden', 403);
		}
		if (isset($_POST['origin']) && isset($_POST['submit'])) {
			if (!empty($_POST['origin'])) {
				$origin = $_POST['origin'];
				if (substr($origin, -1) != ".") {
					$origin = $origin.".";
				}
				
				$serial = date("Ymd")."01";
				
				$sql = "SELECT * FROM dns_soa WHERE origin = ?";
				$res = DNS::getDB()->query($sql, array($origin));
				$soa = DNS::getDB()->fetch_array($res);
							
				if (empty($soa)) {
					$soaData = array($origin, DNS_SOA_NS, DNS_SOA_MBOX, $serial, DNS_SOA_REFRESH, DNS_SOA_RETRY, DNS_SOA_EXPIRE, DNS_SOA_MINIMUM_TTL, DNS_SOA_TTL, 1);
				
					$sql = "INSERT INTO dns_soa (id, origin, ns, mbox, serial, refresh, retry, expire, minimum, ttl, active) VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
					DNS::getDB()->query($sql, $soaData);
					$soaID = DNS::getDB()->last_id();
					
					$sql = "INSERT INTO dns_soa_to_user (id, userID, soaID) VALUES (null, ?, ?)";
					DNS::getDB()->query($sql, array(DNS::getSession()->userID, $soaID));
					
					$sql = "SELECT * FROM dns_template WHERE userID = ?";
					$res = DNS::getDB()->query($sql, array(DNS::getSession()->userID));
					$tpl = DNS::getDB()->fetch_array($res);
					
					$records = array();
					if (!empty($tpl) && !empty($tpl['template'])) {
						$records = explode("\n", $tpl['template']);
					}
					else {
						$records = explode("\n", DNS_DEFAULT_RECORDS);
					}
					
					if (!empty($records)) {
						foreach ($records as $record) {
							$record = str_replace("{domain}", $origin, $record);
							$record = explode(":", $record, 3);
							
							$rrData = array($soaID, $record[0], $record[1], $record[2], ($record[1] == "MX" ? 10 : 0), DNS_SOA_MINIMUM_TTL);
							$sql = 'INSERT INTO dns_rr (id, zone, name, type, data, aux, ttl) VALUES (NULL, ?, ?, ?, ?, ?, ?)';
							DNS::getDB()->query($sql, $rrData);
						}
					}
					DNS::getTPL()->assign(array("error" => '', 'success' => true));
				}
				else {
					DNS::getTPL()->assign(array("error" => 'origin', 'origin' => $_POST['origin']));
				}
			}
			else {
				DNS::getTPL()->assign(array("error" => 'origin'));
			}
		}
		else {
			DNS::getTPL()->assign(array("error" => ''));
		}
	}
}
