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

        //var_dump(Mage::getModel('mailup/job')->load(1));
        
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns
     */
    protected function _prepareColumns()
    {        
        $this->addColumn('id', array(
          'header'    => Mage::helper('mailup')->__('ID'),
          //'align'     =>'right',
          'width'     => '80px',
          'index'     => 'id',
        ));
        
        $this->addColumn('store_id', array(
            'header'    => Mage::helper('mailup')->__('Store'),
            'align'     => 'left',
            //'width'     => '150px',
            'index'     => 'store_id',
            'type'      => 'options',
            'options'   => Mage::getModel('mailup/source_store')->getSelectOptions(),
        ));
        
        $this->addColumn('type', array(
            'header'    => Mage::helper('mailup')->__('Type'),
            //'align'     =>'right',
            //'width'     => '80px',
            'index'     => 'type',
        ));
        
        $this->addColumn('job_id', array(
            'header'    => Mage::helper('mailup')->__('Job ID'),
            //'align'     =>'right',
            'width'     => '80px',
            'index'     => 'job_id',
        ));
        
        // Not really in use yet!
        /*$this->addColumn('status', array(
            'header'    => Mage::helper('mailup')->__('Status'),
            //'align'     =>'right',
            //'width'     => '80px',
            'index'     => 'status',
        ));*/
        
       $this->addColumn('data', array(
            'header'    => Mage::helper('mailup')->__('Info'),
            //'align'     =>'right',
            //'width'     => '80px',
            'index'     => 'data',
       ));
        
       $this->addColumn('event_time', array(
            'header'    => Mage::helper('mailup')->__('Event Time'),
            'type'      => 'datetime', // Add in Date Picker
            //'type'      => 'timestamp',
            //'align'     => 'center',
            'width'     => '180px',
            'index'     => 'event_time',
            //'gmtoffset' => true
        ));
        
//
//
//        $this->addColumn('status', array(
//            'header'    => Mage::helper('importer')->__('Status'),
//            'align'     => 'left',
//            'width'     => '80px',
//            'index'     => 'status',
//            'type'      => 'options',
//            'options'   => array(
//                1 => 'Enabled',
//                2 => 'Disabled',
//            ),
//        ));
//
        
//        $this->addColumn('action',
//            array(
//                'header'    =>  Mage::helper('mailup')->__('Action'),
//                'width'     => '100',
//                'type'      => 'action',
//                'getter'    => 'getId',
//                'actions'   => array(
//                    array(
//                        'caption'   => Mage::helper('mailup')->__('Sync'),
//                        'url'       => array('base'=> '*/*/sync'),
//                        'field'     => 'id'
//                    )
//                ),
//                'filter'    => false,
//                'sortable'  => false,
//                'index'     => 'stores',
//                'is_system' => true,
//        ));

        return parent::_prepareColumns();
    }

//    /**
//     * Prepare Mass Action
//     */
//    protected function _prepareMassaction()
//    {
//        $this->setMassactionIdField('id');
//        $this->getMassactionBlock()->setFormFieldName('importer');
//
//        $this->getMassactionBlock()->addItem('delete', array(
//             'label'    => Mage::helper('importer')->__('Delete'),
//             'url'      => $this->getUrl('*/*/massDelete'),
//             'confirm'  => Mage::helper('importer')->__('Are you sure?')
//        ));
//
//        $statuses = Mage::getSingleton('importer/import')->getOptionArray();
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        
//        return $this;
//    }

    /**
     * Get row url - None editable
     */
    public function getRowUrl($row)
    {
        return '';
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}