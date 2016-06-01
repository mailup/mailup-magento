<?php
class MailUp_MailUpSync_Adminhtml_MailupbackendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Default Action
     */
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("MailUp Jobs"));
	   $this->renderLayout();
    }
    
    /**
     * Run The Job
     */
    public function runjobAction()
    {
        /** @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $id = $this->getRequest()->getParam('id');
        
        if( ! $id) {
            $session->addError(
                Mage::helper('mailup')->__('Invalid Entity')
            );
        }
        
        $entity = Mage::getModel('mailup/job')->load($id);
        if($entity) {   
            Mage::helper('mailup')->runJob($entity->getId());
        }
 
        $session->addSuccess(
            Mage::helper('mailup')->__("Run Job [{$entity->getId()}]")
        );

        $this->_redirect('*/*/index');
    }
    
    /**
     * Delete a job
     */
    public function deleteAction()
    {
        /** @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $id = $this->getRequest()->getParam('id');
        
        if( ! $id) {
            $session->addError(
                Mage::helper('mailup')->__('Invalid Entity')
            );
        }
        
        $entity = Mage::getModel('mailup/job')->load($id);
        $entity->delete();
 
        $session->addSuccess(
            Mage::helper('mailup')->__("Job [{$entity->getId()}] [Deleted]")
        );

        $this->_redirect('*/*/index');
    }
}
