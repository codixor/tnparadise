# Dump of table social
# ------------------------------------------------------------

DROP TABLE IF EXISTS `social`;

CREATE TABLE `social` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `uid` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access_token_secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `oauth1_token_identifier` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oauth1_token_secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oauth2_access_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oauth2_refresh_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oauth2_expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_user_id_provider_unique` (`user_id`,`provider`),
  UNIQUE KEY `social_provider_uid_unique` (`provider`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

