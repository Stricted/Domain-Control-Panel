<?php
namespace dns\system\cache\builder;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @copyright   2013-2015 Jan Altensen (Stricted)
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
			$page = str_replace('Page.class.php', '', basename($page));

			$class = "\\dns".(empty($parameters['module']) ? '' : "\\".$parameters['module'])."\\page\\".$page."Page";
			if (class_exists($class) && is_subclass_of($class, '\\dns\\page\\AbstractPage')) {
				$data[strtolower($page)] = $class;
			}
		}
		
		return $data;
	}
}
