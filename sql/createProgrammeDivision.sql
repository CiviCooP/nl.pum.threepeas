CREATE TABLE `civicrm_programme_division` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programme_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `min_projects` int(11) DEFAULT NULL,
  `max_projects` int(11) DEFAULT NULL,
  `min_budget` int(15) DEFAULT NULL,
  `max_budget` int(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `programme_idx` (`programme_id`),
  CONSTRAINT `fk_programme` FOREIGN KEY (`programme_id`) REFERENCES `civicrm_programme` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8


