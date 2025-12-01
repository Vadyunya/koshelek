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


namespace Tygh\Addons\WalletSystem;

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\SecurityHelper;
use Tygh\Mailer;
use WalletSystemModel\WalletSystemModel;


class Helper{

    public $loadModel;

    public function __construct()
    {
        $this->loadModel = new WalletSystemModel;
    }
    

    function fnWalletSystemGetWalletUserEmailId($wallet_id)
    {
        if (!empty($wallet_id)) {
            $wallet_user_id = $this->loadModel->select('wallet_cash', 'user_id', ['wallet_id' => $wallet_id], 'db_get_field');
            $wallet_user_email = $this->loadModel->select('users', 'email', ['user_id' => $wallet_user_id], 'db_get_field');
            return $wallet_user_email;
        }
    }


    function fnGetWalletUserId($wallet_id)
    {
        $user_id = $this->loadModel->select('wallet_cash', 'user_id', ['wallet_id' => $wallet_id], 'db_get_field');

        return $user_id;
    }


    function fnGetWalletAmount($wallet_id = null, $user_id = null)
    {
        if (!empty($user_id)) {
            $total_cash = $this->loadModel->select('wallet_cash', 'total_cash', ['user_id' => $user_id], 'db_get_field');
        } elseif (!empty($wallet_id)) {
            $total_cash = $this->loadModel->select('wallet_cash', 'total_cash', ['wallet_id' => $wallet_id], 'db_get_field');
        } else {
            $total_cash =0.00;
        }

        return $total_cash;
    }


    function fnGetTotalCreditWallet($wallet_id)
    {
        $total_user_credit = $this->loadModel->select('wallet_credit_log', 'SUM(credit_amount)', ['wallet_id' => $wallet_id], 'db_get_field');
        return $total_user_credit;
    }


    function fnGetTotalDebitWallet($wallet_id)
    {
        $total_user_debit = $this->loadModel->select('wallet_debit_log', 'SUM(debit_amount)', ['wallet_id' => $wallet_id], 'db_get_field');
        return $total_user_debit;
    }


    function fnGetWalletSystemSettingDataForAll($company_id)
    {
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


    function fnGetWalletSystemAllUserGroupForWalletShow($user_id)
    {
        $data = array();
        $data = fn_get_user_usergroups($user_id);
        if(isset($data) && !empty($data)){
            foreach($data as $key=>$value){
                if($value['status'] == 'A'){

                }
                else{
                    unset($data[$key]);
                }
            }
        }
        return $data;
        
    }


    function fnGetWalletSystemTestFinalDataWithDiff($data1,$data2)
    {
        if(!isset($data1['usergroup_ids'])){
            return true;
        }
        elseif(empty($data1['usergroup_ids'])){
            return true;
        }
        elseif(isset($data1['usergroup_ids']) && !empty($data1['usergroup_ids']) && $data1['usergroup_ids'][0] == 0){
            return true;
        }
        else{
            $data2_new = array();
            foreach($data2 as $raj){
                $data2_new[] = $raj['usergroup_id'];
            }
            $containsAllValues = array_intersect($data2_new, $data1['usergroup_ids']);
            if($containsAllValues){
                return true;
            }
            else{
                return false;
            }
        }  
    }


    function fnGenerateOtpAndSentItToWalletPayeeAndReturnOpt($user_id,$wallet_transfer_data,$request,$payee_email,$transfer_email)
    {
        $password = rand(1000,9999);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $wallet_user_name = fn_get_user_name($user_id);
        // fn_print_die($password);
        Mailer::sendMail(array(
            'to' => $payee_email,
            'from' => 'company_orders_department',
            'data' => array(
                'name' => $wallet_user_name,      
                'tra_amount_mail' => $wallet_transfer_data,                        
                'password' => $password,                                            
            ),
            'tpl' => 'addons/wallet_system/send_otp_for_transfer.tpl',
        ));

        return $password;
    }


    function fnWalletSystemResendOtp()
    {
        $password = rand(1000,9999);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $wallet_user_name = fn_get_user_name($_SESSION['auth']['user_id']);
        $tra_amount_mail = array(
            "transfer_email"=>$_SESSION['otp_data']['email'],
            "transfer_amount"=>$_SESSION['otp_data']['amount'],

        );
        // fn_print_die($password);
        Mailer::sendMail(array(
            'to' => $_SESSION['otp_data']['pay_email'],
            'from' => 'company_orders_department',
            'data' => array(
                'name' => $wallet_user_name,      
                'tra_amount_mail' => $tra_amount_mail,                        
                'password' => $password,                                            
            ),
            'tpl' => 'addons/wallet_system/send_otp_for_transfer.tpl',
        ));

        return $password;
    }

    
    function fnWalletSystemResetOtpData()
    {
        unset($_SESSION['otp_data']['pass']);
    }


    function fnWalletSystemChangeUserPoints($value, $current_value ,$user_id, $reason = '', $action = CHANGE_DUE_ADDITION)
    {

        $value = (int) $value;
        if (isset($value)) {

            fn_save_user_additional_data(POINTS, $value, $user_id);
            $change_points = array(
                'user_id' => $user_id,
                'amount' => $current_value - $value,
                'timestamp' => TIME,
                'action' => $action,
                'reason' => $reason
            );

            return $this->loadModel->replace('reward_point_changes', $change_points);
        }

        return '';
    }



    function fnCheckTransferWalletToBank()
    {
        $allow = 'N';

        
        $wallet_system_data = $this->loadModel->getSettingsData();
    
        if($wallet_system_data['status_transfer_wallet_to_bank'] == 'Y'){
            if($wallet_system_data['transfer_for_all_customer'] == 'Y'){
                $allow = 'Y';
            }elseif(isset($wallet_system_data['customers']) && !empty($wallet_system_data['customers'])){

                $customers = explode(',',$wallet_system_data['customers']);
                $user_id = $_SESSION['auth']['user_id'];

                if(in_array($user_id, $customers)){
                    $allow = 'Y';
                }
                
            }
        }   


        return $allow;
    }

    


}
