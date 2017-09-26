<?php

/**
 * MailUp
 *
 * @category    Mailup
 * @package     Mailup_Sync
 */
class MailUp_MailUpSync_Block_Adminhtml_Sync_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('MailUpSyncGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare Collection
     *
     * @return
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('mailup/sync')->getCollection();
        $this->setCollection($collection);
        // Set default sort to ID by highest to lowest (normally shows most recent first)
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id', array(
                'header' => Mage::helper('mailup')->__('ID'),
                'width'  => '80px',
                'index'  => 'id',
            )
        );

        $this->addColumn(
            'store_id', array(
                'header'  => Mage::helper('mailup')->__('Store'),
                'align'   => 'left',
                'index'   => 'store_id',
                'type'    => 'options',
                'options' => Mage::getModel('mailup/source_store')->getSelectOptions(),
            )
        );

        $this->addColumn(
            'customer_id', array(
                'header' => Mage::helper('mailup')->__('Customer ID'),
                'width'  => '80px',
                'index'  => 'customer_id',
            )
        );

        $this->addColumn(
            'entity', array(
                'header' => Mage::helper('mailup')->__('Entity'),
                'index'  => 'entity',
            )
        );

        $this->addColumn(
            'job_id', array(
                'header' => Mage::helper('mailup')->__('Job ID'),
                'width'  => '80px',
                'index'  => 'job_id',
            )
        );

        $this->addColumn(
            'needs_sync', array(
                'header'  => Mage::helper('mailup')->__('Needs Sync'),
                'align'   => 'left',
                'index'   => 'needs_sync',
                'type'    => 'options',
                'options' => array(
                    0 => 'No',
                    1 => 'Yes',
                ),
            )
        );

        $this->addColumn(
            'created', array(
                'header' => Mage::helper('mailup')->__('Created'),
                'type'   => 'timestamp',
                'width'  => '180px',
                'index'  => 'created',
            )
        );

        $this->addColumn(
            'last_sync', array(
                'header' => Mage::helper('mailup')->__('Last Sync Time'),
                'type'   => 'datetime', // Add in Date Picker
                'width'  => '180px',
                'index'  => 'last_sync',
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Get row url - None editable
     */
    public function getRowUrl($row)
    {
        return '';
    }

}
