<?php
/**
 * @deprectiated
 */
require_once dirname(__FILE__) . "/../../Model/MailUpWsImport.php";
require_once dirname(__FILE__) . "/../../Model/Wssend.php";
class MailUp_MailUpSync_Adminhtml_FieldsMappingController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction() {
        $this->loadLayout()->renderLayout();
    }

    public function saveAction() {
        try {
            $post = $this->getRequest()->getPost();
			unset($post["form_key"]);
	        require_once dirname(__FILE__) . "/../../Model/MailUpWsImport.php";
            $wsImport = new MailUpWsImport();
            $wsImport->saveFieldMapping($post);
        } catch (Exception $e) {
            $errorMessage = $this->__('Error: unable to save current filter');
            Mage::getSingleton('adminhtml/session')->addError($errorMessage);
        }

	    $observer = Mage::getModel("mailup/observer");
	    $observer->configCheck();

        $this->_redirect('*/*');
    }
}