DROP TABLE IF EXISTS `glpi_plugin_simcard`;
CREATE TABLE `glpi_plugin_simcard` (
	`ID` int(11) NOT NULL auto_increment,
	`recursive` tinyint(1) NOT NULL default '0',
	`ID_sim1` int(11),
	`pin1` int(4) NOT NULL default '0',
	`FK_line_sim_1` int(11) NOT NULL default '0',
	`ID_sim2` int(11),
	`pin2` int(4) NOT NULL default '0',
	`FK_line_sim_2` int(11) NOT NULL default '0',	
	`puk` int(8) NOT NULL default '0',	
	`FK_entities` int(11) NOT NULL default '0',
	`FK_enterprise` SMALLINT(6) NOT NULL DEFAULT '0',
	`date_mod` datetime default NULL,
	`type` int(11) NOT NULL default '0',
	`comment` varchar(255) collate utf8_unicode_ci NOT NULL 
default '',

	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_simcard_device`;
CREATE TABLE `glpi_plugin_simcard_device` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_simcard` int(11) NOT NULL default '0',
	`FK_device` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_simcard` (`FK_simcard`,`FK_device`,`device_type`),
	KEY `FK_simcard_2` (`FK_simcard`),
	KEY `FK_device` (`FK_device`,`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_simcard_profiles`;
CREATE TABLE `glpi_plugin_simcard_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'simcard',
	`is_default` smallint(6) NOT NULL default '0',
	`simcard` char(1) default NULL,
	`type` int(11) NOT NULL default '0',
	`create_simcard` char(1) default NULL,
	`update_simcard` char(1) default NULL,
	`delete_simcard` char(1) default NULL,

	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'5746','2','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'5746','6','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'5746','7','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'5746','8','5','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'5746','12','6','0');

INSERT INTO `glpi_plugin_simcard_profiles` ( `ID`, `name` , `interface`, `is_default`, `simcard`,`create_simcard`,`update_simcard`,`delete_simcard` ) VALUES ('1', 'post-only','simcard','1','NULL',NULL,NULL,NULL);
INSERT INTO `glpi_plugin_simcard_profiles` ( `ID`, `name` , `interface`, `is_default`, `simcard`,`create_simcard`,`update_simcard`,`delete_simcard` ) VALUES ('2', 'normal','simcard','0','r',NULL,NULL,NULL);
INSERT INTO `glpi_plugin_simcard_profiles` ( `ID`, `name` , `interface`, `is_default`, `simcard`,`create_simcard`,`update_simcard`,`delete_simcard` ) VALUES ('3', 'admin','simcard','0','w','1','1','0');
INSERT INTO `glpi_plugin_simcard_profiles` ( `ID`, `name` , `interface`, `is_default`, `simcard`,`create_simcard`,`update_simcard`,`delete_simcard` ) VALUES ('4', 'super-admin','simcard','0','w','1','1','1');



DROP TABLE IF EXISTS `glpi_dropdown_plugin_simcard_types`;
CREATE TABLE `glpi_dropdown_plugin_simcard_types` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_dropdown_plugin_simcard_types` ( `ID` , `name` , `comments`) VALUES ('1', 'Multitarjeta','');
INSERT INTO `glpi_dropdown_plugin_simcard_types` ( `ID` , `name` , `comments`) VALUES ('2', 'Twin','');
INSERT INTO `glpi_dropdown_plugin_simcard_types` ( `ID` , `name` , `comments`) VALUES ('3', 'Normal','');