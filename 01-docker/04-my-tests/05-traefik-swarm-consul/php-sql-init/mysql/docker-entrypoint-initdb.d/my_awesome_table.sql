/* Create table with 2 fields */
CREATE TABLE `my_awesome_table` (
  `id_my_awesome_table` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `test_my_awesome_table` varchar(100) NOT NULL
);

/* Add one glorious entry */
INSERT INTO `my_awesome_table` (`test_my_awesome_table`)
VALUES ('hey je viens de la bdd');