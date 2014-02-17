CREATE TABLE `civicrm_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `reason` text,
  `work_description` text,
  `qualifications` text,
  `expected_results` text,
  `sector_coordinator_id` int(11) DEFAULT NULL,
  `country_coordinator_id` int(11) DEFAULT NULL,
  `project_officer_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `PROGRAM_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8


