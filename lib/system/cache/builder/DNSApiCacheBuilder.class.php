<?php
namespace dns\system\cache\builder;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2016 Jan Altensen (Stricted)
 */
class DNSApiCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\dns\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 30;
	
	/**
	 * @see	\dns\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		
		$sql = "SELECT * FROM dns_soa where active = ?";
		$statement = DNS::getDB()->query($sql, array(1));
		
		while ($zone = DNS::getDB()->fetch_array($statement)) {
			$data[$zone['origin']] = array();
			$data[$zone['origin']]['soa'] = $zone;
			$data[$zone['origin']]['rr'] = array();
			$data[$zone['origin']]['sec'] = array();
			
			/* resource records */
			$sql2 = "SELECT * FROM dns_rr where zone = ? and active = ?";
			$statement2 = DNS::getDB()->query($sql2, array($zone['id'], 1));
			while ($rr = DNS::getDB()->fetch_array($statement2)) {
				$data[$zone['origin']]['rr'][] = $rr;
			}
			
			if (ENABLE_DNSSEC) {
				/* dnssec keys */
				$sql3 = "SELECT * FROM dns_sec where zone = ? and active = ?";
				$statement3 = DNS::getDB()->query($sql3, array($zone['id'], 1));
				while ($sec = DNS::getDB()->fetch_array($statement3)) {
					$data[$zone['origin']]['sec'][] = $sec;
				}
			}
		}
		
		return $data;
	}
}
