<?php

/**
 * Grid.php
 */
class MailUp_MailUpSync_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('MailUpLogGrid');
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
        $collection = Mage::getModel('mailup/log')->getCollection();
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
            'type', array(
                'header' => Mage::helper('mailup')->__('Type'),
                'index'  => 'type',
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
            'data', array(
                'header' => Mage::helper('mailup')->__('Info'),
                'index'  => 'data',
            )
        );

        $this->addColumn(
            'event_time', array(
                'header' => Mage::helper('mailup')->__('Event Time'),
                'type'   => 'datetime', // Add in Date Picker
                'width'  => '180px',
                'index'  => 'event_time',
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