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

class AddOnPost extends BaseController {
    
    protected $mode;

    protected $requestMethod;

    protected $requestParam;

    public $response;

    public function __construct($mode)
    {
        parent::__construct($mode);
        if($mode == 'update') {
            $this->$mode();
        }
        

    }

    public function update() {
        $error = true;
        if ($_REQUEST['addon'] == 'wallet_system' && (!empty($_REQUEST['wallet_system_data']))) {
            
            $validate_data = $_REQUEST['wallet_system_data'];

            if((!empty($validate_data['min_recharge_amount']) && !empty($validate_data['max_recharge_amount'])) && $validate_data['max_recharge_amount'] <= $validate_data['min_recharge_amount']) {      
            fn_set_notification('E',__("error"),__("text_recharge_max_error",array()));
            $error = false;
            } 

            if((!empty($validate_data['min_refund_amount']) && !empty($validate_data['max_refund_amount'])) && $validate_data['max_refund_amount'] <= $validate_data['min_refund_amount']) {      
            fn_set_notification('E',__("error"),__("text_refund_max_error",array()));
            $error = false;
            }
                
            if((!empty($validate_data['min_transfer_amount']) && !empty($validate_data['max_transfer_amount'])) && $validate_data['max_transfer_amount'] <= $validate_data['min_transfer_amount']) {
            fn_set_notification('E',__("error"),__("text_transfer_max_error",array()));
            $error = false;
            }
                
                
            if($error) {
                fn_trusted_vars('wallet_system_data');
                $this->loadModel->updateSettingData($_REQUEST['wallet_system_data']);  
            }
        }

        if ($_REQUEST['addon'] == 'wallet_system') 
        {
            $wallet_system_data = $this->loadModel->getSettingData();
            Registry::get('view')->assign('wallet_system_data', $wallet_system_data);
        }
    }


}
