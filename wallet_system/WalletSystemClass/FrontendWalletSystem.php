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

use Tygh\Enum\YesNo;
use Tygh\Addons\ProductVariations\ServiceProvider as ProductVariationsServiceProvider;
use Tygh\Registry;
use Tygh\Tygh;
use Tygh\Navigation\LastView;
// include_once(Registry::get("config.dir.addons").'/wallet_system/WalletSystemClass/Common.php');

class FrontendWalletSystem extends BaseController {    
    /**
     * FrontendWalletSystem constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);
        $this->$mode();
    }

    public function create_transfer() {

        $getConfig = $this->loadModel->getSettingsData();

        $suffix="wallet_system.wallet_transfer_user_to_user&email=".$_REQUEST['wallet_transfer_system']['transfer_email']."&amount=".$_REQUEST['wallet_transfer_system']['transfer_amount'];
        if(Registry::get('addons.wallet_system.status_wallet_transfer') == 'N')
        {
            $this->response = array(CONTROLLER_STATUS_DENIED);
        }
        
        if($_REQUEST['wallet_transfer_system']['transfer_email'] == $this->loadModel->getCompanyId(array('email'),array('user_id'=>$this->auth['user_id']),'db_get_field')) {
            fn_set_notification('W',__("error"),__("can_not_transfer_to_own_email"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT,$suffix);
            return;
        }

        $get_transfer_min = !empty($getConfig['min_transfer_amount']) ? $getConfig['min_transfer_amount'] : 0;
        $get_transfer_max = !empty($getConfig['max_transfer_amount']) ? $getConfig['max_transfer_amount'] : 0;
        $get_user_wallet_amount = $this->loadModel->getWalletAmount(null,$this->auth['user_id']);
        
        $transfer_email_id = trim($_REQUEST['wallet_transfer_system']['transfer_email']);

        if (fn_allowed_for('ULTIMATE')) {
            $company_id = Registry::get('runtime.company_id');
            if($company_id != 0) {
                $transfer_user_id = $this->loadModel->getCompanyId(array('user_id'),array('email'=>$transfer_email_id,'company_id'=>$company_id),'db_get_field');
                
            }
        } else {
            $transfer_user_id = $this->loadModel->getCompanyId(array('user_id'),array('email'=>$transfer_email_id),'db_get_field');            
        }

        $check_amount = is_numeric($_REQUEST['wallet_transfer_system']['transfer_amount']);

        if(empty($check_amount)) {
            fn_set_notification('W',__("amount_error"),__("please_insert_only_numeric_value"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT,$suffix);
            return false;
        }

        $_REQUEST['wallet_transfer_system']['transfer_amount'] = fn_format_price_by_currency($_REQUEST['wallet_transfer_system']['transfer_amount'],CART_SECONDARY_CURRENCY,CART_PRIMARY_CURRENCY);

        if($_REQUEST['wallet_transfer_system']['transfer_amount'] > $get_user_wallet_amount) {
            fn_set_notification('W',__("amount_error"),__("transfer_amount_is_more_than_available_cash"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }
        
        if($get_transfer_max < $_REQUEST['wallet_transfer_system']['transfer_amount']
        ||$_REQUEST['wallet_transfer_system']['transfer_amount'] < $get_transfer_min)
        {

            $error_msg=__("transfer_limit_is").fn_format_price_by_currency($get_transfer_min,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY).__("_to_").fn_format_price_by_currency($get_transfer_max,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY);

            fn_set_notification('W',__("amount_error"),$error_msg);
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }

        if(empty($transfer_user_id)) {
            fn_set_notification('W',__("user_not_exist"),__("email_user_not_found_at_store"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix);
            return false;
        }

        $wallet_transfer_data = $_REQUEST['wallet_transfer_system'];






        $get_email_id_for_payee_by_user_id = $_SESSION['auth']['user_id'];
        $get_email_id_for_payee_by_user_id = fn_get_user_email($get_email_id_for_payee_by_user_id);
        $opt_send_to_payee_data = $this->helper->fnGenerateOtpAndSentItToWalletPayeeAndReturnOpt($_SESSION['auth']['user_id'],$wallet_transfer_data,$_REQUEST,$get_email_id_for_payee_by_user_id,$transfer_email_id);
        if($opt_send_to_payee_data){
            $otp_data = array(
                "email" => $_REQUEST['wallet_transfer_system']['transfer_email'],
                "pass" => $opt_send_to_payee_data,
                "amount" => $_REQUEST['wallet_transfer_system']['transfer_amount'],
                "pay_email" => $get_email_id_for_payee_by_user_id,
                "otp_generated_time" => time(),
            );
            $_SESSION['otp_data'] = $otp_data; 
            $suffix22="wallet_system.wallet_opt_varify&email=".$_REQUEST['wallet_transfer_system']['transfer_email']."&amount=".$_REQUEST['wallet_transfer_system']['transfer_amount'];
            
            return $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix22);
        }
        else{
            fn_set_notification('W',__("error"),__("can_not_transfer_otp_or_mail_not_working"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transfer_user_to_user");
        }
        // fn_print_die($opt_send_to_payee_data);
        
        
        
        
        
        // $this->loadModel->createTransferForUser($wallet_transfer_data['transfer_email'],$wallet_transfer_data['transfer_amount']);  
        

        // fn_set_notification('N',__("success"),__("transfer_completed_successfully"));

        // $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transactions");
    }

    public function my_wallet() {
        $getConfig = $this->loadModel->getSettingsData();

        fn_add_breadcrumb(__('my_wallet'));
        
        if ($this->auth['user_id'] == 0)
        {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
        
        Registry::get('view')->assign('total_cash',$this->loadModel->getWalletAmount($wallet_id=null,$user_id=$this->auth['user_id']));
        Registry::get('view')->assign('primary_currency',CART_PRIMARY_CURRENCY);
        
        // fn_print_r($getConfig);

        if(isset($getConfig['status_wallet_transfer']) && $getConfig['status_wallet_transfer'] == 'Y')
        {
            Registry::get('view')->assign('enable_transfer',"yes");
        }
        if(isset($getConfig['status_bank_transfer']) && $getConfig['status_bank_transfer'] == 'Y') {
            Registry::get('view')->assign('enable_transfer_bank',"yes");
        }
    }

    public function wallet_transfer_user_to_user() {
        
        fn_add_breadcrumb(__('transfer_wallet_cash'));
        $getConfig = $this->loadModel->getSettingsData();
        if($getConfig['status_wallet_transfer'] == 'N'){
        fn_set_notification("W","Error",__("you_dont_have_perminssion_to_create_transfer_please_commnuncate_with_amin"));
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
         
        }
        if(Registry::get('addons.wallet_system.status_wallet_transfer') == 'N') {
            $this->response = array(CONTROLLER_STATUS_DENIED);
        }

        if(isset($_REQUEST['email'])) {
            Registry::get('view')->assign('transfer_email',$_REQUEST['email']);
        }

        if(isset($_REQUEST['amount'])) {
            Registry::get('view')->assign('transfer_amount',$_REQUEST['amount']);
        }
        
        if ($this->auth['user_id'] == 0) {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
        
        Registry::get('view')->assign(
            'total_cash',
            $this->loadModel->getWalletAmount($wallet_id=null,$user_id=$this->auth['user_id'])
        );
        Registry::get('view')->assign('primary_currency',CART_PRIMARY_CURRENCY);
        
        if(Registry::get('addons.wallet_system.status_wallet_transfer') == 'Y') {
            Registry::get('view')->assign('enable_transfer',"yes");
        }
    }

    public function cash_add_wallet() {

        $getConfig = $this->loadModel->getSettingsData();
        // fn_print_r($getConfig);        
        
        if(empty($this->auth['user_id'])) {
            fn_set_notification("W","wallet_recharge",__("please_login_first"));

            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login");
        }
    
        $min = !empty($getConfig['min_recharge_amount']) ? $getConfig['min_recharge_amount'] : 0;
        $max = !empty($getConfig['max_recharge_amount']) ? $getConfig['max_recharge_amount'] : 0;

        $min = fn_format_price_by_currency($min,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY);

        $max = fn_format_price_by_currency($max,CART_PRIMARY_CURRENCY,CART_SECONDARY_CURRENCY);

        // fn_print_die($_REQUEST);
        if(!empty($_REQUEST['wallet_system']['recharge_amount']) && !empty($_REQUEST['wallet_system']['total_cash'])){

            if(!is_numeric($_REQUEST['wallet_system']['recharge_amount'])){
                fn_set_notification("W",__("warning"),__("please_enter_the_valid_amount"));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
                return;
            }

            $total_limit_ammount = $_REQUEST['wallet_system']['recharge_amount'] + $_REQUEST['wallet_system']['total_cash'];
            if($total_limit_ammount > 99999999){
                fn_set_notification('W', __('wallet_error'), __('you_are_using_max_ammount_in_your_wallet'));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
                return false;
            }
        }
        if (empty($_REQUEST['wallet_system']['recharge_amount']) 
        || (float)$_REQUEST['wallet_system']['recharge_amount'] < $min 
        || (float)$_REQUEST['wallet_system']['recharge_amount'] > $max 
        || !is_numeric($_REQUEST['wallet_system']['recharge_amount']))
        {

            fn_set_notification('W', __('wallet_error'), __('can_not_proceed_please_check_limit'));
            fn_set_notification("W",__("warning"),__("wallet_limit_is").$min.__("_to_").$max);

            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
            return false;
        }  

        
        if(!empty($_SESSION['cart']['products'])) {
            fn_set_notification("W",__("wallet_recharge"),__("remove_product_from_cart"));

            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet"); 
        }

        if(!empty($_SESSION['cart']['gift_certificates'])) {
            fn_set_notification("W","wallet_recharge",__("remove_product_from_cart"));

            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet"); 
        }
    
        $_SESSION['cart']['wallet_system'] = array();

        $_wr = array();
        $_wr[] = TIME;

        $wallet_system = $_REQUEST['wallet_system'];

        $wallet_system['recharge_amount'] = fn_format_price_by_currency($wallet_system['recharge_amount'],CART_SECONDARY_CURRENCY,CART_PRIMARY_CURRENCY);
        
        if (!empty($wallet_system)) {
            foreach ($wallet_system as $k => $v) {
                $_wr[] = $v;
            }
        }

        $wallet_cart_id=fn_crc32(implode('_', $_wr));

        if (!empty($wallet_cart_id)) {
                    
            $wallet_system['wallet_cart_id'] = $wallet_cart_id;

            $wallet_system['display_subtotal'] = $wallet_system['recharge_amount'];
        
            $_SESSION['cart']['wallet_system'][$wallet_cart_id] = $wallet_system;

            fn_calculate_cart_content($_SESSION['cart'], $this->auth, 'S', true, 'F', true);

            $wallet_system['display_subtotal'] = $_SESSION['cart']['wallet_system'][$wallet_cart_id]['display_subtotal'];
                                
            Registry::get('view')->assign('wallet_system', $wallet_system);

            $msg = Registry::get('view')->fetch('views/checkout/components/product_notification.tpl');
            fn_set_notification('I', __('money_added_in_cart_please_make_a_paymnet'), $msg, 'I');
        }

        fn_save_cart_content($_SESSION['cart'], $this->auth['user_id']);

        if (defined('AJAX_REQUEST')) {
            fn_calculate_cart_content($_SESSION['cart'], $this->auth, false, false, 'F', false);
        }        
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
    }

    public function wallet_transactions() {
        fn_add_breadcrumb(__('wallet_transactions'));

        if ($this->auth['user_id'] == 0) {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
            
        list($wallet_transactions, $search) = $this->loadModel->walletTransactions($_REQUEST, Registry::get('settings.Appearance.elements_per_page'),$this->auth['user_id']);
            
        Registry::get('view')->assign('wallet_transactions', $wallet_transactions);
        Registry::get('view')->assign('search', $search);
    }
    public function wallet_transactions_details() {
        fn_add_breadcrumb(__('wallet_transactions'));

        if ($this->auth['user_id'] == 0) {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
        list($wallet_transactions_details,$wallet_data) = $this->loadModel->walletTransactionsDetails($_REQUEST,Registry::get('settings.Appearance.elements_per_page'),$this->auth['user_id']);    

        Registry::get('view')->assign('wallet_transactions_details', $wallet_transactions_details);
        Registry::get('view')->assign('wallet_data', $wallet_data);
    }
    public function wallet_transactions_details_credit_data() {
        fn_add_breadcrumb(__('wallet_transactions'));

        if ($this->auth['user_id'] == 0) {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
        list($wallet_transactions_details,$wallet_data) = $this->loadModel->walletTransactionsDetails($_REQUEST,Registry::get('settings.Appearance.elements_per_page'),$this->auth['user_id']);    

        // fn_print_r($wallet_transactions_details,$wallet_data);
        Registry::get('view')->assign('wallet_transactions_details', $wallet_transactions_details);
        Registry::get('view')->assign('wallet_data', $wallet_data);
    }
    // wallet_transactions_details_credit_data
    public function wallet_transactions_details_debit_data() {
        fn_add_breadcrumb(__('wallet_transactions'));
        if ($this->auth['user_id'] == 0) {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
        }
        list($wallet_transactions_details,$wallet_data) = $this->loadModel->walletTransactionsDetailsDebitData($_REQUEST,Registry::get('settings.Appearance.elements_per_page'),$this->auth['user_id']);    
        // fn_print_die($wallet_transactions_details,$wallet_data);
        Registry::get('view')->assign('wallet_transactions_details', $wallet_transactions_details);
        Registry::get('view')->assign('wallet_data', $wallet_data);
    }

    public function clear_cart() {
        $cart = & $_SESSION['cart'];
        fn_clear_cart($cart);
        fn_set_notification('N','notice',__("clear_cart_successfully"));
        $this->response = array(CONTROLLER_STATUS_REDIRECT);
    }

    public function apply_wallet_cash() {

        $current_wallet_cash = $this->loadModel->getWalletAmount(null,$this->auth['user_id']);
        $cart_total = $_SESSION['cart']['total'];

        if($cart_total >= $current_wallet_cash) {

            $_SESSION['cart']['wallet']['current_cash']= 0.0;
            $_SESSION['cart']['wallet']['used_cash']= $current_wallet_cash;
        } else {

            $_SESSION['cart']['wallet']['current_cash']= $current_wallet_cash - $cart_total;
            $_SESSION['cart']['wallet']['used_cash']= $cart_total;
        }


        $this->response = array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout?wallet_cash_applied=yes");
    }

    public function remove_wallet_cash() {

        if(version_compare(PRODUCT_VERSION, '4.9.3') == 1 ) {
            $_SESSION['cart']['payment_id']=$_SESSION['cart']['payment']['payment_id'];
        }

        $_SESSION['cart']['payment_id'] = $_SESSION['cart']['payment']['payment_id'];

        unset($_SESSION['cart']['wallet']);

        $this->response = array(CONTROLLER_STATUS_REDIRECT, "checkout.checkout");
    }
    public function wallet_opt_varify() {
        if($_REQUEST){
            if(isset($_REQUEST['email']) && isset($_REQUEST['amount'])){
                Registry::get('view')->assign('email',$_REQUEST['email']);
                Registry::get('view')->assign('ammount',$_REQUEST['amount']);

            }
            else{
                fn_set_notification('W',__("error"),__("can_not_transfer_data_not_found_try_again"));
               return $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transfer_user_to_user");
            }
        }
    }
    public function wallet_varify_otp_data_by_customer() {
        // fn_print_r($_SESSION,$_GET,$_POST);
        if($_REQUEST){
            if(isset($_REQUEST['otp'])){
                if($_SESSION['otp_data']){
                    if(isset($_SESSION['otp_data']['pass'])){
                        if($_SESSION['otp_data']['pass'] == $_REQUEST['otp']){
                                $this->loadModel->createTransferForUser($_SESSION['otp_data']['email'],$_SESSION['otp_data']['amount']);  
                                fn_set_notification('N',__("success"),__("transfer_completed_successfully"));
                                unset($_SESSION['otp_data']);
                                $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transactions");
                        }
                        else{
                            fn_set_notification('W',__("error"),__("please_fill_again_wront_password"));
                            $suffix22="wallet_system.wallet_opt_varify&email=".$_SESSION['otp_data']['email']."&amount=".$_SESSION['otp_data']['amount'];
                            return $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix22);
                        }
                    }
                    else{
                            fn_set_notification('W',__("error"),__("please_fill_again_wront_password_expried_passwork_use"));
                            $suffix22="wallet_system.wallet_opt_varify&email=".$_SESSION['otp_data']['email']."&amount=".$_SESSION['otp_data']['amount'];
                            return $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix22);
                    }
                }
                else{
                    fn_set_notification('W',__("error"),__("please_fill_again_data_expire"));
                    return $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transfer_user_to_user");
                }                
            }
            elseif($_GET['restest_data'] == 'true'){
                $suffix22="wallet_system.wallet_opt_varify&email=".$_SESSION['otp_data']['email']."&amount=".$_SESSION['otp_data']['amount'];
                return $this->response = array(CONTROLLER_STATUS_REDIRECT, $suffix22);
            }
            else{
                fn_set_notification('W',__("error"),__("please_fill_opt_data_not_empty"));
                return $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transfer_user_to_user");
            }
        }
    }
    public function resend_otp() {
        $pass = $this->helper->fnWalletSystemResendOtp();
        $_SESSION['otp_data']['pass'] = $pass;
        $_SESSION['otp_data']['otp_generated_time'] = time();
        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_varify_otp_data_by_customer?restest_data=true");
    }
    public function wallet_system_referace_resend_otp() {
        $this->helper->fnWalletSystemResetOtpData();
        return true;
    }
    public function t_reward() {
        if(isset($_SESSION)){
            if(isset($_SESSION['auth'])){
                if(!empty($_SESSION['auth']['user_id'])){
                    $user_id = $_SESSION['auth']['user_id'];
                    $user_info = fn_get_user_info($user_id);
                    if(!empty($user_info) && isset($user_info['points']) && !empty($user_info['points'])){
                        $customer_points = $user_info['points'];
                        $wallet_setting = $this->helper->fnGetWalletSystemSettingDataForAll('');
                        if(isset($wallet_setting) && !empty($wallet_setting) && isset($wallet_setting['status_reward_points']) && !empty($wallet_setting['status_reward_points']) && $wallet_setting['status_reward_points'] == 'Y'){
                            if(isset($wallet_setting['min_reward_points'])){
                                $min_reward = $wallet_setting['min_reward_points'];
                            }
                            else{
                                $min_reward = 10;
                            }
                            if(isset($wallet_setting['max_reward_points'])){
                                $max_reward = $wallet_setting['max_reward_points'];
                            }
                            else{
                                $max_reward = 100;
                            }
                            Registry::get('view')->assign('max_reward', $min_reward);
                            Registry::get('view')->assign('min_reward', $max_reward);
                            Registry::get('view')->assign('points', $user_info['points']);
                        }
                        else{
                            fn_set_notification('W',__("error"),__("status_is_not_active_for_trafer_reward"));
                            return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
                        }
                    }
                    else{
                        fn_set_notification('W',__("error"),__("reward_points_not_found_please_check_again"));
                        return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
                    }                    
                }
                else{
                        fn_set_notification('W',__("error"),__("reward_points_not_found_please_check_again_and_sign_acount"));
                        return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
                }
            }   
            else{
                return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
            }         
        }else{
            return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
        }
    }
    public function t_reward_transfer() {
        if(isset($_REQUEST['wallet_system_data'])){
            if(isset($_REQUEST['wallet_system_data']['reward_points_commission'])){
                $tranfer_points = $_REQUEST['wallet_system_data']['reward_points_commission'];
                $wallet_setting = $this->helper->fnGetWalletSystemSettingDataForAll('');
                if(!empty($_SESSION['auth']['user_id'])){
                    $user_id = $_SESSION['auth']['user_id'];
                    $user_info = fn_get_user_info($user_id);
                    if(!empty($user_info) && isset($user_info['points']) && !empty($user_info['points'])){
                        $customer_points = $user_info['points'];
                    }
                    else{
                        $customer_points = 0;
                    }
                }
                else{
                    $customer_points = 0;
                }
                if($customer_points>=$tranfer_points){
                    if(isset($wallet_setting['reward_points_commission'])){
                        $commission = $wallet_setting['reward_points_commission'];
                    }
                    else{
                        $commission = 10;
                    }
                    if(isset($wallet_setting['min_reward_points'])){
                        $min_points = $wallet_setting['min_reward_points'];
                    }
                    else{
                        $min_points = 10;
                    }
                    if(isset($wallet_setting['max_reward_points'])){
                        $max_points = $wallet_setting['max_reward_points'];
                    }
                    else{
                        $max_points = 100;
                    }
                    
                    if($tranfer_points>=$min_points && $tranfer_points<=$max_points){
                        $wallet_system = [];
                        $final_wallet_add_amount = $tranfer_points*$commission/100;
                        $final_wallet_add_amount = $tranfer_points - $final_wallet_add_amount;
                        $wallet_system['recharge_amount'] = fn_format_price_by_currency($final_wallet_add_amount,CART_SECONDARY_CURRENCY,CART_PRIMARY_CURRENCY);
                        if ($this->auth['user_id'] == 0) {
                            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
                        }
                        $user_id = $_SESSION['auth']['user_id'];
                        $email_id = fn_get_user_email($user_id);
                        $wallet_id = $this->loadModel->getUserWalletId($user_id);
                        if(isset($wallet_id) && !empty($wallet_id)){
                            $user_wallet_current_cash = $this->loadModel->getWalletCash(array('total_cash'),array('wallet_id'=>$wallet_id),'db_get_field');
                            $user_wallet_updated_cash = $user_wallet_current_cash + (float)$final_wallet_add_amount;
                
                            $updated_cash = array(
                                'total_cash' => $user_wallet_updated_cash
                            );
                
                            $this->loadModel->updateWalletCash($updated_cash,array('wallet_id'=>$wallet_id));            
                
                            $_data = array(
                                'source'         => "credit by admin",
                                'source_id'      => 0,
                                'wallet_id'      => $wallet_id,
                                'credit_amount'  => $final_wallet_add_amount,
                                'total_amount'   => $user_wallet_updated_cash,
                                'timestamp'      => TIME,
                                'refund_reason'  => 'Points Transfer',                            
                            );
                
                            $wallet_credit_log_id = $this->loadModel->insertWalletCreditLog($_data);
                
                            $tran_data=array(
                                'credit_id' => $wallet_credit_log_id,
                                'wallet_id' => $wallet_id,
                                'timestamp' => TIME,
                            );
                            // fn_print_r("yes");
                            $this->helper->fnWalletSystemChangeUserPoints($customer_points-$tranfer_points,$customer_points, $user_id, 'Wallet Trasfer');
                            // fn_print_r("not");
                            $this->loadModel->insertWalletTransaction($tran_data);            
                
                            $this->loadModel->creditWalletNotification($wallet_credit_log_id);
                                    
                            fn_set_notification('N', __('wallet_credit'), 
                            __('amount_has_been_credited_in_user_wallet',array(
                                '[text_amt]' => $final_wallet_add_amount)));
                            fn_set_notification('N',__("success"),__("transfer_completed_successfully"));
                            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_transactions");
                        }
                        else{
                            fn_set_notification('W',__("error"),__("trasfer_points_not_proceed_wallet_id_not_found"));
                            return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
                        }
                    }
                    else{
                        fn_set_notification('W',__("error"),__("trasfer_points_not_exeed_the_given_limit"));
                        return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
                    }
                }
                else{
                    fn_set_notification('W',__("error"),__("points_is_not_trasfer_more_than_avaible_points"));
                    return $this->response = array(CONTROLLER_STATUS_REDIRECT, "reward_points.userlog");
                }
            }
        }     
    }


    public function bank_transfer() {
        $wallet_setting = $this->helper->fnGetWalletSystemSettingDataForAll('');
        if(isset($wallet_setting) && isset($wallet_setting['status_bank_transfer']) && $wallet_setting['status_bank_transfer'] == 'Y'){
            if(isset($wallet_setting['min_bank_transfer']) && !empty($wallet_setting['min_bank_transfer'])){
                $min_bank_transfer = $wallet_setting['min_bank_transfer'];
            }
            else{
                $min_bank_transfer = 10;
            }
            if(isset($wallet_setting['max_bank_transfer']) && !empty($wallet_setting['max_bank_transfer'])){
                $max_bank_transfer = $wallet_setting['max_bank_transfer'];
            }
            else{
                $max_bank_transfer = 100;
            }
            if($this->auth['user_id']){
                $get_user_wallet_amount = $this->loadModel->getWalletAmount(null,$this->auth['user_id']);
            }
            else{
                $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
            }
            Registry::get('view')->assign('min_bank_transfer', $min_bank_transfer);
            Registry::get('view')->assign('max_bank_transfer', $max_bank_transfer);
            Registry::get('view')->assign('wallet_amount', $get_user_wallet_amount);
        }
        else{
            fn_set_notification('N',__("success"),__("bank_transfer_status_not_active"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
        }       
    }




    public function wallet_to_bank() {

        $getConfig = $this->loadModel->getSettingsData();
        
        $user_id = $this->auth['user_id'];
        
        if ($this->auth['user_id'] == 0)
        {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
            return;
        }

        if($this->helper->fnCheckTransferWalletToBank() != 'Y'){
            fn_set_notification("W",__("warning"),__("you_have_not_permission_to_transfer_amount_in_bank"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
            return;
        }


        if(isset($_REQUEST['wallet_to_bank'])){

            if(!is_numeric($_REQUEST['transfer_amount'])){
                fn_set_notification("W",__("warning"),__("please_enter_the_valid_amount"));
                $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.wallet_to_bank");
                return;
            }
        
            if(isset($_REQUEST['bank_id']) && !empty($_REQUEST['transfer_amount'])){
                $transfer_data = [
                    'user_id'       => $user_id,
                    'bank_id'       => $_REQUEST['bank_id'],
                    'transfer_amount' => $_REQUEST['transfer_amount'],
                    'status'    => 'pending'
                ];


                $wallet_cash_data = $this->loadModel->select('wallet_cash', '*', ['user_id' => $user_id], 'db_get_row');
                

                $min_amount = $getConfig['min_amount_transfer_wallet_to_bank'];
                $max_amount = $getConfig['max_amount_transfer_wallet_to_bank'];

                if(isset($wallet_cash_data['total_cash']) && $wallet_cash_data['total_cash'] >= $_REQUEST['transfer_amount']){
                    if($_REQUEST['transfer_amount'] >= $getConfig['min_amount_transfer_wallet_to_bank'] && $_REQUEST['transfer_amount'] <= $getConfig['max_amount_transfer_wallet_to_bank']){
                        $this->loadModel->insert('wallet_bank_transfer', $transfer_data);

                        fn_set_notification('N', __('notification'), __('your_request_submit_successfully'));
                        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.my_wallet");
                        return;
                    }else{
                        fn_set_notification("W",__("warning"),__("bank_transfer_limit_is").$min_amount.__("_to_").$max_amount);
                    }
                }else{
                    fn_set_notification("W",__("warning"),__("invalid_amount"));
                }

                

            }else{
                fn_set_notification('N', __('notification'), __('parameter_missing'));
            }
            
        }

        $banks = $this->loadModel->select('customer_banks', '*', ['user_id' => $user_id], 'db_get_array');

        if(empty($banks)){
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.add_bank");
            return;
        }

        Registry::get('view')->assign('banks', $banks);
        Registry::get('view')->assign('min_bank_transfer', $getConfig['min_amount_transfer_wallet_to_bank']);
        Registry::get('view')->assign('max_bank_transfer', $getConfig['max_amount_transfer_wallet_to_bank']);

    }


    public function add_bank() {
        
        if ($this->auth['user_id'] == 0)
        {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
            $bank_data = [
                'user_id'       => $this->auth['user_id'],
                'ifsc_code'     => $_REQUEST['ifsc_code'],
                'account_number' => $_REQUEST['account_number'],
                'holder_name' => $_REQUEST['holder_name'],
                'bank_name' => $_REQUEST['bank_name']
            ];

            $this->loadModel->insert('customer_banks', $bank_data);

            fn_set_notification('N', __('notification'), __('bank_added_successfully'));

            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.bank_accounts");
        }

    }


    public function update_bank() {
        
        if ($this->auth['user_id'] == 0)
        {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
            $bank_data = [                
                'ifsc_code'     => $_REQUEST['ifsc_code'],
                'account_number' => $_REQUEST['account_number'],
                'holder_name' => $_REQUEST['holder_name'],
                'bank_name' => $_REQUEST['bank_name']
            ];

            $this->loadModel->update('customer_banks', $bank_data, ['id' => $_REQUEST['id']]);

            fn_set_notification('N', __('notification'), __('bank_updated_successfully'));

            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.bank_accounts");
        }


        $bank_data =  $this->loadModel->select('customer_banks', '*', ['id' => $_REQUEST['id']], 'db_get_row');
        if(empty($bank_data)){
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.bank_accounts");
            return;
        }

        Registry::get('view')->assign('banks', $bank_data);        
        
    }
    




    public function bank_accounts() {

        $getConfig = $this->loadModel->getSettingsData();
        
        $user_id = $this->auth['user_id'];
        
        if ($this->auth['user_id'] == 0)
        {
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
            return;
        }

        

        $banks = $this->loadModel->select('customer_banks', '*', ['user_id' => $user_id], 'db_get_array');

        if(empty($banks)){
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.add_bank");
            return;
        }

        Registry::get('view')->assign('banks', $banks);
        

    }


    public function remove_bank() {

        $this->loadModel->delete('customer_banks', ['id' => $_REQUEST['id']]);

        fn_set_notification('N', __('notification'), __('bank_deleted_successfully'));

        $this->response = array(CONTROLLER_STATUS_REDIRECT, "wallet_system.bank_accounts");
        return;
    }


}
?>
