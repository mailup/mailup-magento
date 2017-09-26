<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
 *
 * Test connection button on system configuration section
 */
class MailUp_MailUpSync_Block_Adminhtml_System_Config_Form_Testbutton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml()
    {
        return $this->_toHtml();
    }

    /**
     * Generate button html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $button = $this->getLayout()
                       ->createBlock('adminhtml/widget_button')
                       ->setData(
                           array(
                               'id'    => 'mailup_selftest_button',
                               'label' => $this->helper('adminhtml')->__('Test Connection')
                           )
                       );

        return $button->toHtml();
    }

}
