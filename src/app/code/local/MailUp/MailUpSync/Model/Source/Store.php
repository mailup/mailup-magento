<?php
/**
 * Config.php
 * 
 * Central config model
 */
class MailUp_MailUpSync_Model_Source_Store
{
    /**
     * Options getter
     * 
     * This would be used in Admin config.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $return = array();
        
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    /* @var $store Mage_Core_Model_Store */
                    $return[] = array(
                        'value' => $store->getId(),
                        'label' => $store->getName()
                    );
                }
            }
        }
        
        return $return;
    }
    
    /**
     * Get options as we'd use in a select box.
     * 
     * @return  array
     */
    public function getSelectOptions()
    {
        $return = array();
        
        $return[0] = 'Default'; // include default site / admin
        
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    /* @var $store Mage_Core_Model_Store */
                    $return[$store->getId()] = $store->getName();
                }
            }
        }
        
        return $return;
    }
}