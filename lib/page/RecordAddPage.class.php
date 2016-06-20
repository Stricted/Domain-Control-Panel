<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;
use Mso\IdnaConvert\IdnaConvert;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
class RecordAddPage extends AbstractPage {
	public $activeMenuItem = 'index';
	
	public function prepare() {
		if (!isset($_GET['id']) || empty($_GET['id'])) {
			throw new \Exception('The link you are trying to reach is no longer available or invalid.', 404);
		}
		
		$soaIDs = User::getAccessibleDomains();
		if (!in_array($_GET['id'], $soaIDs)) {
			throw new \Exception('Access denied. You\'re not authorized to view this page.', 403);
		}
		$idna = new IdnaConvert();
		
		$sql = "SELECT * FROM dns_soa WHERE id = ?";
		$res = DNS::getDB()->query($sql, array($_GET['id']));
		$soa = DNS::getDB()->fetch_array($res);
		
		$soa['origin'] = $idna->decode($soa['origin']);
		
		DNS::getTPL()->assign(array("soa" => $soa));
		
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
			
			$sql = 'SELECT * FROM dns_rr WHERE zone = ? AND name = ? AND type = ? AND data = ?';
			$res = DNS::getDB()->query($sql, array($_GET['id'], $name, $type, $data));
			$rr = DNS::getDB()->fetch_array($res);
			if (!empty($rr)) {
				$error = array_merge($error, array('type', 'data'));
			}
			
			if (empty($error)) {
				$sql = 'INSERT INTO dns_rr (id, zone, name, type, data, aux, ttl) VALUES (NULL, ?, ?, ?, ?, ?, ?)';
				if ($type == "SRV" || $type == "DS" || $type == "TLSA") {
					DNS::getDB()->query($sql, array($_GET['id'], $name, $type, $data, $aux, $ttl));
				}
				else {
					DNS::getDB()->query($sql, array($_GET['id'], $name, $type, $idna->encode($data), $aux, $ttl));
				}
				
				$sql = "UPDATE dns_soa SET serial = ? WHERE id = ?";
				DNS::getDB()->query($sql, array($this->fixSerial($soa['serial']), $soa['id']));
				DNS::getTPL()->assign(array('success' => true));
			}
			else {
				if ($type == "SRV" || $type == "DS" || $type == "TLSA") {
					DNS::getTPL()->assign(array('name' => $idna->decode($name), 'type' => $type, 'weight' => $_POST['weight'], 'port' => $_POST['port'], 'data' => $_POST['data'], 'aux' => $aux, 'ttl' => $ttl));
				}
				else {
					DNS::getTPL()->assign(array('name' => $idna->decode($name), 'type' => $type, 'data' => $data, 'aux' => $aux, 'ttl' => $ttl));
				}
			}
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
