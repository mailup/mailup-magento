<?php

$this->startSetup();
$installer = $this;

/**
 * Add Job Type
 */
$this->run("
    ALTER TABLE {$installer->getTable('mailup/job')} 
    ADD `type` INT UNSIGNED DEFAULT 0;
");
/**
 * Need a Simple Key to allow us to utilise the grid.
 */
$this->run("
    ALTER TABLE {$installer->getTable('mailup/sync')} 
    ADD column id int (11) NOT NULL AUTO_INCREMENT,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (id),
    ADD UNIQUE KEY uniq_key (`customer_id`,`entity`,`job_id`, `store_id`);
");
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