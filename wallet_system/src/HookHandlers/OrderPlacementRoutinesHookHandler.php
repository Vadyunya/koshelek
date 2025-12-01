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
use Tygh\Tygh;
use WalletSystemModel\WalletSystemModel;

class OrderPlacementRoutinesHookHandler extends WalletSystemModel{

    public function __construct()
    {
        parent::__construct();
    }

    public function orderPlacementRoutines(&$order_id, &$force_notification, &$order_info, &$_error) {

        $varify_order = $this->getCreditLog(array('source_id'),array('source'=>'recharge','source_id'=>$order_id),'db_get_field');        
    
        if (!empty($varify_order)) {
            if (in_array($order_info['status'], array('N', 'F', 'D'))) {
                $user_current_amount = $this->getWalletAmount(null, $order_info['user_id']);
                $user_updated_amount = $user_current_amount-$order_info['total'];
    
                $user_updated_amount_data = array(
                    'total_cash' => $user_updated_amount
                );

                $this->updateWalletCash($user_updated_amount_data,array('user_id'=>$order_info['user_id']));
                $this->deleteCreditLog(array('source_id'=>$order_id,'source'=>'recharge'));                
            }
        }
            
        if (isset($order_info['gift_certificates'])) {
        } else {
            if (!empty($order_info['payment_method']['processor'])) {

                if (empty($order_info['products'])) {

                    if (!in_array($order_info['status'], array('N', 'F', 'D'))) {

                        $wallet_id = $this->getUserWalletId($order_info['user_id']);
                        $user_total_cash = $this->getWalletAmount($wallet_id, null);

                        if (isset($order_info['payment_surcharge']) && !empty($order_info['payment_surcharge'])) {

                            $credit_amount = $order_info['total'] - $order_info['payment_surcharge'];
                        } else {

                            $credit_amount = $order_info['total'];
                        }
    
                        $user_updated_amount_daa = array(
                            'total_cash' => $credit_amount+$user_total_cash
                        );
                        
                        $this->updateWalletCash($user_updated_amount_daa,array('user_id'=>$order_info['user_id']));                        
    
                        if(fn_allowed_for('ULTIMATE')) {
                            $company_id = Registry::get('runtime.company_id');
                        } else {
                            $company_id = Registry::get('runtime.company_id');
                        }
    
                        $_data = array(
                            'source'         => "recharge",
                            'source_id'      => $order_id,
                            'wallet_id'      => $wallet_id,
                            'credit_amount'  => $credit_amount,
                            'company_id'     => $company_id,
                            'total_amount'   => $user_total_cash+$order_info['total'],
                            'timestamp'      => TIME,    
                        );
    
                        $wallet_credit_log_id = $this->insertWalletCreditLog($_data);                        
                        
                        $tran_data = array(
                            'credit_id' => $wallet_credit_log_id,
                            'wallet_id' => $wallet_id,
                            'timestamp' => TIME,
                        );

                        $this->insertWalletTransaction($tran_data);                        

                        $this->creditWalletNotification($wallet_credit_log_id);
                    }
                }
            }
        }
    
        $varify_debit_order = $this->getOrderData(array('data'),array('type'=>'N','order_id'=>$order_id),'db_get_field');
        
        if (!empty($varify_debit_order)) {

            if (!in_array($order_info['status'], array('N', 'F', 'D'))) {

                if ($order_info['is_parent_order'] == 'N') {

                    $debit_check = $this->getDebitLog(array('order_id'),array('order_id'=>$order_id),'db_get_field');
                    
                } else {

                    $sub_order = $this->getWalletRefundedAmount(array('order_id'),array('parent_order_id'=>$order_info['order_id']),'db_get_array');
                    
                    foreach ($sub_order as $key => $value) {

                        $temp_debit_check = $this->getDebitLog(array('order_id'),array('order_id'=>$value['order_id']),'db_get_field');
                        
                        if (!empty($temp_debit_check)) {
                            $debit_check=$temp_debit_check;
                        }
                    }
                }
                if (empty($debit_check)) {
                    $wallet_info=unserialize($varify_debit_order);
                    $wallet_info_current_cash = array(
                        'total_cash' => $wallet_info['current_cash']
                    );
                    $this->updateWalletCash($wallet_info_current_cash,array('user_id'=>$order_info['user_id']));                    

                    if ($order_info['is_parent_order'] == 'N') {
                        $this->updateOrders(array('pay_by_wallet_amount'=>$wallet_info['used_cash'],array('order_id'=>$order_info['order_id'])));
                        $this->createWalletDebitLog($order_info, $wallet_info);
                    } else {

                        $sub_orders = $this->getWalletRefundedAmount(array('order_id','total'),array('parent_order_id'=>$order_info['order_id']),'db_get_array');
                    
                        $sub_order_current_cash=$wallet_info['current_cash']+$wallet_info['used_cash'];
                        foreach ($sub_orders as $key => $sub_order) {

                            $sub_order_info = fn_get_order_info($sub_order['order_id']);
                            if (isset($sub_order_info['wallet']['used_cash']) && !empty($sub_order_info['wallet']['used_cash'])) {

                                $sub_wallet_info['used_cash'] = $sub_order_info['wallet']['used_cash'];
                                $sub_wallet_info['used_cash'] = $sub_order_info['wallet']['used_cash'];
                                if (!empty($sub_wallet_info['used_cash'])) {

                                    $sub_order_current_cash-=$sub_wallet_info['used_cash'];
                                    $sub_wallet_info['current_cash']=$sub_order_current_cash;

                                    $this->createWalletDebitLog($sub_order_info, $sub_wallet_info);
                                    $this->updateOrders(array('pay_by_wallet_amount'=>$sub_wallet_info['used_cash'],array('order_id'=>$sub_order['order_id'])));
                                    
                                }
                            }
                        }
                    }
                }
            }
        }
    
        if (isset($order_info['wallet']['used_cash']) 
        && $order_info['wallet_refunded_amount'] > 0.0 ) 
        { 
            $remaining_wallet_amount=$order_info['wallet']['used_cash']-$order_info['wallet_refunded_amount'];

            if ($remaining_wallet_amount>0) {

                if ($order_info['total']<=$remaining_wallet_amount) {

                    $credit_wallet_amount = $remaining_wallet_amount-$order_info['total'];

                    $order_info['payment_id'] = 0;
                    $order_info['payment_method'] = array();
                    $this->updateOrders(array('pay_by_wallet_amount'=>$order_info['total']),array('order_id'=>$order_info['order_id']));                    

                    if ($credit_wallet_amount > 0) {

                        $order_info['wallet']['used_cash']=$order_info['total'];
                        $wallet_id = $this->getWalletCash(array('wallet_id'),array('user_id'=>$order_info['user_id']),'db_get_field');                        

                        $user_wallet_amount = $this->getWalletCash(array('total_cash'),array('wallet_id'=>$wallet_id),'db_get_field');                        

                        if(fn_allowed_for('ULTIMATE')) {
                            $company_id = Registry::get('runtime.company_id');
                        } else {
                            $company_id = Registry::get('runtime.company_id');
                        }

                        $_data = array(    
                            'source'         => "order_edit",
                            'source_id'      => $order_info['order_id'],
                            'wallet_id'      => $wallet_id,
                            'credit_amount'  => $credit_wallet_amount,
                            'total_amount'   => $credit_wallet_amount + $user_wallet_amount,
                            'timestamp'      => TIME,
                            'company_id'     => $company_id,
                            'refund_reason'  =>'by_edit_order',
                        );
                        
                        $wallet_credit_log_id = $this->insertWalletCreditLog($_data);                        
                        $tran_data = array(
                            'credit_id' => $wallet_credit_log_id,
                            'wallet_id' => $wallet_id,
                            'timestamp' => TIME,
                        );
                        $this->insertWalletTransaction($tran_data);                        
    
                        $this->creditWalletNotification($wallet_credit_log_id);
                        $data = array(
                            'total_cash' => $credit_wallet_amount + $user_wallet_amount
                        );
                        $this->updateWalletCash($data,array('user_id'=>$order_info['user_id']));                        
                    }

                } else {
                    $this->updateOrders(array('wallet_refunded_amount'=>0.0),array('order_id'=>$order_info['order_id']));
                    $this->updateOrders(array('pay_by_wallet_amount'=>$remaining_wallet_amount),array('order_id'=>$order_info['order_id']));
                    
                }

            } else {
                $this->updateOrders(array('wallet_refunded_amount'=>0.0),array('order_id'=>$order_info['order_id']));
                $this->updateOrders(array('pay_by_wallet_amount'=>0.0),array('order_id'=>$order_info['order_id']));
            }
        }
    
        if(isset($order_info['payment_info']['order_status']) && $order_info['payment_info']['order_status'] == 'N' )
        {  
            $sub_orders = $this->getWalletRefundedAmount(array('order_id'),array('parent_order_id'=>$order_info['order_id']),'db_get_array');           
          
            foreach($sub_orders as $key=> $sub_order )
            {
                $wallet_id = $this->getDebitLog(array('wallet_id'),array('order_id'=>$sub_order['order_id']),'db_get_field');
                
                $debit_id = $this->getDebitLog(array('debit_id'),array('order_id'=>$sub_order['order_id']),'db_get_field');

                if(!empty($wallet_id)) {

                    $debit_amount = $this->getDebitLog(array('debit_amount'),array('order_id'=>$sub_order['order_id']),'db_get_field');                    
                    $current_wallet_amount = $this->getWalletAmount($wallet_id, null);

                    $updated_cash = array(
                        'total_cash' => $current_wallet_amount+$debit_amount
                    );

                    $add_amount = $this->updateWalletCash($updated_cash,array('wallet_id'=>$wallet_id));                    

                    if($add_amount) {
                        $this->deleteRecords('wallet_transaction',array('debit_id'=>$debit_id));
                        $this->deleteRecords('wallet_debit_log',array('order_id'=>$sub_order['order_id']));
                        
                    }
                }
            }    
        }
    }
}