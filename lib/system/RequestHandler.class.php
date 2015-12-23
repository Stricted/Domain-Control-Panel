<?php
namespace dns\system;
use dns\system\cache\builder\ControllerCacheBuilder;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2015 Jan Altensen (Stricted)
 */
class RequestHandler {
	protected $pattern = "";
	protected $routeData = array();
	
	/**
	 * init RequestHandler
	 */
	public function __construct ($module = '') {
		$this->pattern = '~/?(?:(?P<controller>[A-Za-z0-9\-]+)(?:/(?P<id>\d+)(?:-(?P<title>[^/]+))?)?)?~x';
		$controllers = ControllerCacheBuilder::getInstance()->getData(array('module' => $module));
		
		if (DNS::getSession()->username !== null) {
			DNS::getTPL()->assign(array("username" => DNS::getSession()->username));
		}
		else {
			DNS::getTPL()->assign(array("username" => ''));
		}
		
		$className = "";
		if (!empty($_SERVER['QUERY_STRING'])) {
			$this->matches($_SERVER['QUERY_STRING']);
			$this->registerRouteData();
		}
		else {
			$className = '\\dns'.(empty($module) ? '' : '\\'.$module).'\\page\\IndexPage';
		}
		
		if (isset($this->routeData['controller']) && !empty($this->routeData['controller'])) {
			$controller = strtolower($this->routeData['controller']);
			if (isset($controllers[$controller]) && !empty($controllers[$controller])) {
				$className = $controllers[$controller];
			}
			else {
				@header('HTTP/1.0 404 Not Found');
				DNS::getTPL()->assign(array("activeMenuItem" => '', "error" => 'The link you are trying to reach is no longer available or invalid.'));
				DNS::getTPL()->display('error.tpl');
				exit;
			}
		}
		
		if (!User::isLoggedIn() && $className != '\dns\page\LoginPage' && $className != '\dns\page\ApiPage') {
			DNS::getTPL()->display('login.tpl');
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

	/**
	 * Registers route data within $_GET and $_REQUEST.
	 */
	protected function registerRouteData() {
		foreach ($this->routeData as $key => $value) {
			$_GET[$key] = $value;
			$_REQUEST[$key] = $value;
		}
	}
	
	public function matches($requestURL) {
		if (preg_match($this->pattern, $requestURL, $matches)) {
			foreach ($matches as $key => $value) {
				if (!is_numeric($key)) {
					$this->routeData[$key] = $value;
				}
			}
			
			return true;
		}
		
		return false;
	}
}
