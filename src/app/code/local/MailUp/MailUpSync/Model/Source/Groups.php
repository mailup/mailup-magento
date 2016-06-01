<?php
/**
 * Groups
 */

/**
 * Source for groups per list
 */
class MailUp_MailUpSync_Model_Source_Groups
{
    /**
     * @var array
     */
    protected $_cache = array();
    
    /**
     * Get groups as array of options
     *
     * @param int|null $storeId
     * @param int|null $listId
     * @return  array
     */
    public function toOptionArray($storeId = null, $listId = null)
    {
        $websiteCode = Mage::app()->getRequest()->getParam('website');
        $storeCode = Mage::app()->getRequest()->getParam('store');

        // Get store
        if (isset($storeId) && $storeId == false) {
            $storeId = Mage::app()->getStore($storeCode)->getId();
        } elseif ($websiteCode) {
            $storeId = Mage::app()
                ->getWebsite($websiteCode)
                ->getDefaultGroup()
                ->getDefaultStoreId();
        } else {
            $storeId = NULL;
        }

        // Get List ID
        if ($listId === null) {
            $listId = Mage::getStoreConfig('mailup_newsletter/mailup/list', $storeId);
        }

        // Create select
        $selectLists = array();
        $selectLists[0] = array('value' => '', 'label'=>'-- No groups available --');

        // Only attempt to get groups if there is a list specified
        if (Mage::getStoreConfig('mailup_newsletter/mailup/list', $storeId)) {
            // Get groups from list source (which gets both)
            $sourcelist = Mage::getModel('mailup/source_lists');
            $groups = $sourcelist->getListGroups($listId, $storeId);

            // Put groups into option array
            if ($groups !== false) {
                $selectLists[0] = array('value' => '0', 'label'=>'-- Select a group (if any) --');
                foreach ($groups as $index => $groupName) {
                    $selectLists[] = array(
                        'value' => $index,
                        'label' => $groupName
                    );
                }
            }
        }

        return $selectLists;
    }

}
