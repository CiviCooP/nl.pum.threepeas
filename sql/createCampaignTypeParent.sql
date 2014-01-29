CREATE TABLE `civicrm_campaign_type_parent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_type_id` int(11) DEFAULT NULL,
  `parent_campaign_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;


