<?php

/**
 * 
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2015 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Mysql4_Keys_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('payture/keys');
    }

}
