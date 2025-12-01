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
use Tygh\Tygh;
// include_once(Registry::get("config.dir.addons").'/wallet_system/WalletSystemClass/Common.php');

class WalletSystem extends BaseController {
     /**
     * WalletSystem constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);
        $this->$mode();
    }


    public function refund_in_wallet() {

        if (isset($_REQUEST['order_id'])) {

            $order_info = fn_get_order_info($_REQUEST['order_id']);

            $user_data=fn_get_user_info($order_info['user_id']);

            $suffix = 'orders.details&order_id='.$_REQUEST['order_id'];
            
            if(!empty($user_data)) {
        
                Registry::get('view')->assign('order_id', $order_info['order_id']);
                
                if (isset($_REQUEST['refund_amount'])) {

                    Registry::get('view')->assign('amount', $_REQUEST['refund_amount']);

                } else {

                    if (isset($order_info['wallet_refunded_amount']) 
                    && !empty($order_info['wallet_refunded_amount'])) 
                    {
                        Registry::get('view')->assign('amount', $order_info['pay_by_wallet_amount']-$order_info['wallet_refunded_amount']);
                        Registry::get('view')->assign('shipping_cost', $order_info['shipping_cost']);

                    } else {

                        Registry::get('view')->assign('amount', $order_info['pay_by_wallet_amount']);
                        Registry::get('view')->assign('shipping_cost', $order_info['shipping_cost']);

                    }
                }

                if (isset($_REQUEST['refund_reason'])) {

                    Registry::get('view')->assign('reason', $_REQUEST['refund_reason']);
                }

            } else {
                
                fn_set_notification('W', __('warning'), __('user_does_not_exist'));
                $this->response = array(CONTROLLER_STATUS_REDIRECT,$suffix);
            }
        }
    }

    public function refund() {

        $currencies = fn_get_currencies_list();
        
        foreach ($currencies as $key => $value) {
            if ($value['is_primary'] == 'Y') {
                $wk_currency = $value['symbol'];
            }
        }

        $suffix = 'wallet_system.refund_in_wallet?order_id='. $_REQUEST['wallet_refund']['order_id'].'&refund_amount='. $_REQUEST['wallet_refund']['refund_amount'].'&refund_reason='. $_REQUEST['wallet_refund']['refund_reason'];

        $_REQUEST['wallet_refund']['refund_reason'] = str_replace(' ', '', $_REQUEST['wallet_refund']['refund_reason']);

        if(strlen(trim($_REQUEST['wallet_refund']['refund_reason'])) < 1 
        || strlen(trim($_REQUEST['wallet_refund']['refund_reason'])) > 10) 
        {
            fn_set_notification('W',__("error"),__("text_reason_limit",array('[text_start]'=>'1','[text_limit]'=>10)));      
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }

        if(empty($_REQUEST['wallet_refund']['refund_amount'])) 
        {
            fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                '[text_error]'=>'Refund'
              )));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }
        
        if (!empty($_REQUEST['wallet_refund']['order_id']) 
        && !empty($_REQUEST['wallet_refund']['refund_reason']) 
        && !empty($_REQUEST['wallet_refund']['refund_amount'])) 
        {

            $order_info=fn_get_order_info($_REQUEST['wallet_refund']['order_id']);
            if(fn_allowed_for('ULTIMATE')) {

                $company_id = $order_info['company_id'];
            } else {

                $company_id = $order_info['company_id'];
            }
            $user_wallet_current_cash = $this->loadModel->getWalletAmount($wallet_id=null, $order_info['user_id']);
            
            $wallet_id = $this->loadModel->getUserWalletId($order_info['user_id']);

            $min = !empty($this->loadModel->getSettingsData()['min_refund_amount']) ? $this->loadModel->getSettingsData()['min_refund_amount'] : 0;
            $max = !empty($this->loadModel->getSettingsData()['max_refund_amount']) ? $this->loadModel->getSettingsData()['max_refund_amount'] : 0;
            
            if ($_REQUEST['wallet_refund']['refund_amount'] < $min 
            || $_REQUEST['wallet_refund']['refund_amount'] > $max) 
            {
                fn_set_notification('W', __('wallet_error'), __('can_not_add_money_in_wallet_please_check_refund_limit_in_addon_setting'));
                fn_set_notification("W", __("warning"), __("wallet_limit_is").$min.__("_to_").$max);
    
                $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
                return false;
            }
  
            $get_order_refunded_amount = fn_format_price($this->loadModel->getWalletRefundedAmount(array('wallet_refunded_amount'),array('order_id'=>$order_info['order_id']),'db_get_field'));
            
            $remain_amount_to_be_refunded=$order_info['pay_by_wallet_amount'] - fn_format_price($get_order_refunded_amount);
    
            if ($_REQUEST['wallet_refund']['refund_amount'] > fn_format_price($remain_amount_to_be_refunded)) {

                fn_set_notification('w', __("warning"), __("can_not_refunded"));
                fn_set_notification('N', __("remain_amount"), __("remain_unrefunded_amount_regarding_this_order_is").$this->loadModel->walletFormatPrice($remain_amount_to_be_refunded));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
                
            } else {
                $user_wallet_updated_cash=$user_wallet_current_cash+$_REQUEST['wallet_refund']['refund_amount'];
          
                $updated_cash = array(
                    'total_cash' => $user_wallet_updated_cash
                );

                $this->loadModel->updateWalletCash($updated_cash,array('wallet_id'=>$wallet_id));            

                $user_updated_order_refund = $get_order_refunded_amount+$_REQUEST['wallet_refund']['refund_amount'];
                
                $order_wallet_data = array(
                    'wallet_refunded_amount' => $user_updated_order_refund
                );

                $this->loadModel->updateOrders($order_wallet_data,array('order_id'=>$order_info['order_id']));            
    
                $_data = array(
                    'source'        => "refund_order",
                    'source_id'     => $order_info['order_id'],
                    'wallet_id'     => $wallet_id,
                    'credit_amount' => $_REQUEST['wallet_refund']['refund_amount'],
                    'total_amount'  => $user_wallet_updated_cash,
                    'timestamp'     => TIME,
                    'refund_reason' => $_REQUEST['wallet_refund']['refund_reason'],
                    'company_id'    => $company_id,
                );

                $wallet_credit_log_id = $this->loadModel->insertWalletCreditLog($_data);           

                $tran_data=array(
                    'credit_id' => $wallet_credit_log_id,
                    'wallet_id' => $wallet_id,
                    'timestamp' => TIME,
                );

                $this->loadModel->insertWalletTransaction($tran_data);            
        
                $this->loadModel->creditWalletNotification($wallet_credit_log_id);
                        
                fn_set_notification('N', __('wallet_refund'), __('amount_has_been_refunded_in_user_wallet'));
                        
                $this->response = array(CONTROLLER_STATUS_REDIRECT, 'wallet_system.wallet_transaction');
            }
    
        } else {
            fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }
    }

    public function wallet_transaction() {

        
        $this->loadModel->generateSections('wallet_transaction');

        list($wallet_transaction, $search) = $this->loadModel->walletTransactions($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));
        
        Registry::get('view')->assign('wallet_transaction', $wallet_transaction);
        Registry::get('view')->assign('search', $search);
        // fn_print_r($_REQUEST);
        if (isset($_REQUEST['email']) && 
            !empty($_REQUEST['email']) && strlen(trim($_REQUEST['email'])) > 0) 
        {

            $getEmail = $this->loadModel->getUserIdOfEmail(trim($_REQUEST['email']));

            $wallet_id = $this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$getEmail),'db_get_field');

            $credit_total = $this->loadModel->getCreditLog(array('SUM(credit_amount)'),array('wallet_id'=>$wallet_id),'db_get_field');

            $debit_total = $this->loadModel->getDebitLog(array('SUM(debit_amount)'),array('wallet_id'=>$wallet_id),'db_get_field');

        } else {

            if(!empty(Registry::get('runtime.company_id'))) {
                $company_id = Registry::get('runtime.company_id');

                $credit_total = $this->loadModel->getCreditLog(array('SUM(credit_amount)'),array('company_id'=>$company_id),'db_get_field');
                
                $debit_total = $this->loadModel->getDebitLog(array('SUM(debit_amount)'),array('company_id'=>$company_id),'db_get_field');
                
                
            } else {

                $credit_total = $this->loadModel->getCreditLog(array('SUM(credit_amount)'),array(),'db_get_field');
                
                $debit_total = $this->loadModel->getDebitLog(array('SUM(debit_amount)'),array(),'db_get_field');
                
            }

        }

        Registry::get('view')->assign('credit_total', $credit_total);
        Registry::get('view')->assign('debit_total', $debit_total);
    }

    public function wallet_transactions_details_credit_data() {
        fn_add_breadcrumb(__('wallet_transactions'));

        // fn_print_die($_REQUEST);
        if ($this->auth['user_id'] == 0) {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
        list($wallet_transactions_details,$wallet_data) = $this->loadModel->walletTransactionsDetails($_REQUEST,Registry::get('settings.Appearance.elements_per_page'),$this->auth['user_id']);    
        // fn_print_r($wallet_transactions_details,$wallet_data);
        Registry::get('view')->assign('wallet_transactions_details', $wallet_transactions_details);
        Registry::get('view')->assign('wallet_data', $wallet_data);

        if(isset($wallet_transactions_details['extra_info']) && !empty($wallet_transactions_details['extra_info'])){
            try {
                if(strlen($wallet_transactions_details['extra_info']) > 30){
                $wallet_extra_info = unserialize($wallet_transactions_details['extra_info']);
                Registry::get('view')->assign('wallet_extra_info', $wallet_extra_info);
                }
            }catch(\Exception $e) {
               
            }
        }
    }


    public function wallet_transactions_details_debit_data() {
        fn_add_breadcrumb(__('wallet_transactions'));

        // fn_print_die($_REQUEST);
        if ($this->auth['user_id'] == 0) {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
        list($wallet_transactions_details,$wallet_data) = $this->loadModel->walletTransactionsDetailsDebitData($_REQUEST,Registry::get('settings.Appearance.elements_per_page'),$this->auth['user_id']);    
        Registry::get('view')->assign('wallet_transactions_details', $wallet_transactions_details);
        Registry::get('view')->assign('wallet_data', $wallet_data);

        if(isset($wallet_transactions_details['extra_info']) && !empty($wallet_transactions_details['extra_info'])){
            if(strlen($wallet_transactions_details['extra_info']) > 30){
            $wallet_extra_info = unserialize($wallet_transactions_details['extra_info']);
            Registry::get('view')->assign('wallet_extra_info', $wallet_extra_info);
            }
        }
    }


    
    public function wallet_users() {

        $this->loadModel->generateSections('wallet_users');

        
        list($wallet_users, $search) = $this->loadModel->getWalletUsers($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));
        
        Registry::get('view')->assign('wallet_users', $wallet_users);
        Registry::get('view')->assign('search', $search);
        
        if (isset($_REQUEST['email']) && !empty($_REQUEST['email']) && strlen(trim($_REQUEST['email'])) > 0) {

            $credit_total = $this->loadModel->getCreditLog(array('SUM(credit_amount)'),array('wallet_id'=>$this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$this->loadModel->getUserIdOfEmail(trim($_REQUEST['email'])) ),'db_get_field')),'db_get_field');

            $debit_total = $this->loadModel->getDebitLog(array('SUM(debit_amount)'),array('wallet_id'=>$this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$this->loadModel->getUserIdOfEmail(trim($_REQUEST['email']))),'db_get_field') ),'db_get_field');

        } else {

            if(!empty(Registry::get('runtime.company_id'))) {

                $company_id = Registry::get('runtime.company_id');

                $credit_total = $this->loadModel->getCreditLog(array('SUM(credit_amount)'),array('company_id'=> $company_id ),'db_get_field');
                $debit_total  = $this->loadModel->getDebitLog(array('SUM(debit_amount)'),array('company_id'=> $company_id ),'db_get_field');

            } else {

                $credit_total = $this->loadModel->getCreditLog(array('SUM(credit_amount)'),array(),'db_get_field');
                $debit_total  = $this->loadModel->getDebitLog(array('SUM(debit_amount)'),array(),'db_get_field');
            }
        }

        Registry::get('view')->assign('credit_total', $credit_total);
        Registry::get('view')->assign('debit_total', $debit_total);
    }

    public function debit_wallet_manually() {

        if (isset($_REQUEST['wallet_id']) && !empty($_REQUEST['wallet_id'])) {
            Registry::get('view')->assign('wallet_id', $_REQUEST['wallet_id']);
        }
    }

    public function credit_wallet_manually() {

        if (isset($_REQUEST['wallet_id']) && !empty($_REQUEST['wallet_id'])) {
            Registry::get('view')->assign('wallet_id', $_REQUEST['wallet_id']);
        }
    }

    public function credit_wallet() {

        $suffix = 'wallet_system.credit_wallet_manually?wallet_id='. $_REQUEST['wallet_credit']['wallet_id'].'&credit_amount='. $_REQUEST['wallet_credit']['credit_amount'].'&credit_reason='. $_REQUEST['wallet_credit']['credit_reason'];
        
        if(strlen(trim($_REQUEST['wallet_credit']['credit_reason'])) < 1 || strlen(trim($_REQUEST['wallet_credit']['credit_reason'])) > 10) 
        {
            fn_set_notification('W',__("error"),__("text_reason_limit",array('[text_start]'=>'1','[text_limit]'=>10)));      
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }
        
        if($_REQUEST['wallet_credit']['credit_amount'] && !is_numeric($_REQUEST['wallet_credit']['credit_amount'])) 
        {
            
            fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                '[text_error]'=>'Credit'
              )));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }
        

        if (!empty($_REQUEST['wallet_credit']['wallet_id']) 
        && !empty($_REQUEST['wallet_credit']['credit_reason']) 
        && !empty($_REQUEST['wallet_credit']['credit_amount'])) 
        {


            $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('wallet_id'=>$_REQUEST['wallet_credit']['wallet_id']),'db_get_field');
            $user_wallet_updated_cash = $user_wallet_current_cash + (float)$_REQUEST['wallet_credit']['credit_amount'];

            if($user_wallet_updated_cash >= 100000000){
                fn_set_notification('W',__("warning"),__("you_are_using_max_ammount_in_your_wallet"));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
                return false;
            }
            // fn_print_die($user_wallet_current_cash,$user_wallet_updated_cash);
            $updated_cash = array(
                'total_cash' => $user_wallet_updated_cash
            );

            $this->loadModel->updateWalletCash($updated_cash,array('wallet_id'=>$_REQUEST['wallet_credit']['wallet_id']));            

            $_data = array(
                'source'         => "credit by admin",
                'source_id'      => 0,
                'wallet_id'      => $_REQUEST['wallet_credit']['wallet_id'],
                'credit_amount'  => $_REQUEST['wallet_credit']['credit_amount'],
                'total_amount'   => $user_wallet_updated_cash,
                'timestamp'      => TIME,
                'refund_reason'  => $_REQUEST['wallet_credit']['credit_reason'],                            
            );

            $wallet_credit_log_id = $this->loadModel->insertWalletCreditLog($_data);

            $tran_data=array(
                'credit_id' => $wallet_credit_log_id,
                'wallet_id' => $_REQUEST['wallet_credit']['wallet_id'],
                'timestamp' => TIME,
            );

            $this->loadModel->insertWalletTransaction($tran_data);            

            $this->loadModel->creditWalletNotification($wallet_credit_log_id);
                    
            fn_set_notification('N', __('wallet_credit'), 
            __('amount_has_been_credited_in_user_wallet',array(
                '[text_amt]' => $_REQUEST['wallet_credit']['credit_amount'])));

            $this->response = array(CONTROLLER_STATUS_REDIRECT, 'wallet_system.wallet_transaction');

        } else {

            fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }
    }

    public function debit_wallet() {

        $suffix = 'wallet_system.debit_wallet_manually?wallet_id='. $_REQUEST['wallet_debit']['wallet_id'].'&debit_amount='. $_REQUEST['wallet_debit']['debit_amount'].'&debit_reason='. $_REQUEST['wallet_debit']['debit_reason'];

        if(strlen(trim($_REQUEST['wallet_debit']['debit_reason'])) < 1 
        || strlen(trim($_REQUEST['wallet_debit']['debit_reason'])) > 10)
        {
            fn_set_notification('W',__("error"),__("text_reason_limit",array('[text_start]'=>'1','[text_limit]'=>10)));      
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }

        if($_REQUEST['wallet_debit']['debit_amount'] && !is_numeric($_REQUEST['wallet_debit']['debit_amount'])) 
        {
            
            fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                '[text_error]'=>'Debit'
              )));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }

        if (!empty($_REQUEST['wallet_debit']['wallet_id']) 
        && !empty($_REQUEST['wallet_debit']['debit_reason']) 
        && !empty($_REQUEST['wallet_debit']['debit_amount'])) 
        {

            $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('wallet_id'=>$_REQUEST['wallet_debit']['wallet_id']),'db_get_field');
            if ($_REQUEST['wallet_debit']['debit_amount'] > $user_wallet_current_cash) {
                fn_set_notification('W', __('warning'), __('amount_cannot_be_greater'));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
                return false;
            }

            if ($user_wallet_current_cash>0.0) {
                $user_wallet_updated_cash = $user_wallet_current_cash - (float)$_REQUEST['wallet_debit']['debit_amount'];
                $require_dabit_amount=$_REQUEST['wallet_debit']['debit_amount'];
                if ($user_wallet_updated_cash<0.0) {
                    $user_wallet_updated_cash=0.0;
                    $require_dabit_amount=$user_wallet_current_cash;
                }

                $updated_cash = array(
                    'total_cash' => $user_wallet_updated_cash
                );

                $this->loadModel->updateWalletCash($updated_cash,array('wallet_id'=>$_REQUEST['wallet_debit']['wallet_id']));                

                $_data = array(
                    'wallet_id'     => $_REQUEST['wallet_debit']['wallet_id'],
                    'debit_amount'  => $require_dabit_amount,
                    'remain_amount' => $user_wallet_updated_cash,
                    'order_id'      => '0',
                    'timestamp'     => TIME,
                    'area'          => AREA,
                    'debit_reason'  => $_REQUEST['wallet_debit']['debit_reason'],
                    'source'        => "debit by admin",
                );

                $wallet_debit_id = $this->loadModel->insertWalletDebitLog($_data);                

                $tran_data=array(
                    'debit_id' => $wallet_debit_id,
                    'wallet_id' => $_REQUEST['wallet_debit']['wallet_id'],
                    'timestamp' => TIME,
                );

                $this->loadModel->insertWalletTransaction($tran_data);                

                $this->loadModel->debitWalletNotification($wallet_debit_id);

                fn_set_notification('N', __('wallet_debit'), 
                __('amount_has_been_debited_from_user_wallet',array(
                    '[text_amt]' => $require_dabit_amount)));

                $this->response = array(CONTROLLER_STATUS_REDIRECT, 'wallet_system.wallet_transaction');

            } else {

                fn_set_notification('W', __('warning'), __('amount_is_zero'));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
                return false;
            }

        } else {

            fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }
    }


    public function group_debit_wallet() {

        $suffix = 'wallet_system.wallet_users?amount='. $_REQUEST['wallet_credit_debit']['amount'].'&reason='. $_REQUEST['wallet_credit_debit']['reason'];
        
        if(strlen(trim($_REQUEST['wallet_credit_debit']['reason'])) < 1 || strlen(trim($_REQUEST['wallet_credit_debit']['reason'])) > 10) {

            fn_set_notification('W',__("error"),__("text_reason_limit",array('[text_start]'=>'1','[text_limit]'=>10)));      
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }

        $_REQUEST['wallet_credit_debit']['reason'] = str_replace(' ', '', $_REQUEST['wallet_credit_debit']['reason']);

      
        if (!empty($_REQUEST['wallet_credit_debit']['reason']) 
        && !empty($_REQUEST['wallet_credit_debit']['amount']) 
        && !empty($_REQUEST['wallet_credit_debit']['user'])) 
        {
            $list_of_users=explode(",", $_REQUEST['wallet_credit_debit']['user']);

            $amount=$_REQUEST['wallet_credit_debit']['amount'];

            $reason=$_REQUEST['wallet_credit_debit']['reason'];

            foreach ($list_of_users as $key => $user_id) {

                $company_id = $this->loadModel->getCompanyId(array('company_id'),array('user_id'=>$user_id),'db_get_field');
                
                $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('user_id'=>$user_id),'db_get_field');

                $wallet_id = $this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$user_id),'db_get_field');

                if ($user_wallet_current_cash>0.0 && $user_wallet_current_cash>=$amount) {

                    $user_wallet_updated_cash=$user_wallet_current_cash-$amount;
                    $require_dabit_amount=$amount;
                    
                    $updated_cash = array(
                        'total_cash' => $user_wallet_updated_cash
                    );

                    $this->loadModel->updateWalletCash($updated_cash,array('user_id'=>$user_id));                    

                    $_data = array(
                        'wallet_id'     => $wallet_id,
                        'debit_amount'  => $require_dabit_amount,
                        'remain_amount' => $user_wallet_updated_cash,
                        'order_id'      => '0',
                        'timestamp'     => TIME,
                        'area'          => AREA,
                        'debit_reason'  => $reason,
                        'source'        => "debit by admin",
                        'company_id'    => $company_id,
                    );
                    
                    $wallet_debit_id = $this->loadModel->insertWalletDebitLog($_data);                    

                    $tran_data=array(
                        'debit_id'   => $wallet_debit_id,
                        'wallet_id'  => $wallet_id,
                        'timestamp'  => TIME,
                        'company_id' => $company_id,
                    );
                    
                    $this->loadModel->insertWalletTransaction($tran_data);                    

                    fn_set_notification('N', __('wallet_debit'), 
                    __('amount_has_been_debited_from_user_wallet',array(
                        '[text_amt]' => $_REQUEST['wallet_credit_debit']['amount'])));
                    $this->loadModel->adminDebitAmountNotification($wallet_debit_id);

                } else {

                    fn_set_notification('W', __('warning'), __('amount_is_zero'));
                    return false;
                }
            }

            if (defined('AJAX_REQUEST') && AJAX_REQUEST) {
                
                if (isset($_REQUEST['return_url'])) {
                    $redirect_url = $_REQUEST['return_url'];
                }
                $ajax = Tygh::$app['ajax'];
                $ajax->assign('force_redirection', $redirect_url);
                $ajax->assign('non_ajax_notifications', true);                
                
            } else {  
                $this->response = array(CONTROLLER_STATUS_REDIRECT, 'wallet_system.wallet_transaction');
            }

        } else {

            fn_set_notification('W', __('warning'), __('please_fill_all_fields'));
       
                if (isset($_REQUEST['return_url'])) {
                    $this->response = array(CONTROLLER_STATUS_REDIRECT);
                }

            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
        }
    }

    public function group_credit_wallet() {

        $suffix = 'wallet_system.wallet_users?amount='. $_REQUEST['wallet_credit_debit']['amount'].'&reason='. $_REQUEST['wallet_credit_debit']['reason'];

        if(strlen(trim($_REQUEST['wallet_credit_debit']['reason'])) < 1 || strlen(trim($_REQUEST['wallet_credit_debit']['reason'])) > 10) {

            fn_set_notification('W',__("error"),__("text_reason_limit",array('[text_start]'=>'1','[text_limit]'=>10)));      
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }

        $_REQUEST['wallet_credit_debit']['reason'] = str_replace(' ', '', $_REQUEST['wallet_credit_debit']['reason']);

        if (!empty($_REQUEST['wallet_credit_debit']['reason']) 
        && !empty($_REQUEST['wallet_credit_debit']['amount']) 
        && !empty($_REQUEST['wallet_credit_debit']['user']) ) 
        {

            
            $list_of_users=explode(",", $_REQUEST['wallet_credit_debit']['user']);

            $amount=$_REQUEST['wallet_credit_debit']['amount'];
            $reason=$_REQUEST['wallet_credit_debit']['reason'];

            foreach ($list_of_users as $key => $user_id) {

                $company_id = $this->loadModel->getCompanyId(array('company_id'),array('user_id'=>$user_id),'db_get_field');
                
                $check_wallet = $this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$user_id),'db_get_field');                

                if (empty($check_wallet)) {

                    $new_credit_wallet = array(
                        'user_id'    => $user_id,
                        'total_cash' => $amount,
                        'company_id' => $company_id
                    );

                    $wallet_id = $this->loadModel->insertWalletCash($new_credit_wallet);                    

                    $_data = array(
                        'source'        => "credit by admin",
                        'source_id'     =>0,
                        'wallet_id'     => $wallet_id,
                        'credit_amount' => $amount,
                        'total_amount'  => $amount,
                        'timestamp'     => TIME,
                        'refund_reason' => $reason,
                        'company_id'    => $company_id,
                    );
                    $wallet_credit_log_id = $this->loadModel->insertWalletCreditLog($_data);
                    
                    $tran_data = array(
                        'credit_id'  => $wallet_credit_log_id,
                        'wallet_id'  => $wallet_id,
                        'timestamp'  => TIME,
                        'company_id' => $company_id,
                    );
                    $this->loadModel->insertWalletTransaction($tran_data);                    

                    $this->loadModel->adminCreditAmountNotification($wallet_credit_log_id);

                } else {

                    $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('user_id'=>$user_id),'db_get_field');                    

                    $wallet_id = $this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$user_id),'db_get_field');                    

                    $user_wallet_updated_cash=$user_wallet_current_cash+$amount;

                    if($user_wallet_updated_cash >= 100000000){
                        fn_set_notification('W',__("warning"),__("you_are_using_max_ammount_in_your_wallet"));
                        $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
                        return false;
                    }else{
                        $updated_cash = array(
                            'total_cash' => $user_wallet_updated_cash
                        );
                        $this->loadModel->updateWalletCash($updated_cash,array('user_id'=>$user_id));                    

                        $_data = array(
                            'source'        => "credit by admin",
                            'source_id'     => 0,
                            'wallet_id'     => $wallet_id,
                            'credit_amount' => $amount,
                            'total_amount'  => $user_wallet_updated_cash,
                            'timestamp'     => TIME,
                            'refund_reason' => $reason,
                            'company_id'    => $company_id,
                        );
                        
                        $wallet_credit_log_id = $this->loadModel->insertWalletCreditLog($_data);                    

                        $tran_data = array(
                            'credit_id'  => $wallet_credit_log_id,
                            'wallet_id'  => $wallet_id,
                            'timestamp'  => TIME,
                            'company_id' => $company_id,
                        );
                        $this->loadModel->insertWalletTransaction($tran_data);                    

                        $this->loadModel->adminCreditAmountNotification($wallet_credit_log_id);
                    }
                }
            }

            fn_set_notification("N", __("wallet_recharge"), 
            __('money_added_in_user_wallet',array(
                '[text_amt]' => $amount,)));

            if (defined('AJAX_REQUEST') && AJAX_REQUEST) {
                
                if (isset($_REQUEST['return_url'])) {
                    $redirect_url = $_REQUEST['return_url'];
                }
                $ajax = Tygh::$app['ajax'];
                $ajax->assign('force_redirection', $redirect_url);
                $ajax->assign('non_ajax_notifications', true);                
                
            } else {                
                $this->response = array(CONTROLLER_STATUS_REDIRECT, 'wallet_system.wallet_transaction');
            }

            return false;

        } else {

            fn_set_notification('W', __('warning'), __('please_fill_all_fields'));

            // if (defined('AJAX_REQUEST') && AJAX_REQUEST) {
                
            //     if (isset($_REQUEST['return_url'])) {
            //         $redirect_url = $_REQUEST['return_url'];
            //     }
            //     $ajax = Tygh::$app['ajax'];
            //     $ajax->assign('force_redirection', $redirect_url);
            //     $ajax->assign('non_ajax_notifications', true);                
                
            // } else {  
                if (isset($_REQUEST['return_url'])) {

                    $this->response = array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['return_url']);
                }

                $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            // }
        }
    }
    public function export_transaction() {
        $create_csv_data_credit = array();
        $create_csv_data_debit = array();
        $wallet_data = array();
        $wallet_data2 = array();
        $credit_total = 0;
        $debit_total = 0;
        // Set the content type
        $file_add_name = date('Y_m_d', time());
        header('Content-type: application/csv');
        // Set the file name option to a filename of your choice.
        header('Content-Disposition: attachment; filename=wallet_'.$file_add_name.'.csv');
        // Set the encoding
        header("Content-Transfer-Encoding: UTF-8");

        $f = fopen('php://output', 'a'); // Configure fopen to write to the output buffer
        fputcsv($f, ["Id", "Type" , "Source" , "Amount" , "Total Amount", "Wallet Id" ,"Order Id","Company Id" ,"Reason", "Time"]);
        if(isset($_REQUEST['map_ids_credit']) && !empty($_REQUEST['map_ids_credit'])){
            foreach($_REQUEST['map_ids_credit'] as $value){
                $parma['id'] = $value; 
                $data_credit = $this->loadModel->walletTransactionsDetails($parma);
                
                if(isset($data_credit[0]) && !empty($data_credit[0])){
                    $credit_total = $credit_total + $data_credit[0]['credit_amount'];
                    fputcsv($f, [$data_credit[0]['credit_id'], "Credit" , __($data_credit[0]['source']) , $data_credit[0]['credit_amount'] , $data_credit[0]['total_amount'], $data_credit[0]['wallet_id'] ,"",fn_get_company_name($data_credit[0]['company_id']),$data_credit[0]['refund_reason'], date('Y-m-d', $data_credit[0]['timestamp'])]);
                    array_push($create_csv_data_credit,$data_credit[0]);
                }
                if(isset($data_credit[1]) && !empty($data_credit[1])){
                    $wallet_data = $data_credit[1];
                }
            }
        }
        if(isset($_REQUEST['map_ids_debit']) && !empty($_REQUEST['map_ids_debit'])){
            foreach($_REQUEST['map_ids_debit'] as $value){
                $parma['id'] = $value; 
                $data_debit = $this->loadModel->walletTransactionsDetailsDebitData($parma);
               
                if(isset($data_debit[0]) && !empty($data_debit[0])){
                    $debit_total = $debit_total + $data_debit[0]['debit_amount'];
                    fputcsv($f, [$data_debit[0]['debit_id'], "Debit" , __($data_debit[0]['source']) , $data_debit[0]['debit_amount'] , $data_debit[0]['remain_amount'], $data_debit[0]['wallet_id'] ,$data_debit[0]['order_id'], fn_get_company_name($data_debit[0]['company_id']),$data_debit[0]['debit_reason'],date('Y-m-d', $data_debit[0]['timestamp'])]);
                    array_push($create_csv_data_debit,$data_debit[0]);
                }
                if(isset($data_debit[1]) && !empty($data_debit[1])){
                    $wallet_data2 = $data_credit[1];
                }
            }
        }
        fputcsv($f, [""."","","","", "" , "" , "" , "", "" , ""]);
        fputcsv($f, ["","","","" , "" , "" , "Total", "Credit", "=" , "".$credit_total.""]);
        fputcsv($f, ["","","","","","","Total", "Debit", "=","".$debit_total.""]);
        fputcsv($f, ["","","","","","","Total", "","=", $credit_total+$debit_total]);
        // Close the file
        fclose($f);
       
        exit();
       
       
    }

    public function import_transaction() {
        $filename=$_FILES["file"]["tmp_name"];
        $filename_size = $_FILES["file"]["size"];
        $filename_name=$_FILES["file"]["name"]; 
        $extension = pathinfo($filename_name, PATHINFO_EXTENSION);  
        if(isset($extension) && !empty($extension) && $extension == 'csv'){
            if($_FILES["file"]["size"] > 0)
            {
                $file = fopen($filename, "r");
                $count = 0;
                $count_array_data = array();
                while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
                {
                    if($count >= 1){
                        if(isset($getData[0]) && !empty($getData[0])){

                            $count_array_data[] = $getData;
                        }
                    }
                    $count = $count+1;
                }
                if(!empty($count_array_data)){
                    foreach ($count_array_data as $key => $count_user_data) {


                        if(isset($count_user_data[2]) && !empty($count_user_data[2])){
                            $user_id_by_mail = $this->loadModel->select('users', 'user_id', ['email' => $count_user_data[2]], 'db_get_row');
                            if(isset($user_id_by_mail) && !empty($user_id_by_mail)){
                                $company_id = $this->loadModel->getCompanyId(array('company_id'),array('user_id'=>$user_id_by_mail['user_id']),'db_get_field');
                                $check_wallet = $this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$user_id_by_mail['user_id']),'db_get_field');
                                $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('user_id'=>$user_id_by_mail['user_id']),'db_get_field'); 
                                if(isset($count_user_data[0]) && !empty($count_user_data[0])){
                                    $amount = $count_user_data[0];
                                }
                                else{
                                    $amount = 0;
                                }
                                if(isset($count_user_data[1]) && !empty($count_user_data[1])){
                                    $reason = $count_user_data[1];
                                }
                                else{
                                    $reason = "Test";
                                }
                                $my_type_of_credit_or_debit = str_replace(' ', '', $count_user_data[3]);
                                $my_type_of_credit_or_debit = strtolower($my_type_of_credit_or_debit);
                                if(isset($my_type_of_credit_or_debit) && !empty($my_type_of_credit_or_debit) && $my_type_of_credit_or_debit == 'c'){

                                        if (empty($check_wallet)) {
                        
                                            $new_credit_wallet = array(
                                                'user_id'    => $user_id_by_mail['user_id'],
                                                'total_cash' => $amount,
                                                'company_id' => $company_id
                                            );
                        
                                            $wallet_id = $this->loadModel->insertWalletCash($new_credit_wallet);                    
                        
                                            $_data = array(
                                                'source'        => "credit by admin",
                                                'source_id'     =>0,
                                                'wallet_id'     => $wallet_id,
                                                'credit_amount' => $amount,
                                                'total_amount'  => $amount,
                                                'timestamp'     => TIME,
                                                'refund_reason' => $reason,
                                                'company_id'    => $company_id,
                                                'extra_info'    => "By CSV",
                                            );
                                            $wallet_credit_log_id = $this->loadModel->insertWalletCreditLog($_data);
                                            
                                            $tran_data = array(
                                                'credit_id'  => $wallet_credit_log_id,
                                                'wallet_id'  => $wallet_id,
                                                'timestamp'  => TIME,
                                                'company_id' => $company_id,
                                            );
                                            $this->loadModel->insertWalletTransaction($tran_data);                    
                        
                                            $this->loadModel->creditWalletNotification($wallet_credit_log_id);
                        
                                        } else {
                        
                                            $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('user_id'=>$user_id_by_mail['user_id']),'db_get_field');                    
                        
                                            $wallet_id = $this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$user_id_by_mail['user_id']),'db_get_field');                    
                        
                                            $user_wallet_updated_cash=$user_wallet_current_cash+$amount;
                        
                                            $updated_cash = array(
                                                'total_cash' => $user_wallet_updated_cash
                                            );
                                            $this->loadModel->updateWalletCash($updated_cash,array('user_id'=>$user_id_by_mail['user_id']));                    
                        
                                            $_data = array(
                                                'source'        => "credit by admin",
                                                'source_id'     => 0,
                                                'wallet_id'     => $wallet_id,
                                                'credit_amount' => $amount,
                                                'total_amount'  => $user_wallet_updated_cash,
                                                'timestamp'     => TIME,
                                                'refund_reason' => $reason,
                                                'company_id'    => $company_id,
                                                'extra_info'    => "By CSV",
                                            );
                                            
                                            $wallet_credit_log_id = $this->loadModel->insertWalletCreditLog($_data);                    
                        
                                            $tran_data = array(
                                                'credit_id'  => $wallet_credit_log_id,
                                                'wallet_id'  => $wallet_id,
                                                'timestamp'  => TIME,
                                                'company_id' => $company_id,
                                            );
                                            $this->loadModel->insertWalletTransaction($tran_data);                    
                        
                                            $this->loadModel->creditWalletNotification($wallet_credit_log_id);
                                        }
                                }
                                elseif(isset($my_type_of_credit_or_debit) && !empty($my_type_of_credit_or_debit) && $my_type_of_credit_or_debit == 'd'){
                                    $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('user_id'=>$user_id_by_mail['user_id']),'db_get_field');
                                  

                                    $wallet_id = $this->loadModel->getWalletCash(array('wallet_id'),array('user_id'=>$user_id_by_mail['user_id']),'db_get_field');
                                    if(isset($wallet_id) && empty($wallet_id)){
                                        continue;
                                    }


                                    if ($user_wallet_current_cash>0.0 && $user_wallet_current_cash>=$amount) {

                                        $user_wallet_updated_cash=$user_wallet_current_cash-$amount;
                                        $require_dabit_amount=$amount;
                                      
                                        $updated_cash = array(
                                            'total_cash' => $user_wallet_updated_cash
                                        );
                    
                                      
                                        $this->loadModel->updateWalletCash($updated_cash,array('user_id'=>$user_id_by_mail['user_id']));                    
                    
                                        $_data = array(
                                            'wallet_id'     => $wallet_id,
                                            'debit_amount'  => $require_dabit_amount,
                                            'remain_amount' => $user_wallet_updated_cash,
                                            'order_id'      => '0',
                                            'timestamp'     => TIME,
                                            'area'          => AREA,
                                            'debit_reason'  => $reason,
                                            'source'        => "debit by admin",
                                            'company_id'    => $company_id,
                                            'extra_info'    => "By CSV",
                                        );
                                        
                                        $wallet_debit_id = $this->loadModel->insertWalletDebitLog($_data);                    
                    
                                        $tran_data=array(
                                            'debit_id'   => $wallet_debit_id,
                                            'wallet_id'  => $wallet_id,
                                            'timestamp'  => TIME,
                                            'company_id' => $company_id,
                                        );
                                        
                                        $this->loadModel->insertWalletTransaction($tran_data);                    
                    
                                        fn_set_notification('N', __('wallet_debit'), 
                                        __('amount_has_been_debited_from_user_wallet',array(
                                            '[text_amt]' => $amount)));
                                        $this->loadModel->debitWalletNotification($wallet_debit_id);
                    
                                    } else {
                    
                                        fn_set_notification('W', __('warning'), __('amount_is_zero'));
                                    }
                                }
                                else{
                                    fn_set_notification('E', 'Error', "Type Not Found in CSV file");
                                }
                            }
                        }               
                                
                    }
                    fn_set_notification('N', 'Success', "Import Successfully on cs-cart Site");
                    $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transaction");
                    return true;
                }
                else{
                    fn_set_notification('W', __('warning'), __('the_file_contain_no_data_in_file'));
                    $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transaction");
                }
               
            }
            else{
                fn_set_notification('W', __('warning'), __('the_file_contain_no_data_in_file'));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transaction");
            }
        }
        else{
            fn_set_notification('W', __('warning'), __('the_file_support_only_csv_file'));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transaction");
        }
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transaction");
    }

    
    public function monthly_report_send_for_all_user(){
        $date_current_time = date('m/d/y', time());
        $wallet_all_data_user_id = $this->loadModel->select('wallet_cash', 'user_id', [], 'db_get_array');
        if(isset($wallet_all_data_user_id) && !empty($wallet_all_data_user_id)){
            $wallet_setting_data = $this->helper->fnGetWalletSystemSettingDataForAll(0);
            if(isset($wallet_setting_data['report']) && !empty($wallet_setting_data['report'])){
                if($wallet_setting_data['report']['time']){
                    if($wallet_setting_data['report']['time'] == 'q'){
                        $search['time_from'] = date('m/d/y', strtotime($date_current_time. ' -3 months'));
                    }
                    elseif($wallet_setting_data['report']['time'] == 'h'){
                        $search['time_from'] = date('m/d/y', strtotime($date_current_time. ' -6 months'));
                    }
                    elseif($wallet_setting_data['report']['time'] == 'y'){
                        $search['time_from'] = date('m/d/y', strtotime($date_current_time. ' -12 months'));
                    }
                    else{
                        $search['time_from'] = date('m/d/y', strtotime($date_current_time. ' -1 months'));
                    }
                }
                else{
                    $search['time_from'] = date('m/d/y', strtotime($date_current_time. ' -1 months'));
                }
                if(isset($wallet_setting_data['report']['type']) && !empty($wallet_setting_data['report']['type'])){
                    if($wallet_setting_data['report']['type'] == 'credit'){
                        $search['credit_type'] = 'credit';
                    }
                    elseif($wallet_setting_data['report']['type'] == 'debit'){
                        $search['credit_type'] = 'debit';
                    }
                }
            }
            else{
                $search['time_from'] = date('m/d/y', strtotime($date_current_time. ' -1 months'));
            }
            $search['period'] = 'b';
            $search['time_to'] = date('m/d/y', time());
            foreach($wallet_all_data_user_id as $value){
                $search['user_id'] = $value['user_id'];
                list($wallet_transaction, $search) = $this->loadModel->walletTransactions($search, Registry::get('settings.Appearance.admin_elements_per_page'));
                // fn_print_r($wallet_transaction);
                if(isset($wallet_transaction) && !empty($wallet_transaction)){
                    foreach($wallet_transaction as $key=>$data){
                        unset($wallet_transaction[$key]['extra_info']);
                    }
                    $wallet_user_email_id = fn_get_user_email($value['user_id']);
                    $wallet_user_name = fn_get_user_name($value['user_id']);
                    $this->loadModel->sendWalletMailNotificationForAllUser($wallet_transaction,$wallet_user_email_id,$wallet_user_name);
                }
            }
        }
       
    }


    public function wallet_bank_transfer(){

        $bank_transfers = $this->loadModel->select('wallet_bank_transfer', '*', [], 'db_get_array');
        
        foreach($bank_transfers as $key => $bank_transfer){
            $bank_data = $this->loadModel->select('customer_banks', '*', ['id' => $bank_transfer['bank_id']], 'db_get_row');
            $bank_transfers[$key]['bank'] = $bank_data;

            $pair_data = fn_get_image_pairs($bank_transfer['id'], 'transaction_image_banner', 'M', true, false);
            $bank_transfers[$key]['pair_data'] = $pair_data;
        }

        Registry::get('view')->assign('bank_transfers', $bank_transfers);

    }


    public function status_change(){

        $allow_extention = ['png', 'jpeg', 'jpg'];
        if(isset($_REQUEST['id']) && isset($_REQUEST['status'])){

            if(isset($_REQUEST['file_transaction_image_main_image_icon']) && !empty($_REQUEST['file_transaction_image_main_image_icon'])){
                 $extension = pathinfo($_REQUEST['file_transaction_image_main_image_icon'][0], PATHINFO_EXTENSION);

                 if(!in_array($extension, $allow_extention)){
                    fn_set_notification('W', __('warning'), __('invalid_file_format')); 
                    $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_bank_transfer");
                    return true;
                 }
                
            }

            $pair_data = fn_attach_image_pairs('transaction_image_main', 'transaction_image_banner', $_REQUEST['id'], $lang_code = DESCR_SL);

            $transfers_data = $this->loadModel->select('wallet_bank_transfer', '*', ['id' => $_REQUEST['id']], 'db_get_row');

            if($_REQUEST['status'] == 'cancel'){
                $data = ['status' => $_REQUEST['status']];
                $this->loadModel->update('wallet_bank_transfer', $data, ['id' => $_REQUEST['id']]);
                fn_set_notification('N', __("success"), __("status_has_been_changed"));
            }else{

                $amount = $transfers_data['transfer_amount'];
                $user_id = $transfers_data['user_id'];
                
                $wallet_cash_data = $this->loadModel->select('wallet_cash', '*', ['user_id' => $user_id], 'db_get_row');
                if(!empty($wallet_cash_data)){
                    if($this->debit_from_wallet($wallet_cash_data['wallet_id'], $amount)){
                        $data = ['status' => $_REQUEST['status'], 'transaction_id' => $_REQUEST['transaction_id']];
                        $this->loadModel->update('wallet_bank_transfer', $data, ['id' => $_REQUEST['id']]);
                    }
                }

               
            }

            
        }
        
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_bank_transfer");
        return true;
    }





    function debit_from_wallet($wallet_id, $debit_amount){

        
        $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('wallet_id'=>$wallet_id),'db_get_field');
        if ($debit_amount > $user_wallet_current_cash) {
            fn_set_notification('W', __('warning'), __('amount_cannot_be_greater'));            
            return false;
        }

        if ($user_wallet_current_cash > 0.0) {
            $user_wallet_updated_cash = $user_wallet_current_cash - (float)$debit_amount;
            $require_dabit_amount = $debit_amount;
            if ($user_wallet_updated_cash < 0.0) {
                $user_wallet_updated_cash = 0.0;
                $require_dabit_amount = $user_wallet_current_cash;
            }

            $updated_cash = array(
                'total_cash' => $user_wallet_updated_cash
            );

            $this->loadModel->updateWalletCash($updated_cash,array('wallet_id'=>$wallet_id));                

            $_data = array(
                'wallet_id'     => $wallet_id,
                'debit_amount'  => $require_dabit_amount,
                'remain_amount' => $user_wallet_updated_cash,
                'order_id'      => '0',
                'timestamp'     => TIME,
                'area'          => AREA,
                'debit_reason'  => 'transfer wallet to bank',
                'source'        => "debit by admin",
            );

            $wallet_debit_id = $this->loadModel->insertWalletDebitLog($_data);                

            $tran_data=array(
                'debit_id' => $wallet_debit_id,
                'wallet_id' => $wallet_id,
                'timestamp' => TIME,
            );

            $this->loadModel->insertWalletTransaction($tran_data);                

            $this->loadModel->debitWalletNotification($wallet_debit_id);

            fn_set_notification('N', __('wallet_debit'), __('amount_has_been_debited_from_user_wallet',array('[text_amt]' => $require_dabit_amount)));
            
            return $wallet_debit_id;

        } else {

            fn_set_notification('W', __('warning'), __('amount_is_zero'));
            
            return false;
        }


    }

    public function m_bank_transfer_delete() {
        if(isset($_REQUEST['bank_transfer_ids']) && !empty($_REQUEST['bank_transfer_ids'])){
            $bank_transfer_ids =  $_REQUEST['bank_transfer_ids'];
            foreach($bank_transfer_ids as $bank_transfer_id){
                $this->loadModel->delete('wallet_bank_transfer', ['id' => $bank_transfer_id]);
            }
        }
        
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_bank_transfer");
        return true;


    }


    public function bank_transfer_delete() {
        if(isset($_REQUEST['bank_transfer_id']) && !empty($_REQUEST['bank_transfer_id'])){
            $bank_transfer_id =  $_REQUEST['bank_transfer_id'];           
            $this->loadModel->delete('wallet_bank_transfer', ['id' => $bank_transfer_id]);
            
        }
        
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_bank_transfer");
        return true;
    }

}

?>
