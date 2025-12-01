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

class GetOrdersPostHookHandler extends WalletSystemModel {

    public function getOrdersPost($params, &$orders)
    {   
        foreach ($orders as $key => $order) {
            $get_wallet_order_data = $this->getOrderData(array('data'),array('order_id'=>$order['order_id'],'type'=>'N'),'db_get_field');
            
            $get_wallet_order_data = unserialize($get_wallet_order_data);
            
            if (isset($get_wallet_order_data['used_cash'])) {
                $current_controller=Registry::get('runtime.controller');
                
                $current_mode=Registry::get('runtime.mode');
                  
                if ($current_controller == 'orders' || $current_controller == 'index') {
                    if ($current_mode == 'manage' || $current_mode == 'search' || $current_mode == 'index') {                   
                        // $order['total']+=fn_format_price($get_wallet_order_data['used_cash']);
                    }
                }
            }
            $orders[$key]=$order;
        }
    }
}