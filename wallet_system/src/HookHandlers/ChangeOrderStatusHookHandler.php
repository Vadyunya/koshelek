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

class ChangeOrderStatusHookHandler extends WalletSystemModel {

    public function __construct()
    {
        parent::__construct();
    }

    public function changeOrderStatus($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order) {        
        if (!empty($order_info['payment_method'])) {
            if (empty($order_info['payment_method']['processor'])) {
                if ($status_to == 'C') {
                    $user_id = $order_info['user_id'];
                    $order_id = $order_info['order_id'];
                    $wallet_id = $this->getUserWalletId($user_id);

                    $user_wallet_amount = $this->getWalletAmount($wallet_id, null);
                    
                    $check_offline_order = $this->getWalletOfflinePayment(array('*'),array('order_id'=>$order_id,'status'=>'no'),'db_get_array');
                    
                    if (isset($order_info['payment_surcharge']) && !empty($order_info['payment_surcharge'])) {
                        $credit_amount = $order_info['total'] - $order_info['payment_surcharge'];
                    } else {
                        $credit_amount = $order_info['total'];
                    }
    
                    if(fn_allowed_for('ULTIMATE')) {
                        $company_id = $order_info['company_id'];
                    } else {
                        $company_id = $order_info['company_id'];
                    }
                            
                    if (!empty($check_offline_order)) {
                        $_data = array(    
                            'source'         => "recharge",
                            'source_id'      => $order_info['order_id'],
                            'wallet_id'      => $wallet_id,
                            'credit_amount'  => $credit_amount,
                            'company_id'     => $company_id,
                            'total_amount'   => $credit_amount+$user_wallet_amount,
                            'timestamp'      => TIME,
                        );

                        $wallet_credit_log_id = $this->insertWalletCreditLog($_data);                        
                        $tran_data = array(
                            'credit_id' => $wallet_credit_log_id,
                            'wallet_id' => $wallet_id,
                            'timestamp' => TIME,
                        );

                        $this->insertWalletTransaction($tran_data);                        
    
                        $this->walletRechargeNotification($wallet_credit_log_id);

                        $data = array(
                            'total_cash' => $credit_amount + $user_wallet_amount
                        );
                        
                        $this->updateWalletCash($data,array('user_id'=>$user_id));
                        $this->updateWalletOfflinePayment(array('status'=>'yes'),array('order_id'=>$order_id));
                            
                        fn_set_notification("N", __("wallet_recharge"), __("money_added_in_user_wallet",array(
                            '[text_amt]' => $credit_amount)));
                    }
                }
            }

            if ($status_to == 'C') {

                $wk_promotions = $this->getWalletRefundedAmount(array('promotions'),array('order_id'=>$order_info['order_id']),'db_get_field');
                
                $order_total=$order_info['total'];
                if (!empty($wk_promotions)) {

                    $user_id= $order_info['user_id'];
                    $wallet_id=$this->getUserWalletId($user_id);

                    if (!empty($wallet_id)) {

                        $cash_back_amount=$this->applyPromotionBonous($wk_promotions, $order_total);

                        $user_wallet_amount = $this->getWalletAmount($wallet_id, null);
                        if ($cash_back_amount>0) {
                            if(fn_allowed_for('ULTIMATE'))
                            {
                                $company_id = $order_info['company_id'];
                            }
                            else
                            {
                                $company_id = $order_info['company_id'];
                            }
    
                            $_data = array(
    
                            'source'         => "cash back",
                            'source_id'      => $order_info['order_id'],
                            'wallet_id'      => $wallet_id,
                            'credit_amount'  => $cash_back_amount,
                            'total_amount'   => $cash_back_amount+$user_wallet_amount,
                            'timestamp'      => TIME,
                            'company_id'     => $company_id,
                            'refund_reason'  => 'cash back on order total'
                                                     
                            );
                            $wallet_credit_log_id = $this->insertWalletCreditLog($_data);
                            
                            $tran_data = array(
                                'credit_id' => $wallet_credit_log_id,
                                'wallet_id' => $wallet_id,
                                'timestamp' => TIME,
                            );

                            $this->insertWalletTransaction($tran_data);                            
    
                            $this->walletCashbackAmountNotification($wallet_credit_log_id);

                            $data = array(
                                'total_cash' => $cash_back_amount + $user_wallet_amount
                            );
                            
                            $this->updateWalletCash($data, array('user_id'=>$user_id));                            

                            fn_set_notification("N", __("wallet_recharge"), __("money_added_in_user_wallet",array(
                                '[text_amt]' => $cash_back_amount)));
                        }
                    }
                }
            }
        }
    }
}