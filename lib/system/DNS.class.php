<?php
namespace dns\system;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2015 Jan Altensen (Stricted)
 */
class DNS {
	/**
	 * database object
	 *
	 * @var	object
	 */
	protected static $dbObj = null;
	
	/**
	 * template object
	 *
	 * @var	object
	 */
	protected static $tplObj = null;
	
	/**
	 * language array
	 *
	 * @var	array
	 */
	protected $language = array();
	
	/**
	 * init main system
	 */
	public function __construct() {
		spl_autoload_register(array('self', 'autoload'));
		
		$this->initDB();
		self::buildOptions();
		$this->initLanguage();
		$this->initTPL();
		new RequestHandler();
	}
	
	/**
	 * get database
	 */
	public static function getDB() {
		return self::$dbObj;
	}
	
	/**
	 * init database
	 */
	protected function initDB() {
		require(DNS_DIR.'/config.inc.php');
		self::$dbObj = new DB($driver, $host, $user, $pass, $db, $port);
	}
	
	/*
	 * autoload class files from namespace uses
	 *
	 * @param    string    $className
	 */
	public static function autoload ($className) {
		$namespaces = explode('\\', $className);
		if (count($namespaces) > 1) {
			array_shift($namespaces);
			$classPath = DNS_DIR.'/lib/'.implode('/', $namespaces).'.class.php';
			if (file_exists($classPath)) {
				require_once($classPath);
			}
		}
	}
	
	/**
	 * init language system
	 */
	protected function initLanguage () {
		$availableLanguages = array("de", "en");
		$languageCode = 'de';
		$basedir = DNS_DIR.'/lang/';
		if (isset($_GET['l'])) {
			$code = strtolower($_GET['l']);
			if (in_array($code, $availableLanguages)) {
				$languageCode = $code;
			}
			else if (array_key_exists($code, $availableLanguages)) {
				$languageCode = $availableLanguages[$code];
			}
		}
		else if (isset($_SESSION['language'])) {
			$code = strtolower($_SESSION['language']);
			if (in_array($code, $availableLanguages)) {
				$languageCode = $code;
			}
			else if (array_key_exists($code, $availableLanguages)) {
				$languageCode = $availableLanguages[$code];
			}
		}
		else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $_SERVER['HTTP_ACCEPT_LANGUAGE']) {
			$acceptedLanguages = explode(',', str_replace('_', '-', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
			foreach ($acceptedLanguages as $acceptedLanguage) {
				$code = strtolower(preg_replace('%^([a-z]{2}).*$%i', '$1', $acceptedLanguage));
				if (in_array($code, $availableLanguages)) {
					$languageCode = $code;
					break;
				}
			}
		}
		
		$file = $basedir.$languageCode.'.lang.php';
		$_SESSION['language'] = $languageCode;
		
		if (file_exists($file)) {
			require_once($file);
			if (isset($lang) && !empty($lang) && is_array($lang)) {
				$this->language = array_merge($this->language, $lang);
			}
		}
		
		return;
	}
	
	/**
	 * Executes template scripting in a language variable.
	 * 
	 * @param       string          $item
	 * @param       array           $variables
	 * @return      string          result
	 */
	public static function getLanguageVariable ($item, array $variables = array()) {
		$lang = self::getTPL()->getTemplateVars('language');
		
		if ($lang == null) {
			return $item;
		}
		
		if (!empty($variables)) {
			self::getTPL()->assign($variables);
		}
		
		if (isset($lang[$item])) {
			if (strpos($lang[$item], self::getTPL()->left_delimiter) !== false && strpos($lang[$item], self::getTPL()->right_delimiter) !== false) {
				$data = str_replace("\$", '$', $lang[$item]);
				$template_class = self::getTPL()->template_class;
				$template = new $template_class('eval:'.$data, self::getTPL(), self::getTPL());
				return $template->fetch();
			}
			
			return $lang[$item];
		}
		
		return $item;
	}
	
	/**
	 * init template engine
	 */
	protected function initTPL () {
		require(DNS_DIR.'/config.inc.php');
		
		if (isset($_SESSION['tpl']) && !empty($_SESSION['tpl'])) {
			$tpl = $_SESSION['tpl'];
		}
		
		require_once(DNS_DIR.'/lib/api/smarty/Smarty.class.php');
		self::$tplObj = new \Smarty;
		
		self::getTPL()->setTemplateDir(DNS_DIR."/templates/".$tpl);
		self::getTPL()->setCompileDir(DNS_DIR."/templates/compiled/".$tpl);
		/*self::getTPL()->setTemplateDir(DNS_DIR."/templates");*/
		/*self::getTPL()->setCompileDir(DNS_DIR."/templates/compiled");*/
		self::getTPL()->setPluginsDir(DNS_DIR."/lib/api/smarty/plugins");
		self::getTPL()->loadFilter('pre', 'hascontent');
		
		if (!ENABLE_DEBUG_MODE) {
			self::getTPL()->loadFilter('output', 'trimwhitespace');
		}
		
		/*
		self::getTPL()->loadFilter('pre', 'url');
		self::getTPL()->loadFilter('output', 'url');
		*/
		
		/* assign language variables */
		self::getTPL()->assign(array("language" => $this->language));
	}
	
	/**
	 * get template engine
	 */
	public static function getTPL () {
		return self::$tplObj;
	}
	
	/**
	 * build options from database
	 *
	 * @param	boolean	$force
	 */
	public static function buildOptions ($force = false) {
		$file = DNS_DIR."/options.inc.php";
		if (!file_exists($file) || (filemtime($file) + 86400) < time() || $force === true) {
			if (file_exists($file)) {
				@unlink($file);
			}
			
			@touch($file);
			$options = self::getDB()->query("select * from dns_options");
			$content = "<?php\n/* generated at ".gmdate('r')." */\n";
			
			while ($row = self::getDB()->fetch_array($options)) {
				$content .= 'if (!defined("'.strtoupper($row['option']).'")) define("'.strtoupper($row['option']).'", "'.$row['value'].'");'."\n";
			}
			
			$handler = fOpen($file, "a+");
			fWrite($handler, $content);
			fClose($handler);
		}
		
		require_once($file);
	}
}
