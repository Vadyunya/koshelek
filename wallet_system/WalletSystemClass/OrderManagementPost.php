<?php
/******************************************************************
# Wallet--- Wallet                                                *
# ----------------------------------------------------------------*
# author    Webkul                                                *
# copyright Copyright (C) 2010 webkul.com. All Rights Reserved.   *
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL     *
# Websites: http://webkul.com                                     *
*******************************************************************
*/



namespace WalletSystemClass;
use Tygh\Registry;

class OrderManagementPost extends BaseController {
    /**
     * OrderManagementPost constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);
        $this->setRunMode(array('edit','update','update_totals'));
        if(in_array($this->mode,$this->runMode)){
            $this->$mode();
        }
    }

    public function edit() {
        
        $order_info = fn_get_order_info($_REQUEST['order_id']);
        Registry::get('view')->assign('order_info',$order_info);
    }

    public function update() {

        if(isset($_SESSION['cart']['order_id'])){

            $order_info = fn_get_order_info($_SESSION['cart']['order_id']);            
            Registry::get('view')->assign('order_info',$order_info);
            if(isset($order_info['wallet']['used_cash'])) {

                $remaining_wallet_amount=$order_info['wallet']['used_cash']-$order_info['wallet_refunded_amount'];

                if($_SESSION['cart']['total']<=$remaining_wallet_amount) {

                    $_SESSION['cart']['payment_id']=0;
                    $payment_data = fn_get_payment_method_data(0);
                    Registry::get('view')->assign('payment_method', $payment_data);
                    Registry::get('view')->assign('hidden_payment', 1);
                }

                if($_SESSION['cart']['total']>$remaining_wallet_amount) { 
                    if(empty($_SESSION['cart']['payment_id'])) {
                        if(isset($customer_auth['usergroup_ids'])) {
                            $payment_methods = fn_get_payments(array('usergroup_ids' => $customer_auth['usergroup_ids']));
                            Registry::get('view')->assign('payment_method', $payment_data);
                        }
                        
                        Registry::get('view')->assign('hidden_payment', 0);
                    }
                }
            }

        }
    }

    public function update_totals() {
        if(isset($_SESSION['cart']['order_id'])) {

		    $order_info = fn_get_order_info($_SESSION['cart']['order_id']);

            if(isset($order_info['wallet']['used_cash'])) {

                $remaining_wallet_amount = $order_info['wallet']['used_cash']-$order_info['wallet_refunded_amount'];
                if($_SESSION['cart']['total'] <= $remaining_wallet_amount) {

                    $_SESSION['cart']['payment_id']=0;
                    $order_info = fn_get_order_info($_SESSION['cart']['order_id']);
                    $payment_data = fn_get_payment_method_data(0);
                    Registry::get('view')->assign('payment_method', $payment_data);
                    Registry::get('view')->assign('hidden_payment', 1);
                    
                }

                if($_SESSION['cart']['total'] > $remaining_wallet_amount) {
                    if(empty($_SESSION['cart']['payment_id'])) {
                        if(isset($customer_auth['usergroup_ids'])) {
                            $payment_methods = fn_get_payments(array('usergroup_ids' => $customer_auth['usergroup_ids']));
                            Registry::get('view')->assign('payment_method', $payment_data);
                        }
                        Registry::get('view')->assign('hidden_payment', 0);
                    }
                }
            }
        }
    }
}
?>
