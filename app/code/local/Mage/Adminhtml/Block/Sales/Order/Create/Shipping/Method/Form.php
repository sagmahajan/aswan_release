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
 * Adminhtml sales order create shipping method form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_rates;

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_shipping_method_form');
    }

    /**
     * Retrieve quote shipping address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            /*
            if (!empty($groups)) {

                $ratesFilter = new Varien_Filter_Object_Grid();
                $ratesFilter->addFilter($this->getStore()->getPriceFilter(), 'price');

                foreach ($groups as $code => $groupItems) {
                    $groups[$code] = $ratesFilter->filter($groupItems);
                }
            }
            */
            return $this->_rates = $groups;
        }
        return $this->_rates;
    }

    /**
     * Rertrieve carrier name from store configuration
     *
     * @param   string $carrierCode
     * @return  string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $this->getStore()->getId())) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Retrieve current selected shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    /**
     * Check activity of method by code
     *
     * @param   string $code
     * @return  bool
     */
    public function isMethodActive($code)
    {
        return $code===$this->getShippingMethod();
    }

    /**
     * Retrieve rate of active shipping method
     *
     * @return Mage_Sales_Model_Quote_Address_Rate || false
     */
    public function getActiveMethodRate()
    {
        $rates = $this->getShippingRates();
        if (is_array($rates)) {
            foreach ($rates as $group) {
                foreach ($group as $code => $rate) {
                    if ($rate->getCode() == $this->getShippingMethod()) {
                        return $rate;
                    }
                }
            }
        }
        return false;
    }

    public function getIsRateRequest()
    {
        return $this->getRequest()->getParam('collect_shipping_rates');
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(
            Mage::helper('tax')->getShippingPrice(
                $price,
                $flag,
                $this->getAddress(),
                null,
                //We should send exact quote store to prevent fetching default config for admin store.
                $this->getAddress()->getQuote()->getStore()
            ),
            true
        );
    }
	public function getPostcode()
    {
        if (empty($this->_postcode)) {
            $this->_postcode = $this->getQuote()->getShippingAddress()->getPostcode();
        }
        return $this->_postcode;
    }
	
	public function checkShippingMethod($carrierName)
	{	
		$collection = Mage::getModel('zipcodeimport/zipcodeimport')
					->getCollection()->addFieldToFilter($carrierName, Array('eq'=>1))
					->addFieldToFilter('zip_code', Array('eq'=>$this->getPostcode()));
		return $collection;
	}
	public function isCustomShipping($carrierName)
	{
		$read =  Mage::getSingleton('core/resource')->getConnection('core_read');
		$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='fcm_zipcodeimport' AND COLUMN_NAME ='".strtolower($carrierName)."'";
		$result = $read->fetchAll($query);
		if(count($result) < 1)
		return false;
		else
		return true;
	}
}
