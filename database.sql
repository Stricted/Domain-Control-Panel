CREATE TABLE IF NOT EXISTS dns_soa (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	origin VARCHAR(255) NOT NULL,
	ns VARCHAR(255) NOT NULL,
	mbox VARCHAR(255) NOT NULL,
	serial INT(10) NOT NULL DEFAULT '1',
	refresh INT(10) NOT NULL DEFAULT '28800',
	retry INT(10) NOT NULL DEFAULT '7200',
	expire INT(10) NOT NULL DEFAULT '604800',
	minimum INT(10) NOT NULL DEFAULT '86400',
	ttl INT(10) NOT NULL DEFAULT '86400',
	active boolean NOT NULL DEFAULT 1,
	UNIQUE KEY origin (origin)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_rr (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	zone INT(10) NOT NULL,
	name VARCHAR(255) NOT NULL,
	data VARCHAR(255) NOT NULL,
	aux INT(10) NOT NULL,
	ttl INT(10) NOT NULL DEFAULT '86400',
	type enum('A','AAAA','CNAME','HINFO','MX','NAPTR','NS','PTR','RP','SRV','TXT', 'DNSKEY', 'TLSA', 'DS') DEFAULT NULL,
	active boolean NOT NULL DEFAULT 1,
	UNIQUE KEY dns_rr (zone, name, type, data)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_sec (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	zone INT(10) NOT NULL,
	type enum('ZSK','KSK') DEFAULT NULL,
	algo VARCHAR(255) NOT NULL,
	public TEXT,
	private TEXT,
	dsset TEXT,
	active boolean NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_user (
	userID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(255) NOT NULL,
	email VARCHAR(255) NOT NULL,
	password VARCHAR(255) NOT NULL,
	reseller INT(10) NOT NULL DEFAULT '0',
	status INT(10) NOT NULL DEFAULT '0',
	UNIQUE KEY dns_user (username, email, password)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_soa_to_user (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	soaID INT(10) NOT NULL,
	UNIQUE KEY dns_soa_to_user (userID, soaID)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_options (
  optionID int(255) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `option` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  UNIQUE KEY dns_options (`option`, `value`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_api (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	apiKey VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

ALTER TABLE dns_api ADD FOREIGN KEY (userID) REFERENCES dns_user (userID) ON DELETE CASCADE;
ALTER TABLE dns_sec ADD FOREIGN KEY (zone) REFERENCES dns_soa (id) ON DELETE CASCADE;
ALTER TABLE dns_rr ADD FOREIGN KEY (zone) REFERENCES dns_soa (id) ON DELETE CASCADE;
ALTER TABLE dns_soa_to_user ADD FOREIGN KEY (userID) REFERENCES dns_user (userID) ON DELETE CASCADE;
ALTER TABLE dns_soa_to_user ADD FOREIGN KEY (soaID) REFERENCES dns_soa (id) ON DELETE CASCADE;

INSERT INTO dns_options VALUES (1, 'dns_api_key', 'aa');
INSERT INTO dns_options VALUES (4, 'dns_soa_minimum_ttl', '60');
INSERT INTO dns_options VALUES (3, 'enable_debug_mode', '1');
INSERT INTO dns_options VALUES (2, 'offline', '0');

INSERT INTO `dns_user` VALUES (1, 'admin', 'example@example.net', '$2a$08$XfcfTGc1LlmOHWUt/2sfNeFLEwqESy6wmrIIJMyQS1j5pwembqiae', '0', '2');
