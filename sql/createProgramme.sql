CREATE TABLE `civicrm_programme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) DEFAULT NULL,
  `description` text,
  `manager_id` int(11) DEFAULT NULL,
  `budget` int(11) DEFAULT NULL,
  `goals` text,
  `requirements` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `MANAGER` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

