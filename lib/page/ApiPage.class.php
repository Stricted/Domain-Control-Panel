<?php
namespace dns\page;
use dns\system\cache\builder\DNSApiCacheBuilder;
use dns\system\DNS;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class ApiPage extends AbstractPage {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	public function prepare() {
		$key = "";
		if (isset($_REQUEST['key'])) {
			$key = $_REQUEST['key'];
		}
		
		if (!defined('DNS_API_KEY') || $key != DNS_API_KEY || empty($key)) {
			header('Content-Type: application/json');
			echo json_encode(array("error" => "wrong access key"), JSON_PRETTY_PRINT);
			exit;
		}
		else {
			$data = DNSApiCacheBuilder::getInstance()->getData();
			header('Content-Type: application/json');
			echo json_encode($data, JSON_PRETTY_PRINT);
			exit;
		}
	}
}
