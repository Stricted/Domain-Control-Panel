<?php
namespace dns\page;
use dns\system\helper\IDatabase;
use dns\system\helper\TDatabase;
use dns\system\DNS;
use dns\system\User;
use Mso\IdnaConvert\IdnaConvert;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
class DomainAddPage extends AbstractPage implements IDatabase {
	use TDatabase;
	public $activeMenuItem = 'add';
	
	public function prepare() {
		if (User::isReseller() === false) {
			throw new \Exeption('Forbidden', 403);
		}
		if (isset($_POST['origin']) && isset($_POST['submit'])) {
			if (!empty($_POST['origin'])) {
				$idna = new IdnaConvert();
				$origin = $_POST['origin'];
				if (substr($origin, -1) != ".") {
					$origin = $origin.".";
				}
				
				$origin = $idna->encode($origin);
				
				$serial = date("Ymd")."01";
				
				$sql = "SELECT * FROM dns_soa WHERE origin = ?";
				$res = $this->db->query($sql, array($origin));
				$soa = $this->db->fetch_array($res);
							
				if (empty($soa)) {
					$soaData = array($origin, DNS_SOA_NS, DNS_SOA_MBOX, $serial, DNS_SOA_REFRESH, DNS_SOA_RETRY, DNS_SOA_EXPIRE, DNS_SOA_MINIMUM_TTL, DNS_SOA_TTL, 1);
				
					$sql = "INSERT INTO dns_soa (id, origin, ns, mbox, serial, refresh, retry, expire, minimum, ttl, active) VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
					$this->db->query($sql, $soaData);
					$soaID = $this->db->last_id();
					
					$sql = "INSERT INTO dns_soa_to_user (id, userID, soaID) VALUES (null, ?, ?)";
					$this->db->query($sql, array(DNS::getSession()->userID, $soaID));
					
					$sql = "SELECT * FROM dns_template WHERE userID = ?";
					$res = $this->db->query($sql, array(DNS::getSession()->userID));
					$tpl = $this->db->fetch_array($res);
					
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
							$this->db->query($sql, $rrData);
						}
					}
					$this->tpl->assign(array("error" => '', 'success' => true));
				}
				else {
					$this->tpl->assign(array("error" => 'origin', 'origin' => $_POST['origin']));
				}
			}
			else {
				$this->tpl->assign(array("error" => 'origin'));
			}
		}
		else {
			$this->tpl->assign(array("error" => ''));
		}
	}
}
