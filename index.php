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
require_once("lib/system/DNS.class.php");
new DNS();
