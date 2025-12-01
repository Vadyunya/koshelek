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

use WalletSystemModel\WalletSystemModel;

class ChangeOrderStatusPostHookHandler extends WalletSystemModel{

    public function __construct()
    {
        parent::__construct();
    }

    public function changeOrderStatusPost($order_id, $status_to, $status_from, $force_notification, $place_order, $order_info, $edp_data) {
    
        $order_info = fn_get_order_info($order_id);        
        if( ($order_info['pay_by_wallet_amount'] > 0) && ($status_to== 'I' || $status_to== 'D'))
        {
            
            if(fn_allowed_for('ULTIMATE')){
                $company_id = $order_info['company_id'];
            } else {
                $company_id = $order_info['company_id'];
            }

            $user_wallet_current_cash = $this->getWalletAmount($wallet_id=null, $order_info['user_id']);

            $wallet_id=$this->getUserWalletId($order_info['user_id']);

            $get_order_refunded_amount=fn_format_price(
                $this->getWalletRefundedAmount(array('wallet_refunded_amount'),array('order_id'=>$order_info['order_id']),'db_get_field')
            );
            
            $remain_amount_to_be_refunded = $order_info['pay_by_wallet_amount'] - fn_format_price($get_order_refunded_amount);
            
            if($remain_amount_to_be_refunded > 0) {

                $user_wallet_updated_cash = $user_wallet_current_cash + $remain_amount_to_be_refunded;                
                $updated_cash = array(
                    'total_cash' => $user_wallet_updated_cash
                );

                $this->updateWalletCash($updated_cash,array('wallet_id'=>$wallet_id));                
                $user_updated_order_refund = $get_order_refunded_amount+$remain_amount_to_be_refunded;

                $order_wallet_data = array(
                    'wallet_refunded_amount' => $user_updated_order_refund
                );

                $this->updateOrders($order_wallet_data,array('order_id'=>$order_info['order_id']));                
    
                $_data = array(
                    'source'         => "refund_cancelled_order",
                    'source_id'      => $order_info['order_id'],
                    'wallet_id'      => $wallet_id,
                    'credit_amount'  => $remain_amount_to_be_refunded,
                    'total_amount'   => $user_wallet_updated_cash,
                    'timestamp'      => TIME,
                    'refund_reason'  => 'Canceled Product',
                    'company_id'     => $company_id,
                );

                $wallet_credit_log_id = $this->insertWalletCreditLog($_data);
                
                $tran_data = array(
                    'credit_id' => $wallet_credit_log_id,
                    'wallet_id' => $wallet_id,
                    'timestamp' => TIME,
                );

                $this->insertWalletTransaction($tran_data);                
                fn_set_notification('N', __('wallet_refund'), __('amount_has_been_refunded_in_user_wallet after_cancelation_of_product'));

                $this->walletAmountOrderRefundNotification($wallet_credit_log_id);

            }
    
            else{
                
            }
        }
    
        else{
    
        }
    }
}