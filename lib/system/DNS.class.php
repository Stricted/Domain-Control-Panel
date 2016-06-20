<?php
namespace dns\system;

if (!defined('DNS_VERSION')) define('DNS_VERSION', '3.0.0 Beta');

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
class DNS {
	/**
	 * module name
	 *
	 * @var	string
	 */
	protected static $module = '';
	
	/**
	 * database object
	 *
	 * @var	object
	 */
	protected static $dbObj = null;
	
	/**
	 * session object
	 *
	 * @var	object
	 */
	protected static $sessionObj = null;
	
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
	protected static $language = array();
	
	/**
	 * language code
	 *
	 * @var	string
	 */
	protected static $languageCode = '';
	
	/**
	 * init main system
	 */
	public function __construct($module = '') {
		self::$module = $module;
		spl_autoload_register(array('dns\system\DNS', 'autoload'));
		set_exception_handler(array('dns\system\DNS', 'handleException'));
		set_error_handler(array('dns\system\DNS', 'handleError'), E_ALL);
		
		$this->initDB();
		self::buildOptions();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		new RequestHandler(self::$module);
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
	
	/**
	 * init session system
	 */
	protected function initSession() {
		self::$sessionObj = new SessionHandler();
	}
	
	/**
	 * return session object
	 */
	public static function getSession() {
		return self::$sessionObj;
	}
	
	/*
	 * autoload class files from namespace uses
	 *
	 * @param    string    $className
	 */
	public static function autoload ($className) {
		$namespaces = explode('\\', $className);
		if (count($namespaces) > 1) {
			$abbreviation = array_shift($namespaces);
			if ($abbreviation == "dns") {
				$classPath = DNS_DIR.'/lib/'.implode('/', $namespaces).'.class.php';
				if (file_exists($classPath)) {
					require_once($classPath);
				}
			}
			else if ($abbreviation == "Mso") {
				array_shift($namespaces);
				
				$classPath = DNS_DIR.'/lib/system/api/idna-convert/src/'.implode('/', $namespaces).'.php';
				if (file_exists($classPath)) {
					require_once($classPath);
				}
			}
		}
	}
	
	/**
	 * Calls the show method on the given exception.
	 * 
	 * @param	\Exception	$e
	 */
	public static final function handleException(\Exception $e) {
		try {
			if ($e instanceof SystemException) {
				$e->show();
				exit;
			}
			
			// repack Exception
			self::handleException(new SystemException($e->getMessage(), $e->getCode(), '', $e));
		}
		catch (\Exception $exception) {
			die("<pre>DNS::handleException() Unhandled exception: ".$exception->getMessage()."\n\n".$exception->getTraceAsString());
		}
	}
	
	/**
	 * Catches php errors and throws instead a system exception.
	 * 
	 * @param	integer		$errorNo
	 * @param	string		$message
	 * @param	string		$filename
	 * @param	integer		$lineNo
	 */
	public static final function handleError($errorNo, $message, $filename, $lineNo) {
		if (error_reporting() != 0) {
			$type = 'error';
			switch ($errorNo) {
				case 2:
					$type = 'warning';
					break;
				case 8:
					$type = 'notice';
					break;
			}
			
			throw new SystemException('PHP '.$type.' in file '.$filename.' ('.$lineNo.'): '.$message, 0);
		}
	}
	
	/**
	 * Returns true if the debug mode is enabled, otherwise false.
	 * 
	 * @return	boolean
	 */
	public static function debugModeIsEnabled() {
		// try to load constants
		if (!defined('ENABLE_DEBUG')) {
			self::buildOptions();
		}
		
		if (defined('ENABLE_DEBUG') && ENABLE_DEBUG) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * init language system
	 */
	protected function initLanguage () {
		/*
		 * @TODO: activate this later
		 * self::buildlanguage();
		 */
		$availableLanguages = array("de", "en");
		$languageCode = 'de';
		
		if (isset($_GET['l'])) {
			$code = strtolower($_GET['l']);
			if (in_array($code, $availableLanguages)) {
				$languageCode = $code;
			}
			else if (array_key_exists($code, $availableLanguages)) {
				$languageCode = $availableLanguages[$code];
			}
		}
		else if (self::getSession()->language !== null) {
			$code = strtolower(self::getSession()->language);
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
		
		self::$languageCode = $languageCode;
		
		// @TODO: remove this later
		/* try to load module language files */
		if (!empty(self::$module)) {
			$basedir = DNS_DIR.'/'.self::$module.'/lang/';
			$file = $basedir.$languageCode.'.lang.php';
			self::getSession()->register('language', $languageCode);
			
			if (file_exists($file)) {
				require_once($file);
				if (isset($lang) && !empty($lang) && is_array($lang)) {
					$this->language = array_merge($this->language, $lang);
				}
			}
		}
		
		/* load default language files */
		$basedir = DNS_DIR.'/lang/';
		$file = $basedir.$languageCode.'.lang.php';
		self::getSession()->register('language', $languageCode);
		
		if (file_exists($file)) {
			require_once($file);
			if (isset($lang) && !empty($lang) && is_array($lang)) {
				self::$language = array_merge(self::$language, $lang);
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
		$lang = self::$language;
		
		if ($lang == null) {
			return $item;
		}
		
		if (!empty($variables)) {
			self::getTPL()->assign($variables);
		}
		
		if (isset($lang[$item])) {
			if (strpos($lang[$item], self::getTPL()->left_delimiter) !== false && strpos($lang[$item], self::getTPL()->right_delimiter) !== false) {
				$data = str_replace("\$", '$', $lang[$item]);
				$dir = self::getTPL()->getTemplateDir();
				
				if (is_array($dir)) {
					$dir = $dir[0];
				}
				
				$filename = "lang.".self::$languageCode.".".sha1($item).".tpl";
				if (file_exists($dir.$filename)) {
					$mtime = filemtime($dir.$filename);
					$maxLifetime = 3600;
					
					if ($mtime === false || ($maxLifetime > 0 && (time() - $mtime) > $maxLifetime)) {
						@unlink($dir.$filename);
					}
				}
				
				if (!file_exists($dir.$filename)) {
					$h = fopen($dir.$filename, "a+");
					fwrite($h, '{* '.$item.' *}'.$lang[$item]);
					fclose($h);
				}
				
				return self::getTPL()->fetch($filename);
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
		
		if (self::getSession()->tpl !== null && !empty(self::getSession()->tpl)) {
			$tpl = self::getSession()->tpl;
		}
		
		if (!file_exists(DNS_DIR.'/lib/system/api/smarty/libs/Smarty.class.php')) {
			throw new SystemException('Unable to find Smarty');
		}
		
		require_once(DNS_DIR.'/lib/system/api/smarty/libs/Smarty.class.php');
		self::$tplObj = new \Smarty;
		
		if (!empty(self::$module)) {
			// try first to load the template from module then from core
			self::getTPL()->addTemplateDir(DNS_DIR.'/'.self::$module."/templates/".$tpl);
		}
		
		self::getTPL()->addTemplateDir(DNS_DIR."/templates/".$tpl);
		self::getTPL()->setCompileDir(DNS_DIR.(empty(self::$module) ? '' : '/'.self::$module)."/templates/compiled/".$tpl);
		self::getTPL()->setPluginsDir(array(
			DNS_DIR."/lib/system/api/smarty/libs/plugins",
			DNS_DIR."/lib/system/template/plugins"
		));
		self::getTPL()->loadFilter('pre', 'hascontent');
		
		if (!ENABLE_DEBUG) {
			self::getTPL()->loadFilter('output', 'trimwhitespace');
		}
		
		/* assign language variables */
		self::getTPL()->assign(array(
			"isReseller" => User::isReseller(),
			"isAdmin" => User::isAdmin()
		));
		
		/*self::getTPL()->assign("version", mb_substr(sha1(DNS_VERSION), 0, 8));*/

	}
	
	/**
	 * get template engine
	 */
	public static function getTPL () {
		return self::$tplObj;
	}
	
	/**
	 * Creates a random hash.
	 * 
	 * @return	string
	 */
	public static function generateRandomID() {
		return sha1(microtime() . uniqid(mt_rand(), true));
	}
	
	/**
	 * Creates an UUID.
	 * 
	 * @return	string
	 */
	public static function generateUUID() {
		return strtoupper(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
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
				$content .= "if (!defined('".strtoupper($row['optionName'])."')) define('".strtoupper($row['optionName'])."', ".((is_bool($row['optionValue']) || is_numeric($row['optionValue'])) ? intval($row['optionValue']) : "'".addcslashes($row['optionValue'], "'\\")."'").");\n";
			}
			
			$handler = fOpen($file, "a+");
			fWrite($handler, $content);
			fClose($handler);
		}
		
		require_once($file);
	}
	
	/**
	 * build language files from database
	 *
	 * @param	boolean	$force
	 */
	public static function buildlanguage ($force = false) {
		$availableLanguages = array("de", "en");
		foreach ($availableLanguages as $languageID => $languageCode) {
			
			$file = DNS_DIR."/lang/".$languageCode.".lang.php";
			if (!file_exists($file) || (filemtime($file) + 86400) < time() || $force === true) {
				if (file_exists($file)) {
					@unlink($file);
				}
				
				@touch($file);
				
				$items = self::getDB()->query("select * from dns_language where languageID = ?", array($languageID));
				$content = "<?php\n/**\n* language: ".$languageCode."\n* encoding: UTF-8\n* generated at ".gmdate("r")."\n* \n* DO NOT EDIT THIS FILE\n*/\n";
				$content .= "\$lang = array();\n";
				while ($row = self::getDB()->fetch_array($items)) {
					print_r($row);
					$content .= "\$lang['".$row['languageItem']."'] = '".str_replace("\$", '$', $row['languageValue'])."';\n";
				}
				
				$handler = fOpen($file, "a+");
				fWrite($handler, $content);
				fClose($handler);
			}
		}
	}
}
