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

class PreAddToCartHookHandler{

    public function preAddToCart(&$product_data, &$cart, &$auth, &$update) {
        if (!empty($cart['wallet_system'])) {
            fn_set_notification('W', 'Warning', __('wallet_recharge_with_products_not_accepted'));
    
            $product_data = array();
        }
    }
}