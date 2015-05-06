<?php
namespace dns\util;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2015 Jan Altensen (Stricted)
 */
class ParseZone {
	/**
	 * domain name
	 *
	 * var	string
	 */
	private $origin = '';
	
	/**
	 * lines of zone file
	 *
	 * var	array
	 */
	private $lines = array();
	
	/**
	 * global ttl
	 *
	 * var	integer
	 */
	private $ttl = 0;
	
	/**
	 * parsed soa record
	 *
	 * var	array
	 */
	private $soa = array();
	
	/**
	 * parsed resource records
	 *
	 * var	array
	 */
	private $records = array();
	
	/**
	 * init this class
	 *
	 * param	string	$file
	 * param	string	$origin
	 */
	public function __construct ($file, $origin = "") {
		if (!empty($origin)) $this->origin = $origin;
		
		// unify newlines
		$file = preg_replace("/(\r\n)|(\r)/", "\n", $file);
		
		// unify all lines
		$file = preg_replace_callback('/(\([^()]*\))/', function ($matches) {
			$a = explode("\n", $matches[0]);
			$b = array();
			foreach ($a as $line) {
				
				// unify whitespaces
				$line = preg_replace('/\s+/', ' ', $line);
				
				// strip comments
				$line = preg_replace('/(\s+)?(;|#)([\s\S]+)?/i', '', $line);
				$b[] = $line;
			}
			$line = implode("", $b);
			
			return str_replace(array("( ", "(", " )", ")"), "", $line);
		}, $file);
		
		$this->lines = explode("\n", $file);
		
		/*
		 * limit lines to 200, if more is needed we can change it
		 */
		if (count($this->lines) > 200) {
			throw new \Exception('zone file to big for parsing');
		}
	}
	
	/**
	 * parse zone file
	 */
	public function parse () {
		foreach ($this->lines as $line) {
			// unify whitespaces
			$line = preg_replace('/\s+/', ' ', $line);
			
			// strip comments
			$line = preg_replace('/(\s+)?(;|#)([\s\S]+)?/i', '', $line);
		
			/* ignore these lines */
			if (empty($line)) continue;
			if (strpos($line, "RRSIG") !== false) continue;
			if (strpos($line, "NSEC") !== false) continue;
			if (strpos($line, "DNSKEY") !== false) continue;
			if (strpos($line, "SPF") !== false) continue;

			$this->parseORIGIN($line);
			$this->parseTTL($line);
			
			if (strpos($line, "SOA") !== false) {
				$this->parseSOA($line);
				continue;
			}
			
			// parse all other records
			$this->parseRR($line);
		}
	}
	
	/**
	 * parse ORIGIN
	 *
	 * param	string	$line
	 */
	public function parseORIGIN ($line) {
		if (preg_match('/\$ORIGIN ([*-a-z0-9.]+)/i', $line, $match)) {
			$origin = $match[1];
			if (empty($this->origin)) {
				$this->origin = $origin;
			}
			else {
				if ($this->origin != $origin) {
					throw new \Exception('parse error');
				}
			}
		}
	}
	
	/**
	 * parse TTL
	 *
	 * param	string	$line
	 */
	public function parseTTL ($line) {
		if (preg_match('/\$TTL ([0-9]+)([a-z]+)?/i', $line, $match)) {
			if (isset($match[2])) {
				$this->ttl = $this->formatTime($match[1], $match[2]);
			}
			else {
				$this->ttl = $match[1];
			}
		}
	}
	
	/**
	 * parse RR
	 *
	 * param	string	$line
	 */
	public function parseRR ($line) {
		if (preg_match("/([*-a-z0-9.]+)? ([0-9]+)?(?: )?(IN)?(?: )?([a-z]+) ([\s\S]+)/i", $line, $matches)) {
			$record=array();
			// parse domain name
			if (!empty($this->origin) && $matches[1] == "@") {
				$record['name'] = $this->origin;
			}
			else {
				if (empty($matches[1])) {
					$record['name'] = $this->origin;
				}
				else {
					$record['name'] = $matches[1];
				}
			}
			
			// parse ttl
			if (empty($matches[2])) {
				$record['ttl'] = $this->ttl;
			}
			else {
				$record['ttl'] = $matches[2];
			}
			
			// parse type
			$record['type'] = $matches[4];
			if ($matches[4] == 'MX' || $matches[4] == 'SRV' || $matches[4] == 'DS') {
				$exp = explode(' ', $matches[5], 2);
				$record['aux'] = $exp[0];
				$record['data'] = $exp[1];
			}
			else {
				$record['aux'] = 0;
				$record['data'] = $matches[5];
			}
			
			// parse data
			if (strpos($record['data'], "@") !== false && !empty($this->origin)) {
				$record['data'] = str_replace("@", $this->origin, $record['data']);
			}
			
			$this->records[] = $record;
		}
	}
	
	/**
	 * parse SOA
	 *
	 * param	string	$line
	 */
	public function parseSOA ($line) {
		if (preg_match("/([@a-z0-9.-]+) ([0-9]+)?([a-z]+)?(?: )?(IN)?(?: )?(?:[a-z]+) ([-a-z0-9.]+) ([@-a-z0-9.]+) ([0-9a-]+) ([0-9]+)([a-z]+)? ([0-9]+)([a-z]+)? ([0-9]+)([a-z]+)? ([0-9]+)([a-z]+)?/i", $line, $matches)) {
			// set domain name
			if ($matches[1] == "@") {
				if (empty($this->origin)) {
					throw new \Exception('parse error');
				}
			}
			else {
				if (empty($this->origin)) {
					if (empty($matches[1])) {
						throw new \Exception('parse error');
					}
					else {
						$this->origin = $matches[1];
					}
				}
				else {
					if ($this->origin != $matches[1]) {
						throw new \Exception('parse error');
					}
				}
			}
			
			$this->soa['origin'] = $this->origin;
			$this->soa['ns'] = $matches[5];
			
			// replace @ with .
			if (strpos($matches[6], "@") !== false) {
				$this->soa['mbox'] = str_replace("@", ".", $matches[6]);
			}
			else {
				$this->soa['mbox'] = $matches[6];
			}
			
			$this->soa['serial'] = $matches[7];
			
			// parse refresh
			if (isset($matches[9]) && !empty($matches[9])) {
				$this->soa['refresh'] =  $this->formatTime($matches[8], $matches[9]);
			}
			else {
				$this->soa['refresh'] =  $matches[8];
			}
			
			// parse retry
			if (isset($matches[11]) && !empty($matches[11])) {
				$this->soa['retry'] = $this->formatTime($matches[10], $matches[11]);
			}
			else {
				$this->soa['retry'] = $matches[10];
			}
			
			// parse expire
			if (isset($matches[13]) && !empty($matches[13])) {
				$this->soa['expire'] = $this->formatTime($matches[12], $matches[13]);
			}
			else {
				$this->soa['expire'] = $matches[12];
			}
			
			// parse minimum and ttl
			if (isset($matches[3]) && !empty($matches[3]) && $matches[3] != "IN" && $matches[3] != "SO") {
				$this->soa['minimum'] = $this->formatTime($matches[2], $matches[3]);
				$this->soa['ttl'] = $this->formatTime($matches[2], $matches[3]);
			}
			else {
				if (!empty($matches[2])) {
					$this->soa['minimum'] = $matches[2];
					$this->soa['ttl'] = $matches[2];
					$this->ttl = $this->soa['ttl'];
				}
				else {
					$this->soa['minimum'] = $this->ttl;
					$this->soa['ttl'] = $this->ttl;
				}
			}
		}
	}
	
	/**
	 * returns the parsed zone file
	 *
	 * @return	array
	 */
	public function getParsedData () {
		return array('soa' => $this->soa, 'rr' => $this->records);
	}
	
	/**
	 * format ttl to seconds
	 *
	 * @param	integer	$time
	 * @param	string	$modifier
	 * @return	integer
	 */
	public function formatTime ($time, $modifier = '') {
		if (!empty($modifier)) {
			switch($modifier) {
				case "y":
				case "Y":
					$multiplier=86400*365;
					break;
				case 'w':
				case 'W':
					$multiplier=86400*7;
					break;
				case "d":
				case "D":
					$multiplier=86400;
					break;
				case "h":
				case "H":
					$multiplier=3600;
					break;
				case "m":
				case "M":
					$multiplier=60;
					break;
				case "s":
				case "S":
				default:
					$multiplier=1;
					break;
			}
			
			return $time * $multiplier;
		}
		else {
			return $time;
		}
	}
}
