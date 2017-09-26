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
$installer->run(
    "UPDATE {$cronScheduleTable} SET job_code='mailup_mailupsync' WHERE job_code='MailUp_MailUpSync'"
);
$installer->run(
    "UPDATE {$cronScheduleTable} SET job_code='mailup_mailupsync_autosync' WHERE job_code='sevenlike_mailup_autosync'"
);
$installer->run(
    "UPDATE {$cronScheduleTable} SET job_code='mailup_mailupsync_autosync' WHERE job_code='MailUp_MailUpSync_autosync'"
);

/**
 * Remove or rename old sevenlike cron settings
 */
// Get all sevenlike_mailup cron entries
$collection = Mage::getModel('core/config_data')->getCollection();
$collection->addPathFilter('crontab/jobs/sevenlike_mailup/schedule');
// Foreach entry, try to insert. If exists, ignore. Delete originals
$configTable = Mage::getSingleton("core/resource")->getTableName("core_config_data");
foreach ($collection as $conf) {
    $installer->run(
        "INSERT IGNORE INTO {$configTable} (`scope`, `scope_id`, `path`, `value`) VALUES (".
        "'{$conf->getScope()}', ".
        "'{$conf->getScopeId()}', ".
        "'crontab/jobs/mailup_mailupsync/schedule/cron_expr', ".
        "'{$conf->getValue()}')"
    );
    $installer->run("DELETE FROM {$configTable} WHERE config_id='{$conf->getConfigId()}'");
}

$this->endSetup();
