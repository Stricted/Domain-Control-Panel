<?php
namespace dns\system;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2015 Jan Altensen (Stricted)
 */
class RequestHandler {
	/**
	 * init RequestHandler
	 */
	public function __construct ($module = '') {
		$className = "";
		$pages = glob(DNS_DIR.'/lib/'.(empty($module) ? '' : $module.'/').'page/*Page.class.php');
		if (isset($_GET["page"]) && !empty($_GET["page"])) {
			if (strtolower($_GET["page"]) != "abstract") {
				foreach ($pages as $page) {
					$page = str_replace('Page.class.php', '', basename($page));
					if (strtolower($_GET["page"]) == strtolower($page)) {
						$class = "\\dns".(empty($module) ? '' : "\\".$module)."\\page\\".$page."Page";
						if (class_exists($class) && is_subclass_of($class, '\\dns\\page\\AbstractPage')) {
							$className = $class;
						}
						break;
					}
				}
			}
		}
		else {
			$className = '\\dns'.(empty($module) ? '' : '\\'.$module).'\\page\\IndexPage';
		}
		
		if (!User::isLoggedIn() && $className != '\dns\page\LoginPage' && $className != '\dns\page\ApiPage') {
			DNS::getTPL()->display('login.tpl');
			exit;
		}
		
		if (DNS::getSession()->username !== null) {
			DNS::getTPL()->assign(array("username" => DNS::getSession()->username));
		}
		
		if (empty($className)) {
			@header('HTTP/1.0 404 Not Found');
			DNS::getTPL()->assign(array("activeMenuItem" => '', "error" => 'The link you are trying to reach is no longer available or invalid.'));
			DNS::getTPL()->display('error.tpl');
			exit;
		}
		
		// handle offline mode
		if (defined('OFFLINE') && OFFLINE) {
			$admin = User::isAdmin();
			$available = false;
			
			if (defined($className . '::AVAILABLE_DURING_OFFLINE_MODE') && constant($className . '::AVAILABLE_DURING_OFFLINE_MODE')) {
				$available = true;
			}
			
			if (!$admin && !$available) {
				@header('HTTP/1.1 503 Service Unavailable');
				DNS::getTPL()->display('offline.tpl');
				exit;
			}
		}
		
		try {
			new $className();
		}
		catch (\Exception $e) {
			if ($e->getCode() == 404) {
				@header('HTTP/1.0 404 Not Found');
			}
			else if ($e->getCode() == 403) {
				@header('HTTP/1.0 403 Forbidden');
			}
			
			/* show error page */
			DNS::getTPL()->assign(array("activeMenuItem" => '', "error" => $e->getMessage()));
			DNS::getTPL()->display('error.tpl');
			exit;
		}
	}
}
