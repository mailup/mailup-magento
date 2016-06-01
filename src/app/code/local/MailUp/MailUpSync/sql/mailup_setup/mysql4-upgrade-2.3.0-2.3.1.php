<?php

$this->startSetup();
$installer = $this;
/**
 * We want to record the process id, and the number of attempts we've made at 
 * processing the job!
 */
$this->run("
    ALTER TABLE {$installer->getTable('mailup/job')} 
    ADD `process_id` INT UNSIGNED DEFAULT NULL,
    ADD `tries` INT UNSIGNED DEFAULT 0;
");

$this->endSetup();