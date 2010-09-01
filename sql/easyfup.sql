# CocoaMySQL dump
# Version 0.7b4
# http://cocoamysql.sourceforge.net
#
# Host: localhost (MySQL 5.0.41)
# Database: easyfup
# Generation Time: 2008-01-02 21:54:26 +0100
# ************************************************************

# Dump of table files
# ------------------------------------------------------------

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `hashname` varchar(255) NOT NULL,
  `extension` varchar(255) default '',
  `mime_type` varchar(32) NOT NULL,
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `uploaded` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_download` datetime NOT NULL default '0000-00-00 00:00:00',
  `size` int(11) default NULL,
  `locked` tinyint(1) default NULL,
  `download_count` int(11) NOT NULL default '0',
  `traffic_used` int(11) NOT NULL default '0',
  `password` varchar(255) default NULL,
  `firstdownloaderase` tinyint NOT NULL default 0,
  `deletehash` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL default '',
  `public` tinyint(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;



# Dump of table formkeys
# ------------------------------------------------------------

DROP TABLE IF EXISTS `formkeys`;

CREATE TABLE `formkeys` (
  `id` int(11) NOT NULL auto_increment,
  `formkey` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `added` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `added` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



