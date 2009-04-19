/* Used to remove cookie column */



alter table files add column `firstdownloaderase` tinyint NOT NULL default 0;
alter table files add column `deletehash` varchar(255) NOT NULL;
alter table files add column `description` mediumtext NOT NULL default '';
alter table files drop column `ip`;
