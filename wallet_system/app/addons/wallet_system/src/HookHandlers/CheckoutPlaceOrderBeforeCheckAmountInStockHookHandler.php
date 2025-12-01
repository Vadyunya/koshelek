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

class CheckoutPlaceOrderBeforeCheckAmountInStockHookHandler extends WalletSystemModel{

    public function __construct()
    {
        parent::__construct();
    }

    public function checkoutPlaceOrderBeforeCheckAmountInStock($cart,$auth)
    { 
        if(isset($cart['wallet']['used_cash']))
        {  
            $current_wallet_amount = $this->getWalletAmount('',$auth['user_id']);
            if($cart['wallet']['used_cash']>$current_wallet_amount)
            {  
                $cart['wallet']['used_cash'] = $current_wallet_amount;

                fn_set_notification('W',__('warning'),__("your_current_cash_less_than_order_total"));
                unset($_SESSION['cart']['wallet']);

                $_SESSION['cart']['wallet']['current_cash'] = $this->getWalletAmount(null, $_SESSION['auth']['user_id']);
                fn_redirect('checkout.checkout');
            }
        }
    }
}