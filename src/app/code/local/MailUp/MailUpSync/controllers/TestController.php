<?php
/**
 * TestController.php
 */
class MailUp_MailUpSync_TestController extends Mage_Core_Controller_Front_Action
{
    /**
     * Predispatch: should set layout area
     * 
     * This is causing an issue and making 404s, something to do with the install
     * being messed up and the code inside parent method doing something strange!
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        $config = Mage::getModel('mailup/config');
        /* @var $config MailUp_MailUpSync_Model_Config */

        if( ! $config->isTestMode()) {
            die('Access Denied.');
        }

        return parent::preDispatch();
    }
    
    /**
     * Default Action
     */
    public function indexAction()
    { 
        //$this->loadLayout();
        //$this->renderLayout();
        //var_dump(Mage::helper('mailup')->getAllCustomerAttributes());

        die('done');
    }
    
    public function SubscriberAction()
    {
        $helper = Mage::helper('mailup');
        
        var_dump($helper->isSubscriber(27, 1));
        var_dump($helper->isSubscriber(29, 99));
    }

    /**
     * Start the process, if we've already run NewImportProcess
     * and we have a process ID we can Start it.
     */
    public function startProcessAction()
    {
        require_once dirname(__FILE__) . "/../Model/MailUpWsImport.php";
        require_once dirname(__FILE__) . "/../Model/Wssend.php";
        
        $wsSend = new MailUpWsSend($job->getStoreId());
		$wsImport = new MailUpWsImport($job->getStoreId());
		$accessKey = $wsSend->loginFromId();
        
        /**
         * We need the ListID and ListGuid, which we will NOT
         * have for sync items, as we've not saved the process id
         * or anything else!!
         */
        
        //StartProcess(int idList, int listGUID, int idProcess)
        
        /*$return = $wsImport->startProcess(array(
            'idList'    => $job->getListid(),
            'listGUID'  => $job->getListGuid(),
            'idProcess' => $job->getProcessId()
        ));*/
    }
    
    /**
     * Test the models..
     */
    public function modelsAction()
    {
        $jobTask = Mage::getModel('mailup/sync');
        /* @var $jobTask MailUp_MailUpSync_Model_Sync */
        
        $job = Mage::getModel('mailup/job');
        /* @var $job MailUp_MailUpSync_Model_Job */
        
        $tasks = $jobTask->getSyncItemsCollection();
        foreach($tasks as $task) {
            var_dump($task->getData());
        }
        
        foreach($jobTask->fetchByJobId(0) as $task) {
            var_dump($task->getData());
        }
        
        var_dump($jobTask->getJob());
    }
    
    /**
     * Show Current Processes
     */
    public function processesAction()
    {
        require_once dirname(dirname(__FILE__)) . "/Model/MailUpWsImport.php";
        require_once dirname(dirname(__FILE__)) . "/Model/Wssend.php";
        $wsimport = new MailUpWsImport();
        
        var_dump($wsimport->getProcessDetail(array(
            
        )));
    }
}
