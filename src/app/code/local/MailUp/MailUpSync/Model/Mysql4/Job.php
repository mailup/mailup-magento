<?php
/**
 * Job.php
 */
class MailUp_MailUpSync_Model_Mysql4_Job extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("mailup/job", "id");
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
}