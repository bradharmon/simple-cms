CREATE TABLE `regions` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `div_id` varchar(32) NOT NULL,
  `data` varchar(50000) NOT NULL,
  PRIMARY KEY  (`id`)
);
