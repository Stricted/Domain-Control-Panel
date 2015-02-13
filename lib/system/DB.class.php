<?php
namespace dns\system;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2013-2015 Jan Altensen (Stricted)
 */
class DB {
	/**
	 * PDO object
	 * @var	object
	 */
	private $conn = null;
	
	/**
	 * error string
	 * @var	string
	 */
	private $error = '';

	/**
	 * Connects to SQL Server
	 *
	 * @param	string	$driver
	 * @param	string	$host
	 * @param	string	$username
	 * @param	string	$password
	 * @param	string	$database
	 * @param	integer	$port
	 * @param	array	$options
	 * @return	boolean
	 */
	public function __construct($driver, $host, $username, $password, $database, $port = 0, $options = array()) {
		if (!extension_loaded("pdo")) {
			// check if extension loaded
			die("Missing <a href=\"http://www.php.net/manual/en/book.pdo.php\">PDO</a> PHP extension.");
		}
		
		$driver = strtolower($driver);
		try {
			if ($driver == "mysql") {
				if (!extension_loaded("pdo_mysql")) {
					// check if extension loaded
					die("Missing <a href=\"http://php.net/manual/de/ref.pdo-mysql.php\">pdo_mysql</a> PHP extension.");
				}
				
				if (empty($port)) {
					$port=3306;
				}
				
				$this->conn = new \PDO("mysql:host=".$host.";port=".$port.";dbname=".$database, $username, $password, $options);
			}
			else if ($driver == "pgsql") {
				if (!extension_loaded("pdo_pgsql")) {
					// check if extension loaded
					die("Missing <a href=\"http://php.net/manual/de/ref.pdo-pgsql.php\">pdo_pgsql</a> PHP extension.");
				}
				
				if (empty($port)) {
					$port=5432;
				}
				
				$this->conn = new \PDO("pgsql:host=".$host.";port=".$port.";dbname=".$database, $username, $password, $options);
			}
			else if ($driver == "sqlite") {
				if (!extension_loaded("pdo_sqlite")) {
					// check if extension loaded
					die("Missing <a href=\"http://php.net/manual/de/ref.pdo-sqlite.php\">pdo_sqlite</a> PHP extension.");
				}
				
				if (!file_exists($database)) {
					@touch($database);
				}
				
				if (file_exists($database) && is_readable($database) && is_writable($database)) {
					$this->conn = new \PDO("sqlite:".$database, $username, $password, $options);
				}
				else {
					$this->error = "cant crate/connect the/to sqlite database";
					return false;
				}
			}
			else{
				$this->error = "not supported database type found";
				return false;
			}
			
			return true;
			
		}
		catch (\PDOException $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}
	
	/*
	 * close the database connection
	 */
	public function close () {
		$this->conn = null;
	}
	
	/**
	 * Sends a database query to SQL server.
	 *
	 * @param	string	$res
	 * @param	array	$bind 		
	 * @return	integer	
	 */
	public function query ($res, $bind = array()) {
		try {
			$query = null;
			$query = $this->conn->prepare($res);
			
			if (is_array($bind) && !empty($bind)) {
				$query->execute($bind);
			}
			else {
				$query->execute();
			}
			
			return $query;
		}
		catch (\PDOException $e) {
			$this->error = $e->getMessage();
		}
	}
	
	/**
	 * Gets a row from SQL database query result.
	 *
	 * @param	string	$res
	 * @return	array
	 */
	public function fetch_array ($res) {
		try {
			return $res->fetch(\PDO::FETCH_ASSOC);
		}
		catch (\PDOException $e) {
			$this->error = $e->getMessage();
		}
	}
	
	/**
	 * return the last insert id
	 *
	 * @return	integer
	 */
	public function last_id () {
		return $this->conn->lastInsertId();
	}
	
	/**
	 * Returns SQL last error.
	 *
	 * @return	string
	 */
	public function error () {
		return $this->error;
	}
	
	/**
	 * call PDO methods
	 *
	 * @param	string	$name
	 * @param	array	$arguments
	 * @return	mixed
	 */
	public function __call($name, $arguments) {
		if (!method_exists($this->conn, $name)) {
			throw new \Exception("unknown method '".$name."'");
		}

		return call_user_func_array(array($this->conn, $name), $arguments);
	}
}
