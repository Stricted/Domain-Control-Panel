<?php
namespace dns\util;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2015 Jan Altensen (Stricted)
 */
class DNSSECUtil {
	
	/**
	 * calculate the DS record for parent zone
	 *
	 * @param	string	$owner
	 * @param	string	$algorithm
	 * @param	string	$publicKey
	 * @return	array
	 */
	public static function calculateDS ($owner, $algorithm, $publicKey) {
		$owner = self::convertOwner($owner);
		$flags = '0101';
		$protocol = '03';
		$algorithm = '0'.dechex($algorithm);
		$publicKey = bin2hex(base64_decode($publicKey));
		
		$string = hex2bin($owner.$flags.$protocol.$algorithm.$publicKey);
		
		$sha1 = strtoupper(sha1($string));
		$sha256 = strtoupper(hash('sha256', $string));
		
		return array('sha1' => $sha1, 'sha256' => $sha256);
	}
	
	/**
	 * convert the domain name to HEX
	 *
	 * @param	string	$owner
	 * @return	string
	 */
	public static function convertOwner ($owner) {
		$return = '';
		
		$data = explode(".", $owner);
		$return .= '0'.dechex(strlen($data[0]));
		$data[0] = str_split($data[0]);
		for ($i = 0; $i < count($data[0]); $i++) {
			$byte = strtoupper(dechex(ord($data[0][$i])));
			$byte = str_repeat('0', 2 - strlen($byte)).$byte;
			$return .= $byte;
		}
		
		$return .= '0'.dechex(strlen($data[1]));
		$data[1] = str_split($data[1]);
		
		for ($i = 0; $i < count($data[1]); $i++) {
			$byte = strtoupper(dechex(ord($data[1][$i])));
			$byte = str_repeat('0', 2 - strlen($byte)).$byte;
			$return .= $byte;
		}
		
		$return .= '00';
		
		return $return;
	}
	
	/**
	 * validate DNSSEC public key
	 *
	 * @param	string	$content
	 * @return	boolean
	 */
	 public static function validatePublicKey ($content) {
		$pattern = "; This is a (key|zone)-signing key, keyid (?P<keyid>[0-9]+), for (?P<domain>[\s\S]+)\.\n";
		$pattern .= "; Created: (?P<created>[0-9]+) \(([a-z0-9: ]+)\)\n";
		$pattern .= "; Publish: (?P<publish>[0-9]+) \(([a-z0-9: ]+)\)\n";
		$pattern .= "; Activate: (?P<activate>[0-9]+) \(([a-z0-9: ]+)\)\n";
		$pattern .= "([\s\S]+). IN DNSKEY 25(6|7) 3 (?P<algorithm>[0-9]+) (?P<key>[\s\S]+)(\n)?";
		preg_match('/'.$pattern.'/i', $content, $matches);
		if (!empty($matches)) {
			if (!in_array($matches['algorithm'], array(1, 2, 3, 5, 6, 7, 8, 10, 12, 13, 14))) {
				return false;
			}
			
			$data = explode(' ', $matches['key']);
			foreach ($data as $d) {
				if (base64_encode(base64_decode($d, true)) !== $d) {
					return false;
				}
			}
		}
		else {
			return false;
		}
		
		return true;
	}
	
	/**
	 * validate DNSSEC private key
	 *
	 * @param	string	$content
	 * @return	boolean
	 */
	public static function validatePrivateKey ($content) {
		$pattern = "Private-key-format: v([0-9a-z.]+)\n";
		$pattern .= "Algorithm: (?P<algorithm>[0-9]+) \(([0-9a-z\-]+)\)\n";
		$pattern .= "Modulus: (?P<modulus>[\s\S]+)\n";
		$pattern .= "PublicExponent: (?P<publicexponent>[\s\S]+)\n";
		$pattern .= "Prime1: (?P<prime1>[\s\S]+)\n";
		$pattern .= "Prime2: (?P<prime2>[\s\S]+)\n";
		$pattern .= "Exponent1: (?P<exponent1>[\s\S]+)\n";
		$pattern .= "Exponent2: (?P<exponent2>[\s\S]+)\n";
		$pattern .= "Coefficient: (?P<coefficient>[\s\S]+)\n";
		$pattern .= "Created: (?P<created>[0-9]+)\n";
		$pattern .= "Publish: (?P<publish>[0-9]+)\n";
		$pattern .= "Activate: (?P<activate>[0-9]+)(\n)?";
		
		preg_match('/'.$pattern.'/i', $content, $matches);
		if (!empty($matches)) {
			if (!in_array($matches['algorithm'], array(1, 2, 3, 5, 6, 7, 8, 10, 12, 13, 14))) {
				return false;
			}
			else if (base64_encode(base64_decode($matches['modulus'], true)) !== $matches['modulus']) {
				return false;
			}
			else if (base64_encode(base64_decode($matches['publicexponent'], true)) !== $matches['publicexponent']) {
				return false;
			}
			else if (base64_encode(base64_decode($matches['prime1'], true)) !== $matches['prime1']) {
				return false;
			}
			else if (base64_encode(base64_decode($matches['prime2'], true)) !== $matches['prime2']) {
				return false;
			}
			else if (base64_encode(base64_decode($matches['exponent1'], true)) !== $matches['exponent1']) {
				return false;
			}
			else if (base64_encode(base64_decode($matches['exponent2'], true)) !== $matches['exponent2']) {
				return false;
			}
			else if (base64_encode(base64_decode($matches['coefficient'], true)) !== $matches['coefficient']) {
				return false;
			}
		}
		else {
			return false;
		}
		
		return true;
	}
}
