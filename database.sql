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
	--- data VARCHAR(255) NOT NULL,
	data TEXT,
	aux INT(10) NOT NULL,
	ttl INT(10) NOT NULL DEFAULT '86400',
	type enum('A', 'AAAA', 'CNAME', 'MX', 'PTR', 'SRV', 'TXT', 'TLSA', 'NS', 'DS') DEFAULT NULL,
	active boolean NOT NULL DEFAULT 1,
	--- UNIQUE KEY dns_rr (zone, name, type, data)
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
	optionName VARCHAR(255) NOT NULL DEFAULT '',
	optionValue MEDIUMTEXT,
	UNIQUE KEY optionName (optionName)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_api (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	apiKey VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_template (
	templateID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	template TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_session (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sessionID VARCHAR(255) NOT NULL DEFAULT '',
	expire INT(10) NOT NULL,
	sessionData TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_permissions (
	permissionID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	permission VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_permissions_to_user (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID INT(10) NOT NULL,
	permissionID VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dns_language (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	languageID INT(10) NOT NULL,
	languageItem VARCHAR(255) NOT NULL,
	languageValue VARCHAR(255) NOT NULL
) ENGINE=InnoDB;



ALTER TABLE dns_api ADD FOREIGN KEY (userID) REFERENCES dns_user (userID) ON DELETE CASCADE;
ALTER TABLE dns_sec ADD FOREIGN KEY (zone) REFERENCES dns_soa (id) ON DELETE CASCADE;
ALTER TABLE dns_rr ADD FOREIGN KEY (zone) REFERENCES dns_soa (id) ON DELETE CASCADE;
ALTER TABLE dns_soa_to_user ADD FOREIGN KEY (userID) REFERENCES dns_user (userID) ON DELETE CASCADE;
ALTER TABLE dns_soa_to_user ADD FOREIGN KEY (soaID) REFERENCES dns_soa (id) ON DELETE CASCADE;
ALTER TABLE dns_template ADD FOREIGN KEY (userID) REFERENCES dns_user (userID) ON DELETE CASCADE;
ALTER TABLE dns_permissions_to_user ADD FOREIGN KEY (userID) REFERENCES dns_user (userID) ON DELETE CASCADE;
ALTER TABLE dns_permissions_to_user ADD FOREIGN KEY (permissionID) REFERENCES dns_permissions (id) ON DELETE CASCADE;

INSERT INTO dns_options VALUES (1, 'dns_api_key', '0E2372C5-E5A3-424B-82E5-75AD723A9447');
INSERT INTO dns_options VALUES (2, 'offline', '0');
INSERT INTO dns_options VALUES (3, 'enable_debug', '1');
INSERT INTO dns_options VALUES (4, 'dns_default_records', '{domain}:NS:ns1.stricted.de.\n{domain}:NS:ns2.stricted.de.\n{domain}:NS:ns3.stricted.de.\n{domain}:NS:ns4.stricted.de.\n{domain}:NS:ns5.stricted.de.\n{domain}:MX:mail.{domain}\n{domain}:A:84.200.248.52\n{domain}:AAAA:2001:1608:12:1::def\n*.{domain}:A:84.200.248.52\n*.{domain}:AAAA:2001:1608:12:1::def\n{domain}:TXT:\"v=spf1 mx -all\"');
INSERT INTO dns_options VALUES (5, 'dns_soa_mbox', 'info.stricted.de.');
INSERT INTO dns_options VALUES (6, 'dns_soa_ns', 'ns1.stricted.de.');
INSERT INTO dns_options VALUES (7, 'dns_soa_ttl', '86400');
INSERT INTO dns_options VALUES (8, 'dns_soa_refresh', '28800');
INSERT INTO dns_options VALUES (9, 'dns_soa_retry', '7200');
INSERT INTO dns_options VALUES (10, 'dns_soa_expire', '604800');
INSERT INTO dns_options VALUES (11, 'dns_soa_minimum_ttl', '60');
INSERT INTO dns_options VALUES (12, 'enable_dnssec', '1');


INSERT INTO `dns_user` VALUES (1, 'admin', 'example@example.net', '$2a$08$XfcfTGc1LlmOHWUt/2sfNeFLEwqESy6wmrIIJMyQS1j5pwembqiae', '0', '2');
