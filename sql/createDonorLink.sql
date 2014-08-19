CREATE TABLE IF NOT EXISTS `civicrm_donor_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donation_entity` varchar(75) DEFAULT NULL,
  `donation_entity_id` int(11) DEFAULT NULL,
  `entity` varchar(75) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `number_projects` int(11) DEFAULT NULL,
  `is_active` tinyint(4),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `DONATION_ENTITY_id` (`donation_entity_id`),
  KEY `ENTITY_id` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
