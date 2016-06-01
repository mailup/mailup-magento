<?php
/**
 * Filters.php
 * 
 * Adminhtml block for the filters section
 */
class MailUp_MailUpSync_Block_Filters extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
	    return parent::_toHtml();
    }
    
    /**
     * Get an array of all stores
     * 
     * @return  array
     */
    protected function _getStoresArray()
    {
        $config = Mage::getModel('mailup/config');
        /* @var $config MailUp_MailUpSync_Model_Config */
        return $config->getStoreArray();
    }
}
