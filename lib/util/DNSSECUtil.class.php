<?php
namespace dns\util;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2015 Jan Altensen (Stricted)
 */
class DNSSECUtil {
	
	function calculateDS ($owner, $algorithm, $publicKey) {
		$owner = $this->convertOwner($owner);
		$flags = '0101';
		$protocol = '03';
		$algorithm = '0'.dechex($algorithm);
		$publicKey = bin2hex(base64_decode($publicKey));
		
		$string = hex2bin($owner.$flags.$protocol.$algorithm.$publicKey);
		
		$sha1 = sha1($string);
		$sha256 = hash('sha256', $string);
		
		return array('sha1' => $sha1, 'sha256' => $sha256);
	}
	
	function convertOwner ($owner) {
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
}
