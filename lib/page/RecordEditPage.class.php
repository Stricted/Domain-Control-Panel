<?php
namespace dns\page;
use dns\system\api\idna\idna_convert;
use dns\system\DNS;
use dns\system\User;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
class RecordEditPage extends AbstractPage {
	public $activeMenuItem = 'index';
	
	public function prepare() {
		if (!isset($_GET['id']) || empty($_GET['id'])) {
			throw new \Exception('The link you are trying to reach is no longer available or invalid.', 404);
		}
		$idna = new idna_convert();
		
		$sql = "SELECT * FROM dns_rr WHERE id = ?";
		$res = DNS::getDB()->query($sql, array($_GET['id']));
		$rr = DNS::getDB()->fetch_array($res);
		
		$soaIDs = User::getAccessibleDomains();
		if (!in_array($rr['zone'], $soaIDs)) {
			throw new \Exception('Access denied. You\'re not authorized to view this page.', 403);
		}
				
		$sql = "SELECT * FROM dns_soa WHERE id = ?";
		$res = DNS::getDB()->query($sql, array($rr['zone']));
		$soa = DNS::getDB()->fetch_array($res);
		
		$soa['origin'] = $idna->decode($soa['origin']);
		
		DNS::getTPL()->assign(array("soa" => $soa, "rr" => $rr));
		
		$types = array('A', 'AAAA', 'CNAME', 'MX', 'PTR', 'SRV', 'TXT', 'TLSA', 'NS', 'DS');
		$error = array();
		if (isset($_POST['submit']) && !empty($_POST['submit'])) {
			if (isset($_POST['name']) && isset($_POST['ttl']) && !empty($_POST['ttl']) && isset($_POST['type']) && !empty($_POST['type']) && isset($_POST['data']) && !empty($_POST['data'])) {
				$type = trim($_POST['type']);
				
				if (!empty($_POST['name'])) {
					$name = $idna->encode(trim($_POST['name']));
				}
				else {
					$name = $idna->encode(trim($soa['origin']));
				}
				
				if (in_array($type, $types)) {
					$aux = 0;
					if (($type == "MX" || $type == "TLSA" || $type == "SRV" || $type == "DS") && isset($_POST['aux']) && !empty($_POST['aux'])) {
							$aux = trim($_POST['aux']);
					}
					
					$data = trim($_POST['data']);
					if ($type == "SRV" || $type == "DS") {
						if (isset($_POST['weight']) && !empty($_POST['weight']) && isset($_POST['port']) && !empty($_POST['port'])) {
							if ($type == "SRV") {
								$data = $idna->encode($data);
							}
							$data = trim($_POST['weight']).' '.trim($_POST['port']).' '.$data;
						}
						else {
							$error = array_merge($error, array('weight', 'port', 'data'));
						}
					}
					
					$ttl = $_POST['ttl'];
					if ($ttl < DNS_SOA_MINIMUM_TTL) {
						$ttl = DNS_SOA_MINIMUM_TTL;
					}
					
					if ($type == "TLSA") {
						if ($aux != 3) {
							// fallback
							$aux = 3;
						}
						
						if (isset($_POST['weight']) && isset($_POST['port'])) {
							if (!is_numeric($_POST['weight'])) {
								$error = array_merge($error, array('weight'));
							}
							else if (!is_numeric($_POST['port'])) {
								$error = array_merge($error, array('weight'));
							}
							else if (strlen($_POST['data']) != 64) {
								$error = array_merge($error, array('data'));
							}
							else {
								$data = trim($_POST['weight']).' '.trim($_POST['port']).' '.$data;
							}
						}
						else {
							$error = array_merge($error, array('weight', 'port', 'data'));
						}
					}
					
					if ($type == "A") {
						if (filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
							$error = array_merge($error, array('data'));
						}
					}
					else if ($type == "AAAA") {
						if (filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
							$error = array_merge($error, array('data'));
						}
					}
				}
				else {
					$error = array_merge($error, array('type'));
				}				
			}
			else {
				$error = array_merge($error, array('name', 'ttl', 'data'));
			}
			
			$sql = 'SELECT * FROM dns_rr WHERE zone = ? AND name = ? AND type = ? AND data = ? AND id != ?';
			$res = DNS::getDB()->query($sql, array($rr['zone'], $name, $type, $data, $_GET['id']));
			$rr = DNS::getDB()->fetch_array($res);
			if (!empty($rr)) {
				$error = array_merge($error, array('type', 'data'));
			}
			
			if (empty($error)) {
				
				$sql = 'UPDATE dns_rr SET name = ?, type = ?, aux = ?, data = ?, ttl = ? WHERE id = ?';
				if ($type == "SRV" || $type == "DS" || $type == "TLSA") {
					DNS::getDB()->query($sql, array($name, $type, $aux, $data, $ttl, $_GET['id']));
				}
				else {
					DNS::getDB()->query($sql, array($name, $type, $aux, $idna->encode($data), $ttl, $_GET['id']));
				}
				
				$sql = "UPDATE dns_soa SET serial = ? WHERE id = ?";
				DNS::getDB()->query($sql, array($this->fixSerial($soa['serial']), $soa['id']));
				
				$sql = "SELECT * FROM dns_rr WHERE id = ?";
				$res = DNS::getDB()->query($sql, array($_GET['id']));
				$rr = DNS::getDB()->fetch_array($res);
				
				$weight = 0;
				$port = 0;
				$data = $rr['data'];
				$type = $rr['type'];
				$name = $idna->decode($rr['name']);
				$aux = $rr['aux'];
				$ttl = $rr['ttl'];
				if ($type == "SRV" || $type == "DS" || $type == "TLSA") {
					$datae = explode(" ", $data);
					$weight = $datae[0];
					$port = $datae[1];
					if ($type == "SRV") {
						$data = $idna->decode($datae[2]);
					}
					else {
						$data = $datae[2];
					}
				}
				else {
					$data = $idna->decode($data);
				}
				
				DNS::getTPL()->assign(array('name' => $name, 'type' => $type, 'weight' => $weight, 'port' => $port, 'data' => $data, 'aux' => $aux, 'ttl' => $ttl));
				DNS::getTPL()->assign(array('success' => true));
			}
			else {
				if ($type == "SRV" || $type == "DS" || $type == "TLSA") {
					DNS::getTPL()->assign(array('name' => $idna->decode($name), 'type' => $type, 'weight' => $_POST['weight'], 'port' => $_POST['port'], 'data' => $_POST['data'], 'aux' => $aux, 'ttl' => $ttl));
				}
				else {
					DNS::getTPL()->assign(array('name' => $idna->decode($name), 'type' => $type, 'data' => $idna->decode($data), 'aux' => $aux, 'ttl' => $ttl));
				}
			}
		}
		else {
			$weight = 0;
			$port = 0;
			$data = $rr['data'];
			$type = $rr['type'];
			$name = $idna->decode($rr['name']);
			$aux = $rr['aux'];
			$ttl = $rr['ttl'];
			if ($type == "SRV" || $type == "DS" || $type == "TLSA") {
				$datae = explode(" ", $data);
				$weight = $datae[0];
				$port = $datae[1];
				if ($type == "SRV") {
					$data = $idna->decode($datae[2]);
				}
				else {
					$data = $datae[2];
				}
			}
			else {
				$data = $idna->decode($data);
			}
			
			DNS::getTPL()->assign(array('name' => $name, 'type' => $type, 'weight' => $weight, 'port' => $port, 'data' => $data, 'aux' => $aux, 'ttl' => $ttl));
		}
		
		DNS::getTPL()->assign(array("error" => $error));
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
