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

class MvePlaceOrderHookHandler extends WalletSystemModel{

    public function mvePlaceOrder(&$order_info, $company_data, $action, $__order_status, $cart, &$_data) {

        if (isset($cart['wallet']['used_cash']) 
        && !empty($cart['wallet']['used_cash'])) 
        {
            $_data['order_amount']+=fn_format_price($cart['wallet']['used_cash']);

            $order_info['total']+=$cart['wallet']['used_cash'];

            $company_data = fn_get_company_data($order_info['company_id']);

            if (isset($company_data['plan_id']) && !empty($company_data['plan_id'])) {

                $addons = Registry::get('addons');
                if (isset($addons['vendor_plans']['status']) && $addons['vendor_plans']['status'] == 'A') {
                    
                    $company_data = $this->getVendorCommissionData($order_info, $company_data['plan_id']);
                    $commission_amount = 0;
                    if ($company_data['commission_type'] == 'P') {
                        //Calculate commission amount and check if we need to include shipping cost
                        $commission_amount = (($_data['order_amount'] - (Registry::get('settings.Vendors.include_shipping') == 'N' ?  $order_info['shipping_cost'] : 0)) * $company_data['commission'])/100;
                    } else {
                        $commission_amount = $company_data['commission'];
                    }
                    $_data['commission_amount']=$commission_amount;
                    $_data['commission'] = $company_data['commission'];
                    $_data['commission_type'] = $company_data['commission_type'];
                }
            }
        }
    }
}