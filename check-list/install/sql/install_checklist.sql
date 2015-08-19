SET SQL_SAFE_UPDATES=0$


-- Create new Tables for Check List
-- ================================

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%checklist` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `orde` int(11) default NULL,
  `menu_id` int(11) NOT NULL default '0',
  `indent` int(11) NOT NULL default '0',
  `header` int(11) NOT NULL default '0',
  `period` int(11) NOT NULL default '0',
  `tekst` varchar(250) default NULL,
  `disabled` int(11) NOT NULL default '0',
  `start` date NOT NULL,
  `help` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `orde` (`orde`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1$

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%checklist_entries` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `datum` datetime default NULL,
  `door` varchar(30) default NULL,
  `ref` int(11) default NULL,
  `tekst` text,
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `datum` (`datum`),
  KEY `ref` (`ref`),
  FULLTEXT KEY `tekst` (`tekst`),
  FULLTEXT KEY `volletekst` (`tekst`,`door`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1$


SET SQL_SAFE_UPDATES=1$	