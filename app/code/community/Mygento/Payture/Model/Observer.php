<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Observer extends Varien_Object
{

    public function sendEmail($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethodInstance()->getCode() == 'payture') {
            Mage::helper('payture')->sendEmailByOrder($order);
        }
    }
}
