<?php
namespace dns\system;
use dns\system\cache\builder\ControllerCacheBuilder;
use dns\system\route\Literal;
use dns\system\route\Regex;
use dns\system\route\Request;
use dns\system\route\Segment;
use Zend\Router\Http\RouteMatch;
use Zend\Router\SimpleRouteStack;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2016 Jan Altensen (Stricted)
 */
class RequestHandler extends SingletonFactory {
	protected $router = null;
	protected $apiModule = false;
	
	/**
	 * init	RequestHandler
	 */
	protected function init () {
		$this->router = new SimpleRouteStack();
		if (DNS::getSession()->username !== null) {
			DNS::getTPL()->assign(array("username" => DNS::getSession()->username));
		}
		else {
			DNS::getTPL()->assign(array("username" => ''));
		}
	}
	
	/**
	 * set the default rules
	 *
	 * @param	string	$module
	 **/
	public function setRoutes($module='') {
		if ($module == "api") {
			$this->apiModule = true;
		}
		
		/* load the controllers from cache and build routes */
		$controllers = ControllerCacheBuilder::getInstance()->getData(array('module' => $module));
		$routes = [];
		
		foreach ($controllers as $name => $data) {
			$routes[$name] = Segment::factory([ 'route' => $name.'[/][/:id[-:title]]', 'constraints' => [ 'id' => '[0-9]+', 'title' => '[a-zA-Z0-9_.-/]+' ], 'defaults' => [ 'controller' => $data ] ]);
		}
		
		$this->router->setRoutes($routes);
	}
	
	/**
	 * @see \Zend\Mvc\Router\SimpleRouteStack::addRoute()
	 *
	 * @param  string  $name
	 * @param  mixed   $route
	 */
	public function addRoute ($name, $route) {
		$this->router->addRoute($name, $route);
	}
	
	/**
	 * @see	\Zend\Mvc\Router\SimpleRouteStack::addRoutes()
	 *
	 * @param	array|Traversable	$routes
	 */
	public function addRoutes ($routes) {
		$this->router->addRoutes($routes);
	}
	
	/**
	 * Get the added routes
	 *
	 * @return	Traversable list of all routes
	 */
	public function getRoutes() {
		return $this->router->getRoutes();
	}
	
	/**
	 * handle the request
	 */
	public function handle () {
		$match = $this->router->match(new Request());
		if ($match !== null) {
			$this->registerRouteData($match);
			
			$className = $match->getParam("controller");
			
			if (!User::isLoggedIn() && $this->apiModule == false && $className != 'dns\page\LoginPage' && $className != 'dns\page\ApiPage') {
				DNS::getTPL()->display('login.tpl');
				exit;
			}
			
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
				
				// show error page
				DNS::getTPL()->assign(array("activeMenuItem" => '', "error" => $e->getMessage()));
				DNS::getTPL()->display('error.tpl');
				exit;
			}
		}
		else {
			@header('HTTP/1.0 404 Not Found');
			DNS::getTPL()->assign(array("activeMenuItem" => '', "error" => 'The link you are trying to reach is no longer available or invalid.'));
			DNS::getTPL()->display('error.tpl');
			exit;
		}
	}
	
	/**
	 * Registers route data within $_GET and $_REQUEST.
	 */
	protected function registerRouteData(RouteMatch $route) {
		foreach ($route->getParams() as $key => $value) {
			$_GET[$key] = $value;
			$_REQUEST[$key] = $value;
		}
	}
}
