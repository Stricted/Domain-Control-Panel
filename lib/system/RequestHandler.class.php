<?php
namespace dns\system;
use dns\system\cache\builder\ControllerCacheBuilder;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Regex;
use Zend\Mvc\Router\SimpleRouteStack;
use Zend\ServiceManager\ServiceManager;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2016 Jan Altensen (Stricted)
 */
class RequestHandler {
	protected $pattern = "";
	protected $routeData = array();
	
	/**
	 * init RequestHandler
	 */
	public function __construct ($module = '') {
		if (DNS::getSession()->username !== null) {
			DNS::getTPL()->assign(array("username" => DNS::getSession()->username));
		}
		else {
			DNS::getTPL()->assign(array("username" => ''));
		}
		
		$router = new SimpleRouteStack();
		
		$router->addRoute('', Literal::factory([ 'route' => '', 'defaults' => [ 'controller' => 'dns\page\IndexPage' ] ]));
		$router->addRoute('Index', Literal::factory([ 'route' => 'Index', 'defaults' => [ 'controller' => 'dns\page\IndexPage' ] ]));
		$router->addRoute('index', Literal::factory([ 'route' => 'index', 'defaults' => [ 'controller' => 'dns\page\IndexPage' ] ]));
		$router->addRoute('Login', Literal::factory([ 'route' => 'Login', 'defaults' => [ 'controller' => 'dns\page\LoginPage' ] ]));
		$router->addRoute('Logout', Literal::factory([ 'route' => 'Logout', 'defaults' => [ 'controller' => 'dns\page\LogoutPage' ] ]));
		$router->addRoute('DomainList', Literal::factory([ 'route' => 'DomainList', 'defaults' => [ 'controller' => 'dns\page\DomainListPage' ] ]));
		$router->addRoute('DomainAdd', Literal::factory([ 'route' => 'DomainAdd', 'defaults' => [ 'controller' => 'dns\page\DomainAddPage' ] ]));
		//$router->addRoute('DomainAdd', Regex::factory([ 'regex' => 'DomainEdit/(?P<id>\d+)(/)?', 'defaults' => [ 'controller' => 'dns\page\DomainEditPage' ], 'spec' => '/DomainEdit/%id%' ]));
		
		$match = $router->match(new Request());
		if ($match !== null) {
			foreach ($match->getParams() as $key => $value) {
				$_GET[$key] = $value;
				$_REQUEST[$key] = $value;
			}
			
			$className = $match->getParam("controller");
			
			if (!User::isLoggedIn() && $className != 'dns\page\LoginPage' && $className != 'dns\page\ApiPage') {
				echo $className;
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
}
