<?php
/**
 * IndexController.php
 */
class MailUp_MailUpSync_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Predispatch: should set layout area
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        $config = Mage::getModel('mailup/config');
        /* @var $config MailUp_MailUpSync_Model_Config */
        
        //if( ! $config->isTestMode()) {
        //    die('Access Denied.');
        //}
        
        parent::preDispatch();
        
        return $this;
    }
    
    /**
     * Default Action
     */
    public function indexAction()
    {
        
    }
    
    /**
     * Clean the Resource Table.
     */
    public function cleanAction()
    {
        return;
        
        Mage::helper('mailup')->cleanResourceTable();
    }
    
    public function showAction()
    {
        return;
        
        Mage::helper('mailup')->showResourceTable();
    }
}
