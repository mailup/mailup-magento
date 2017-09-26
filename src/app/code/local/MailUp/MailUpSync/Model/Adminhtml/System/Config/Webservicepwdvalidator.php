<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
 */
class MailUp_MailUpSync_Model_Adminhtml_System_Config_Webservicepwdvalidator extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $value = $this->getValue();
        if (strlen($value) == 0) {
            Mage::throwException(Mage::helper("mailup")->__("Please fill the web service password"));
        }

        return parent::save();
    }
}
