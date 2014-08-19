CREATE TABLE IF NOT EXISTS `civicrm_contribution_number_projects` (
  `contribution_id` int(11) NOT NULL,
  `number_projects` int(11) DEFAULT NULL,
  PRIMARY KEY (`contribution_id`),
  UNIQUE KEY `contribution_id_UNIQUE` (`contribution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

