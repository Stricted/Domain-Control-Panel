<?php
namespace dns\page;
use dns\system\DNS;
use dns\system\User;
use dns\api\idna\idna_convert;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class IndexPage extends AbstractPage {
	public $activeMenuItem = 'index';
	
	public function prepare() {
		$domains = array();
		$soaIDs = User::getAccessibleDomains();
		$idna = new idna_convert();
		
		$sortField = "id";
		$sortOrder = "ASC";
		$sqlOrderBy = "";
		$validSortFields = array('id', 'origin', 'serial');
		
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
		$pages = intval(ceil(count($soaIDs) / $itemsPerPage));
		
		
		if (count($soaIDs) > 0) {
			$sql = "SELECT * FROM dns_soa WHERE id IN (".str_repeat('?, ', count($soaIDs) - 1). "?)".(!empty($sqlOrderBy) ? " ORDER BY ".$sqlOrderBy : '')." LIMIT " . $sqlLimit . " OFFSET " . $sqlOffset;
			$res = DNS::getDB()->query($sql, $soaIDs);
			while ($row = DNS::getDB()->fetch_array($res)) {
				$sql2 = "SELECT count(*) as count FROM dns_rr WHERE zone = ?";
				$res2 = DNS::getDB()->query($sql2, array($row['id']));
				$row2 = DNS::getDB()->fetch_array($res2);
				$row['origin'] = $idna->decode($row['origin']);
				$row['rrc'] = $row2['count'];
				$domains[] = $row;
			}
		}
		
		DNS::getTPL()->assign(array(
			'domains' => $domains,
			'pageNo' => $pageNo,
			'pages' => $pages,
			'sortField' => $sortField,
			'sortOrder' => $sortOrder
		));
	}
}
