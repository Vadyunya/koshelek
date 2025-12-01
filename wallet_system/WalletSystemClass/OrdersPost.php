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
use Tygh\Notifications\EventIdProviders\OrderProvider;
use Tygh\Pdf;
use Tygh\Registry;
use Tygh\Shippings\Shippings;
use Tygh\Storage;
use Tygh\Tygh;
use Tygh\Tools\Url;

class OrdersPost extends BaseController {
     /**
     * OrdersPost constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);
        $this->setRunMode(array('details','print_invoice','print_packing_slip','manage'));
        if(in_array($this->mode,$this->runMode)){
            $this->$mode();
        }
    }

    public function details() {

        $order_info = fn_get_order_info($_REQUEST['order_id']);
        
        if(isset($order_info['gift_certificates'])) {
        } else { 	
            if(empty($order_info['products'])) {

                Registry::get('view')->assign('wallet_recharge',"yes");
            }
        } 
        if(fn_check_permissions('rma','manage', 'admin', 'POST') 
        && fn_check_permissions('rma','update', 'admin', 'POST')) 
        {
            Registry::get('view')->assign('show_wallet_refund',"yes");
        } 
    }

    public function print_invoice() {
        if (!empty($_REQUEST['order_id'])) {
            fn_print_order_invoices($_REQUEST['order_id'], !empty($_REQUEST['format']) && $_REQUEST['format'] == 'pdf');
        }
    }

    public function print_packing_slip() {
        if (!empty($_REQUEST['order_id'])) {
            fn_print_order_invoices($_REQUEST['order_id'], !empty($_REQUEST['format']) && $_REQUEST['format'] == 'pdf');
        }
    }

    public function manage() {

        $params = $_REQUEST;
    
        list($orders, $search, $totals) = fn_get_orders($params, Registry::get('settings.Appearance.admin_elements_per_page'), true);

        Tygh::$app['view']->assign('totals', $totals);
    }
}
?>
