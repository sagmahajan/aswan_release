<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('source')
            ->addAttributeToSelect('campaign')
			->addAttributeToSelect('telephone')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
        /*$collection->getSelect()->joinLeft( array('custdob'=> 'customer_entity_datetime'), 'custdob.entity_id = e.entity_id', 
                                            array("DATE_FORMAT(custdob.value,'%d %M, %Y') AS customer_dob"));*/
        $collection->getSelect()->joinLeft( array('custgender'=> 'customer_entity_int'), 
                                                  'custgender.entity_id = e.entity_id AND custgender.attribute_id = 18', 
                                            array("custgender.value AS customer_gender"));
	
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $gender = array(1=>"Male", 2=>"Female");
        
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
	
	$this->addColumn('prefix', array(
            'header'    => Mage::helper('customer')->__('Prefix'),
            'width'     => '50px',
            'index'     => 'prefix'
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname'
        ));
        
	$this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname'
        ));
        
	/*$this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));*/

        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '50',
            'index'     => 'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('customer_gender', array(
            'header' => Mage::helper('adminhtml')->__('Gender'),
            'align' => 'left',
            'type' => 'options',
            'options' => array(1 => 'Male', 2 => 'Female', NULL => '-'),
            'index' => 'customer_gender',
        ));

        $this->addColumn('Mobile', array(
            'header'    => Mage::helper('customer')->__('Mobile'),
            'width'     => '100',
            'index'     => 'telephone'
        ));
        $this->addColumn('source', array(
            'header'    => Mage::helper('customer')->__('Source'),
            'width'     => '100',
            'index'     => 'source'
        ));
        $this->addColumn('campaign', array(
            'header'    => Mage::helper('customer')->__('Campaign'),
            'width'     => '100',
            'index'     => 'campaign'
        ));
        /* $this->addColumn('Telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone'
        )); */

        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        ));
        /*
        $this->addColumn('customer_dob', array(
            'header'    => Mage::helper('customer')->__('DOB'),
            'width'     => '100',
            'index'     => 'customer_dob',
        ));
        
        $this->addColumn('customer_gender', array(
            'header'    => Mage::helper('customer')->__('Gender'),
            'width'     => '90',
            'index'     => 'customer_gender',
            'type'      =>  'options',
            'options'   =>  $gender
        ));
	*/
        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        ));

        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('customer')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('customer')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('newsletter_subscribe', array(
             'label'    => Mage::helper('customer')->__('Subscribe to Newsletter'),
             'url'      => $this->getUrl('*/*/massSubscribe')
        ));

        $this->getMassactionBlock()->addItem('newsletter_unsubscribe', array(
             'label'    => Mage::helper('customer')->__('Unsubscribe from Newsletter'),
             'url'      => $this->getUrl('*/*/massUnsubscribe')
        ));

        $groups = $this->helper('customer')->getGroups()->toOptionArray();

        array_unshift($groups, array('label'=> '', 'value'=> ''));
        $this->getMassactionBlock()->addItem('assign_group', array(
             'label'        => Mage::helper('customer')->__('Assign a Customer Group'),
             'url'          => $this->getUrl('*/*/massAssignGroup'),
             'additional'   => array(
                'visibility'    => array(
                     'name'     => 'group',
                     'type'     => 'select',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('customer')->__('Group'),
                     'values'   => $groups
                 )
            )
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
