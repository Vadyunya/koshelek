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



namespace WalletSystemClass;
use Tygh\Registry;

class RmaPost extends BaseController {
    /**
     * RmaPost constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);
        $this->setRunMode('add_wallet');
        if(in_array($this->mode,$this->runMode)){
            $this->$mode();
        }
    }

    public function add_wallet() {

        $change_return_status = $_REQUEST['change_return_status'];
        $return_info = fn_get_return_info($change_return_status['return_id']);
        $order_info= fn_get_order_info($return_info['order_id']);
        
        // fn_print_r($change_return_status,$return_info,$order_info);
        // fn_print_r($_REQUEST);
        if (!empty($_REQUEST['accepted'])) {
            
            $total = 0;
            foreach ((array) $_REQUEST['accepted'] as $item_id => $v) {

                if (isset($v['chosen']) && $v['chosen'] == 'Y') {
                    $total += $v['amount'] * $return_info['items'][RETURN_PRODUCT_ACCEPTED][$item_id]['price'];
                }
            }
            
            $get_order_refunded_amount = $this->loadModel->getWalletRefundedAmount(array('wallet_refunded_amount'),array('order_id'=>$return_info['order_id']),'db_get_field');
            

            $remain_amount_to_be_refunded=$order_info['subtotal']-$get_order_refunded_amount;
            $remain_amount_to_be_refunded = round($remain_amount_to_be_refunded,2);
            // fn_print_r($total,$get_order_refunded_amount,$remain_amount_to_be_refunded);
            if($total > $remain_amount_to_be_refunded) {

                // fn_print_r("hello");
                fn_set_notification('w',__("warning"),__("can_not_refunded"));
                fn_set_notification('N',__("remain_amount"),__("remain_unrefunded_amount_regarding_this_order_is").$_SESSION['settings']['secondary_currencyA']['value']." ".$remain_amount_to_be_refunded,true);
                $this->response = array(CONTROLLER_STATUS_REDIRECT, "rma.details?return_id=$change_return_status[return_id]");
            }
        
            if (!empty($total)) {
                // fn_print_r("hello");

                $wallet_credit_id = $this->loadModel->createReturnWallet($return_info['order_id'], fn_format_price($total), $change_return_status['return_id'], $return_info['user_id']);
                    
            if(!empty($wallet_credit_id)) {

                $return_info['extra'] = unserialize($return_info['extra']);
                if (!isset($return_info['extra']['wallet'])) {
                    $return_info['extra']['wallet'] = array();
                }

                $return_info['extra']['wallet'] = fn_array_merge(
                    $return_info['extra']['wallet'], 
                    array($wallet_credit_id => array(
                        'amount' => fn_format_price($total))
                    )
                );

                $_data = array('extra' => serialize($return_info['extra']));

                $this->loadModel->updateOrders(array('wallet_refunded_amount'=> $get_order_refunded_amount+$total),array('order_id'=>$order_info['order_id']));

                $this->loadModel->updateRmareturnOrders($_data,array('return_id'=>$change_return_status['return_id']));                
            }
            }
        }
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "rma.details?return_id=$change_return_status[return_id]");
    }
}
?>
