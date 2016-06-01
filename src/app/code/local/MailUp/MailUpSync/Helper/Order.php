<?php
/**
 * Order helper methods for MailUp
 */
class MailUp_MailUpSync_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Filter an order collection by status/state depending on MailUp config
     * NOTE that cannot override collection consistently as the class changed name in 1.6
     *
     * @param Varien_Data_Collection_Db $collection
     * @return $this
     */
    public function addStatusFilterToOrders($collection)
    {
        $config = Mage::getModel('mailup/config');

        // Add condition to skip orders that have incorrect statuses
        $allowedStatuses = $config->getQualifyingOrderStatuses();
        // If config options, use the given statuses
        if (count($allowedStatuses) > 0) {
            $collection->addAttributeToFilter('status', $allowedStatuses);
        } else {
            // Else, use complete, closed and processing state only
            $allowedStates = $config->getDefaultQualifyingStates();
            $collection->addAttributeToFilter('state', $allowedStates);
        }

        return $this;
    }

}
