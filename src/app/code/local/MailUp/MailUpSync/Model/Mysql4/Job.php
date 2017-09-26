<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
 */
class MailUp_MailUpSync_Model_Mysql4_Job extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("mailup/job", "id");
    }
}
