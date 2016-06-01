<?php
/**
 * Install
 */
$installer = $this;
$this->startSetup();

$installer->run("
  CREATE TABLE IF NOT EXISTS `mailup_filter_hints` (
  `filter_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `hints` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`filter_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
/**
 * Install jobs Table
 */
$installer->run("
   DROP TABLE IF EXISTS {$installer->getTable('mailup/job')};
   CREATE TABLE IF NOT EXISTS {$installer->getTable('mailup/job')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `mailupgroupid` int(11) DEFAULT NULL,
  `list_id` int(11) DEFAULT NULL,
  `list_guid` varchar(255) DEFAULT NULL,
  `send_optin` tinyint(1) NOT NULL,
  `as_pending` tinyint(1) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `process_id` INT UNSIGNED DEFAULT NULL,
  `tries` INT UNSIGNED DEFAULT 0,
  `type` INT UNSIGNED DEFAULT 0,
  `queue_datetime` datetime NOT NULL,
  `start_datetime` datetime,
  `finish_datetime` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
/**
 * Install Job Sync Tasks
 */
$installer->run("
  DROP TABLE IF EXISTS {$installer->getTable('mailup/sync')};
  CREATE TABLE IF NOT EXISTS {$installer->getTable('mailup/sync')} (
  `id` int (11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `job_id` int(11) NOT NULL,
  `needs_sync` tinyint(1) DEFAULT 1,
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_sync` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY uniq_key (`customer_id`,`entity`,`job_id`, `store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
/**
 * Install Log Table
 */
$this->run("
    DROP TABLE IF EXISTS {$installer->getTable('mailup/log')};
    CREATE TABLE IF NOT EXISTS {$installer->getTable('mailup/log')}  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `data` TEXT DEFAULT NULL,
  `event_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
   
$this->endSetup();