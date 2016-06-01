<?php

class MailUp_MailUpSync_Model_Adminhtml_System_Source_Cron_Hours
{
    /**
     * Fetch options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $hours = array();
        for ($i = 1; $i <= 24; $i++) {
            $hours[] = array('label' => $i, 'value' => $i);
        }
        return $hours;
    }
}
