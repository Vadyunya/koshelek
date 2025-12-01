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

class UpdateProfileHookHandler extends WalletSystemModel{

    public function __construct() {
        parent::__construct();
    }

    public function updateProfile(&$action, $user_data, $current_user_data) {
        if (!empty($user_data['user_id']) && $action == 'add') {

            $user_id = $user_data['user_id'];
            $check_wallet = $this->getWalletCash(array('wallet_id'),array('user_id'=>$user_id),'db_get_field');            

            if (empty($check_wallet)) {

                $credit_amount = !empty($this->getSettingsData()['new_registration_amount']) ? $this->getSettingsData()['new_registration_amount'] : 0;
                $allow_credit_amount = $this->getSettingsData()['new_registration_cash_back'];

                if (!empty($credit_amount) && $credit_amount > 0 && $allow_credit_amount == 'Y') {

                    if(fn_allowed_for('ULTIMATE')) {
                        // $company_id = Registry::get('runtime.company_id');
                        $company_id = $this->getCompanyId(array('company_id'),array('user_id'=>$user_id),'db_get_field');                        
                    } else {
                        $company_id = Registry::get('runtime.company_id');
                    }

                    $new_register_wallet_recharge = array(
                        'user_id'=> $user_id,
                        'total_cash'=>$credit_amount,
                        'company_id'=>$company_id
                    );
                    
                    $wallet_id = $this->insertWalletCash($new_register_wallet_recharge);                    
    
                    $_data = array(
                        'source'         => "new registration",
                        'wallet_id'      => $wallet_id,
                        'credit_amount'  => $credit_amount,
                        'total_amount'   => $credit_amount,
                        'company_id'     => $company_id,
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

                    fn_set_notification("N", __("wallet_recharge"), __("money_added_in_user_wallet",array(
                        '[text_amt]' => $credit_amount)));
                }
            }
        }
    }
}