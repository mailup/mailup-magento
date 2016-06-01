<?php
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
     * @return  int|FALSE
     */
    public function getIdByUniqueKey($customerId, $jobId, $storeId)
    {
        return $this->_getResource()->getIdByUniqueKey(
            $customerId,
            $jobId,
            $storeId
        );
        
        /*return $this->_getResource()->getIdByUniqueKey(
            $this->getCustomerId(), 
            $this->getJobId(), 
            $this->getStoreId()
        );*/
    }
    
    /**
     * Load by unique Key
     */
    public function loadByUniqueKey()
    {
        //(`customer_id`,`entity`,`job_id`, `store_id`)
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
//        SELECT ms.*, ce.email FROM {$syncTableName} ms 
//                JOIN $customer_entity_table_name ce 
//                    ON (ms.customer_id = ce.entity_id) 
//                WHERE 
//                ms.needs_sync=1 
//                AND ms.entity='customer' 
//                AND job_id=0"
        
        $customerEntityTable = Mage::getSingleton('core/resource')->getTableName('customer_entity');
        //$customerEntityTable = $this->getTable('customer/entity');
        $collection = $this->getCollection();
        /* @var $collection MailUp_MailUpSync_Model_Mysql4_Sync_Collection */
        
        $collection
            ->addFieldToSelect('*')
            ->addFieldToFilter('job_id', array('eq' => 0))
            ->addFieldToFilter('needs_sync', array('eq' => 1))
            ->getSelect()->join($customerEntityTable, "main_table.customer_id = {$customerEntityTable}.entity_id", "email")
        ;
            
        return $collection;
    }
    
    /**
     * Get Sync entries for a particular job.
     * 
     * @return  MailUp_MailUpSync_Model_Mysql4_Sync_Collection
     */
    public function fetchByJobId($jobId)
    {
        //return $this->_getResource()->fetchByJobId($jobId);
        return $this->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('job_id', array('eq' => $jobId))
        ;
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