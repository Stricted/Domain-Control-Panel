<?php
namespace dns\system;
use dns\system\cache\builder\AclCacheBuilder;
use dns\system\cache\builder\DomainCacheBuilder;
use dns\system\cache\builder\UserCacheBuilder;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2016 Jan Altensen (Stricted)
 */
class AclHandler extends SingletonFactory {
	protected $acl = null;
	
	protected function init () {
		$this->acl = new Acl();
		
		$users = UserCacheBuilder::getInstance()->getData();
		$domains = DomainCacheBuilder::getInstance()->getData();
		$permissions = AclCacheBuilder::getInstance()->getData();
		
		/* add users */
		foreach ($users as $user) {
			$this->acl->addRole((string)$user['userID']);
		}
		
		/* add domains */
		foreach ($domains as $domain) {
			$this->acl->addResource((string)$domain['id']);
		}
		
		/* ass assignments */
		foreach ($permissions as $permission) {
			var_dump($permission);
			$this->acl->allow((string)$permission['userID'], (string)$permission['soaID']);
		}
		
		var_dump($this->acl->getRole(1));
		exit;
	}
	
	protected function resetCache () {
		AclCacheBuilder::getInstance()->reset();
		DomainCacheBuilder::getInstance()->reset();
		UserCacheBuilder::getInstance()->reset();
	}
	
	public function canAccess ($soaID, $userID = null) {
		if ($userID === null) $userID = DNS::getSession()->userID;
		return $this->acl->isAllowed((string)$soaID, (string)$userID);
	}
	
	public function addPermission ($soaID, $userID = null) {
		if ($userID === null) $userID = DNS::getSession()->userID;
		
		$sql = "INSERT INTO dns_soa_to_user (id, userID, soaID) VALUES (null, ?, ?)";
		DNS::getDB()->query($sql, array($userID, $soaID));
		
		$this->acl->allow((string)$userID, (string)$soaID);
		$this->resetCache();
	}
	
	public function delPermission ($soaID, $userID = null) {
		if ($userID === null) $userID = DNS::getSession()->userID;
		
		$sql = "DELETE FROM dns_soa_to_user WHERE userID = ? AND soaID = ?";
		DNS::getDB()->query($sql, array($userID, $soaID));
		
		$this->acl->removeAllow((string)$userID, (string)$soaID);
		$this->resetCache();
	}
	
	public function getUsersForDomain ($soaID) {
		$users = [];
		$permissions = AclCacheBuilder::getInstance()->getData();
		
		foreach ($permissions as $permission) {
			if ($permission['soaID'] == $soaID) {
				$users[] = $permission['userID']
			}
			else {
				continue;
			}
		}
		
		return $users;
	}
	
	public function getDomainsForUser ($userID) {
		$domains = [];
		$permissions = AclCacheBuilder::getInstance()->getData();
		
		foreach ($permissions as $permission) {
			if ($permission['userID'] == $userID) {
				$domains[] = $permission['soaID']
			}
			else {
				continue;
			}
		}
		
		return $domains;
	}
}
