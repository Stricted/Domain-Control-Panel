<?php
namespace dns\api\page;
use dns\page\AbstractPage;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2016 Jan Altensen (Stricted)
 */
class IndexPage extends AbstractPage {
	public function prepare () {
		/* we have no index page */
		exit;
	}
}
