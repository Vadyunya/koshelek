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

class PreUpdateOrderHookHandler{

    public function preUpdateOrder(&$cart, $order_id) {
        // if(isset($cart['wallet']['used_cash']) && !empty($cart['wallet']['used_cash']))
        // {
        //  $cart['total']+=$cart['wallet']['used_cash'];
        // }
    }
    
}