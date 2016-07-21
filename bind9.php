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
				
				if (strpos($txt, " ") !== false) {
					if (substr($txt, -1) != '"' && substr($txt, 0, 1) != '"') {
						$record['data'] = '"'.$txt.'"';
					}
				}
				
				if (strpos($txt, '" "') !== false && $txt != '" "') {
					$record['data'] = '('.$record['data'].')'; // for dkim records
				}
				
				if (strpos($record['data'], "v=spf1") !== false) {
					$out .= $record['name']."\t".$record['ttl']."\tIN\tSPF\t" . $record['data']."\n";
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
				shell_exec("mkdir -p ".$dir);
			}
			
			if ($sec['type'] == "ZSK" || $sec['type'] == "KSK") {
				if (!empty($sec['public']) && !empty($sec['private'])) {
					preg_match("/; This is a (key|zone)-signing key, keyid ([0-9]+), for ".$zone['soa']['origin']."/i", $sec['public'], $match);
					$filename1 = getFileName ($zone['soa']['origin'], $sec['algo'], $match[2], "pub");
					$filename2 = getFileName ($zone['soa']['origin'], $sec['algo'], $match[2], "priv");
										
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
		
		$handler = fOpen("/srv/bind/".$zone['soa']['origin']."db", "a+");
		fWrite($handler, $out);
		fClose($handler);
		
		$signed = false;
		if ($sign === true) {
			shell_exec("cd /srv/bind/ && /usr/sbin/dnssec-signzone -r /dev/urandom -A -N INCREMENT -K /srv/bind/dnssec/".$zone['soa']['origin']."/ -o ".$zone['soa']['origin']." -t ".$zone['soa']['origin']."db");
			if (file_exists("/srv/bind/".$zone['soa']['origin']."db.signed")) {
				$signed = true;
			}
		}
		
		$cout = "zone \"" . $zone['soa']['origin'] . "\" {\n";
		$cout .= "\ttype master;\n";
		$cout .= "\tnotify no;\n";
		$cout .= "\tfile \"/srv/bind/".$zone['soa']['origin']."db".($signed === true ? ".signed" : "")."\";\n";
		$cout .= "};\n\n";

		$handler = fOpen("/srv/bind/domains.cfg", "a+");
		fWrite($handler, $cout);
		fClose($handler);
	}
	
	shell_exec("/etc/init.d/bind9 reload");
}

function getFileName ($zone, $algo, $id, $type) {
	$len = strlen($id);
	if ($len == "1") {
		$id = "0000".$id;
	}
	else if ($len == "2") {
		$id = "000".$id;
	}
	else if ($len == "3") {
		$id = "00".$id;
	}
	else if ($len == "4") {
		$id = "0".$id;
	}
	if ($type == "pub") {
		$type = "key";
	}
	else if ($type == "priv") {
		$type = "private";
	}
	
	if ($algo == "8") {
		$algo = "008";
	}
	else if ($algo == "10") {
		$algo = "010";
	}
	
	return "K".$zone."+".$algo."+".$id.".".$type;
}
