<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Checkout extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'payture';
    protected $_formBlockType = 'payture/form';
    protected $_infoBlockType = 'payture/info';
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $_canRefund = true;
    protected $_isGateway = true;
    protected $_canCapturePartial = true;

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('payture/payment/process', array('_secure' => true));
    }

    /**
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     *
     * @SuppressWarnings("unused")
     */
    public function initialize($action, $stateObject)
    {
        if ($status = $this->getConfigData('order_status')) {
            $stateObject->setStatus($status);
            $state = $this->_getAssignedState($status);
            $stateObject->setState($state);
            $stateObject->setIsNotified(true);
        }
        return $this;
    }

    protected function _getAssignedState($status)
    {
        $item = Mage::getResourceModel('sales/order_status_collection')
                ->joinStates()
                ->addFieldToFilter('main_table.status', $status)
                ->getFirstItem();
        return $item->getState();
    }

    public function getMethodTitle()
    {
        return Mage::getStoreConfig('payment/payture/title');
    }
}
