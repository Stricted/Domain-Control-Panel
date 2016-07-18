<?php
namespace dns\system\cache\builder;
use dns\system\DNS;

/**
 * Caches the simple ACL settings per object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class AclCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [];
				
		$sql = "SELECT * FROM dns_soa_to_user";
		$statement = DNS::getDB()->query($sql);
		
		while ($row = DNS::getDB()->fetch_array($statement)) {
			$data[] = $row;
		}
		
		return $data;
	}
}