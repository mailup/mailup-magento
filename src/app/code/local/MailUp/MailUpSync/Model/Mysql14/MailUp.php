<?php

class MailUp_MailUpSync_Model_Mysql4_MailUp extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('mailup/mailup', 'mailup_id');
    }
}