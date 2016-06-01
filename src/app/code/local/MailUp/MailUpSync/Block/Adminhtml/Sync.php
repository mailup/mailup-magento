<?php  
/**
 * Sync.php
 */
class MailUp_MailUpSync_Block_Adminhtml_Sync extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
    public function __construct()
    {        
        $this->_controller = 'adminhtml_sync';
        $this->_blockGroup = 'mailup';
        
        $this->_headerText = Mage::helper('mailup')->__('MailUp Task Data');
        //$this->_addButtonLabel = Mage::helper('mailup')->__('Add Item');
        
        parent::__construct();
        
        $this->_removeButton('add');
    }
}