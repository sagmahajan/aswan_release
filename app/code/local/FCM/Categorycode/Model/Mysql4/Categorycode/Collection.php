<?php

class FCM_Categorycode_Model_Mysql4_Categorycode_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('categorycode/categorycode');
    }
}