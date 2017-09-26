<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
 */
class MailUp_MailUpSync_Block_Adminhtml_System_Config_Form_Field_Timezone
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml()
    {
        $timezoneList = Mage::app()->getLocale()->getTranslationList('windowstotimezone');

        // Select only current timezone
        $timezone = date_default_timezone_get();

        $timezoneStr = $timezone;
        if (isset($timezoneList[$timezone])) {
            $timezoneStr = "{$timezoneList[$timezone]} ({$timezone})";
        }

        return "<span>{$timezoneStr}</span>";
    }

}
