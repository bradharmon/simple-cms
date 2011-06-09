CREATE TABLE `regions` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `page` varchar(255) NOT NULL,
  `div_id` varchar(32) NOT NULL,
  `content` varchar(50000) NOT NULL,
  PRIMARY KEY  (`id`)
);
