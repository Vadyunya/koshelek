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

class PrePlaceOrderHookHandler{

    public function prePlaceOrder(&$cart, &$allow) {

        if (isset($cart['order_id'])) {

            $order_info=fn_get_order_info($cart['order_id']);
            if (isset($order_info['wallet']['used_cash'])) {

                if ($cart['total']<=$order_info['wallet']['used_cash']) {

                    $cart['payment_id']=0;
                    unset($cart['payment_info']);
                }
            }
        }

        if(isset($cart['wallet']['used_cash'])) {
            $cart['total']+=$cart['wallet']['used_cash'];            
        }
    }
}