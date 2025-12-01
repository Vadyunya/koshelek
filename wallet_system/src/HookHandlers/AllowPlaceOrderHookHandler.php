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

class AllowPlaceOrderHookHandler{

    public function allowPlaceOrder(&$total, &$cart) {
        if (isset($cart['wallet_system'])) {
            // Need to skip shipping
            $cart['shipping_failed'] = false;
            $cart['company_shipping_failed'] = false;
        }
    }
}