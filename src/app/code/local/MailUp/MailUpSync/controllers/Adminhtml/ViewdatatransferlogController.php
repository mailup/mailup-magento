<?php

require_once dirname(__FILE__) . "/../../Model/MailUpWsImport.php";
require_once dirname(__FILE__) . "/../../Model/Wssend.php";
class MailUp_MailUpSync_Adminhtml_ViewdatatransferlogController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}

	public function searchAction() {
		$this->loadLayout()->renderLayout();
	}
}