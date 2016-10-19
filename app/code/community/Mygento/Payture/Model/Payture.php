<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Payture
{

    public function processOrder($order, $enc_key)
    {
        $collection = Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('orderid', $order->getId());
        $item = $collection->getFirstItem();
        if ($item->getSessionid() == null) {
            $sessionid = $this->initSession($order, $enc_key, $item->getId());
        } else {
            $sessionid = $item->getSessionid();
        }
        if ($sessionid != false) {
            return Mage::helper('payture')->getHost() . 'Pay?SessionId=' . $sessionid;
        }
        return false;
    }

    protected function requestApiGet($url, $arpost)
    {

        //Create a CURL GET request
        // @codingStandardsIgnoreStart
        $ch = curl_init();
        $data = '';
        foreach ($arpost as $key => $value) {
            $data.= $key . '=' . $value . ';';
        }
        $full_url = $url . "?Key=" . Mage::helper('payture')->getKey() . '&Data=' . urlencode($data);
        Mage::helper('payture')->addLog($full_url);
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        // @codingStandardsIgnoreEnd   
        return $result;
    }

    private function initSession($order, $enc_key, $itemid)
    {
        $paytype = Mage::getStoreConfig('payment/payture/paytype');
        $request = array(
            'SessionType' => $paytype,
            'OrderId' => $order->getId(),
            'Amount' => $order->getGrandTotal() * 100,
            'Total' => $order->getGrandTotal(),
            'IP' => $order->getRemoteIp(),
            'Url' => Mage::getUrl('payture/payment/result/', array('_secure' => true, 'order' => $enc_key)),
        );
        //add product names
        $products = '';
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            if ($item->getOriginalPrice() > 0) {
                $products.=$item->getName() . ', ';
            }
        }

        if (substr($products, strlen($products) - 2, 2) == ', ') {
            $products = substr($products, 0, strlen($products) - 2);
        }
        $request['Product'] = $products;
        $result = $this->requestApiGet(Mage::helper('payture')->getHost() . 'Init', $request);
        Mage::helper('payture')->addLog($result);
        $xml = simplexml_load_string($result);
        if ((bool) $xml["Success"][0] == true) {
            if ($xml["SessionId"][0]) {
                $item = Mage::getModel('payture/keys')->load($itemid);
                $item->setSessionid($xml["SessionId"][0]);
                $item->setPaytype($paytype);
                $item->setState('New');
                $item->setDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $item->save();
                return $xml["SessionId"][0];
            }
        } else {
            $session = Mage::getSingleton('checkout/session');
            $session->addError($xml["ErrCode"][0]);
            return false;
        }
    }

    public function processStatus($status, $order_id, $key_id)
    {
        $order = Mage::getModel('sales/order')->load($order_id);
        if ($order->getId()) {
            $sess = Mage::getModel('payture/keys')->load($key_id);
            if ($status == 'Charged') {
                Mage::helper('payture')->addTransaction($order);
                $sess->setState('Complete');
            } else {
                $sess->setState($status);
            }
            $sess->save();
        }
    }

    public function checkSign($order_id, $check_result)
    {
        $string = $order_id . '|' . Mage::getStoreConfig('payment/payture/frame_token');
        if (hash('sha512', $string) == $check_result) {
            return true;
        }
        return false;
    }
}
