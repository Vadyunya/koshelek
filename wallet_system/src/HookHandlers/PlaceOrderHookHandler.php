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

class PlaceOrderHookHandler extends WalletSystemModel {

    public function placeOrder(&$order_id, &$action, &$order_status, &$cart)
    {
        if (isset($cart['wallet_system']) && !empty($cart['wallet_system'])) {
            $order_info = fn_get_order_info($order_id);
            if (empty($order_info['payment_data']['processor'])) {
                $data=array(
                'wallet_id' => $this->getUserWalletId($order_info['user_id']),'order_id' => $order_id);
                
                $this->replaceWalletOffline($data);
            }
            
        }
    
        if (isset($cart['wallet']['used_cash']) && !empty($cart['wallet']['used_cash'])) {         
            $data=array(
                'data' => serialize($cart['wallet']),
                'order_id' => $order_id,
                'type' => 'N'
            );

            $this->replaceOrderdata($data);

        } else {

            $data = $this->getOrderData(array('data'),array('order_id' => $order_id,'type'=>'N'),'db_get_field');

            if (!empty($data)) {
                $this->deleteOrderData(array('order_id'=>$order_id,'type'=>'N'));                
            }
        }
    }
}