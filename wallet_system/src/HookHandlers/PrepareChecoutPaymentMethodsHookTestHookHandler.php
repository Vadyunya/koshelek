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

class PrepareChecoutPaymentMethodsHookTestHookHandler extends WalletSystemModel {
    public function prepareChecoutPaymentMethodsHookTest($cart, $auth, &$payment_groups) {
        if(isset($_SESSION['cart']['wallet_system']) && !empty($_SESSION['cart']['wallet_system'])){
            $getConfig22 = $this->getSettingsData();
            $rest_payment_data = array();
            if(isset($getConfig22['wk_stop_data']) && !empty($getConfig22['wk_stop_data'])){
                foreach($getConfig22['wk_stop_data'] as $data=>$value){
                    $rest_payment_data = array_merge($rest_payment_data,[$value]);
                }
            }
            if ($rest_payment_data) {
                foreach ($payment_groups as $g_key => $group) {
                    foreach ($group as $p_key => $payment) {
                        if (in_array($payment['payment_id'], $rest_payment_data)) {
                            unset($payment_groups[$g_key][$p_key]);
                        }
                    }
                    if (empty($payment_groups[$g_key])) {
                        unset($payment_groups[$g_key]);
                    }
                }
            } 
        }

}
}