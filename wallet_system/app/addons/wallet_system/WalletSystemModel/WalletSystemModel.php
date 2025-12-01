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



namespace WalletSystemModel;

use Tygh\Registry;
use Tygh\Navigation\LastView;
use Tygh\Mailer;
use Tygh\Models\VendorPlan;
use Tygh\Settings;

class WalletSystemModel extends BaseModel {

    public function __construct() {
        parent::__construct();
    }

    public function walletSystemSettingData($wallet_system_data,$company_id = null) {

        if (!$setting_id = Settings::instance()->getId('wallet_system_tpl_data', '')) {

            $setting_id = Settings::instance()->update(array(
                'name' =>           'wallet_system_tpl_data',
                'section_id' =>     0,
                'section_tab_id' => 0,
                'type' =>           'A', 
                'position' =>       0,
                'is_global' =>      'N',
                'handler' =>        ''
            ));
        }
        
        if(isset($_REQUEST['wallet_system_data']['transfer_for_all_customer']) && $_REQUEST['wallet_system_data']['transfer_for_all_customer'] == 'Y'){                  
            $wallet_system_data['customers'] = '';
        }
        
        Settings::instance()->updateValueById($setting_id, serialize($wallet_system_data), $company_id);
    }

    public function getSettingsData($company_id = null) {

        static $cache;
        if (empty($cache['settings_' . $company_id])) {

          $settings = Settings::instance()->getValue('wallet_system_tpl_data', '', $company_id);
          $settings = unserialize($settings);
          if (empty($settings)) {
            $settings = array();
          }
          $cache['settings_' . $company_id] = $settings;
          
        }
        return $cache['settings_' . $company_id];
    }
    /**
     * 
     * @return int
     */
    public function getWalletCash($params = array(),$where = array(),$method) {

        return $this->select('wallet_cash',$params,$where,$method);
    }
    
    /**
     * 
     * @return void
     */
    public function getCreditLog($params = array(),$where = array(),$method) {

        return $this->select('wallet_credit_log',$params,$where,$method);
    }

    /**
     * 
     * @return int
     */
    public function getDebitLog($params = array(),$where = array(),$method) {

        return $this->select('wallet_debit_log',$params,$where,$method);
    }

    /**
     * 
     * @return void
     */
    public function insertWalletDebitLog($params = array()) {

        return $this->insert('wallet_debit_log',$params);
    }
    
    /**
     * 
     * @return void
     */
    public function getCompanyId($params = array(),$where = array(),$method) {

        return $this->select('users',$params,$where,$method);
    }   

    /**
     * 
     * @return void
     */
    public function insertWalletCash($params = array()) {

        return $this->insert('wallet_cash',$params);
    }

    /**
     * 
     * @return void
     */
    public function insertWalletCreditLog($param = array()){

        return $this->insert('wallet_credit_log',$param);
    }

    /**
     * 
     * @return void
     */
    public function insertWalletTransaction($param = array()) {

        $this->insert('wallet_transaction',$param);
    }

    /**
     * 
     * @return void
     */
    public function updateWalletCash($param = array(),$where = array()) {

        return $this->update('wallet_cash',$param,$where);
    }

    public function deleteCreditLog($param = array()) {
        $this->delete('wallet_credit_log',$param);
    }

    public function getWalletRefundedAmount($param = array(),$where = array(),$method) {
        return $this->select('orders',$param,$where,$method);
    }

    public function updateOrders($param = array(),$where = array()) {
        return $this->update('orders',$param,$where);
    }

    public function updateRmareturnOrders($param = array(),$where = array()) {
        return $this->update('rma_returns',$param,$where);
    }

    public function getWalletOfflinePayment($params = array(),$where = array(), $method) {
        return $this->select('wallet_offline_payment',$params,$where,$method);
    }

    public function updateWalletOfflinePayment($params = array(),$where = array()) {
        return $this->update('wallet_offline_payment',$params,$where);
    }

    public function getOrderData($params = array(),$where = array(),$method) {
        return $this->select('order_data',$params,$where,$method);
    }

    public function deleteOrderData($params = array()) {
        $this->delete('order_data',$params);
    }

    public function deleteRecords($table, $params = array()) {
        $this->delete($table,$params);
    }

    public function replaceWalletOffline($params = array()) {
        $this->replace('wallet_offline_payment',$params);
    }

    public function replaceOrderdata($params = array()) {
        $this->replace('order_data',$params);
    }
    /**
     * 
     * @return void
     */
    public function getUserIdOfEmail($email) {
        if (!empty($email)) {            
            $user_id = $this->select('users', 'user_id', "email LIKE '%$email%'", 'db_get_field');
             
            return $user_id;
        } else {
                return null;
            }
    }

    function getWalletUserId($wallet_id) {

        $user_id = $this->select('wallet_cash', 'user_id', ['wallet_id' => $wallet_id], 'db_get_field');

        return $user_id;
    }


    public function adminCreditAmountNotification($credit_id) {
        $wallet_credit_log_data = $this->select('wallet_credit_log', '*', ['credit_id' =>$credit_id], 'db_get_row');
        if(empty($wallet_credit_log_data)){
            return false;
        }

        $wallet_cash_data = $this->select('wallet_cash', '*', ['wallet_id' =>$wallet_credit_log_data['wallet_id']], 'db_get_row');

        $user_email = $this->select('users','email',['user_id' => $wallet_cash_data['user_id']],'db_get_field');
        $user_name = fn_get_user_name($wallet_cash_data['user_id']);
        
        $wallet_data = array (
            'user_name' => $user_name,
            'amount' => $wallet_credit_log_data['credit_amount'],
            'total_cash'  => $wallet_credit_log_data['total_amount']
        );

        Mailer::sendMail(array(
            'to' => $user_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'admin_credit_amount_email_template_for_customer',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                
            ), 'C');

        $admin_email = 'default_company_orders_department';
        Mailer::sendMail(array(
            'to' => $admin_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'admin_credit_amount_email_template_for_admin',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                    
            ), 'C');

    }


    public function adminDebitAmountNotification($debit_id) {

        $wallet_debit_log_data = $this->select('wallet_debit_log', '*', ['debit_id' =>$debit_id], 'db_get_row');
        if(empty($wallet_debit_log_data)){
            return false;
        }

        $wallet_cash_data = $this->select('wallet_cash', '*', ['wallet_id' =>$wallet_debit_log_data['wallet_id']], 'db_get_row');

        $user_email = $this->select('users','email',['user_id' => $wallet_cash_data['user_id']],'db_get_field');
        $user_name = fn_get_user_name($wallet_cash_data['user_id']);
        
        $wallet_data = array (
            'user_name' => $user_name,
            'amount' => $wallet_debit_log_data['debit_amount'],
            'total_cash'  => $wallet_debit_log_data['remain_amount']
        );

        Mailer::sendMail(array(
            'to' => $user_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'admin_debit_amount_email_template_for_customer',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                
            ), 'C');

        $admin_email = 'default_company_orders_department';
        Mailer::sendMail(array(
            'to' => $admin_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'admin_debit_amount_email_template_for_admin',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                    
            ), 'C');

    }





    public function walletRechargeNotification($credit_id) {
        $wallet_credit_log_data = $this->select('wallet_credit_log', '*', ['credit_id' =>$credit_id], 'db_get_row');
        if(empty($wallet_credit_log_data)){
            return false;
        }

        $wallet_cash_data = $this->select('wallet_cash', '*', ['wallet_id' =>$wallet_credit_log_data['wallet_id']], 'db_get_row');

        $user_email = $this->select('users','email',['user_id' => $wallet_cash_data['user_id']],'db_get_field');
        $user_name = fn_get_user_name($wallet_cash_data['user_id']);
        
        $wallet_data = array (
            'user_name' => $user_name,
            'amount' => $wallet_credit_log_data['credit_amount'],
            'total_cash'  => $wallet_credit_log_data['total_amount']
        );

        Mailer::sendMail(array(
            'to' => $user_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_recharge_email_template_for_customer',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                
            ), 'C');

        $admin_email = 'default_company_orders_department';
        Mailer::sendMail(array(
            'to' => $admin_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_recharge_email_template_for_admin',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                    
            ), 'C');

    }



    public function walletAmountOrderRefundNotification($credit_id) {

        $wallet_credit_log_data = $this->select('wallet_credit_log', '*', ['credit_id' =>$credit_id], 'db_get_row');
        if(empty($wallet_credit_log_data)){
            return false;
        }

        $wallet_cash_data = $this->select('wallet_cash', '*', ['wallet_id' =>$wallet_credit_log_data['wallet_id']], 'db_get_row');

        $user_email = $this->select('users','email',['user_id' => $wallet_cash_data['user_id']],'db_get_field');
        $user_name = fn_get_user_name($wallet_cash_data['user_id']);
        
        $wallet_data = array (
            'user_name' => $user_name,
            'amount' => $wallet_credit_log_data['credit_amount'],
            'total_cash'  => $wallet_credit_log_data['total_amount']
        );

        Mailer::sendMail(array(
            'to' => $user_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_amount_order_refund_email_template_for_customer',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                
            ), 'C');

        $admin_email = 'default_company_orders_department';
        Mailer::sendMail(array(
            'to' => $admin_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_amount_order_refund_email_template_for_admin',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                    
            ), 'C');

    }




    public function walletCashbackAmountNotification($credit_id) {
        
        $wallet_credit_log_data = $this->select('wallet_credit_log', '*', ['credit_id' =>$credit_id], 'db_get_row');
        if(empty($wallet_credit_log_data)){
            return false;
        }

        $wallet_cash_data = $this->select('wallet_cash', '*', ['wallet_id' =>$wallet_credit_log_data['wallet_id']], 'db_get_row');

        $user_email = $this->select('users','email',['user_id' => $wallet_cash_data['user_id']],'db_get_field');
        $user_name = fn_get_user_name($wallet_cash_data['user_id']);
        
        $wallet_data = array (
            'user_name' => $user_name,
            'amount' => $wallet_credit_log_data['credit_amount'],
            'total_cash'  => $wallet_credit_log_data['total_amount']
        );

        Mailer::sendMail(array(
            'to' => $user_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_cashback_amount_email_template_for_customer',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                
            ), 'C');

        $admin_email = 'default_company_orders_department';
        Mailer::sendMail(array(
            'to' => $admin_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_cashback_amount_email_template_for_admin',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                    
            ), 'C');

    }


    public function walletAmountUsedNotification($debit_id, $order_id = '') {
        
        $wallet_debit_log_data = $this->select('wallet_debit_log', '*', ['debit_id' =>$debit_id], 'db_get_row');
        if(empty($wallet_debit_log_data)){
            return false;
        }

        $wallet_cash_data = $this->select('wallet_cash', '*', ['wallet_id' =>$wallet_debit_log_data['wallet_id']], 'db_get_row');

        $user_email = $this->select('users','email',['user_id' => $wallet_cash_data['user_id']],'db_get_field');
        $user_name = fn_get_user_name($wallet_cash_data['user_id']);
        
        $wallet_data = array (
            'user_name' => $user_name,
            'order_number' => $order_id,
            'amount' => $wallet_debit_log_data['debit_amount'],
            'total_cash'  => $wallet_debit_log_data['remain_amount']
        );

        Mailer::sendMail(array(
            'to' => $user_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_amount_used_email_template_for_customer',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                
            ), 'C');

        $admin_email = 'default_company_orders_department';
        Mailer::sendMail(array(
            'to' => $admin_email,
            'from' => 'company_orders_department',
            'data' => array('wallet_data' => $wallet_data),
            'template_code' => 'wallet_amount_used_email_template_for_admin',
            'tpl' => 'addons/wallet_system/credit.tpl',
                                    
            ), 'C');

    }











    /**
     * 
     * @return bool
     */
    public function creditWalletNotification($param) {

        $data = $this->select('wallet_credit_log',array('source','source_id','wallet_id','credit_amount','total_amount'),array('credit_id'=>$param),'db_get_array');        

        $user_id = self::getUserWalletId($data[0]['wallet_id']);

        $wallet_data['email'] = $this->select('users',array('email'),array('user_id' => $user_id),'db_get_field');

        $wallet_data['user_name'] = fn_get_user_name($user_id);
        $wallet_data['amount']= $data[0]['credit_amount'];
        $wallet_data['total_cash']= $data[0]['total_amount'];
        $wallet_data['source']= $data[0]['source'];
        $wallet_data['source_id']= $data[0]['source_id'];

        if (Registry::get('settings.Appearance.email_templates') == 'old') {
            Mailer::sendMail(array(
                'to' => $wallet_data['email'],
                'from' => 'company_orders_department',
                'data' => array(
                        'wallet_data' => $wallet_data
                                    
                ),
                'tpl' => 'addons/wallet_system/credit.tpl',
                                    
            ), 'C');
        } else {
            $currencies = Registry::get('currencies');
            $currency = $currencies[CART_PRIMARY_CURRENCY];
            if ($currency['after'] == 'Y') {
                $wallet_data['amount'] .= ' ' . $currency['symbol'];
                $wallet_data['total_cash'] .= ' ' . $currency['symbol'];
            } else {
                $wallet_data['amount'] = $currency['symbol'] . $wallet_data['amount'];
                $wallet_data['total_cash'] = $currency['symbol'] . $wallet_data['total_cash'];
            }
                                    
            Mailer::sendMail(array(
                'to' => $wallet_data['email'],
                'from' => 'company_orders_department',
                'data' => array('wallet_data' => $wallet_data),
                'template_code' => 'wallet_credit',
                'tpl' => 'addons/wallet_system/credit.tpl',
                                    
                ), 'C');
        }
        return true;
    }

    /**
     * 
     * @return bool
     */
    function debitWalletNotification($wallet_debit_log_id) {

        $data = $this->select('wallet_debit_log',array('order_id','wallet_id','debit_amount','remain_amount'),array('debit_id'=>$wallet_debit_log_id),'db_get_array');

        $user_id = self::getWalletUserId($data[0]['wallet_id']);

        $wallet_data['email'] = $this->select('users',array('email'),array('user_id'=>$user_id),'db_get_field');

        $wallet_data['user_name'] = fn_get_user_name($user_id);

        $wallet_data['amount'] = $data[0]['debit_amount'];
        $wallet_data['total_cash'] = $data[0]['remain_amount'];
        $wallet_data['order_id'] = $data[0]['order_id'];

        if (Registry::get('settings.Appearance.email_templates') == 'old') {
            Mailer::sendMail(array(
                        'to' => $wallet_data['email'],
                        'from' => 'company_orders_department',
                        'data' => array(
                                'wallet_data' => $wallet_data
                                            
                        ),
                        'tpl' => 'addons/wallet_system/debit.tpl',
                ), 'C');
        } else {

            $currencies = Registry::get('currencies');
            $currency = $currencies[CART_PRIMARY_CURRENCY];

            if ($currency['after'] == 'Y') {

                $wallet_data['amount'] .= ' ' . $currency['symbol'];
                $wallet_data['total_cash'] .= ' ' . $currency['symbol'];
            } else {

                $wallet_data['amount'] = $currency['symbol'] . $wallet_data['amount'];
                $wallet_data['total_cash'] = $currency['symbol'] . $wallet_data['total_cash'];
            }
            Mailer::sendMail(array(
                                'to' => $wallet_data['email'],
                                'from' => 'company_orders_department',
                                'data' => array(
                                        'wallet_data' => $wallet_data       
                                    ),
                                'template_code' => 'wallet_debit',
                                'tpl' => 'addons/wallet_system/debit.tpl',
                            ), 'C');
        }

        return true;
    }

    /**
     * 
     * @return void
     */
    public function generateSections($section) {

        Registry::set('navigation.dynamic.sections', array(
            'wallet_users' => array(
                    'title' => __('wallet_users'),
                    'href' => 'wallet_system.wallet_users',
                    ),
            'wallet_transaction' => array(
                    'title' => __('wallet_transaction'),
                    'href' => 'wallet_system.wallet_transaction',
            ),
        ));
        Registry::set('navigation.dynamic.active_section', $section);

        return true;
    }

    /**
     * 
     * @return array
     */
    public function walletTransactions($params, $items_per_page = 0, $user_id = null) {

        $auth = $this->auth;
        // fn_print_die($params);
        $params = LastView::instance()->update('wallet_transaction', $params);
        // Set default values to input params
        $default_params = array(
                'page' => 1,
                'items_per_page' => $items_per_page
            );
        $params = array_merge($default_params, $params);
        // Define fields that should be retrieved
        $fields = array(
                '?:wallet_transaction.credit_id',
                '?:wallet_transaction.debit_id',
                '?:wallet_transaction.timestamp',
                '?:wallet_transaction.wallet_id',
            );
        // Define sort fields
        $sortings = array(
                'credit_id' => "?:wallet_transaction.credit_id",
                'debit_id' => "?:wallet_transaction.debit_id",
                'wallet_id' => "?:wallet_transaction.wallet_id",
                'timestamp' => "?:wallet_transaction.timestamp",
            );
        $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

        $condition = $join = '';

        if (isset($params['email']) && fn_string_not_empty($params['email'])) {

            $s_user_id=self::getUserIdOfEmail(trim($params['email']));
            if (empty($s_user_id)) {
                $s_wallet_id = 0;
            } else {
                $s_wallet_id = $this->select('wallet_cash',array('wallet_id'),array('user_id'=>$s_user_id),'db_get_field');
                if (empty($s_wallet_id)) {
                    $s_wallet_id=0;
                }
            }
            $condition .= db_quote(" AND ?:wallet_transaction.wallet_id = ?i", $s_wallet_id);

        }
        if (isset($params['user_id']) && fn_string_not_empty($params['user_id'])) {

            $condition .= db_quote(" AND ?:wallet_transaction.wallet_id = ?i", self::getUserWalletId(trim($params['user_id'])));

        }
        
        if (!empty($params['period']) && $params['period'] != 'A') {
            list($params['time_from'], $params['time_to']) = fn_create_periods($params);

            $condition .= db_quote(" AND (?:wallet_transaction.timestamp >= ?i AND ?:wallet_transaction.timestamp <= ?i)", $params['time_from'], $params['time_to']);
        }
        if (!empty($user_id)) {
            $condition .= db_quote(" AND ?:wallet_transaction.wallet_id IN (?n)", self::getUserWalletId($user_id));
        }

        if (isset($params['credit_type']) && fn_string_not_empty($params['credit_type']) && $params['credit_type'] == 'credit') {
            $condition .= db_quote(" AND ?:wallet_transaction.debit_id = ?i", 0);
            // for credit;
            if (isset($params['wallet_credit_or_debit_id']) && fn_string_not_empty($params['wallet_credit_or_debit_id'])) {
                $condition .= db_quote(" AND ?:wallet_transaction.credit_id = ?i", $params['wallet_credit_or_debit_id']);
            }
        } elseif(isset($params['credit_type']) && fn_string_not_empty($params['credit_type']) && $params['credit_type'] == 'debit') {
            $condition .= db_quote(" AND ?:wallet_transaction.credit_id = ?i", 0);
                // for debit
            if (isset($params['wallet_credit_or_debit_id']) && fn_string_not_empty($params['wallet_credit_or_debit_id'])) {
                $condition .= db_quote(" AND ?:wallet_transaction.debit_id = ?i", $params['wallet_credit_or_debit_id']);
            }
        } else {
            // both credit or debit;
            if (isset($params['wallet_credit_or_debit_id']) && fn_string_not_empty($params['wallet_credit_or_debit_id'])) {
                $condition .= db_quote(" AND ?:wallet_transaction.credit_id = ?i OR ?:wallet_transaction.debit_id = ?i", $params['wallet_credit_or_debit_id'],$params['wallet_credit_or_debit_id']);
            }
        }
        if (isset($params['wallet_trasaction_id']) && fn_string_not_empty($params['wallet_trasaction_id'])) {

            $condition .= db_quote(" AND ?:wallet_transaction.transaction_id = ?i", $params['wallet_trasaction_id']);

        }
        $limit = '';
        if (!empty($params['items_per_page'])) {
            $company_id = Registry::get('runtime.company_id');
            if(!empty($company_id) && AREA == 'A')
            { 
                $debit = $this->select('wallet_debit_log',array('COUNT(*)'),array('company_id'=>$company_id),'db_get_field');
                
                $credit = $this->select('wallet_credit_log',array('COUNT(*)'),array('company_id'=>$company_id),'db_get_field');
                
                $params['total_items'] = $debit + $credit;
                $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
            }
            elseif(!empty($company_id) && AREA == 'C')
            { 
                $wallet_id = self::getUserWalletId($auth['user_id']);
                $debit  = $this->select('wallet_debit_log',array('COUNT(*)'),array('wallet_id'=>$wallet_id),'db_get_field');

                
                $credit = $this->select('wallet_credit_log',array('COUNT(*)'),array('wallet_id'=>$wallet_id),'db_get_field');

                $params['total_items'] = $debit + $credit;
                $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
            }
            else 
            {
                $params['total_items'] = $this->select('wallet_transaction', 'COUNT(*)', "$condition", 'db_get_field');
                $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
            }
        }
        $wallet_transaction = $this->select('wallet_transaction', implode(',', $fields), "$condition $sorting $limit", 'db_get_array');

        if(fn_allowed_for('ULTIMATE'))
        {
            $company_id = Registry::get('runtime.company_id');
            
            if(!empty($company_id) && $auth['user_type'] == 'A' && AREA == 'C'){
                $wallet_id = self::getUserWalletId($auth['user_id']);
                foreach ($wallet_transaction as $key => $transaction) {
                    if (empty($transaction['credit_id'])) {
                        $wallet_transaction[$key]= $this->select('wallet_debit_log', '*', ['debit_id' => $transaction['debit_id'], 'wallet_id' => $wallet_id], 'db_get_row'); 
                    } elseif (empty($transaction['debit_id'])) {
                        $wallet_transaction[$key]= $this->select('wallet_credit_log', '*', ['credit_id' => $transaction['credit_id'], 'wallet_id' => $wallet_id], 'db_get_row');
                    }
                }
            }
            elseif(!empty($company_id))
            { 
                $wallet_id = self::getUserWalletId($auth['user_id']);
                foreach ($wallet_transaction as $key => $transaction) {
                    if (empty($transaction['credit_id'])) {
                        $wallet_transaction[$key]= $this->select('wallet_debit_log', '*', ['debit_id' => $transaction['debit_id'], 'wallet_id' => $wallet_id], 'db_get_row'); 
                    } elseif (empty($transaction['debit_id'])) {
                        $wallet_transaction[$key]= $this->select('wallet_credit_log', '*', ['credit_id' => $transaction['credit_id'], 'wallet_id' => $wallet_id], 'db_get_row');
                    }
                }
            }
            else
            { 
                foreach ($wallet_transaction as $key => $transaction) {
                    if (empty($transaction['credit_id'])) {
                        $wallet_transaction[$key]= $this->select('wallet_debit_log', '*', ['debit_id' => $transaction['debit_id']], 'db_get_row');
                    } elseif (empty($transaction['debit_id'])) {
                        $wallet_transaction[$key]= $this->select('wallet_credit_log', '*', ['credit_id' => $transaction['credit_id']], 'db_get_row');
                    }
                }
            }
        }
        else 
        {
            foreach ($wallet_transaction as $key => $transaction) {
                if (empty($transaction['credit_id'])) {
                    $wallet_transaction[$key]= $this->select('wallet_debit_log', '*', ['debit_id' => $transaction['debit_id']], 'db_get_row');
                } elseif (empty($transaction['debit_id'])) {
                    $wallet_transaction[$key]= $this->select('wallet_credit_log', '*', ['credit_id' => $transaction['credit_id']], 'db_get_row');
                }
            }
        }
        
        LastView::instance()->processResults('wallet_transaction', $wallet_transaction, $params);
        return array($wallet_transaction, $params);
    }
    


        /**
     * 
     * @return array
     */
    public function walletTransactionsDetails($params, $items_per_page = 0, $user_id = null) {

        $auth = $this->auth;
        $wallet_transaction_details = $this->select('wallet_credit_log', '*', ['credit_id' => $params['id']], 'db_get_row');
        $wallet_transaction_details_wallet_cash = $this->select('wallet_cash', '*', ['wallet_id' => $wallet_transaction_details['wallet_id']], 'db_get_row');
        return array($wallet_transaction_details,$wallet_transaction_details_wallet_cash);
    }


    public function walletTransactionsDetailsDebitData($params, $items_per_page = 0, $user_id = null) {

        $auth = $this->auth;
        $wallet_transaction_details = $this->select('wallet_debit_log', '*', ['debit_id' => $params['id']], 'db_get_row');
        // fn_print_r($wallet_transaction_details);
        $wallet_transaction_details_wallet_cash = $this->select('wallet_cash', '*', ['wallet_id' => $wallet_transaction_details['wallet_id']], 'db_get_row');
        // fn_print_r($wallet_transaction_details,$wallet_transaction_details_wallet_cash);
        return array($wallet_transaction_details,$wallet_transaction_details_wallet_cash);
    }
    /**
     * 
     * @return int
     */
    function getUserWalletId($user_id) {

        if(fn_allowed_for('ULTIMATE')) {
            $company_id = Registry::get('runtime.company_id');
        } else {
            $company_id = Registry::get('runtime.company_id');
        }

        $wallet_id = $this->select('wallet_cash',array('wallet_id'),array('user_id'=>$user_id),'db_get_field');
        
        if (empty($wallet_id)) {

            $data = array(
                'user_id' => $user_id,
                'total_cash' => 0.00,
                'company_id' => $company_id
            );

            $wallet_id = $this->insert('wallet_cash',$data);            
        }

        return $wallet_id;
    }

    /**
     * 
     * @return array
     */
    function getWalletUsers($params, $items_per_page = 0) {

        $params = LastView::instance()->update('wallet_transaction', $params);

        // Set default values to input params
        $default_params = array(
                'page' => 1,
                'items_per_page' => $items_per_page
            );

        $params = array_merge($default_params, $params);

        // Define fields that should be retrieved
        $fields = array(
                '?:wallet_cash.wallet_id',
                '?:wallet_cash.user_id',
                '?:wallet_cash.total_cash',
            );

        // Define sort fields
        $sortings = array(
                'total_cash' => "?:wallet_cash.total_cash",
                'user_id' => "?:wallet_cash.user_id",
                'wallet_id' => "?:wallet_cash.wallet_id",
            );
        $condition = " ";

        $sorting = db_sort($params, $sortings, 'user_id', 'desc');            

        if (isset($params['email']) && fn_string_not_empty($params['email'])) {
            $s_user_id = self::getUserIdOfEmail(trim($params['email']));
                        
            if (empty($s_user_id)) {
                $s_wallet_id = 0;
            } else {
                $s_wallet_id= $this->select('wallet_cash', 'wallet_id', ['user_id' => $s_user_id], 'db_get_field');
                
                if (empty($s_wallet_id)) {
                    $s_wallet_id=0;
                }
            }
                    
            $condition .= db_quote(" AND ?:wallet_cash.wallet_id = ?i", $s_wallet_id);
        }
                
        $limit = '';
        if (!empty($params['items_per_page'])) {
            $params['total_items'] = $this->select('wallet_cash', 'COUNT(*)', "$condition", 'db_get_field');
            $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        }

        $wallet_cash = $this->select('wallet_cash', implode(',', $fields), "$condition $sorting $limit", 'db_get_array');

        LastView::instance()->processResults('wallet_cash', $wallet_cash, $params);

        return array($wallet_cash, $params);
    }
    
    /**
     * 
     * @return int
     */
    function getWalletAmount($wallet_id = null, $user_id = null) {
        if (!empty($user_id)) {

            $total_cash = $this->select('wallet_cash',array('total_cash'),array('user_id'=>$user_id),'db_get_field');
            
        } elseif (!empty($wallet_id)) {

            $total_cash = $this->select('wallet_cash',array('total_cash'),array('wallet_id'=>$wallet_id),'db_get_field');
            
        } else {

            $total_cash =0.00;
        }

        return $total_cash;
    }

    /**
     * 
     * @return int
     */
    function walletFormatPrice($price) {

        $currency = Registry::get('currencies.' . CART_PRIMARY_CURRENCY);

        $price = fn_format_rate_value(
            $price,
            'F',
            $currency['decimals'],
            $currency['decimals_separator'],
            $currency['thousands_separator'],
            $currency['coefficient']
            );

        return $currency['after'] == 'Y' ? $price . $currency['symbol'] : $currency['symbol'] . $price;
    }

    /**
     * 
     * @return void
     */
    function createTransferForUser($email, $amount) {
        if (!empty($this->auth['user_id'])) {

            $sender_wallet_id = self::getUserWalletId($this->auth['user_id']);

            $reciever_user_id = $this->select('users',array('user_id'),array('email'=>$email),'db_get_field');            

            $reciever_wallet_id = self::getUserWalletId($reciever_user_id);

            $reciever_wallet_total_cash = self::getWalletAmount($reciever_wallet_id, null);

            $sender_wallet_total_cash = self::getWalletAmount($sender_wallet_id, null);

            $sender_email_id = $this->select('users',array('email'),array('user_id'=>$this->auth['user_id']),'db_get_field');

            $extra_info= array(
                'sender_email' => $sender_email_id,
                'reciever_email' => $email,
                'transfer_amount' => $amount,
                'timestamp' => TIME,
                'sender_remain_amount' => $sender_wallet_total_cash - $amount,
                'reciever_remain_amount' => $reciever_wallet_total_cash + $amount,
            );
            // cash_credt_to_user

            
            $updated_cash = array(
                'total_cash' => $amount + $reciever_wallet_total_cash
            );
            $this->update('wallet_cash',$updated_cash,array('user_id'=>$reciever_user_id));            

            if(fn_allowed_for('ULTIMATE')) {
                $company_id = Registry::get('runtime.company_id');
            } else {
                $company_id = Registry::get('runtime.company_id');
            }

            $_data = array(
                'source'         => "transfer",
                'source_id'      => '0',
                'wallet_id'      => $reciever_wallet_id,
                'credit_amount'  => $amount,
                'total_amount'   => $reciever_wallet_total_cash+$amount,
                'timestamp'      => TIME,
                'company_id'     => $company_id,
                'extra_info'     => serialize($extra_info),
            );

            $wallet_credit_log_id = $this->insert('wallet_credit_log',$_data);

            $tran_data=array(
                'credit_id' => $wallet_credit_log_id,
                'wallet_id' => $reciever_wallet_id,
                'timestamp' => TIME,
            );
            $this->insert('wallet_transaction',$tran_data);            

            self::creditWalletNotification($wallet_credit_log_id);
                            
            $updated_cash = array(
                'total_cash' => $sender_wallet_total_cash-$amount
            );
            $this->update('wallet_cash',$updated_cash,array('user_id'=>$_SESSION['auth']['user_id']));           

            $data = array(
                'wallet_id' => $sender_wallet_id,
                'debit_amount' => $amount,
                'remain_amount' => $sender_wallet_total_cash-$amount,
                'order_id' => '0',
                'timestamp' => TIME,
                'area' => AREA,
                'company_id' => $company_id,
                'extra_info'     => serialize($extra_info),
                'source'         => "transfer",
            );
                
            $wallet_debit_id = $this->insert('wallet_debit_log',$data);            

            $tran_data=array(
                'debit_id' => $wallet_debit_id,
                'wallet_id' => $sender_wallet_id,
                'timestamp' => TIME,
            );
            $this->insert('wallet_transaction',$tran_data);            

            self::debitWalletNotification($wallet_debit_id);
        }
    }

    public function createReturnWallet($order_id, $amount, $return_id, $user_id) {

        $seeting_data_for_wallet_add_on = self::getSettingsData();
        $min = !empty($seeting_data_for_wallet_add_on['min_refund_amount']) ? $seeting_data_for_wallet_add_on['min_refund_amount'] : 0;

        $max = !empty($seeting_data_for_wallet_add_on['max_refund_amount']) ? $seeting_data_for_wallet_add_on['max_refund_amount'] : 0;

        $order_info = fn_get_order_info($order_id);

        // fn_print_die($order_id,$order_info,$amount,$return_id,$user_id,$min,$max);
        if(fn_allowed_for('ULTIMATE')) {
            $company_id = $order_info['company_id'];
        } else {
            $company_id = $order_info['company_id'];
        }
                    
        if ($amount < $min 
        || $amount > $max) 
        {
            fn_set_notification('W', __('wallet_error'), __('can_not_add_money_in_wallet_please_check_refund_limit_in_addon_setting'));
            fn_set_notification("N", __("wallet_limit"), __("wallet_limit_is").' '.$min.__("_to_").' '.$max);

            $result = array();
        } else {

            $user_wallet_amount = self::getWalletAmount($wallet_id = null, $user_id);
                                    
            if (empty($user_wallet_amount)) {
                $data = array(
                    'user_id'    => $user_id,
                    'total_cash'     => $amount,
                    'company_id'  => $company_id
                );

                $wallet_id = $this->loadModel->insert('wallet_cash', $data);
            } else {

                $total_cash = $user_wallet_amount + $amount;
                $data = array(
                        'total_cash' => $total_cash
                    );
                $this->update('wallet_cash',$data,array('user_id'=>$user_id));                
            }

            $_data = array(
                'source'         => "refund_rma",
                'source_id'      => $return_id,
                'wallet_id'      => self::getUserWalletId($user_id),
                'credit_amount'  => $amount,
                'total_amount'   => $amount+$user_wallet_amount,
                'timestamp'      => TIME,
                'company_id'     => $company_id,
                'refund_reason'      => "RMA generated By Customer",
            );

            $wallet_credit_log_id = $this->insert('wallet_credit_log',$_data);            
            $tran_data = array(
                'credit_id'  => $wallet_credit_log_id,
                'wallet_id'  => self::getUserWalletId($user_id),
                'company_id' => $company_id,
                'timestamp'  => TIME,
            );

            $this->insert('wallet_transaction',$tran_data);            

            self::creditWalletNotification($wallet_credit_log_id);
            
            $result = $wallet_credit_log_id;
            fn_set_notification("N", __("wallet_refund"), __("money_added_in_user_wallet",array(
                '[text_amt]' => $amount)));
        }

        return $result;
    }

    public function displayOrderTotals($orders) {
        $wallet_recharge_orders = array();
        $wallet_recharge_orders = $this->select('wallet_offline_payment', 'order_id', [], 'db_get_fields');
        $result = array();
        $result['gross_total'] = 0;
        $result['totally_paid'] = 0;
        if (is_array($orders)) {
            foreach ($orders as $k => $v) {
                $result['gross_total'] += $v['total'];
                if ($v['status'] == 'C' || $v['status'] == 'P') {
                    if(!in_array($v['order_id'], $wallet_recharge_orders))
                    $result['totally_paid'] += $v['total'];
                }
            }
        }
        return $result;
    }

    public function getUserSessionProducts($timestamp_from,$timestamp_to,$company_condition) {

        return $this->select('user_session_products', 'COUNT(*)', " AND timestamp BETWEEN $timestamp_from AND $timestamp_to $company_condition GROUP BY user_id", 'db_get_fields');
    }

    public function getUserSessionProductsDiffrence($timestamp_from,$timestamp_to,$company_condition,$time_difference) {

        return $this->select('user_session_products', 'COUNT(*)', "AND timestamp BETWEEN ".($timestamp_from - $time_difference)." AND ".($timestamp_to - $time_difference)." $company_condition GROUP BY user_id", 'db_get_fields');
    }

    public function getOrdersData($timestamp_from, $timestamp_to, $company_condition)
    {
        return $this->mtSelect('orders', " ?:status_descriptions.description as status_name, ?:orders.status, COUNT(*) as count, SUM(?:orders.total) as total, SUM(?:orders.shipping_cost) as shipping", "?:statuses.type = '" . STATUSES_ORDER . "' AND ?:orders.timestamp > $timestamp_from AND ?:orders.timestamp < $timestamp_to AND ?:status_descriptions.lang_code = '" . CART_LANGUAGE . "' $company_condition  GROUP BY ?:orders.status", 'db_get_array', "INNER JOIN ?:statuses ON ?:statuses.status = ?:orders.status INNER JOIN ?:status_descriptions ON ?:status_descriptions.status_id = ?:statuses.status_id");
    }

    public function applyPromotionBonous($wk_promotions, $order_total) {
        if (!empty($wk_promotions)) {

            $wk_promotions_list=unserialize($wk_promotions);
            foreach ($wk_promotions_list as $key => $bonuses) {

                foreach ($bonuses as $key1 => $bonus) {

                    foreach ($bonus as $key2 => $value) {

                        if (isset($value['bonus'])&& $value['bonus']=='wallet_cash_back') {

                            $discount_condition=$value['discount_bonus'];
                            $discount_value=$value['discount_value'];
                            if ($discount_condition=='by_fixed') {

                                return $discount_value;
                            } elseif ($discount_condition=='by_percentage') {

                                $cash_back_amount=$discount_value*$order_total/100;
                                return $cash_back_amount;
                            }
                        }
                    }
                }
            }
        }
    }

    public function getVendorCommissionData($order_info, $plan_id) {
        if ($plan_id
		&& $plan = VendorPlan::model()->find($plan_id)) 
        {
            $commission = $order_info['total'] > 0 ? $plan->commission : 0;
            $commission_amount = 0;

            //Calculate commission amount and check if we need to include shipping cost
            $shipping_cost = Registry::get('addons.vendor_plans.include_shipping') == 'N' ? $order_info['shipping_cost'] : 0;
            $commission_amount = ($order_info['total'] - $shipping_cost) * $commission / 100;

            //Check if we need to take payment surcharge from vendor
            if (Registry::get('addons.vendor_plans.include_payment_surcharge') == 'Y') {
                $commission_amount += $order_info['payment_surcharge'];
            }

            if ($commission_amount > $order_info['total']) {
                $commission_amount = $order_info['total'];
            }

            $data['commission'] = $commission;
            $data['commission_amount'] = $commission_amount;
            $data['commission_type'] = 'P'; // Backward compatibility
        }
	    return $data;
    }

    function createWalletDebitLog($order_info, $wallet_info) {
        if(fn_allowed_for('ULTIMATE')) {
            $company_id = Registry::get('runtime.company_id');
        } else {
            $company_id = Registry::get('runtime.company_id');
        }

        $data = array(
            'wallet_id' => self::getUserWalletId($order_info['user_id']),
            'debit_amount' => $wallet_info['used_cash'],
            'remain_amount' => $wallet_info['current_cash'],
            'order_id' => $order_info['order_id'],
            'timestamp' => TIME,
            'company_id' => $company_id,
            'area' => AREA,
        );
        
        $wallet_debit_id = $this->insert('wallet_debit_log',$data);
        $tran_data=array(
            'debit_id' => $wallet_debit_id,
            'wallet_id' => self::getUserWalletId($order_info['user_id']),
            'timestamp' => TIME,
        );

        $this->insert('wallet_transaction',$tran_data);        

        $this->walletAmountUsedNotification($wallet_debit_id, $order_info['order_id']);

    }


    function sendWalletMailNotificationForAllUser($data,$email,$name,$credit_total = 0,$debit_total = 0,$total = 0) {
       
        $credit_total = 0;
        $debit_total = 0;
        if(isset($data) && !empty($data)){
            foreach($data as $value){
                if(isset($value['credit_id']) && !empty($value['credit_id'])){
                    $credit_total = $credit_total + $value['credit_amount'];
                }
                if(isset($value['debit_id']) && !empty($value['debit_id'])){
                    $debit_total = $debit_total + $value['debit_amount'];
                }
            }
        }






        // fn_print_die($data);
            Mailer::sendMail(array(
                        'to' => $email,
                        'from' => 'company_orders_department',
                        'data' => array(
                            'data' => $data,
                            'name' => $name,       
                            'credit' => $credit_total,       
                            'debit' => $debit_total,
                            'total' => $credit_total+$debit_total    
                                            
                        ),
                        'tpl' => 'addons/wallet_system/month_report.tpl',
                ));
        return true;
    }
}