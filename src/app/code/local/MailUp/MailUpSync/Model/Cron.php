<?php
/**
 * Cron.php
 * 
 * Scheduled Task handler.
 */
require_once dirname(__FILE__) . "/MailUpWsImport.php";
require_once dirname(__FILE__) . "/Wssend.php";

class MailUp_MailUpSync_Model_Cron
{
    const LOCK_INDEX_ID = "mailupcronrun";
    /**
     * Run the Task
     * 
     * IF ANY Job we run fails, due to another processes being run we should
     * gracefully exit and wait our next go!
     * 
     * Also change auto sync to just create a job, and run a single job Queue!
     */
	public function run()
	{
        if($this->_config()->isLogEnabled()) {
            $this->_config()->dbLog("Cron [Triggered]");
        }

        /**
         * This doesn't exist in 1.3.2!
         */
        $indexProcess = new Mage_Index_Model_Process();
        $indexProcess->setId(self::LOCK_INDEX_ID);
        if ($indexProcess->isLocked()) {
            // Check how old the lock is - unlock after 1hr
            if ($this->_lockIsOld(self::LOCK_INDEX_ID)) {
                $indexProcess->unlock();
            } else {
                $this->_config()->log('MAILUP: cron already running or locked');
                return false;
            }
        }
        $indexProcess->lockAndBlock();

        try {
            require_once dirname(__FILE__) . '/../Helper/Data.php';
            $db_read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $db_write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $syncTableName = Mage::getSingleton('core/resource')->getTableName('mailup/sync');
            $jobsTableName = Mage::getSingleton('core/resource')->getTableName('mailup/job');
            $lastsync = gmdate("Y-m-d H:i:s");
            // reading customers (jobid == 0, their updates)
            $customer_entity_table_name = Mage::getSingleton('core/resource')->getTableName('customer_entity');

            /**
             * Now Handle Jobs we need to Sync, and all customers attached to each job
             */
            foreach(Mage::getModel('mailup/job')->fetchQueuedOrStartedJobsCollection() as $jobModel) {
                /* @var $jobModel MailUp_MailUpSync_Model_Job */
                $job = $jobModel->getData();

                $storeId = isset($job['store_id']) ? $job['store_id'] : NULL;

                // If job is auto-sync and cron is not enabled for the job's site, skip the job
                if ($jobModel->isAutoSync() && ! $this->_config()->isCronExportEnabled($storeId)) {
                    $this->_config()->dbLog("Auto-Task skipped as auto-sync disabled for site", $job["id"], $storeId);
                    continue;
                }

                $stmt = $db_write->query(
                    "UPDATE {$jobsTableName}
                    SET status='started', start_datetime='" . gmdate("Y-m-d H:i:s") . "'
                    WHERE id={$job["id"]}"
                );
                $customers = array();
                $job['mailupNewGroup'] = 0;
                $job['mailupIdList'] = Mage::getStoreConfig('mailup_newsletter/mailup/list', $storeId);
                $job["mailupGroupId"] = $job["mailupgroupid"];
                $job["send_optin_email_to_new_subscribers"] = $job["send_optin"];

                // If group is 0 and there is a default group, set group to this group
                $defaultGroupId = Mage::getStoreConfig('mailup_newsletter/mailup/default_group');
                if ($job["mailupGroupId"] == 0 && $defaultGroupId !== null) {
                    $job["mailupGroupId"] = $defaultGroupId;
                }

                $tmp = Mage::getSingleton('mailup/source_lists');
                $tmp = $tmp->toOptionArray($storeId); // pass store id!
                foreach ($tmp as $t) {
                    if ($t["value"] == $job['mailupIdList']) {
                        $job['mailupListGUID'] = $t["guid"];
                        $job["groups"] = $t["groups"];
                        break;
                    }
                }
                unset($tmp);
                unset($t);
                $stmt = $db_read->query("
                    SELECT ms.*, ce.email
                    FROM {$syncTableName} ms
                    JOIN $customer_entity_table_name ce
                        ON (ms.customer_id = ce.entity_id)
                    WHERE ms.needs_sync=1
                    AND ms.entity='customer'
                    AND job_id={$job["id"]}"
                );
                while ($row = $stmt->fetch()) {
                    $customers[] = $row["customer_id"];
                }
                /**
                 * Send the Data!
                 */
                $returnCode = MailUp_MailUpSync_Helper_Data::generateAndSendCustomers($customers, $job, $storeId);
                /**
                 * Check return OK
                 */
                if($returnCode === 0) {
                    $customerCount = count($customers);
                    $db_write->query("
                        UPDATE {$syncTableName} SET needs_sync=0, last_sync='$lastsync'
                        WHERE job_id = {$job["id"]}
                        AND entity='customer'"
                    );
                    $this->_config()->dbLog("Job Task [update] [Synced] [customer count:{$customerCount}]", $job["id"], $storeId);
                    // finishing the job also
                    $db_write->query("
                        UPDATE {$jobsTableName} SET status='finished', finish_datetime='" . gmdate("Y-m-d H:i:s") . "'
                        WHERE id={$job["id"]}"
                    );
                    $this->_config()->dbLog("Jobs [Update] [Complete] [{$job["id"]}]", $job["id"], $storeId);
                }
                /**
                 * Only successfull if we get 0 back. False is also a fail.
                 */
                else {
                    $stmt = $db_write->query(
                        "UPDATE {$jobsTableName} SET status='queued' WHERE id={$job["id"]}"
                    );
                    if($this->_config()->isLogEnabled()) {
                        $this->_config()->dbLog(sprintf("generateAndSendCustomers [ReturnCode] [ERROR] [%d]", $returnCode), $job["id"], $storeId);
                    }
                }
            }
        } catch (Exception $e) {
            // In case of otherwise uncaught error, unlock and re-throw
            $indexProcess->unlock();
            throw $e;
        }

        $indexProcess->unlock();
 
        if ($this->_config()->isLogEnabled()) {
           $this->_config()->dbLog("Cron [Completed]");
        }
	}

    /**
     * Whether a given file was modified over $hoursOld ago
     *
     * @param string $id
     * @param int $hoursOld
     * @return bool
     */
    protected function _lockIsOld($id, $hoursOld=1)
    {
        $varDir = Mage::getConfig()->getVarDir('locks');
        $filename = $varDir . DS . 'index_process_' . $id . '.lock';
        if (!is_file($filename)) {
            return true;
        }
        if (time()-filemtime($filename) > $hoursOld * 3600) {
            return true;
        }

        // File exists and is less than the specified number of hours old
        return false;
    }
    
    /**
     * Run Auto Sync Jobs
     */
    public function autoSync()
    {
        // Only run Auto Sync Jobs
        $job = Mage::getModel('mailup/job');
        /* @var $job MailUp_MailUpSync_Model_Job */
        
        foreach($job->fetchAutoSyncQueuedJobsCollection() as $job) {
           
        }
    }
    
    /**
     * Run Manual Sync Jobs
     */
    public function manualSync()
    {
        // Only run Auto Sync Jobs
        
        $job = Mage::getModel('mailup/job');
        /* @var $job MailUp_MailUpSync_Model_Job */
        
        foreach($job->fetchManualSyncQueuedJobsCollection() as $job) {
            
        }
    }
    
    /**
     * Start the next job in the Queue!
     */
    public function startNextJob()
    {
        $jobModel = Mage::getModel('mailup/job');
        /* @var $jobModel MailUp_MailUpSync_Model_Job */
        foreach($jobModel->fetchQueuedJobsCollection() as $job) {
            /* @var $job MailUp_MailUpSync_Model_Job */
            
            /**
             * Try and Start it... if it fails, we can try the next one!
             */
        }
    }
    
    /**
     * Add the jobs to the import queue on Mailup.
     */
    public function newImportProcesses()
    {
        
    }
	
    /**
     * handle connection issues
     * 
     * @todo    implement
     */
	public static function resendConnectionErrors()
	{
        // never implemented.
	}
    
    /**
     * @var MailUp_MailUpSync_Model_Config
     */
    protected $_config;
    
    /**
     * Get the config
     * 
     * @reutrn MailUp_MailUpSync_Model_Config
     */
    protected function _config()
    {        
        if(NULL === $this->_config) {
            $this->_config = Mage::getModel('mailup/config');
        }
        
        return $this->_config;
    }
}