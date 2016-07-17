<?php
namespace dns\system\cache\builder;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2016 Jan Altensen (Stricted)
 */
class ControllerCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\dns\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 3600;
	
	/**
	 * @see	\dns\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		
		$pages = glob(DNS_DIR.'/lib/'.(empty($parameters['module']) ? '' : $parameters['module'].'/').'page/*Page.class.php');
		
		foreach ($pages as $page) {
			$pageName = str_replace('Page.class.php', '', basename($page));

			$className = "\\dns".(empty($parameters['module']) ? '' : "\\".$parameters['module'])."\\page\\".$pageName."Page";
			if (class_exists($className) && is_subclass_of($className, '\\dns\\page\\AbstractPage')) {
				if ($className == '\dns\page\LoginPage') {
					$data[''] = $className;
				}
				$data[$pageName] = $className;
			}
		}
		
		return $data;
	}
}
