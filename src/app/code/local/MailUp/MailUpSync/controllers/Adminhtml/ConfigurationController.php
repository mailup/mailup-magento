<?php

require_once dirname(__FILE__) . "/../../Model/MailUpWsImport.php";
require_once dirname(__FILE__) . "/../../Model/Wssend.php";
class MailUp_MailUpSync_Adminhtml_ConfigurationController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$url = Mage::getModel('adminhtml/url');
		$url = $url->getUrl("adminhtml/system_config/edit", array(
			"section" => "mailup_newsletter"
		));
		Mage::app()->getResponse()->setRedirect($url);
	}

    /**
     * Get groups for given list
     */
    public function getgroupsAction()
    {
        // Get passed list ID to get groups for
        $listId = $this->getRequest()->getParam('list');
        if ($listId === null) {
            $output = '<option>-- Could not find list --</option>';
        } else {
            $groups = Mage::getSingleton('mailup/source_groups')->toOptionArray(null, $listId);

            // Render output directly (as output is so simple)
            $output = '';
            foreach ($groups as $group) {
                $output .= "<option value=\"{$group['value']}\">{$group['label']}</option>\n";
            }
        }

        $this->getResponse()->setBody($output);
    }

    /**
     * Run connection-to-mailup test other system configurations that area relevant
     */
    public function testconnectionAction()
    {
        // Get login details from AJAX
        $urlConsole = $this->getRequest()->getParam('url_console');
        $usernameWs = $this->getRequest()->getParam('username_ws');
        $passwordWs = $this->getRequest()->getParam('password_ws');

        // Ensure that all required fields are given
        if ($urlConsole === null || $usernameWs === null || $passwordWs === null) {
            $class = 'notice';
            $message = $this->__('Please fill in MailUp console URL, Username and Password before testing');
            $output = '<ul class="messages"><li class="' . $class . '-msg"><ul><li>' . $message . '</li></ul></li></ul>';
            $this->getResponse()->setBody($output);
            return;
        }

        $messages = array();

        // Close connection to avoid mysql gone away errors
        $res = Mage::getSingleton('core/resource');
        $res->getConnection('core_write')->closeConnection();

        // Test connection
        $storeId = Mage::app()->getStore();
        $retConn = Mage::helper('mailup')->testConnection($urlConsole, $usernameWs, $passwordWs, $storeId);
        $messages = array_merge($messages, $retConn);

        // Config tests
        $retConfig = Mage::helper('mailup')->testConfig();
        $messages = array_merge($messages, $retConfig);

        // Re-open connection to avoid mysql gone away errors
        $res->getConnection('core_write')->getConnection();

        // Connect up the messages to be returned as ajax
        $renderedMessages = array();
        if (count($messages) > 0) {
            foreach ($messages as $msg) {
                $renderedMessages[] = '<li class="' . $msg['type'] . '-msg"><ul><li>' . $msg['message'] . '</li></ul></li>';
            }
        }
        $output = '<ul class="messages">' . implode("\n", $renderedMessages) . '</ul>';
        $this->getResponse()->setBody($output);
    }
}
