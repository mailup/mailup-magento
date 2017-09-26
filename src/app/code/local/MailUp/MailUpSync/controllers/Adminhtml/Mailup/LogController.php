<?php

/**
 * LogController.php
 */
class MailUp_MailUpSync_Adminhtml_Mailup_LogController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Default Action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Log Queue"));
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('newsletter/mailup/mailup_log');
    }
}
