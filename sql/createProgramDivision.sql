CREATE TABLE `civicrm_program_division` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `min_projects` int(11) DEFAULT NULL,
  `max_projects` int(11) DEFAULT NULL,
  `min_budget` int(15) DEFAULT NULL,
  `max_budget` int(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `program_idx` (`program_id`),
  CONSTRAINT `fk_program` FOREIGN KEY (`program_id`) REFERENCES `civicrm_program` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8


