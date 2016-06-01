<?php
/**
 * MailUpWsImport.php
 */
class MailUpWsImport
{
    /**
     * NewImportProcess Return Codes
     */
    const ERROR_UNRECOGNISED = -400;
    const ERROR_XML_EMPTY = 401;
    const ERROR_XML_TO_CSV_FAILED = -402;
    const ERROR_NEW_IMPORT_PROCESS_FAILED = -403;
    const ERROR_CONFIRMATION_EMAIL = -410;
    /**
     * StartImportProcesses Return Codes
     */
    const ERROR_LISTID_LISTGUID_MISMATCH = -450;
    const ERROR_UNRECOGNISED_600 = -600;
    const ERROR_IMPORT_PROCESS_RUNNING_FOR_LIST = -601;
    const ERROR_IMPORT_PROCESS_RUNNING_FOR_DIFF_LIST = -602;
    const ERROR_CHECKING_PROCESS_STATUS = -603;
    const ERROR_STARTING_PROCESS_JOB = -604;
    
    const STARTIMPORTPROCESSES_SUCCESS = 0;
    
    /*protected $_messages = array(
        'ERROR_UNRECOGNISED' => -400,
        'ERROR_XML_EMPTY' => 401,
        'ERROR_XML_TO_CSV_FAILED' => -402,
        'ERROR_NEW_IMPORT_PROCESS_FAILED' => -403,
        'ERROR_CONFIRMATION_EMAIL' => -410,
        'ERROR_LISTID_LISTGUID_MISMATCH' => -450,
        'ERROR_UNRECOGNISED_600' => -600,
        'ERROR_IMPORT_PROCESS_RUNNING_FOR_DIFF_LIST' => -602,
        'ERROR_CHECKING_PROCESS_STATUS' => -603,
        'ERROR_STARTING_PROCESS_JOB' => -604
    );*/
    
    /**
     * @var string
     */
	protected $ns = "http://ws.mailupnet.it/";
    /**
     * @var string
     */
	protected $rCode;
    /**
     * @var SoapClient
     */
	private $soapClient;
    /**
     * @var string
     */
	private $xmlResponse;
    /**
     * @var DomDocument
     */
	protected $domResult;
    /**
     * @var MailUp_MailUpSync_Model_Config 
     */
    protected $_config;
    /**
     * @var int
     */
    protected $storeId;

    /**
     * Constructor
     */
	function __construct($storeId = NULL) 
    {
        $this->setStoreId($storeId);
        
        $this->_config = $config = Mage::getModel('mailup/config');
        /* @var $config MailUp_MailUpSync_Model_Config */
        
		$urlConsole = Mage::getStoreConfig('mailup_newsletter/mailup/url_console', $this->storeId);
		$WSDLUrl = 'http://'. $urlConsole .'/services/WSMailUpImport.asmx?WSDL';
		$user = Mage::getStoreConfig('mailup_newsletter/mailup/username_ws', $this->storeId);
		$password = Mage::getStoreConfig('mailup_newsletter/mailup/password_ws', $this->storeId);
		$headers = array(
            'User'      => $user, 
            'Password'  => $password
        );
		$this->header = new SOAPHeader($this->ns, 'Authentication', $headers);

        if ($this->_config()->isLogEnabled($this->storeId)) {
            Mage::log("Connecting to {$urlConsole} as {$user}");
        }
        
		try {
			$this->soapClient = new SoapClient($WSDLUrl, array('trace' => 1, 'exceptions' => 1, 'connection_timeout' => 10));
			$this->soapClient->__setSoapHeaders($this->header);
		} 
        catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper("mailup")->__("Unable to connect to MailUp console"));
		}
	}
    
    /**
     * Set the store ID
     * 
     * @param int
     */
    public function setStoreId($id)
    {
        $this->storeId = $id;
        
        return $this;
    }
    
    /**
     * Create a New Group
     * 
     * @todo    CHECK THE API - might have been updated??
     * The API states the signature of this method is:
     * 
     *      CreateGroup(int idList, int listGUID, string newGroupName)
     * 
     * 
     * @param type $newGroup
     * @return boolean
     */
	public function creaGruppo($newGroup)
    {
		if ( ! is_object($this->soapClient)) { 
            return false;
        }
		try {
			$this->soapClient->CreateGroup($newGroup);
			$this->printLastRequest();
			$this->printLastResponse();
			$returnCode = $this->readReturnCode('CreateGroup', 'ReturnCode');
            if ($this->_config()->isLogEnabled($this->storeId)) { 
                $this->_config()->dbLog(sprintf(
                    "Mailup: Create a new Group [%s] [List:%s] [%s]",
                    $newGroup['newGroupName'],
                    $newGroup['listGUID'],
                    $returnCode
                ));
            }
            return $returnCode;
		} 
        catch (SoapFault $soapFault) {
			Mage::log('SOAP error', 0);
			Mage::log($soapFault, 0);
		}
	}

    /**
     * GetNlList
     * 
     * KNOWN RESTRICTION
     * Characters & and " are not escaped in returned response, so please avoid these 
     * characters in names of lists and groups otherwise you will experience some problems due to an invalid returned XML
     * 
     * @todo    parse the XML response correctly and return something nice.
     * @return  string
     */
	public function GetNlList() 
    {
		if ( ! is_object($this->soapClient)) {
            return false;
        }
		try {
			$this->soapClient->GetNlLists();
			$this->printLastRequest();
			$this->printLastResponse();
			$result = $this->soapClient->__getLastResponse();
			if ($this->_config()->isLogEnabled($this->storeId)) { 
               $this->_config()->log($result, 0);
            }
			return $result;
		} 
        catch (SoapFault $soapFault) {
			Mage::log('SOAP error', 0);
			Mage::log($soapFault, 0);
		}
	}

    /**
     * newImportProcess
     * 
     * @see     http://help.mailup.com/display/mailupapi/WSMailUpImport.NewImportProcess
     * @param   type $importProcessData
     * @return  int
     */
	public function newImportProcess($importProcessData) 
    {
		if ( ! is_object($this->soapClient)) { 
            return false;
        }
		try {
			$this->soapClient->NewImportProcess($importProcessData);
			$this->printLastResponse();
            /**
             * This isn't correct.
             * 
             * There's only a NewImportPrcoess return code if it's successful.
             * 
             * If not we've got to look for the other format return code..
             */
			$returncode = $this->readReturnCode('NewImportProcess', 'ReturnCode');
			if ($this->_config()->isLogEnabled($this->storeId)) {
                $this->_config()->dbLog("newImportProcess [ReturnCode] [{$returncode}]", 0);
            }
			return $returncode;
		} 
        catch (SoapFault $soapFault) {
			Mage::log('SOAP error', 0);
			Mage::log($soapFault, 0);
			return false;
		}
	}

    /**
     * Start Process
     * 
     * StartProcess(int idList, int listGUID, int idProcess)
     * 
     * @see     http://help.mailup.com/display/mailupapi/WSMailUpImport.StartProcess
     * @param   type $processData
     * @return  boolean
     */
	public function startProcess($processData) 
    {
		if ( ! is_object($this->soapClient)) {
            return false;
        }
		try {
			$this->soapClient->StartProcess($processData);
			$this->printLastResponse();
            $returncode = $this->readReturnCode('mailupMessage', 'ReturnCode');
			if ($this->_config()->isLogEnabled($this->storeId)) {
                $this->_config()->log("mailup: startProcess");
            }
            
			return $returncode;
		} 
        catch (SoapFault $soapFault) {
			Mage::log('SOAP error', 0);
			Mage::log($soapFault, 0);
			return FALSE;
		}
	}

    /**
     * Process Detail
     * 
     * GetProcessDetails(int idList, int listGUID, int idProcess)
     * 
     * @param   array $processData
     * @return  boolean
     */
	public function getProcessDetail($processData) 
    {
		if ( ! is_object($this->soapClient)) { 
            return false;
        }
		try {
			if ($this->_config()->isLogEnabled($this->storeId)) {
                //GetProcessDetails(int idList, int listGUID, int idProcess)
                $res = $this->soapClient->GetProcessDetails($processData);
                $this->_config()->log($res, 0);
                return $res;
            }
		} 
        catch (SoapFault $soapFault) {
			Mage::log('SOAP error', 0);
			Mage::log($soapFault, 0);
		}        
	}

    /**
     * startImportProcesses
     * 
     * @see     http://help.mailup.com/display/mailupapi/WSMailUpImport.StartImportProcesses
     * @param   type $processData
     * @return  int|bool
     */
	public function startImportProcesses($processData) 
    {
		if ( ! is_object($this->soapClient)) { 
            return false;
        }
		try {
			$this->soapClient->StartImportProcesses($processData);
            $returnCode = $this->_getStartImportProcessResult();
            /**
             * @todo    handle response better
             * 
             * We need to check this to see if we really are done, or the process is in a queue
             * or already running etc!
             */
            if ($this->_config()->isLogEnabled($this->storeId)) { 
                $this->_config()->dbLog("startImportProcesses [triggered]");
                $this->_config()->dbLog(sprintf("startImportProcesses [ReturnCode] [%d]", $returnCode));
                //$this->_config()->log($returnCode);
            }
			$this->printLastRequest();
			$this->printLastResponse();
            
			return $returnCode;
		} 
        catch (SoapFault $soapFault) {
			Mage::log('SOAP error', 0);
			Mage::log($soapFault, 0);
			return FALSE;
		}
	}

    /**
     * Get the return code from the XML response.
     * 
     * @staticvar string
     * @param type $func
     * @param type $param
     * @return boolean|int
     */
	private function readReturnCode($func, $param) 
    {
		if ( ! is_object($this->soapClient)) {
            return FALSE;
        }

        //prendi l'XML di ritorno se non l'ho già preso
        $this->xmlResponse = $this->soapClient->__getLastResponse();

        $dom = new DomDocument();
        $dom->loadXML($this->xmlResponse) or die('File XML non valido!');
        $xmlResult = $dom->getElementsByTagName($func.'Result');
        /**
         * Not successful, try and get a MailupMessae instead.
         * 
         * Check the API, it's not got a consistent return format! it's different if there's an issue
         */
        if(empty($xmlResult)) {
            $xmlResult = $dom->getElementsByTagName('mailupMessage');
        }

        $this->domResult = new DomDocument();
        $this->domResult->LoadXML(html_entity_decode($xmlResult->item(0)->nodeValue)) or die('File XML non valido!');
        /**
         * @todo FIX
         * 
         * Getting an error here, during Cron. 
         * Fatal error: Call to a member function getElementsByTagName() 
         */
        if(isset($this->domResult) && is_object($this->domResult)) {
            $rCode = $this->domResult->getElementsByTagName($param);
            return (int) $rCode->item(0)->nodeValue;
        }
        else {
            $this->_config()->dbLog('readReturnCode [No Return Code]');
            //$this->_config()->log('readReturnCode [No Return Code]');
            return 9999;
        }
        
		return FALSE;
	}
    
    /**
     * Get the result form the Import Process
     * 
     * An array of status code's the first one is the overall return code, 
     * but may be followed by return codes for each process started.
     * 
     * <mailupMessage>
        <mailupBody>
            <ReturnCode>0</ReturnCode>
            <processes>
                <process>
                    <processID>696</processID>
                    <listID>4</listID>
                    <ReturnCode>0</ReturnCode><!-- 0 = success -->
                    <processID>697</processID>
                    <listID>4</listID>
                    <ReturnCode>0</ReturnCode>
                </process>
            </processes>
        </mailupBody>
    </mailupMessage>
     * 
     * @param   string
     * @return  int
     */
    protected function _getStartImportProcessResult($param = 'ReturnCode')
    {
        if ( ! is_object($this->soapClient)) {
            return FALSE;
        }

        $this->xmlResponse = $this->soapClient->__getLastResponse();

        $dom = new DomDocument();
        $dom->loadXML($this->xmlResponse) or die('File XML non valido!');
        $xmlResult = $dom->getElementsByTagName('StartImportProcessesResult');

        $this->domResult = new DomDocument();
        $this->domResult->LoadXML(html_entity_decode($xmlResult->item(0)->nodeValue)) or die('File XML non valido!');
		
        if(isset($this->domResult) && is_object($this->domResult)) {
            $returnCodes = array();
            $nodes = $this->domResult->getElementsByTagName($param);
            foreach($nodes as $node) {
                $returnCodes[] = $node->nodeValue;
            }
            
            $processIds = array();
            $nodes = $this->domResult->getElementsByTagName('processID');
            foreach($nodes as $node) {
                $processIds[] = $node->nodeValue;
            }

            $overallReturnCode = array_shift($returnCodes); // first one is the overall returnCode.
            /**
             * Lets get the return codes for each process
             */
            if(count($returnCodes) > 0 && count($returnCodes) == count($processIds)) {
                /**
                 * @todo Use StartImportProcesses HERE!
                 * 
                 * We now have a list of Process IDs which have been added to the queue.
                 * We can now go through them all and try and start them!
                 */
                $returnCodes = array_combine($processIds, $returnCodes);
                $this->_config()->log('getStartImportProcessResult [Process Return Codes]');
                $this->_config()->log($returnCodes);
            }
            
            return (int) $overallReturnCode;
        }
        else {
            $this->_config()->log('getStartImportProcessResult [No ReturnCode]');
            return 9999;
        }
        
		return FALSE;
    }
    
    /**
     * NOT IN USE /////////////////////
     * 
     * Get the result form New Import Process
     * 
     * @param   string
     * @return  array
     */
    protected function _getNewImportProcessResult($param = 'ReturnCode')
    {
        if ( ! is_object($this->soapClient)) {
            return FALSE;
        }

        $this->xmlResponse = $this->soapClient->__getLastResponse();

        $dom = new DomDocument();
        $dom->loadXML($this->xmlResponse) or die('File XML non valido!');
        $xmlResult = $dom->getElementsByTagName('mailupMessage');

        $this->domResult = new DomDocument();
        $this->domResult->LoadXML(html_entity_decode($xmlResult->item(0)->nodeValue)) or die('File XML non valido!');
		
        if(isset($this->domResult) && is_object($this->domResult)) {
            $rCode = $this->domResult->getElementsByTagName($param);
            return $rCode->nodeValue;
        }
        else {
            $this->_config()->log('getNewImportProcessResult [No ReturnCode]');
            return 9999;
        }
        
		return FALSE;
    }

    /**
     * Print the last request
     * 
     * @return void
     */
	private function printLastRequest()
	{
		return;
        
		if ($this->_config()->isLogEnabled($this->storeId)) {
            $this->soapClient->__getLastRequest();
        }
	}

    /**
     * Print the Last Response
     */
	private function printLastResponse()
	{
		if ($this->_config()->isLogEnabled($this->storeId)) { 
            Mage::log('Mailup: printLastResponse');
            Mage::log($this->soapClient->__getLastResponse());
        }
	}

    /**
     * Get filtered customers 
     * 
     * @todo    refactor
     * @param
     * @param   int
     * @return  array
     */
	public function getCustomersFiltered($request, $storeId = NULL)
	{
		$TIMEZONE_STORE = new DateTimeZone(Mage::getStoreConfig("general/locale/timezone"));
		$TIMEZONE_UTC = new DateTimeZone("UTC");

		//inizializzo l'array dei clienti
		$customersFiltered = array();

		if (!$request->getRequest()->getParam('mailupCustomerFilteredMod')) {
			//ottengo la collection con tutti i clienti
			$customerCollection = Mage::getModel('customer/customer')
				->getCollection()
				->addAttributeToSelect('entity_id')
				->addAttributeToSelect('group_id')
				->addAttributeToSelect('created_at')
                ->addAttributeToSelect('store_id')
                //->getSelect()->query()
            ;
            /**
             * If StoreID = 0 we will not bother to filter...
             */
            if(isset($storeId) && ! empty($storeId)) {
                $customerCollection->addAttributeToFilter('store_id', array(
                    'eq' => $storeId
                ));
            }
		    $customerCollection = $customerCollection->getSelect()->query();

			while ($row = $customerCollection->fetch()) {
				$customersFiltered[] = $row;
			}

			// if required, select only those that are (or are not) subscribed in Magento
			if ($request->getRequest()->getParam('mailupSubscribed') > 0) {
                // Base status on option (1 -> must be subscribed. 2 -> must NOT be subscribed
                if ($request->getRequest()->getParam('mailupSubscribed') == 1) {
                    $expectedStatus = true;
                } else {
                    $expectedStatus = false;
                }
                // Filter list of customers by expected subscription status
				$tempSubscribed = array();
				foreach ($customersFiltered as $customer) {
					$customerItem = Mage::getModel('customer/customer')->load($customer['entity_id']);
                    $subscriptionStatus = Mage::getModel('newsletter/subscriber')->loadByCustomer($customerItem)->isSubscribed();
					if ($subscriptionStatus === $expectedStatus) {
						$tempSubscribed[] = $customer;
					}
				}

                $customersFiltered = self::intersectByEntityId($tempSubscribed, $customersFiltered);
			}
			/**
             * FILTER 1 PURCHASED: Depending on whether or not customer has made ​​purchases
             *   0 = all, 1 = those who purchased, 2 = someone who has never purchased
             */
			$count = 0;
			$result = array();
			$tempPurchased = array();
			$tempNoPurchased = array();

			if ($request->getRequest()->getParam('mailupCustomers') > 0) {
				foreach ($customersFiltered as $customer) {
					$result[] = $customer;
					// Filter orders based on customer id
                    $orders = Mage::getResourceModel('sales/order_collection')
                        ->addAttributeToFilter('customer_id', $result[$count]['entity_id']);
                    Mage::helper('mailup/order')->addStatusFilterToOrders($orders);

					// Add customer to either purchased or non-purchased array based on whether any orders
					if ($orders->getData()) {
						$tempPurchased[] = $result[$count];
					} 
                    else {
						$tempNoPurchased[] = $result[$count];
					}
					//unsetto la variabile
					unset($orders); //->unsetData();
					$count++;
				}

				if ($request->getRequest()->getParam('mailupCustomers') == 1) {
					$customersFiltered = self::intersectByEntityId($tempPurchased, $customersFiltered);
				} 
                elseif ($request->getRequest()->getParam('mailupCustomers') == 2) {
					$customersFiltered = self::intersectByEntityId($tempNoPurchased, $customersFiltered);
				}
			}
			/**
             * FILTER 1 BY PRODUCT: Based on whether customer purchased a specific product
             */
			$count = 0;
			$result = array();
			$tempProduct = array();

			if ($request->getRequest()->getParam('mailupProductSku')) {
				foreach ($customersFiltered as $customer) {
					$result[] = $customer;

					// Filter orders based on customer id
                    $orders = Mage::getResourceModel('sales/order_collection')
                        ->addAttributeToFilter('customer_id', $result[$count]['entity_id']);
                    Mage::helper('mailup/order')->addStatusFilterToOrders($orders);

					$purchasedProduct = 0;
					$mailupProductId = Mage::getModel('catalog/product')
                        ->getIdBySku($request->getRequest()->getParam('mailupProductSku'));

					foreach ($orders->getData() as $order) {
						$orderIncrementId = $order['increment_id'];

						//carico i dati di ogni ordine
						$orderData = Mage::getModel('sales/order')->loadByIncrementID($orderIncrementId);
						$items = $orderData->getAllItems();
						$ids = array();
						foreach ($items as $itemId => $item) {
							$ids[] = $item->getProductId();
						}

						if (in_array($mailupProductId, $ids)) {
							$purchasedProduct = 1;
						}
					}

					//aggiungo il cliente ad un determinato array in base a se ha ordinato o meno
					if ($purchasedProduct == 1) {
						$tempProduct[] = $result[$count];
					}

					//unsetto la variabile
					unset($orders); //->unsetData();

					$count++;
				}

				$customersFiltered = self::intersectByEntityId($tempProduct, $customersFiltered);
			}
			/**
             * FILTER 3 BY CATEGORY: Depending on whether bought at least one product in a given category
             */
			$count = 0;
			$result = array();
			$tempCategory = array();
			if ($request->getRequest()->getParam('mailupCategoryId') > 0) {
				foreach ($customersFiltered as $customer) {
					$result[] = $customer;
					// Filter orders based on customer id
                    $orders = Mage::getResourceModel('sales/order_collection')
                        ->addAttributeToFilter('customer_id', $result[$count]['entity_id']);
                    Mage::helper('mailup/order')->addStatusFilterToOrders($orders);

					foreach ($orders->getData() as $order) {

						$orderIncrementId = $order['increment_id'];

						// Load data for each order (very slow)
						$orderData = Mage::getModel('sales/order')->loadByIncrementID($orderIncrementId);
						$items = $orderData->getAllItems();
                        /**
                         * Category ID, and it's descendants
                         */
                        $searchCategories = Mage::helper('mailup')->getSubCategories($request->getRequest()->getParam('mailupCategoryId'));
						foreach ($items as $product) {
                            $_prod = Mage::getModel('catalog/product')->load($product->getProductId()); // need to load full product for cats.
                            $productCategories = Mage::getResourceSingleton('catalog/product')->getCategoryIds($_prod);
                            $matchingCategories = array_intersect($productCategories, $searchCategories);
                            if(is_array($matchingCategories) && ! empty($matchingCategories)) {
                                $tempCategory[] = $result[$count];
								break 2;
                            }
						}
					}
					unset($orders);
					$count++;
				}
				$customersFiltered = self::intersectByEntityId($tempCategory, $customersFiltered);
			}

			/**
             * FILTER 4 CUSTOMER GROUP
             */
			$count = 0;
			$result = array();
			$tempGroup = array();

			if ($request->getRequest()->getParam('mailupCustomerGroupId') > 0) {
				foreach ($customersFiltered as $customer) {
					if ($customer['group_id'] == $request->getRequest()->getParam('mailupCustomerGroupId')) {
						$tempGroup[] = $customer;
					}
				}

				$customersFiltered = self::intersectByEntityId($tempGroup, $customersFiltered);
			}
			//FINE FILTRO 4 GRUPPO DI CLIENTI: testato ok


			//FILTRO 5 PAESE DI PROVENIENZA
			$count = 0;
			$result = array();
			$tempCountry = array();

			if ($request->getRequest()->getParam('mailupCountry') != '0') {
				foreach ($customersFiltered as $customer) {
					//ottengo la nazione del primary billing address
					$customerItem = Mage::getModel('customer/customer')->load($customer['entity_id']);
					$customerAddress = $customerItem->getPrimaryBillingAddress();
					$countryId = $customerAddress['country_id'];

					if ($countryId == $request->getRequest()->getParam('mailupCountry')) {
						$tempCountry[] = $customer;
					}

					//unsetto la variabile
					unset($customerItem); //->unsetData();
				}

				$customersFiltered = self::intersectByEntityId($tempCountry, $customersFiltered);
			}
			//FINE FILTRO 5 PAESE DI PROVENIENZA: testato ok


			//FILTRO 6 CAP DI PROVENIENZA
			$count = 0;
			$result = array();
			$tempPostCode = array();

			if ($request->getRequest()->getParam('mailupPostCode')) {
				foreach ($customersFiltered as $customer) {
					//ottengo la nazione del primary billing address
					$customerItem = Mage::getModel('customer/customer')->load($customer['entity_id']);
					$customerAddress = $customerItem->getPrimaryBillingAddress();
					$postCode = $customerAddress['postcode'];

					if ($postCode == $request->getRequest()->getParam('mailupPostCode')) {
						$tempPostCode[] = $customer;
					}

					//unsetto la variabile
					unset($customerItem); //->unsetData();
				}

				$customersFiltered = self::intersectByEntityId($tempPostCode, $customersFiltered);
			}
			//FINE FILTRO 6 CAP DI PROVENIENZA: testato ok


			//FILTRO 7 DATA CREAZIONE CLIENTE
			$count = 0;
			$result = array();
			$tempDate = array();

			if ($request->getRequest()->getParam('mailupCustomerStartDate') || $request->getRequest()->getParam('mailupCustomerEndDate') ) {
				foreach ($customersFiltered as $customer) {
					$createdAt = $customer['created_at'];
					$createdAt = new DateTime($createdAt, $TIMEZONE_UTC);
					$createdAt->setTimezone($TIMEZONE_STORE);
					$createdAt = (string)$createdAt->format("Y-m-d H:i:s");
					$filterStart = '';
					$filterEnd = '';

					if ($request->getRequest()->getParam('mailupCustomerStartDate')) {
						$date =  Zend_Locale_Format::getDate(
                            $request->getRequest()->getParam('mailupCustomerStartDate'), 
                            array(
                                'locale'=>Mage::app()->getLocale()->getLocale(), 
                                'date_format'=>Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), 
                                'fix_date'=>true
                            )
                        );
						$date['month'] = str_pad($date['month'], 2, 0, STR_PAD_LEFT);
						$date['day'] = str_pad($date['day'], 2, 0, STR_PAD_LEFT);
						$filterStart = "{$date['year']}-{$date['month']}-{$date['day']} 00:00:00";
					}
					if ($request->getRequest()->getParam('mailupCustomerEndDate')) {
						$date =  Zend_Locale_Format::getDate(
                            $request->getRequest()->getParam('mailupCustomerEndDate'), 
                            array(
                                'locale'=>Mage::app()->getLocale()->getLocale(), 
                                'date_format'=>Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), 
                                'fix_date'=>true
                            )
                        );
						$date['month'] = str_pad($date['month'], 2, 0, STR_PAD_LEFT);
						$date['day'] = str_pad($date['day'], 2, 0, STR_PAD_LEFT);
						$filterEnd = "{$date['year']}-{$date['month']}-{$date['day']} 23:59:59";
					}
					if ($filterStart && $filterEnd) {
						//compreso tra start e end date
						if ($createdAt >= $filterStart and $createdAt <= $filterEnd) {
							$tempDate[] = $customer;
						}
					} 
                    elseif ($filterStart) {
						// >= di start date
						if ($createdAt >= $filterStart) {
							$tempDate[] = $customer;
						}
					} 
                    else {
						// <= di end date
						if ($createdAt <= $filterEnd) {
							$tempDate[] = $customer;
						}
					}
				}

				$customersFiltered = self::intersectByEntityId($tempDate, $customersFiltered);
			}
			//FINE FILTRO 7 DATA CREAZIONE CLIENTE: testato ok


			//FILTRO 8 TOTALE ACQUISTATO
			$count = 0;
			$result = array();
			$tempTotal = array();

			if ($request->getRequest()->getParam('mailupTotalAmountValue') > 0) {
				foreach ($customersFiltered as $customer) {
					$result[] = $customer;

					//filtro gli ordini in base al customer id
					$orders = Mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToFilter('customer_id', $result[$count]['entity_id'])
                    ;

					$totalOrdered = 0;

					foreach ($orders->getData() as $order) {
						if(isset($order["status"]) && ! in_array($order["status"], array("closed", "complete", "processing"))) {
                            continue;
                        }
						$totalOrdered += $order['subtotal'];
					}

					if ($totalOrdered == $request->getRequest()->getParam('mailupTotalAmountValue') 
                        && $request->getRequest()->getParam('mailupTotalAmountCond') == "eq") {
						$tempTotal[] = $result[$count];
					}

					if ($totalOrdered > $request->getRequest()->getParam('mailupTotalAmountValue') 
                        && $request->getRequest()->getParam('mailupTotalAmountCond') == "gt") {
						$tempTotal[] = $result[$count];
					}

					if ($totalOrdered < $request->getRequest()->getParam('mailupTotalAmountValue') 
                        && $request->getRequest()->getParam('mailupTotalAmountCond') == "lt" ) {
						$tempTotal[] = $result[$count];
					}

					$count++;

					//unsetto la variabile
					unset($orders); //->unsetData();
				}

				$customersFiltered = self::intersectByEntityId($tempTotal, $customersFiltered);
			}
			//FINE FILTRO 8 TOTALE ACQUISTATO: testato ok


			//FILTRO 9 DATA ACQUISTATO
			$count = 0;
			$result = array();
			$tempOrderedDateYes = array();
			$tempOrderedDateNo = array();

			if ($request->getRequest()->getParam('mailupOrderStartDate') 
                || $request->getRequest()->getParam('mailupOrderEndDate') ) {
				foreach ($customersFiltered as $customer) {
					$result[] = $customer;

					//filtro gli ordini in base al customer id
					$orders = Mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToFilter('customer_id', $result[$count]['entity_id'])
                    ;

					$orderedDate = 0;

					foreach ($orders->getData() as $order) {
                        if(isset($order["status"]) && ! in_array($order["status"], array("closed", "complete", "processing"))) {
                            continue;
                        }
						$createdAt = $order['created_at'];
						$createdAt = new DateTime($createdAt, $TIMEZONE_UTC);
						$createdAt->setTimezone($TIMEZONE_STORE);
						$createdAt = (string)$createdAt->format("Y-m-d H:i:s");
						$filterStart = '';
						$filterEnd = '';

						if ($request->getRequest()->getParam('mailupOrderStartDate')) {
							$date =  Zend_Locale_Format::getDate(
                                $request->getRequest()->getParam('mailupOrderStartDate'), 
                                array(
                                    'locale'=>Mage::app()->getLocale()->getLocale(), 
                                    'date_format'=>Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), 
                                    'fix_date'=>true
                                )
                            );
							$date['month'] = str_pad($date['month'], 2, 0, STR_PAD_LEFT);
							$date['day'] = str_pad($date['day'], 2, 0, STR_PAD_LEFT);
							$filterStart = "{$date['year']}-{$date['month']}-{$date['day']} 00:00:00";
						}
						if ($request->getRequest()->getParam('mailupOrderEndDate')) {
							$date =  Zend_Locale_Format::getDate(
                                $request->getRequest()->getParam('mailupOrderEndDate'), 
                                array(
                                    'locale'=>Mage::app()->getLocale()->getLocale(), 
                                    'date_format'=>Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), 
                                    'fix_date'=>true
                                )
                            );
							$date['month'] = str_pad($date['month'], 2, 0, STR_PAD_LEFT);
							$date['day'] = str_pad($date['day'], 2, 0, STR_PAD_LEFT);
							$filterEnd = "{$date['year']}-{$date['month']}-{$date['day']} 23:59:59";
						}

						if ($filterStart and $filterEnd) {
							//compreso tra start e end date
							if ($createdAt >= $filterStart and $createdAt <= $filterEnd) {
								$orderedDate = 1;
							}
						} 
                        elseif ($filterStart) {
							// >= di start date
							if ($createdAt >= $filterStart) {
								$orderedDate = 1;
							}
						} 
                        else {
							// <= di end date
							if ($createdAt <= $filterEnd) {
								$orderedDate = 1;
							}
						}

						//unsetto la variabile
						unset($orders); //->unsetData();
					}

					if ($orderedDate == 1) {
						$tempOrderedDateYes[]  = $result[$count];
					} 
                    else {
						$tempOrderedDateNo[]  = $result[$count];
					}

					$count++;
				}

				if ($request->getRequest()->getParam('mailupOrderYesNo') == 'yes') {
					$customersFiltered = self::intersectByEntityId($tempOrderedDateYes, $customersFiltered);
				} 
                else {
					$customersFiltered = self::intersectByEntityId($tempOrderedDateNo, $customersFiltered);
				}
			}
			//FINE FILTRO 9 DATA ACQUISTATO: testato ok
		} 
        else {
			//GESTISCO LE MODIFICHE MANUALI
			$count = 0;
			$result = array();
			$tempMod = array();

			$emails = explode("\n", $request->getRequest()->getParam('mailupCustomerFilteredMod'));

			foreach ($emails as $email) {
				$email = trim($email);

				if (strstr($email, '@') !== false) {
					$customerModCollection = Mage::getModel('customer/customer')
						->getCollection()
						->addAttributeToSelect('email')
						->addAttributeToFilter('email', $email);

					$added = 0;

					foreach ($customerModCollection as $customerMod) {
						$tempMod[] = $customerMod->toArray();
						$added = 1;
					}

					if ($added == 0) {
						$tempMod[] = array('entity_id'=>0, 'firstname'=>'', 'lastname'=>'', 'email'=>$email);
					}
				}
			}

			//$customersFiltered = self::intersectByEntityId($tempMod, $customersFiltered);
			$customersFiltered = $tempMod;
		}
		//FINE GESTISCO LE MODIFICHE MANUALI

		return $customersFiltered;
	}

    /**
     * Get Filter Hints
     * 
     * @return array
     */
	public function getFilterHints()
    {
		$filter_hints = array();
		try {
			// fetch write database connection that is used in Mage_Core module
			$connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read');

			// now $write is an instance of Zend_Db_Adapter_Abstract
			$result = $connectionRead->query("select * from mailup_filter_hints");

			while ($row = $result->fetch()) {
				array_push($filter_hints, array('filter_name' => $row['filter_name'], 'hints' => $row['hints']));
			}
		} catch (Exception $e) {
			Mage::log('Exception: '.$e->getMessage(), 0);
			die($e);
		}

		return $filter_hints;
	}

    /**
     * Save Filter Hint
     * 
     * @param type $filter_name
     * @param type $post
     */
	public function saveFilterHint($filter_name, $post) 
    {
		try {
			$hints = '';
			foreach ($post as $k => $v) {
				if ($v!='' && $k!='form_key') {
					if ($hints!='') {
						$hints .= '|';
					}
					$hints .= $k.'='.$v;
				}
			}
			//(e.g. $hints = 'mailupCustomers=2|mailupSubscribed=1';)
			$connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connectionWrite->query("INSERT INTO mailup_filter_hints (filter_name, hints) VALUES ('".$filter_name."', '".$hints."')");
		} 
        catch (Exception $e) {
			Mage::log('Exception: '.$e->getMessage(), 0);
			die($e);
		}
	}

    /**
     * Delete Filter Hint
     * 
     * @param type $filter_name
     */
	public function deleteFilterHint($filter_name) 
    {
		try {
			$connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connectionWrite->query("DELETE FROM mailup_filter_hints WHERE filter_name LIKE '".$filter_name."'");
		} 
        catch (Exception $e) {
			Mage::log('Exception: '.$e->getMessage(), 0);
			die($e);
		}
	}

    /**
     * Get Field Mapping
     * 
     * @todo    Fix to use the config for mappings, per store..
     * @param   int
     * @return  array
     */
	public function getFieldsMapping($storeId = NULL) 
    {
        $config = Mage::getModel('mailup/config');
        /* @var $config MailUp_MailUpSync_Model_Config */
        return $config->getFieldsMapping($storeId);
	}
    
    /**
     * @depreciated
     * @param   array
     */
	public function saveFieldMapping($post) 
    {
		try {
			$connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connectionWrite->query("DELETE FROM mailup_fields_mapping");
			foreach ($post as $k => $v) {
				if (strlen($v) == 0) continue;
				$connectionWrite->insert("mailup_fields_mapping", array(
					"magento_field_name" => $k,
					"mailup_field_id" => $v
				));
			}
		} catch (Exception $e) {
			Mage::log('Exception: '.$e->getMessage(), 0);
			die($e);
		}
	}
    
    /**
     * Get the config
     * 
     * @return MailUp_MailUpSync_Model_Config 
     */
    protected function _config()
    {
        return $this->_config;
    }
    
    function __destruct() 
    {
		unset($this->soapClient);
	}

    /**
     * Get a list of functions from the web service.
     */
	public function getFunctions() 
    {
		print_r($this->soapClient->__getFunctions());
	}

    /**
     * Recursive intersection of $array1 and $array2 by entity IDs
     * NOTE that php's self::intersectByEntityId is not recursive, so cannot be used on arrays of arrays
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function intersectByEntityId($array1, $array2)
    {
        $tempIds = array();
        foreach ($array1 as $entity1) {
            if (isset($entity1['entity_id']))
                $tempIds[$entity1['entity_id']] = true;
        }
        $tempArray = array();
        foreach ($array2 as $entity2) {
            if (isset($entity2['entity_id']) && isset($tempIds[$entity2['entity_id']]))
                $tempArray[] = $entity2;
        }

        return $tempArray;
    }
}