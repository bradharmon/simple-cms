DROP TABLE `users`;

CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` varchar(64) NOT NULL UNIQUE,
  `password` varchar(64) NOT NULL,
  `salt` varchar(22) NOT NULL,
  `verified` BOOLEAN NOT NULL,
  `verification_hash` varchar(13) default NULL,
  PRIMARY KEY(`id`)
);
