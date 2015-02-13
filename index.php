<?php
namespace dns;
use dns\system\DNS;

session_start();

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
define("DNS_DIR", dirname(__FILE__));
define("ENABLE_DEBUG_MODE", false);
// remove this later
define('DNS_API_KEY', 'aa');
define('OFFLINE', false);
define('DNS_SOA_MINIMUM_TTL', 60);
require_once("lib/system/DNS.class.php");
new DNS();
