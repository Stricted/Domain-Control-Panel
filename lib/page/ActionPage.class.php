<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;
use dns\util\ParseZone;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class ActionPage extends AbstractPage {	
	public function prepare() {
		if (!isset($_POST['action']) || empty($_POST['action']) || !isset($_POST['dataID'])) {
			echo "failure";
			exit;
		}
		
		$action = trim($_POST['action']);
		$dataID = intval(trim($_POST['dataID']));
		if ($action == "toggleDomain") {
			if (User::isReseller() === false) {
				echo "failure";
				exit;
			}
			
			$soaIDs = User::getAccessibleDomains();
			if (!in_array($dataID, $soaIDs)) {
				echo "failure";
				exit;
			}
			
			$sql = "SELECT active, serial FROM dns_soa WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($dataID));
			$soa = DNS::getDB()->fetch_array($res);
			
			$active = ($soa['active'] ? 0 : 1);
			
			$sql = "UPDATE dns_soa SET active = ?, serial = ? WHERE id = ?";
			DNS::getDB()->query($sql, array($active, $this->fixSerial($soa['serial']), $dataID));
			
			echo "success";
			exit;
		}
		else if ($action == "deleteDomain") {
			if (User::isReseller() === false) {
				echo "failure";
				exit;
			}
			
			$soaIDs = User::getAccessibleDomains();
			if (!in_array($dataID, $soaIDs)) {
				echo "failure";
				exit;
			}
			
			$sql = "DELETE FROM dns_soa WHERE id = ?";
			DNS::getDB()->query($sql, array($dataID));
			
			echo "success";
			exit;
		}
		else if ($action == "toggleRecord") {
			$sql = "SELECT zone FROM dns_rr WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($dataID));
			$rr = DNS::getDB()->fetch_array($res);
			$soaID = $rr['zone'];
			
			$soaIDs = User::getAccessibleDomains();
			if (!in_array($soaID, $soaIDs)) {
				echo "failure";
				exit;
			}
			
			$sql = "SELECT active FROM dns_rr WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($dataID));
			$rr = DNS::getDB()->fetch_array($res);
			
			$active = ($rr['active'] ? 0 : 1);
			
			$sql = "UPDATE dns_rr SET active = ? WHERE id = ?";
			DNS::getDB()->query($sql, array($active, $dataID));
			
			$sql = "SELECT serial FROM dns_soa WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($soaID));
			$soa = DNS::getDB()->fetch_array($res);
			
			$sql = "UPDATE dns_soa SET serial = ? WHERE id = ?";
			DNS::getDB()->query($sql, array($this->fixSerial($soa['serial']), $soaID));
			
			echo "success";
			exit;
		}
		else if ($action == "deleteRecord") {
			$sql = "SELECT zone FROM dns_rr WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($dataID));
			$rr = DNS::getDB()->fetch_array($res);
			$soaID = $rr['zone'];
			
			$soaIDs = User::getAccessibleDomains();
			if (!in_array($soaID, $soaIDs)) {
				echo "failure";
				exit;
			}
			
			$sql = "DELETE FROM dns_rr WHERE id = ?";
			DNS::getDB()->query($sql, array($dataID));
			
			$sql = "SELECT serial FROM dns_soa WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($soaID));
			$soa = DNS::getDB()->fetch_array($res);
			
			$sql = "UPDATE dns_soa SET serial = ? WHERE id = ?";
			DNS::getDB()->query($sql, array($this->fixSerial($soa['serial']), $soaID));
			
			echo "success";
			exit;
		}
		else if ($action == "toggleSec") {
			$sql = "SELECT zone FROM dns_sec WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($dataID));
			$rr = DNS::getDB()->fetch_array($res);
			$soaID = $rr['zone'];
			
			$soaIDs = User::getAccessibleDomains();
			if (!in_array($soaID, $soaIDs)) {
				echo "failure";
				exit;
			}
			
			$sql = "SELECT active FROM dns_sec WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($dataID));
			$rr = DNS::getDB()->fetch_array($res);
			
			$active = ($rr['active'] ? 0 : 1);
			
			$sql = "UPDATE dns_sec SET active = ? WHERE id = ?";
			DNS::getDB()->query($sql, array($active, $dataID));
			
			$sql = "SELECT serial FROM dns_soa WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($soaID));
			$soa = DNS::getDB()->fetch_array($res);
			
			$sql = "UPDATE dns_soa SET serial = ? WHERE id = ?";
			DNS::getDB()->query($sql, array($this->fixSerial($soa['serial']), $soaID));
			
			echo "success";
			exit;
		}
		else if ($action == "deleteSec") {
			$sql = "SELECT zone FROM dns_sec WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($dataID));
			$rr = DNS::getDB()->fetch_array($res);
			$soaID = $rr['zone'];
			
			$soaIDs = User::getAccessibleDomains();
			if (!in_array($soaID, $soaIDs)) {
				echo "failure";
				exit;
			}
			
			$sql = "DELETE FROM dns_sec WHERE id = ?";
			DNS::getDB()->query($sql, array($dataID));
			
			$sql = "SELECT serial FROM dns_soa WHERE id = ?";
			$res = DNS::getDB()->query($sql, array($soaID));
			$soa = DNS::getDB()->fetch_array($res);
			
			$sql = "UPDATE dns_soa SET serial = ? WHERE id = ?";
			DNS::getDB()->query($sql, array($this->fixSerial($soa['serial']), $soaID));
			
			echo "success";
			exit;
		}
		else if ($action == "requestApiKey") {
			if (User::isLoggedIn()) {
				$sql = "SELECT * FROM dns_api WHERE userID = ?";
				$res = DNS::getDB()->query($sql, array(DNS::getSession()->userID));
				$row = DNS::getDB()->fetch_array($res);
				
				if (empty($row)) {
					$apiKey = DNS::generateUUID();
					
					$sql = "INSERT INTO dns_api (id, userID, apiKey) VALUES (NULL, ?, ?)";
					DNS::getDB()->query($sql, array(DNS::getSession()->userID, $apiKey));
					
					echo $apiKey;
					exit;
				}
			}
		}
		else if ($action == "import") {
			if (isset($_POST['zone']) && !empty($_POST['zone'])) {
				if ($dataID == 0) {
					if (isset($_POST['origin']) && !empty($_POST['origin'])) {
						/*
						if (User::isReseller() === false) {
							echo "failure";
							exit;
						}
						*/
						// new zone
					}
				}
				else {
					$soaIDs = User::getAccessibleDomains();
					if (!in_array($dataID, $soaIDs)) {
						echo "failure";
						exit;
					}
					
					$sql = 'SELECT * FROM dns_soa where id = ?';
					$res = DNS::getDB()->query($sql, array($dataID));
					$res = DNS::getDB()->fetch_array($res);
					$soa = $res;
					
					$parser = new ParseZone($_POST['zone'], $soa['origin']);
					
					try {
						$parser->parse();
					}
					catch (\Exception $e) {
						echo "failure";
						exit;
					}
					
					$data = $parser->getParsedData();
					if (!empty($data['rr'])) {
						// delete existing records
						foreach ($data['rr'] as $rr) {
							// dont update the default ns entrys, we add them automatically, all other ns entrys will be updated
							if (strtolower($rr['type']) != "ns" && strtolower($rr['name']) != strtolower($soa['origin'])) {
								// import data
							}
						}
					}
					else {
						echo "failure";
						exit;
					}
				}
			}
		}
		else if ($action == "export") {
			$sql = 'SELECT * FROM dns_soa where id = ?';
			$res = DNS::getDB()->query($sql, array($dataID));
			$res = DNS::getDB()->fetch_array($res);
			$soa = $res;
			
			$soaIDs = User::getAccessibleDomains();
			if (!in_array($soa['id'], $soaIDs)) {
				echo "failure";
				exit;
			}
			
			$out = ";; Domain:\t".$soa['origin']."\n";
			$out .= ";; Exported:\t".date("Y-m-d H:i:s")."\n";
			$out .= ";; \n";
			$out .= ";; This file is intended for use for informational and archival\n";
			$out .= ";; purposes ONLY and MUST be edited before use on a production\n";
			$out .= ";; DNS server.  In particular, you must:\n";
			$out .= ";;   -- update the SOA record with the correct authoritative name server\n";
			$out .= ";;   -- update the SOA record with the contact e-mail address information\n";
			$out .= ";;   -- update the NS record(s) with the authoritative name servers for this domain.\n";
			$out .= ";; \n";
			$out .= ";; For further information, please consult the BIND documentation\n";
			$out .= ";; located on the following website:\n";
			$out .= ";; \n";
			$out .= ";; http://www.isc.org/\n";
			$out .= ";; \n";
			$out .= ";; And RFC 1035:\n";
			$out .= ";; \n";
			$out .= ";; http://www.ietf.org/rfc/rfc1035.txt\n";
			$out .= ";; \n";
			$out .= ";; Please note that we do NOT offer technical support for any use\n";
			$out .= ";; of this zone data, the BIND name server, or any other third-party\n";
			$out .= ";; DNS software.\n";
			$out .= ";; \n";
			$out .= ";;\tUse at your own risk.\n";
			$out .= ";; \n";
			
			$out .= $soa['origin']."\t".$soa['minimum']."\tIN\tSOA\t".$soa['ns']."\t".$soa['mbox']."\t(\n";
			$out .=	"\t\t".$soa['serial']."\t; Serial\n";
			$out .=	"\t\t".$soa['refresh']."\t\t; Refresh\n";
			$out .=	"\t\t".$soa['retry']."\t\t; Retry\n";
			$out .=	"\t\t".$soa['expire']."\t\t; Expire\n";
			$out .=	"\t\t180 )\t\t; Negative Cache TTL\n";
			$out .=	";;\n";
			
			$sql = 'SELECT * FROM dns_rr where zone = ?';
			$res = DNS::getDB()->query($sql, array($soa['id']));
			while ($record = DNS::getDB()->fetch_array($res)) {
				if (!$record['active']) {
					$out .= ";; ";
				}
				
				if ($record['type'] == "MX" || $record['type'] == "SRV" || $record['type'] == "TLSA" || $record['type'] == "DS") {
					$out .= $record['name']."\t".$record['ttl']."\tIN\t".$record['type']."\t".$record['aux']."\t".$record['data']."\n";
				}
				else if ($record['type'] == "TXT") {
					$txt = $record['data'];
					
					if (strpos($txt, " ") !== false) {
						if (substr($txt, -1) != '"' && substr($txt, 0, 1) != '"') {
							if (substr($txt, -1) != "'" && substr($txt, 0, 1) != "'") {
								$record['data'] = '"'.$txt.'"';
							}
						}
					}
					
					if (strpos($record['data'], "v=spf1") !== false) {
						$out .= $record['name']."\t".$record['ttl']."\tIN\tSPF\t" . $record['data']."\n";
					}
					
					$out .= $record['name']."\t".$record['ttl']."\tIN\t".$record['type']."\t" . $record['data']."\n";
				}
				else {
					$out .= $record['name']."\t".$record['ttl']."\tIN\t".$record['type']."\t\t" . $record['data']."\n";
				}
			}
			
			echo $out;
			exit;
		}
		
		echo "failure";
		exit;
	}
	
	public function fixSerial ($old) {
		if (substr($old, 0, -2) == date("Ymd")) {
			$new = $old + 1;
		}
		else {
			$new = date("Ymd")."01";
		}
		
		return $new;
	}
}
