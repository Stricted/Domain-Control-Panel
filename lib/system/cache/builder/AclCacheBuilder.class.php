<?php
namespace dns\system\cache\builder;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2016 Jan Altensen (Stricted)
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
