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

namespace Tygh\Addons\WalletSystem\HookHandlers;

use Tygh\Registry;
use WalletSystemModel\WalletSystemModel;

class GetOrderInfoHookHandler extends WalletSystemModel {

    public function getOrderInfo(&$order, &$additional_data){
        if (isset($order['order_id'])) {

            $amount = $this->getCreditLog(array('credit_amount'),array('source'=> 'recharge', 'source_id'=> $order['order_id']),'db_get_field');
            if (empty($amount)) {

                $check = $this->getWalletOfflinePayment(array('order_id'),array('order_id'=>$order['order_id']),'db_get_field');                
    
                if (!empty($check)) {
                    if (isset($order['payment_surcharge']) && !empty($order['payment_surcharge'])) {
                        $amount = $order['total'] - $order['payment_surcharge'];
                    } else {
                        $amount = $order['total'];
                    }
                }
            }
            if (!empty($amount)) {
                $order['display_subtotal'] = $amount;
                $order['subtotal'] = $amount;
                $order['wallet_system']['recharge_amount'] = $amount;
            }
    
            $get_wallet_order_data = $this->getOrderData(array('data'),array('order_id'=>$order['order_id'],'type'=>'N'),'db_get_field');
            
            $get_wallet_order_data = unserialize($get_wallet_order_data);
                
            if (isset($get_wallet_order_data['used_cash'])) {
                $order['wallet']=$get_wallet_order_data;
                //$order['subtotal_discount']=$get_wallet_order_data['used_cash'];
                $current_controller=Registry::get('runtime.controller');
             
                $current_mode=Registry::get('runtime.mode');
                    
                if ($current_controller == 'orders') {
                    if ($current_mode == 'details' 
                    || $current_mode == 'print_invoice' 
                    || $current_mode == 'manage') 
                    {
                        // $order['total']+=$get_wallet_order_data['used_cash'];
                    }
                }
         
                if ($current_mode == 'add_wallet' && $current_controller = 'rma') {
                    $order['total']+=$get_wallet_order_data['used_cash'];
                }
    
                if ($current_mode == 'refund' && $current_controller = 'wallet_system') {
                    $order['total']+=$get_wallet_order_data['used_cash'];
                }
                if ($current_mode == 'refund_in_wallet' && $current_controller = 'wallet_system') {
                    $order['total']+=$get_wallet_order_data['used_cash'];
                }
            }
    
            if (!isset($order['wallet']['used_cash'])) {
                $get_wallet_used_cash = $this->getWalletRefundedAmount(array('pay_by_wallet_amount'),array('order_id'=>$order['order_id']),'db_get_field');
                
                if (!empty($get_wallet_used_cash)&& $get_wallet_used_cash>0.0) {
                    $used_cash_data= array(
                    'wallet'=>array(
                            'used_cash'=>$get_wallet_used_cash
                        )
                    );
                    $order = array_merge($order, $used_cash_data);
                }
            }
        }
        
        // if(isset($_REQUEST['dispatch']) &&  ($_REQUEST['dispatch'] == 'orders.update_status' || $_REQUEST['dispatch'] == 'checkout.place_order')){
        //     $email_template = Registry::get('settings.Appearance.email_templates');
        //     if($email_template == 'new'){
        //         if (isset($order['wallet']['used_cash'])) {
        //             $order['total'] = $order['wallet']['used_cash'] + $order['total'];
        //         }
        //     }
        // }
        if ((Registry::get('runtime.controller') == 'payment_notification' && Registry::get('runtime.mode') == 'return') || $_REQUEST['dispatch'] == 'orders.update_status') {
            $email_template = Registry::get('settings.Appearance.email_templates');
            if ($email_template == 'new') {
                if (isset($order['wallet']['used_cash'])) {
                    // $order['total'] = $order['wallet']['used_cash'] + $order['total'];
                }
            }
        }
    }
}