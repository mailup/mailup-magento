<?php

class MailUpWsSend
{
	protected $WSDLUrl = 'http://services.mailupnet.it/MailupSend.asmx?WSDL';
	private $soapClient;
	private $xmlResponse;
	protected $domResult;
    /**
     * @var int
     */
    protected $storeId;
    /**
     * @var MailUp_MailUpSync_Model_Config
     */
    protected $_config;
    
    
	function __construct($storeId = NULL) 
    {
		$this->soapClient = new SoapClient(
            $this->WSDLUrl, 
            array('trace' => 1, 'exceptions' => 1, 'connection_timeout' => 10)
        );
        
        if($storeId === NULL) {
            //$storeId = Mage::app()->getDefaultStoreView()->getStoreId();
        }
        
        $this->setStoreId($storeId);
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
	
	function __destruct() 
    {
		unset($this->soapClient); 
	}
	
	public function getFunctions() 
    {
		print_r($this->soapClient->__getFunctions()); 
	}
	
    /**
     * Login
     * 
     * @return int
     */
	public function login() 
    {
		$loginData = array('user' => Mage::getStoreConfig('mailup_newsletter/mailup/user', $this->storeId),
				'pwd' => Mage::getStoreConfig('mailup_newsletter/mailup/password', $this->storeId),
				'url' => Mage::getStoreConfig('mailup_newsletter/mailup/url_console', $this->storeId));
		
		$result = get_object_vars($this->soapClient->Login($loginData));
		$xml = simplexml_load_string($result['LoginResult']);
        $xml = get_object_vars($xml);
                
        //echo $xml['errorDescription'];

        return $xml['errorCode'];
	}

    /**
     * Login, returning access key or false on failing to login
     *
     * @param null $user
     * @param null $pwd
     * @param null $consoleId
     * @return false|string Access key or false
     */
    public function loginFromId($user=null, $pwd=null, $consoleId=null)
    {
        // login with webservice user
        $user = ($user !== null) ? $user : Mage::getStoreConfig('mailup_newsletter/mailup/username_ws', $this->storeId);
        $pwd = ($pwd !== null) ? $pwd : Mage::getStoreConfig('mailup_newsletter/mailup/password_ws', $this->storeId);
        $consoleId = ($consoleId !== null) ? $consoleId : substr($user, 1);

        return $this->_loginFromId($user, $pwd, $consoleId);
    }

    /**
     * Login, returning access key or false on failing to login
     *
     * @param null $user
     * @param null $pwd
     * @param null $consoleId
     * @return string|false Access key or false
     */
    protected function _loginFromId($user, $pwd, $consoleId)
    {
        try {
            //login with webservice user
            $loginData = array ('user' => $user, 'pwd' => $pwd, 'consoleId' => $consoleId);

            $result = get_object_vars($this->soapClient->LoginFromId($loginData));
            $xml = simplexml_load_string($result['LoginFromIdResult']);
            if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $this->storeId)) Mage::log($xml);

            $errorCode = (string)$xml->errorCode;
            $errorDescription = (string)$xml->errorDescription;
            $accessKey = (string)$xml->accessKey;

            if ($errorCode != 0) {
                Mage::log('Error code: '.$errorCode);
                Mage::log('Error description: '.$errorDescription);
                throw new Exception($errorDescription);
            }

            return $accessKey;
        } catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
			$errorDescription = $soapFault;
        } catch (Exception $e) {
            Mage::log($e->getMessage(), 0);
			$errorDescription = $e->getMessage();
        }
		
		$GLOBALS["__sl_mailup_login_error"] = $errorDescription;
		return false;
    }

    public function GetFields($accessKey) 
    {
        $fields = null;

        try {
            $result = get_object_vars($this->soapClient->GetFields_st(array('accessKey' => $accessKey)));
            $xml = simplexml_load_string($result['GetFields_stResult']);

            if ($xml->Error) {
                throw new Exception($xml->Error);
            }

            $fields = $this->_parseGetFieldsXmlResponse($xml);
        } 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
        } 
        catch (Exception $e) {
            Mage::log('Custom exception', 0);
            Mage::log($e->getMessage(), 0);
        }

        return $fields;
    }

    private function _parseGetFieldsXmlResponse($xmlSimpleElement) 
    {
        $fields = $this->_getFieldsDefaultConfiguration();

        if ($xmlSimpleElement->Fields && sizeof($xmlSimpleElement->Fields->Field) > 0) {
			$fields = array();
            if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $this->storeId))
                Mage::log('Fields returned, overwriting default configuration', Zend_Log::INFO);
            foreach ($xmlSimpleElement->Fields->Field as $fieldSimpleElement) {
                $fields[(string)$fieldSimpleElement['Name']] = (string)$fieldSimpleElement['Id'];
            }
        }
        if (Mage::getStoreConfig('mailup_newsletter/mailup/enable_log', $this->storeId))
            Mage::log($fields);
        return $fields;
    }

    private function _getFieldsDefaultConfiguration()
    {
        $fields = array();

        $fields['nome'] = '1';
        $fields['cognome'] = '2';
        $fields['azienda'] = '3';
        $fields['città'] = '4';
        $fields['provincia'] = '5';
        $fields['cap'] = '6';
        $fields['regione'] = '7';
        $fields['paese'] = '8';
        $fields['indirizzo'] = '9';
        $fields['fax'] = '10';
        $fields['telefono'] = '11';
        $fields['IDCliente'] = '12';
        $fields['IDUltimoOrdine'] = '13';
        $fields['DataUltimoOrdine'] = '14';
        $fields['TotaleUltimoOrdine'] = '15';
        $fields['IDProdottiUltimoOrdine'] = '16';
        $fields['IDCategorieUltimoOrdine'] = '17';
        $fields['DataUltimoOrdineSpedito'] = '18';
        $fields['IDUltimoOrdineSpedito'] = '19';
        $fields['DataCarrelloAbbandonato'] = '20';
        $fields['TotaleCarrelloAbbandonato'] = '21';
        $fields['IDCarrelloAbbandonato'] = '22';
        $fields['TotaleFatturato'] = '23';
        $fields['TotaleFatturatoUltimi12Mesi'] = '24';
        $fields['TotaleFatturatoUltimi30gg'] = '25';
        $fields['IDTuttiProdottiAcquistati'] = '26';

        return $fields;
    }

	
	public function logout() 
    {
		try {
			$this->soapClient->Logout(array('accessKey' => $this->accessKey));
			if ($this->readReturnCode('Logout', 'errorCode') != 0) {
				echo '<br /><br />Errore Logout'. $this->readReturnCode('Logout', 'errorDescription');
            }
		} 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
		}
	}
	
	public function getLists() 
    {
		try {
			$this->soapClient->GetLists(array('accessKey' => $this->accessKey));
			if ($this->readReturnCode('GetLists', 'errorCode') != 0) {
				echo '<br /><br />Errore GetLists: '. $this->readReturnCode('GetLists', 'errorDescription');
            } 
            else {
                $this->printLastResponse();
            }
		} 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
		}
	}
	
	public function getGroups($params) 
    {
		try {
			$params = array_merge((array)$params, array('accessKey' => $this->accessKey));
			$this->soapClient->GetGroups($params);
			if ($this->readReturnCode('GetGroups', 'errorCode') != 0) {
				echo '<br /><br />Errore GetGroups: '. $this->readReturnCode('GetGroups', 'errorDescription');
            } 
            else {
                $this->printLastResponse();
            }
		} 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
		}
	}
	
	public function getNewsletters($params) 
    {
		try {
			$params = array_merge((array)$params, array('accessKey' => $this->accessKey));
			$this->soapClient->GetNewsletters($params);
			if ($this->readReturnCode('GetNewsletters', 'errorCode') != 0) {
				echo '<br /><br />Errore GetNewsletters: '. $this->readReturnCode('GetNewsletters', 'errorDescription');
            } 
            else {
                $this->printLastResponse();
            }
		} 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
		}
	}
	
	public function createNewsletter($params) 
    {
		try {
			$params = array_merge((array)$params, array('accessKey' => $this->accessKey));
			$this->soapClient->createNewsletter($params);

            $this->printLastRequest();
			if($this->readReturnCode('CreateNewsletter', 'errorCode') != 0) {
				echo '<br /><br />Errore CreateNewsletter: '. $this->readReturnCode('CreateNewsletter', 'errorCode') .' - '. 
                     $this->readReturnCode('CreateNewsletter', 'errorDescription')
                ;
            } 
            else {
                $this->printLastResponse();
            }
		} 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
		}
	}
	
	public function sendNewsletter($params) 
    {
		try {
			$params = array_merge((array)$params, array('accessKey' => $this->accessKey));
			$this->soapClient->SendNewsletter($params);
			$this->printLastRequest();
			if ($this->readReturnCode('SendNewsletter', 'errorCode') != 0) {
				echo '<br /><br />Errore SendNewsletter: '. $this->readReturnCode('SendNewsletter', 'errorCode') .' - '. 
                     $this->readReturnCode('SendNewsletter', 'errorDescription')
                ;
            } 
            else {
                $this->printLastResponse();
            }
		} 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
		}
	}
	
	public function sendNewsletterFast($params) 
    {
		try {
			$params = array_merge((array)$params, array('accessKey' => $this->accessKey));
			$this->soapClient->SendNewsletterFast($params);
			$this->printLastRequest();
			if ($this->readReturnCode('SendNewsletterFast', 'errorCode') != 0) {
				echo '<br /><br />Errore SendNewsletterFast: '. $this->readReturnCode('SendNewsletterFast', 'errorCode') .
                     ' - '. $this->readReturnCode('SendNewsletterFast', 'errorDescription')
                ;
            } 
            else {
                $this->printLastResponse();
            }
		} 
        catch (SoapFault $soapFault) {
            Mage::log('SOAP error', 0);
            Mage::log($soapFault, 0);
		}
	}
	
    /**
     * Get the return code
     * 
     * @staticvar string $func_in
     * @param type $func
     * @param type $param
     * @return type
     */
	private function readReturnCode($func, $param) 
    {
		static $func_in = ''; //static variable to test xmlResponse update
		if ($func_in != $func) { //(!isset($this->xmlResponse))
			$func_in = $func;
			//prendi l'XML di ritorno se non l'ho gia' preso
			$this->xmlResponse = $this->soapClient->__getLastResponse();
		
			$dom = new DomDocument();
			$dom->loadXML($this->xmlResponse) or die('File XML non valido!');
			$xmlResult = $dom->getElementsByTagName($func.'Result');
			
			$this->domResult = new DomDocument();
			$this->domResult->LoadXML(html_entity_decode($xmlResult->item(0)->nodeValue)) or die('File XML non valido!');
		}

		$rCode = $this->domResult->getElementsByTagName($param);
		return $rCode->item(0)->nodeValue;
	}
	
	private function printLastRequest() 
    {
		echo '<br />Request :<br />'. htmlentities($this->soapClient->__getLastRequest()) .'<br />';
	}
	
	private function printLastResponse() 
    {
		echo '<br />XMLResponse: '. $this->soapClient->__getLastResponse() .'<br />'; //htmlentities();
	}

    //TODO: seems unused, remove if so
	public function getAccessKey() 
    {
		return $this->accessKey;
	}
	
	public function option($key, $value) 
    {
		return array('Key' => $key, 'Value' => $value);
	}

    //TODO: TEST stuff (this shouldn't be here)
    public function loginTest() 
    {
        $loginData = array('user' => 'a7410', 'pwd' => 'GA6VAN0W', 'url' => 'g4a0.s03.it');

        $result = get_object_vars($this->soapClient->Login($loginData));
        $xml = simplexml_load_string($result['LoginResult']);
        $xml = get_object_vars($xml);

        if ($xml['errorCode'] > 0) {
            echo $xml['errorDescription'].'<br /><br />';
        }

        return $xml['errorCode'];
    }

    public function testSoap() 
    {
        $client = new SoapClient('http://soapclient.com/xml/soapresponder.wsdl', array('trace' => 1, 'exceptions' => 1, 'connection_timeout' => 10));
        //print_r($client->__getFunctions());
        return $client->Method1('x12qaq','c56tf3');
    }
    
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