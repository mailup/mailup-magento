<?php

class MailUp_MailUpSync_Model_Webserviceusernamevalidator  extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		$value = $this->getValue();
		if (strlen($value) == 0) {
			Mage::throwException(Mage::helper("mailup")->__("Please fill the web service username"));
		}

		if (!preg_match("/a[0-9]+/", $value)) {
			Mage::throwException(Mage::helper("mailup")->__("Web service username is not in the right format"));
		}

		return parent::save();
	}
}