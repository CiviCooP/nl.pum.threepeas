CREATE TABLE IF NOT EXISTS `civicrm_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) DEFAULT NULL,
  `programme_id` int(11) DEFAULT NULL,
  `reason` text,
  `work_description` text,
  `qualifications` text,
  `expected_results` text,
  `customer_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `PROGRAMME_id` (`programme_id`),
  KEY `CUSTOMER_id` (`customer_id`),
  KEY `COUNTRY_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
