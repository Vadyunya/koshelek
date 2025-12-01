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

class PlaceSubordersHookHandler{

    public function placeSuborders(&$cart, &$sub_order_cart)
    {
        if (!empty($cart['wallet']['used_cash'])) {
            $sub_order_cart['total']+=$cart['wallet']['used_cash'];
            $suborder_total=$sub_order_cart['total']+$sub_order_cart['payment_surcharge'];
            $get_order_perchentage=(($suborder_total*100)/($cart['wallet']['used_cash']+$cart['total']));
            $sub_order_cart['wallet']['used_cash']=($get_order_perchentage*$cart['wallet']['used_cash'])/100;
            $sub_order_cart['total']-=$sub_order_cart['wallet']['used_cash'];
        }
    }
}