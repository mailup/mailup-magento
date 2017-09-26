<?php
/**
 * Update Mailup
 */
$installer = $this;
$this->startSetup();

/**
 * Rename sync jobs to remove sevenlike reference
 */
$cronScheduleTable = Mage::getSingleton("core/resource")->getTableName("cron_schedule");
$installer->run("UPDATE {$cronScheduleTable} SET job_code='mailup_mailupsync' WHERE job_code='sevenlike_mailup'");

$this->endSetup();
