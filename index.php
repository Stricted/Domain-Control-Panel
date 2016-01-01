<?php
namespace dns;
use dns\system\DNS;

session_start();
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
define("DNS_DIR", dirname(__FILE__));
require_once("lib/system/DNS.class.php");
new DNS();
