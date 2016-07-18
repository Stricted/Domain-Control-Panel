<?php
namespace dns\system\cache\builder;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2016 Jan Altensen (Stricted)
 */
class UserCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\dns\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 30;
	
	/**
	 * @see	\dns\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		
		$sql = "SELECT * FROM dns_user";
		$statement = DNS::getDB()->query($sql);
		
		while ($row = DNS::getDB()->fetch_array($statement)) {
			$data[] = $row;
		}
		
		return $data;
	}
}
