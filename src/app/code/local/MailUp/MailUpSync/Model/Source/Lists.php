<?php
/**
 * Lists.php
 */
require_once dirname(__DIR__) . "/MailUpWsImport.php";
require_once dirname(__DIR__) . "/Wssend.php";

class MailUp_MailUpSync_Model_Source_Lists
{
    /**
     * @var array
     */
    protected $_cache = array();
    
    /**
     * Get as options
     * 
     * array(
     *     array(
     *          'value'     => (string)$list['idList'], 
                'label'     => (string)$list['listName'], 
                'guid'      =>(string)$list['listGUID'], 
                "groups"    => array(
     *           ...
     *          )
     *     )
     * )
     * 
     * @return  array
     */
    public function toOptionArray($storeId = NULL) 
    {
        $websiteCode = Mage::app()->getRequest()->getParam('website');
        $storeCode = Mage::app()->getRequest()->getParam('store');
        
        if(isset($storeId) && $storeId != FALSE) {
            $storeId = $storeId; // ?
        }
        elseif($storeCode) {
            $storeId = Mage::app()->getStore($storeCode)->getId();
            $cacheId = 'mailup_fields_array_store_'.$storeId;
        }
        elseif($websiteCode) {
            $storeId = Mage::app()
                ->getWebsite($websiteCode)
                ->getDefaultGroup()
                ->getDefaultStoreId()
            ;
            $cacheId = 'mailup_fields_array_store_'.$storeId;
        }
        else {
            $storeId = NULL;
            $cacheId = 'mailup_fields_array';
            //$storeId = Mage::app()->getDefaultStoreView()->getStoreId();
        }

        // Create select
        $selectLists = array();

        if (Mage::getStoreConfig('mailup_newsletter/mailup/url_console', $storeId) 
            && Mage::getStoreConfig('mailup_newsletter/mailup/username_ws', $storeId) 
            && Mage::getStoreConfig('mailup_newsletter/mailup/password_ws', $storeId)) {
            
            $wsSend = new MailUpWsSend($storeId);
            $accessKey = $wsSend->loginFromId();
            
            if ($accessKey !== false) {
	            require_once dirname(__DIR__) . "/MailUpWsImport.php";
                $wsImport = new MailUpWsImport($storeId);
                
                $xmlString = $wsImport->GetNlList();

                $selectLists[0] = array('value' => 0, 'label'=>'-- Select a list (if any) --');

                if($xmlString) {
                    $xmlString = html_entity_decode($xmlString);
                    $startLists = strpos($xmlString, '<Lists>');
                    if ($startLists === false) {
                        if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $storeId))
                            Mage::log('MailUpWsImport failed even though login succeeded');
                        return $selectLists;
                    }
                    $endPos = strpos($xmlString, '</Lists>');
                    $endLists = $endPos + strlen('</Lists>') - $startLists;
                    $xmlLists = substr($xmlString, $startLists, $endLists);
                    $xmlLists = str_replace("&", "&amp;", $xmlLists);
                    $xml = simplexml_load_string($xmlLists);
                    $count = 1;
                    foreach ($xml->List as $list) {
						$groups = array();
						foreach ($list->Groups->Group as $tmp) {
							$groups[(string)$tmp["idGroup"]] = (string)$tmp["groupName"];
						}
                        $selectLists[$count] = array(
                            'value'     => (string)$list['idList'], 
                            'label'     => (string)$list['listName'], 
                            'guid'      =>(string)$list['listGUID'], 
                            "groups"    => $groups
                        );
                        $count++;
                    }
                }
            } else {
                if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $storeId)) Mage::log('LoginFromId failed');
                $selectLists[0] = array('value' => 0, 'label'=>$GLOBALS["__sl_mailup_login_error"]);
            }
        }

        return $selectLists;
    }

    /**
     * Get an array of list data, and its groups.
     *
     * @param $listId
     * @param $storeId
     * @return bool|array
     */
    public function getListDataArray($listId, $storeId) 
    {
        $listData = $this->getDataArray($storeId);
        if (isset($listData[$listId])) {
           return $listData[$listId];
        }

        // If list not found, return false
        if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $storeId)) {
            Mage::log('Invalid List ID: ' . $listId);
        }

        return false;
    }
    
    /**
     * Get an array of all lists, and their groups!
     *
     * @param string $storeId
     * @return  array
     */
    public function getDataArray($storeId) 
    {
        $selectLists = array();

        // If cache is set, use that
        if (isset($this->_cache[$storeId])) {
            return $this->_cache[$storeId];
        }

        // If login details not set, return empty list
        if (!$this->_config()->getUrlConsole($storeId) ||
                !$this->_config()->getUsername($storeId) ||
                !$this->_config()->getPassword($storeId)) {
            if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $storeId))
                Mage::log('Login details not complete - cannot retrieve lists');
            return $selectLists;
        }

        // Attempt login (return empty if fails)
        $wsSend = new MailUpWsSend($storeId);
        $accessKey = $wsSend->loginFromId();
        if ($accessKey === false) {
            if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $storeId))
                Mage::log('Login failed - cannot retrieve lists');
            return $selectLists;
        }

        // Attempt to make call to get lists from API
        require_once dirname(__DIR__) . "/MailUpWsImport.php";
        $wsImport = new MailUpWsImport($storeId);
        $xmlString = $wsImport->GetNlList();
        if (!$xmlString) {
            if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $storeId))
                Mage::log('MailUpWsImport got empty response when fetching lists even though login succeeded');
            return $selectLists;
        }

        // Try to decode response. If <Lists> is not in selection, then return
        $xmlString = html_entity_decode($xmlString);
        $startLists = strpos($xmlString, '<Lists>');
        // On XML error, $startLists will fail
        if ($startLists === false) {
            if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $storeId))
                Mage::log('MailUpWsImport got error response when fetching lists');
            return $selectLists;
        }

        // Extract lists and their groups from <List> section of response
        $endPos = strpos($xmlString, '</Lists>');
        $endLists = $endPos + strlen('</Lists>') - $startLists;
        $xmlLists = substr($xmlString, $startLists, $endLists);
        $xmlLists = str_replace("&", "&amp;", $xmlLists);
        $xml = simplexml_load_string($xmlLists);
        foreach ($xml->List as $list) {
            $groups = array();
            foreach ($list->Groups->Group as $tmp) {
                $groups[(string)$tmp["idGroup"]] = (string)$tmp["groupName"];
            }
            $selectLists[(string)$list['idList']] = array(
                'idList' => (string)$list['idList'],
                'listName' => (string)$list['listName'],
                'listGUID' => (string)$list['listGUID'],
                "groups" => $groups
            );
        }

        // Cache results as this is a success
        $this->_cache[$storeId] = $selectLists;

        return $selectLists;
    }
    
    /**
     * Get a List Guid
     * 
     * @param   int
     * @param   int
     * @return  string|false
     */
    public function getListGuid($listId, $storeId)
    {
        $listData = $this->getListDataArray($listId, $storeId);

        if ($listData === false || !isset($listData['listGUID'])) {
            return false;
        }
        
        return $listData['listGUID'];
    }
    
    /**
     * Get the groups for a given list.
     * 
     * @param   int|false
     */
    public function getListGroups($listId, $storeId)
    {
        $listData = $this->getListDataArray($listId, $storeId);

        if ($listData === false || !isset($listData['groups'])) {
            return false;
        }
        
        return $listData['groups'];
    }
    
    /**
     * @var MailUp_MailUpSync_Model_Config
     */
    protected $_config;
    
    /**
     * Get the config
     * 
     * @reutrn MailUp_MailUpSync_Model_Config
     */
    protected function _config()
    {        
        if(NULL === $this->_config) {
            $this->_config = Mage::getModel('mailup/config');
        }
        
        return $this->_config;
    }
}
