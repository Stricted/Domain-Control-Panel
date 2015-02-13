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
		if (count($soaIDs) > 0) {
			$sql = "SELECT * FROM dns_soa WHERE id IN (".str_repeat('?, ', count($soaIDs) - 1). "?)";
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
		
		DNS::getTPL()->assign(array("domains" => $domains));
	}
}
