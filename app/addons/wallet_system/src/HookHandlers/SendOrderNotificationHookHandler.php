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

class SendOrderNotificationHookHandler extends WalletSystemModel{

    public function sendOrderNotification(&$order_info, $edp_data, $force_notification, $notified, $send_order_notification) {

        $get_wallet_order_data = $this->getOrderData(array('data'),array('order_id'=>$order_info['order_id'],'type'=>'N'),'db_get_field');        
    
        $get_wallet_order_data = unserialize($get_wallet_order_data);
        
        if (isset($get_wallet_order_data['used_cash'])) {
            $order_info['total']+=fn_format_price($get_wallet_order_data['used_cash']);
        }
    }
}