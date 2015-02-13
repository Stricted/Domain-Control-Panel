Domain-Control-Panel
==================

**Domain-Control-Panel** is powered by [Stricted](https://github.com/Stricted) for easy managing your dns server with MySQL databases. I'm testing my work just on linux (Debian 6/7) machines, so i don't know if it works without problems on Windows machines. Feel free to give me feedback.

### Requirements
---
You may need for a working webinterface:

 * Linux machine (Debian 6+ recommended; windows may work too, but not recommended)
 * Linux knowledge
 * At least php 5.3.2+ and activated PDO-Extension (for mysql connection)
 * MySQL-database
 * working DNS server (Bind9 with DNSSEC is supported, myDNS and PowerDNS are supported too but without DNSSEC)
 * some time and basic knowledge about DNS

### Installation
---
The installation isn't so easy than it will be in the future, when we've have enough time to build a easy and nice installation system. For now, you'll need to import the database structure manually and set up the correct data in the config file. If you're a little bit experienced, it shouldn't be a big problem. Feel free to open a issue, when you need help with the installation process.

 * Clone the git repository: `git clone https://github.com/Stricted/Domain-Control-Panel.git`
 * Change the `config.inc.php.example` file to your needs and rename it to `config.inc.php`
 * Import the structure in `database.sql` file in your database.
 * Open the page and use the default user and password to login: `admin`

### Screenshots
---
![Screenshot 1](https://stricted.net/img/domain_panel.png "Screenshot 1")

### License
---
This project is licensed under [GNU LESSER GENERAL PUBLIC LICENSE Version 3](https://github.com/Stricted/Domain-Control-Panel/blob/master/LICENSE).

### Live Demo
---
Not available yet.

### Feature List
---
 * bind9 support (with DNSSEC)
 * Cronjob for creating zones for bind9
 * add Resource Records
 * delete Resource Records
 * deactivate/activate Resource Records
 * user login
 * backup zone files
 * API for nameserver synchronize
 * support for internationalized domain names (IDN/IDNA)
 
### Work in Progress
---
 * multi language support
 * userAPI
 * import existing bind9 zone files
 * easy installation assistant with step by step
 * error handling with mail support and logging (planned but i don't know if i build that)
 * Reseller system
 * add Domain functionality
 * limit zones and Resource Records (per zone) for users
 * ticket system
 * own zone template for new domains/zones (only for reseller)
 * new design