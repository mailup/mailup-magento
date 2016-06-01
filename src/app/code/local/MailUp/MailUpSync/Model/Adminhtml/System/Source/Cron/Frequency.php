<?php

class MailUp_MailUpSync_Model_Adminhtml_System_Source_Cron_Frequency
{
    const HOURLY = 0;
    const EVERY_2_HOURS = 1;
    const EVERY_6_HOURS = 2;
    const EVERY_12_HOURS = 3;
	const DAILY = 4;

    /**
     * Get the frequency in hours given a frequency index such as
     * MailUp_MailUpSync_Model_Adminhtml_System_Source_Cron_Frequency::EVERY_2_HOURS
     *
     * @param int $frequencyIndex
     * @return null|int
     */
    public static function getPeriod($frequencyIndex)
    {
        static $periodMapping = array(
            self::HOURLY => 1,
            self::EVERY_2_HOURS => 2,
            self::EVERY_6_HOURS => 6,
            self::EVERY_12_HOURS => 12,
            self::DAILY => 24
        );

        // If no valid entry, return null
        if (!isset($periodMapping[$frequencyIndex])) {
            return null;
        }

        return $periodMapping[$frequencyIndex];
    }

    /**
     * Fetch options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'Hourly',
                'value' => self::HOURLY),
            array(
                'label' => 'Every 2 Hours',
                'value' => self::EVERY_2_HOURS),
            array(
                'label' => 'Every 6 hours',
                'value' => self::EVERY_6_HOURS),
            array(
                'label' => 'Every 12 hours',
                'value' => self::EVERY_12_HOURS),
            array(
                'label' => 'Daily',
                'value' => self::DAILY),

        );
    }
}
