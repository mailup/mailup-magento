<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
 */

/**
 * Sync.php
 *
 * @method  int     getStoreId()
 * @method  int     getCustomerId()
 * @method  int     getJobId()
 */
class MailUp_MailUpSync_Model_Sync extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init("mailup/sync");
    }

    /**
     * Get the ID from the unique values
     *
     * @param   int
     * @param   int
     * @param   int
     *
     * @return  int|FALSE
     */
    public function getIdByUniqueKey($customerId, $jobId, $storeId)
    {
        return $this->_getResource()->getIdByUniqueKey(
            $customerId,
            $jobId,
            $storeId
        );
    }

    /**
     * Load by unique Key
     */
    public function loadByUniqueKey()
    {
        return $this->_getResource()->loadByUniqueKey();
    }

    /**
     * Mark as Synced
     */
    public function setAsSynced()
    {
        $this->setNeedsSync(0);

        return $this;
    }

    /**
     * Do we need Synced?
     *
     * @return  bool
     */
    public function isNeedningSynced()
    {
        return $this->getNeedsSync() == 1;
    }

    /**
     * Get a collection of items which need Synced
     *
     * @return  MailUp_MailUpSync_Model_Mysql4_Sync_Collection
     */
    public function getSyncItemsCollection()
    {
        $customerEntityTable = Mage::getSingleton('core/resource')->getTableName('customer_entity');
        $collection          = $this->getCollection();

        $collection
            ->addFieldToSelect('*')
            ->addFieldToFilter('job_id', array('eq' => 0))
            ->addFieldToFilter('needs_sync', array('eq' => 1))
            ->getSelect()->join(
                $customerEntityTable,
                "main_table.customer_id = {$customerEntityTable}.entity_id",
                "email"
            );

        return $collection;
    }

    /**
     * Get Sync entries for a particular job.
     *
     * @param   $jobId
     *
     * @return  MailUp_MailUpSync_Model_Mysql4_Sync_Collection
     */
    public function fetchByJobId($jobId)
    {
        return $this->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('job_id', array('eq' => $jobId));
    }

    /**
     * Get the job model.
     *
     * @return  MailUp_MailUpSync_Model_Job
     */
    public function getJob()
    {
        return Mage::getModel('mailup/job')->load($this->getJobId());
    }
}
