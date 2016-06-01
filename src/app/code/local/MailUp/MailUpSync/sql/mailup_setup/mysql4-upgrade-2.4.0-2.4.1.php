<?php
/**
 * Install
 */
$installer = $this;
$this->startSetup();

/**
 * Install Job Sync Tasks
 * 
 * Change to InnoDB, and add in Foreign Key
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
  UNIQUE KEY uniq_key (`customer_id`,`entity`,`job_id`, `store_id`),
  INDEX `fk_jobs_idx` (`job_id` ASC),
  CONSTRAINT `fk_jobs`
    FOREIGN KEY (`job_id`)
    REFERENCES {$installer->getTable('mailup/job')} (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
   
$this->endSetup();
