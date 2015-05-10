<?php
namespace dns\system;

/**
 * A SystemException is thrown when an unexpected error occurs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
// @codingStandardsIgnoreFile
class SystemException extends \Exception {
	/**
	 * error description
	 * @var	string
	 */
	protected $description = null;
	
	/**
	 * additional information
	 * @var	string
	 */
	protected $information = '';
	
	/**
	 * additional information
	 * @var	string
	 */
	protected $functions = '';
    
	/**
	 * exception id
	 * @var	string
	 */
	protected $exceptionID = '';
	
	/**
	 * Creates a new SystemException.
	 * 
	 * @param	string		$message	error message
	 * @param	integer		$code		error code
	 * @param	string		$description	description of the error
	 * @param	\Exception	$previous	repacked Exception
	 */
	public function __construct($message = '', $code = 0, $description = '', \Exception $previous = null) {
		parent::__construct((string) $message, (int) $code, $previous);
		$this->description = $description;
	}
	
	/**
	 * Removes database password from stack trace.
	 * @see	\Exception::getTraceAsString()
	 */
	public function __getTraceAsString() {
		$e = ($this->getPrevious() ?: $this);
        $string = $e->getTraceAsString();
		$string = preg_replace('/PDO->__construct\(.*\)/', 'PDO->__construct(...)', $string);
		$string = preg_replace('/DB->__construct\(.*\)/', 'DB->__construct(...)', $string);
		return $string;
	}
    
	/**
	 * @see	\Exception::getMessage()
	 */
	public function _getMessage() {
		$e = ($this->getPrevious() ?: $this);
		return $e->getMessage();
	}
    
	/**
	 * Returns the description of this exception.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Returns exception id
	 * 
	 * @return	string
	 */
	public function getExceptionID() {
		if (empty($this->exceptionID)) {
			$this->logError();
		}
		
		return $this->exceptionID;
	}
    
	/**
	 * Writes an error to log file.
	 */
	protected function logError() {
		if (!empty($this->exceptionID)) {
			return;
		}
		
		$logFile = DNS_DIR . '/log/' . gmdate('Y-m-d', time()) . '.txt';
		
		// try to create file
		@touch($logFile);
		
		// validate if file exists and is accessible for us
		if (!file_exists($logFile) || !is_writable($logFile)) {
			/*
				We cannot recover if we reached this point, the server admin
				is urged to fix his pretty much broken configuration.
				
				GLaDOS: Look at you, sailing through the air majestically, like an eagle... piloting a blimp.
			*/
			return;
		}
		
		$e = ($this->getPrevious() ?: $this);
		
		// don't forget to update ExceptionLogViewPage, when changing the log file format
		$message = gmdate('r', time())."\n".
			'Message: '.$e->getMessage()."\n".
			'File: '.$e->getFile().' ('.$e->getLine().")\n".
			'PHP version: '.phpversion()."\n".
			'DNS version: '.DNS_VERSION."\n".
			'Request URI: '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')."\n".
			'Referrer: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')."\n".
			'User-Agent: '.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '')."\n".
			'Information: '.json_encode($this->information)."\n".
			"Stacktrace: \n  ".implode("\n  ", explode("\n", $this->__getTraceAsString()))."\n";
		
		// calculate Exception-ID
		$this->exceptionID = sha1($message);
		$message = "<<<<<<<<".$this->exceptionID."<<<<\n".$message."<<<<\n\n";
		
		// append
		@file_put_contents($logFile, $message, FILE_APPEND);
	}
    
	/**
	 * @see	\wcf\system\exception\IPrintableException::show()
	 */
	public function show() {
		// send status code
		@header('HTTP/1.1 503 Service Unavailable');
		
		// print report
		$e = ($this->getPrevious() ?: $this);
		?><!DOCTYPE html>
		<html>
			<head>
				<title>Fatal error: <?php echo htmlspecialchars($this->_getMessage(), ENT_COMPAT, 'UTF-8'); ?></title>
				<meta charset="utf-8" />
				<style>
					.systemException {
						font-family: 'Trebuchet MS', Arial, sans-serif !important;
						font-size: 80% !important;
						text-align: left !important;
						border: 1px solid #036;
						border-radius: 7px;
						background-color: #eee !important;
						overflow: auto !important;
					}
					.systemException h1 {
						font-size: 130% !important;
						font-weight: bold !important;
						line-height: 1.1 !important;
						text-decoration: none !important;
						text-shadow: 0 -1px 0 #003 !important;
						color: #fff !important;
						word-wrap: break-word !important;
						border-bottom: 1px solid #036;
						border-top-right-radius: 6px;
						border-top-left-radius: 6px;
						background-color: #369 !important;
						margin: 0 !important;
						padding: 5px 10px !important;
					}
					.systemException div {
						border-top: 1px solid #fff;
						border-bottom-right-radius: 6px;
						border-bottom-left-radius: 6px;
						padding: 0 10px !important;
					}
					.systemException h2 {
						font-size: 130% !important;
						font-weight: bold !important;
						color: #369 !important;
						text-shadow: 0 1px 0 #fff !important;
						margin: 5px 0 !important;
					}
					.systemException pre, .systemException p {
						text-shadow: none !important;
						color: #555 !important;
						margin: 0 !important;
					}
					.systemException pre {
						font-size: .85em !important;
						font-family: "Courier New" !important;
						text-overflow: ellipsis;
						padding-bottom: 1px;
						overflow: hidden !important;
					}
					.systemException pre:hover{
						text-overflow: clip;
						overflow: auto !important;
					}
				</style>
			</head>
			<body>
				<div class="systemException">
					<h1>Fatal error: <?php if(!$this->getExceptionID()) { ?>Unable to write log file, please make &quot;<?php echo DNS_DIR; ?>/log/&quot; writable!<?php } else { echo htmlspecialchars($this->_getMessage(), ENT_COMPAT, 'UTF-8'); } ?></h1>
                    
                    <?php if (DNS::debugModeIsEnabled()) { ?>
						<div>
							<?php if ($this->getDescription()) { ?><p><br /><?php echo $this->getDescription(); ?></p><?php } ?>
							
							<h2>Information:</h2>
							<p>
								<b>error message:</b> <?php echo htmlspecialchars($this->_getMessage(), ENT_COMPAT, 'UTF-8'); ?><br>
								<b>error code:</b> <?php echo intval($e->getCode()); ?><br>
								<?php echo $this->information; ?>
								<b>file:</b> <?php echo htmlspecialchars($e->getFile(), ENT_COMPAT, 'UTF-8'); ?> (<?php echo $e->getLine(); ?>)<br>
								<b>php version:</b> <?php echo htmlspecialchars(phpversion(), ENT_COMPAT, 'UTF-8'); ?><br>
								<b>dns version:</b> <?php echo DNS_VERSION; ?><br>
								<b>date:</b> <?php echo gmdate('r'); ?><br>
								<b>request:</b> <?php if (isset($_SERVER['REQUEST_URI'])) echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_COMPAT, 'UTF-8'); ?><br>
								<b>referer:</b> <?php if (isset($_SERVER['HTTP_REFERER'])) echo htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_COMPAT, 'UTF-8'); ?><br>
							</p>
							
							<h2>Stacktrace:</h2>
							<pre><?php echo htmlspecialchars($this->__getTraceAsString(), ENT_COMPAT, 'UTF-8'); ?></pre>
						</div>
                    <?php } else { ?>
						<div>
							<h2>Information:</h2>
							<p>
								<?php if (!$this->getExceptionID()) { ?>
									Unable to write log file, please make &quot;<?php echo DNS_DIR; ?>/log/&quot; writable!
								<?php } else { ?>
									<b>ID:</b> <code><?php echo $this->getExceptionID(); ?></code><br>
									<?php echo "Please send the ID above to the site administrator."; ?>
								<?php } ?>
							</p>
						</div>
                    <?php } ?>
					<?php echo $this->functions; ?>
				</div>
			</body>
		</html>
		
		<?php
	}
}
