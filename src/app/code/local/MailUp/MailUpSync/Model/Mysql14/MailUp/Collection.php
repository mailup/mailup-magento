<?php

class MailUp_MailUpSync_Model_Mysql4_MailUp_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        //parent::__construct();
        $this->_init('mailup/mailup');
    }
}