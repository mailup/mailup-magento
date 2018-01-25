<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
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
            ->where('customer_id = :customer_id AND job_id = :job_id AND store_id = :store_id');

        $bind = array(
            ':customer_id'  => $customerId,
            ':job_id'       => $jobId,
            ':store_id'     => $storeId
        );

        return $adapter->fetchOne($select, $bind);
    }
}
