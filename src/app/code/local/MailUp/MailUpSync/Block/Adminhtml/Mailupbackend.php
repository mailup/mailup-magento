<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
 */
class MailUp_MailUpSync_Block_Adminhtml_Mailupbackend extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_mailup';
        $this->_blockGroup = 'mailup';

        $this->_headerText = Mage::helper('mailup')->__('MailUp Scheduled Tasks');

        parent::__construct();

        $this->_removeButton('add');
    }

}
