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

class GetExternalDiscountsHookHandler{

    public function getExternalDiscounts($product, &$discounts) {
        $current_controller=Registry::get('runtime.controller');
             
        if (isset($_SESSION['cart']['wallet']['used_cash']) 
        && $current_controller == 'checkout') 
        {
            $discounts += fn_format_price($_SESSION['cart']['wallet']['used_cash'], CART_LANGUAGE);
        }
    }
}