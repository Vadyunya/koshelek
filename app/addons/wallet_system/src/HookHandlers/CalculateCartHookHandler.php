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

class CalculateCartHookHandler{

    /**
     * @var object this variable is used to run the model method in class.
     */
    protected $loadModel;

    /**
     * @var string mode is the method of the class.
     */
    protected $auth;


    function calculateCart(&$cart, &$cart_products, &$auth) {
        if (isset($cart['wallet_system'])) {
            foreach ($cart['wallet_system'] as $cart_id => $wallet_data) {
                $cart['amount'] = 1;
                $cart['total'] = $wallet_data['recharge_amount'];
                $cart['subtotal'] = $wallet_data['recharge_amount'];
                $cart['display_subtotal'] = $wallet_data['recharge_amount'];

                $cart['pure_subtotal'] = $wallet_data['recharge_amount'];
            }
            $cart['shipping_failed'] = false;
            $cart['company_shipping_failed'] = false;
        }

        if (!empty($cart['wallet']['used_cash'])) {
            
            $this->loadModel = new WalletSystemModel();
            $this->auth = $_SESSION['auth'];
            
            $current_wallet_cash = $this->loadModel->getWalletAmount(null,$this->auth['user_id']);
            $cart_total = $_SESSION['cart']['total'];

            if($cart_total >= $current_wallet_cash) {

                $_SESSION['cart']['wallet']['current_cash']= 0.0;
                $_SESSION['cart']['wallet']['used_cash']= $current_wallet_cash;
            } else {

                $_SESSION['cart']['wallet']['current_cash']= $current_wallet_cash - $cart_total;
                $_SESSION['cart']['wallet']['used_cash']= $cart_total;
            }


            $cart['total']-=$cart['wallet']['used_cash'];
        }
    }



}