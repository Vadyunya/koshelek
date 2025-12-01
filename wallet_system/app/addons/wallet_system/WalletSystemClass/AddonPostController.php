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

class AddonPostController extends BaseController {
    /**
     * WalletSystem constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);

        $this->setRunMode('update');
        if(in_array($this->mode,$this->runMode)){
            $this->$mode();
        }
        
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $error = true;        
            if ($this->mode == 'update' 
            && $_REQUEST['addon'] == 'wallet_system' 
            && (!empty($_REQUEST['wallet_system_data']))) 
            {
              
                $validate_data = $_REQUEST['wallet_system_data'];

                if(!empty($validate_data['new_registration_amount']) && $validate_data['new_registration_amount'] > 99999999){

                  fn_set_notification('W',__("warning"),__("wk_wallet_system_text_max_limit_error",array(
                    '[text_max]'=>'Recharge',
                    '[text_value]'=>'99999999'
                  )));
                  $error = false;
                }    

                if(empty($validate_data['min_recharge_amount'])){
                  fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                    '[text_error]'=>'Minimum recharge'
                  )));      
                    $error = false;
                } 
                if(empty($validate_data['max_recharge_amount'])){
                  fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                    '[text_error]'=>'Maximum recharge'
                  )));      
                    $error = false;
                }

                if(empty($validate_data['min_refund_amount'])){
                  fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                    '[text_error]'=>'Minimum refund'
                  )));      
                    $error = false;
                }

                if(empty($validate_data['max_refund_amount'])){
                  fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                    '[text_error]'=>'Maximum refund'
                  )));      
                    $error = false;
                }

                if(empty($validate_data['min_transfer_amount'])){
                  fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                    '[text_error]'=>'Minimum transfer'
                  )));      
                    $error = false;
                }

                if(empty($validate_data['max_transfer_amount'])){
                  fn_set_notification('W',__("warning"),__("wk_wallet_system_text_empty_error",array(
                    '[text_error]'=>'Maximum transfer'
                  )));      
                    $error = false;
                }

                if(!empty($validate_data['max_recharge_amount']) && !empty($validate_data['min_recharge_amount'])){
                  if($validate_data['max_recharge_amount'] < $validate_data['min_recharge_amount']) {
                    fn_set_notification('W',__("warning"),__("wk_wallet_system_text_condition_min_error",array(
                      '[text_min]'=>'Minimum recharge',
                      '[text_min_last]'=>'maximum'
                    )));      
                    $error = false;
                  }

                  if($validate_data['max_recharge_amount'] > 99999999) {
                    fn_set_notification('W',__("warning"),__("wk_wallet_system_text_max_limit_error",array(
                      '[text_max]'=>'Max recharge',
                      '[text_value]'=>'99999999'
                    )));      
                    $error = false;
                  }
                }

                if(!empty($validate_data['max_refund_amount']) && !empty($validate_data['min_refund_amount'])){
                  if($validate_data['max_refund_amount'] < $validate_data['min_refund_amount']) {
                    fn_set_notification('E',__("warning"),__("wk_wallet_system_text_condition_min_error",array(
                      '[text_min]'=>'Minimum refund',
                      '[text_min_last]'=>'maximum'
                    )));      
                    $error = false;
                  }

                  if($validate_data['max_refund_amount'] > 99999999) {
                    fn_set_notification('E',__("warning"),__("wk_wallet_system_text_max_limit_error",array(
                      '[text_max]'=>'Max refund',
                      '[text_value]'=>'99999999'
                    )));      
                    $error = false;
                  }    
                }
                if(!empty($validate_data['max_transfer_amount']) && !empty($validate_data['min_transfer_amount'])){
                  if($validate_data['max_transfer_amount'] < $validate_data['min_transfer_amount']) {
                    fn_set_notification('E',__("warning"),__("wk_wallet_system_text_condition_min_error",array(
                      '[text_min]'=>'Minimum transfer',
                      '[text_min_last]'=>'maximum'
                    )));      
                    $error = false;
                  }

                  if($validate_data['max_transfer_amount'] > 99999999) {
                    fn_set_notification('E',__("warning"),__("wk_wallet_system_text_max_limit_error",array(
                      '[text_max]'=>'Max transfer',
                      '[text_value]'=>'99999999'
                    )));      
                    $error = false;
                  }
                }
                if(!empty($validate_data['max_reward_points']) && !empty($validate_data['min_reward_points'])){
                  if($validate_data['max_reward_points'] < $validate_data['min_reward_points']) {
                    fn_set_notification('E',__("warning"),__("wk_wallet_system_max_reward_point_err",array(
                      '[text_max]'=>'maximum reward',
                      '[text_max_last]'=>'minimum reward'
                    )));      
                    $error = false;
                  }

                  if($validate_data['max_reward_points'] > 99999999) {
                    fn_set_notification('E',__("warning"),__("wk_wallet_system_text_max_limit_error",array(
                      '[text_max]'=>'Max reward',
                      '[text_value]'=>'99999999'
                    )));      
                    $error = false;
                  }
                }
               
                
                
                if($error) {
                  fn_trusted_vars('wallet_system_data');
                  
                  $this->loadModel->walletSystemSettingData($_REQUEST['wallet_system_data']);  
                }
              }
        } else {

            if ($_REQUEST['addon'] == 'wallet_system') {
                $wallet_system_data = $this->loadModel->getSettingsData();
                Registry::get('view')->assign('wallet_system_data', $wallet_system_data);
            }
        }

        $active_tab = isset($_REQUEST['selected_sub_section'])?$_REQUEST['selected_sub_section']:'wallet_system_tab_general';
        Registry::get('view')->assign('active_tab', $active_tab);
    }


}
