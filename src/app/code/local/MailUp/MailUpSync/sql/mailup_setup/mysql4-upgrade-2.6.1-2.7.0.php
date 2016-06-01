<?php
/**
 * Install
 */
$installer = $this;
$this->startSetup();

/**
 * Alter jobs Table - add column for message_id
 */
$installer->run("
   ALTER TABLE {$installer->getTable('mailup/job')}
   ADD COLUMN `message_id` int AFTER `as_pending`
");

$this->endSetup();
