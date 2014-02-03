CREATE TABLE `civicrm_program` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) DEFAULT NULL,
  `description` text,
  `contact_id_manager` int(11) DEFAULT NULL,
  `budget` int(11) DEFAULT NULL,
  `goals` text,
  `requirements` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `MANAGER` (`contact_id_manager`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

