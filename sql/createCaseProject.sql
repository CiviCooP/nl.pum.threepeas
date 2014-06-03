CREATE TABLE `civicrm_case_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11),
  `case_id` int(11),
  `is_active` tinyint(4),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `PROJECT_id` (`project_id`),
  KEY `CASE_id` (`case_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8


