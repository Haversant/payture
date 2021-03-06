<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2017 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Cron
{

    public function fivemin()
    {
        Mage::helper('payture')->addLog('Start of cron run');
        $collection = Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('sessionid', array('neq' => null));
        $collection->addFieldToFilter('state', array('neq' => 'Complete'));
        $collection->addFieldToFilter('date', array('gteq' => Mage::getModel('core/date')->date('Y-m-d H:i:s', strtotime('-1 hour', time()))));
        Mage::helper('payture')->addLog('Checking ' . count($collection) . ' order tickets');
        foreach ($collection as $_ticket) {
            Mage::helper('payture')->checkTicket($_ticket);
        }
        Mage::helper('payture')->addLog('End of cron run');
    }

    public function completeOrders()
    {
        Mage::helper('payture')->addLog('Start of cron run');
        $collection = Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('sessionid', array('neq' => null));
        $collection->addFieldToFilter('state', array('eq' => 'Authorized'));
        $collection->addFieldToFilter('date', array('gteq' => Mage::getModel('core/date')->date('Y-m-d H:i:s', strtotime('-48 hours', time()))));
        Mage::helper('payture')->addLog('Checking ' . count($collection) . ' authorized tickets');
        foreach ($collection as $_ticket) {
            Mage::helper('payture')->checkTicket($_ticket);
        }
        Mage::helper('payture')->addLog('End of cron run');
    }
}
