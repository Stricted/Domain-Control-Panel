<?php
/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2014-2016 Jan Altensen (Stricted)
 */
$data = file_get_contents("https://dns-control.eu/API/?key=xxx");
$data = json_decode($data, true);
if (is_array($data) && !isset($data['error'])) {
	shell_exec("rm -rf /srv/bind/*");
	shell_exec("mkdir -p /srv/bind/signed/");
	shell_exec("mkdir -p /srv/bind/unsigned/");
	shell_exec("mkdir -p /srv/bind/dnssec/");
	shell_exec("mkdir -p /srv/bind/dsset/");
	
	foreach ($data as $zone) {
		$out = $zone['soa']['origin']."\t".$zone['soa']['minimum']."\tIN\tSOA\t".$zone['soa']['ns']." ".$zone['soa']['mbox']." (\n";
		$out .=	"\t\t\t\t".$zone['soa']['serial']."\t; Serial\n";
		$out .=	"\t\t\t\t".$zone['soa']['refresh']."\t\t; Refresh\n";
		$out .=	"\t\t\t\t".$zone['soa']['retry']."\t\t; Retry\n";
		$out .=	"\t\t\t\t".$zone['soa']['expire']."\t\t; Expire\n";
		$out .=	"\t\t\t\t180 )\t\t; Negative Cache TTL\n";
		$out .=	";\n";

		$signed = false;
		$zsk = false;
		$ksk = false;
		foreach ($zone['rr'] as $record) {
			if ($record['type'] == "DNSKEY") {
				if ($record['aux'] == 256) {
					$zsk = true;
				}
				else if ($record['aux'] == 257) {
					$ksk = true;
				}
				
				$out .= $record['name']."\t".$record['ttl']."\tIN\t".$record['type']."\t".$record['aux']."\t".$record['data']."\n";
			}
			else if ($record['type'] == "MX" || $record['type'] == "SRV" || $record['type'] == "TLSA" || $record['type'] == "DS") {
				$out .= $record['name']."\t".$record['ttl']."\tIN\t".$record['type']."\t".$record['aux']."\t".$record['data']."\n";
			}
			else if ($record['type'] == "TXT") {
				$txt = $record['data'];
				
				if (strpos($txt, " ") !== false && strpos($txt, '" "') !== false && $txt != '" "') {
					if (substr($txt, 0, 1) != '(' && substr($txt, -1) != ')') {
						if (substr($txt, 0, 1) != '"' && substr($txt, -1) != '"') {
							$record['data'] = '("'.$txt.'")';
						}
						else {
							$record['data'] = '('.$txt.')';
						}
					}
				}
				else if (substr($txt, 0, 1) != '"' && substr($txt, -1) != '"') {
					$record['data'] = '"'.$txt.'"';
				}
				
				$out .= $record['name']."\t".$record['ttl']."\tIN\t".$record['type']."\t" . $record['data']."\n";
			}
			else {
				$out .= $record['name']."\t".$record['ttl']."\tIN\t".$record['type']."\t\t" . $record['data']."\n";
			}
		}
		
		$zskkey = false;
		$kskkey = false;
		foreach ($zone['sec'] as $sec) {
			$dir = "/srv/bind/dnssec/".$zone['soa']['origin']."/";
			if (!file_exists($dir)) {
				shell_exec("mkdir -p " . escapeshellcmd($dir));
			}
			
			if ($sec['type'] == "ZSK" || $sec['type'] == "KSK") {
				if (!empty($sec['public']) && !empty($sec['private'])) {
					preg_match("/; This is a (key|zone)-signing key, keyid ([0-9]+), for ".$zone['soa']['origin']."/i", $sec['public'], $match);
					$filename1 = getFileName ($zone['soa']['origin'], $sec['algo'], $match[2], "key");
					$filename2 = getFileName ($zone['soa']['origin'], $sec['algo'], $match[2], "private");
										
					if (file_exists($dir.$filename1)) {
						unlink($dir.$filename1);
					}
					
					if (file_exists($dir.$filename2)) {
						unlink($dir.$filename2);
					}
					
					$handler = fOpen($dir.$filename1, "a+");
					fWrite($handler, $sec['public']);
					fClose($handler);
					
					$handler = fOpen($dir.$filename2, "a+");
					fWrite($handler, $sec['private']);
					fClose($handler);
					
					if (file_exists($dir.$filename1) && file_exists($dir.$filename2)) {
						/* fallback for missing DNSKEY record */
						if ($zsk === false || $ksk === false) {
							preg_match("/".$zone['soa']['origin']." IN DNSKEY ([0-9]+) ([0-9]+) ([0-9]+) ([\s\S]+)/i", $sec['public'], $match);
							$out .= $zone['soa']['origin']."\t60\tIN\tDNSKEY\t".$match[1]."\t".$match[2]." ".$match[3]." ".$match[4]."\n";
							if ($sec['type'] == "ZSK") {
								$zsk = true;
							}
							else if ($sec['type'] == "KSK") {
								$ksk = true;
							}
						}
						
						if ($sec['type'] == "ZSK") {
							$zskkey = true;
						}
						else if ($sec['type'] == "KSK") {
							$kskkey = true;
						}
					}
				}
			}
		}
		
		$sign = false;
		if ($zsk === true && $ksk === true && $zskkey === true && $kskkey === true) {
			$sign = true;
		}
		
		$handler = fOpen("/srv/bind/unsigned/".$zone['soa']['origin']."db", "a+");
		fWrite($handler, $out);
		fClose($handler);
		
		$signed = false;
		if ($sign === true) {
			shell_exec("cd /srv/bind/ && /usr/sbin/dnssec-signzone -r /dev/urandom -A -N INCREMENT -d dsset/ -f signed/".escapeshellcmd($zone['soa']['origin'])."db -K dnssec/".escapeshellcmd($zone['soa']['origin'])."/ -o ".escapeshellcmd($zone['soa']['origin'])." unsigned/".escapeshellcmd($zone['soa']['origin'])."db");
			if (file_exists("/srv/bind/signed/".$zone['soa']['origin']."db")) {
				$signed = true;
			}
		}
		
		$cout = "zone \"" . $zone['soa']['origin'] . "\" {\n";
		$cout .= "\ttype master;\n";
		$cout .= "\tnotify no;\n";
		$cout .= "\tfile \"/srv/bind/".($signed === true ? "signed" : "unsigned")."/".$zone['soa']['origin']."db\";\n";
		$cout .= "};\n\n";

		$handler = fOpen("/srv/bind/domains.cfg", "a+");
		fWrite($handler, $cout);
		fClose($handler);
	}
	
	shell_exec("/etc/init.d/bind9 reload");
}

function getFileName ($zone, $algo, $id, $type) {
	$id = str_repeat('0', 5 - strlen($id)).$id;
	$algo = str_repeat('0', 3 - strlen($algo)).$algo;
	return "K".$zone."+".$algo."+".$id.".".$type;
}
