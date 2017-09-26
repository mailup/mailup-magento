<?php

/**
 * Filters.php
 *
 * Adminhtml block for the filters section
 */
class MailUp_MailUpSync_Block_Filters extends Mage_Core_Block_Template
{
    /**
     * Get an array of all stores
     *
     * @return  array
     */
    protected function _getStoresArray()
    {
        $config = Mage::getModel('mailup/config');

        return $config->getStoreArray();
    }
}
