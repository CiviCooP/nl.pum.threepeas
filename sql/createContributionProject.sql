CREATE TABLE IF NOT EXISTS `civicrm_contribution_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contribution_id` int(11) DEFAULT NULL,
  `number_projects` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `CONTRIBUTION_id` (`contribution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
