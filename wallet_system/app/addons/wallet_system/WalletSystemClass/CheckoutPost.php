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

class CheckoutPost extends BaseController {
    /**
     * CheckoutPost constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);

        $this->setRunMode('checkout');
        if(in_array($this->mode,$this->runMode)){
            $this->$mode();
        }
        
    }

    public function checkout() {
        $user_wallet_total_amnt = $this->loadModel->getWalletAmount($wallet_id=null,$user_id=Tygh::$app['session']['auth']['user_id']);

        Registry::get('view')->assign('user_wallet_total_amnt', $user_wallet_total_amnt);

       
        if(!empty($_SESSION['cart']['gift_certificates']) && !empty($_SESSION['cart']['wallet_system'])) 
        {
            fn_set_notification('E',__("error"),__("remove_gift_certificate_first"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "checkout.cart"); 
        }
        if(!empty($_SESSION['cart']['product_groups']) && !empty($_SESSION['cart']['wallet_system'])) 
        {
            fn_set_notification('E',__("error"),__("remove_product_from_cart"));
            $this->response = array(CONTROLLER_STATUS_REDIRECT, "checkout.cart"); 
        }

        if(isset($this->requestParam['is_ajax']) && isset($this->requestParam['shipping_ids']))
        {
            Tygh::$app['ajax']->assign('force_redirection', 'checkout');
            $this->response = [CONTROLLER_STATUS_NO_CONTENT];
        }
    }
}
?>
