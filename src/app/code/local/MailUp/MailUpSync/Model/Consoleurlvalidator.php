<?php

class MailUp_MailUpSync_Model_Consoleurlvalidator  extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		$value = $this->getValue();
		if (strlen($value) == 0) {
			Mage::throwException(Mage::helper("mailup")->__("Please fill the admin console URL"));
		}

		$validator = new Zend_Validate_Hostname();
		if (!$validator->isValid($value)) {
			Mage::throwException(Mage::helper("mailup")->__("Admin console URL is not in the right format"));
		}

		return parent::save();
	}
}