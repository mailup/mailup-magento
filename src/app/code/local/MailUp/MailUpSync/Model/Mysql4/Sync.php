<?php
/**
 * Job.php
 */
class MailUp_MailUpSync_Model_Mysql4_Sync extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("mailup/sync", "id");
    }
    
    /**
     * Get the ID from the unique values
     * 
     * @param   int
     * @param   int
     * @param   int
     */
    public function getIdByUniqueKey($customerId, $jobId, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('customer_id = :customer_id AND job_id = :job_id AND store_id = :store_id')
        ;

        $bind = array(
            ':customer_id'  => $customerId,
            ':job_id'       => $jobId,
            ':store_id'     => $storeId
        );

        return $adapter->fetchOne($select, $bind);
    }
    
    
    /**
     * Load by unique Key
     */
    public function loadByUniqueKey()
    {
        //(`customer_id`,`entity`,`job_id`, `store_id`)
        
        
    }
    
//    /**
//     * Get product identifier by sku
//     *
//     * @param string $sku
//     * @return int|false
//     */
//    public function getIdBySku($sku)
//    {
//        $adapter = $this->_getReadAdapter();
//
//        $select = $adapter->select()
//            ->from('phpsolut_import')
//            ->where('sku = :sku');
//
//        $bind = array(':sku' => (string)$sku);
//
//        return $adapter->fetchOne($select, $bind);
//    }
    
    //return $this->_getReadAdapter()->fetchOne(
    //         'select connect_id from '.$this->getMainTable().' where sku=?',
   //          $sku
    //     );
}