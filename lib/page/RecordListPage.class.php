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
class RecordListPage extends AbstractPage {
	public $activeMenuItem = 'index';
	
	public function prepare() {
		if (!isset($_GET['id']) || empty($_GET['id'])) {
			throw new \Exception('The link you are trying to reach is no longer available or invalid.', 404);
		}
		
		$soaIDs = User::getAccessibleDomains();
		if (!in_array($_GET['id'], $soaIDs)) {
			throw new \Exception('Access denied. You\'re not authorized to view this page.', 403);
		}
		
		$idna = new idna_convert();
		
		$sql = "SELECT count(*) as count FROM dns_rr WHERE zone = ?";
		$res = DNS::getDB()->query($sql, array($_GET['id']));
		$row = DNS::getDB()->fetch_array($res);
		$count = $row['count'];
		
		$sortField = "type";
		$sortOrder = "ASC";
		$sqlOrderBy = "";
		$validSortFields = array('id', 'name', 'type', 'ttl', 'data');
		
		if (isset($_GET['sortField'])) {
			if (in_array($_GET['sortField'], $validSortFields)) {
				$sortField = $_GET['sortField'];
			}
		}
		
		if (isset($_GET['sortOrder'])) {
			if ($_GET['sortOrder'] == "ASC" || $_GET['sortOrder'] == "DESC") {
				$sortOrder = $_GET['sortOrder'];
			}
		}
		
		if (!empty($sortField) && !empty($sortField)) {
			$sqlOrderBy = $sortField." ".$sortOrder;
		}
		
		$pageNo = 1;
		if (isset($_GET['pageNo']) && !empty($_GET['pageNo'])) {
			$pageNo = intval($_GET['pageNo']);
		}
		
		$itemsPerPage = 20;
		$pages = 0;
		
		$sqlLimit = $itemsPerPage;
		$sqlOffset = ($pageNo - 1) * $itemsPerPage;
		$pages = intval(ceil($count / $itemsPerPage));
		
		$sql = "SELECT * FROM dns_soa WHERE id = ?";
		$res = DNS::getDB()->query($sql, array($_GET['id']));
		$soa = DNS::getDB()->fetch_array($res);
		
		$soa['origin'] = $idna->decode($soa['origin']);
		
		$records = array();
		
		$sql = "SELECT * FROM dns_rr WHERE zone = ?".(!empty($sqlOrderBy) ? " ORDER BY ".$sqlOrderBy : '')." LIMIT " . $sqlLimit . " OFFSET " . $sqlOffset;
		$res = DNS::getDB()->query($sql, array($_GET['id']));
		while ($row = DNS::getDB()->fetch_array($res)) {
			$row['name'] = $idna->decode($row['name']);
			if ($row['type'] == "SRV") {
				$data = explode(" ", $row['data']);
				$weight = $data[0];
				$port = $data[1];
				$data = $idna->encode($data[2]);
				
				$data = $weight.' '.$port.' '.$data;
				
				$row['data'] = $idna->decode($data);
			}
			else {
				if ($row['type'] == "TLSA" || $row['type'] == "DS") {
					$row['data'] = $row['data'];
				}
				else {
					$row['data'] = $idna->decode($row['data']);
				}
			}
			$records[] = $row;
		}
		
		DNS::getTPL()->assign(array(
			'records' => $records,
			'soa' => $soa,
			'pageNo' => $pageNo,
			'pages' => $pages,
			'count' => $count,
			'sortField' => $sortField,
			'sortOrder' => $sortOrder
		));
	}
}
