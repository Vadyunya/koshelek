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

class CheckoutPre extends BaseController {
    /**
     * CheckoutPre constructor.
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
        
        if (!empty($_SESSION['auth']['user_id'])) {            
            if (isset($_REQUEST['wallet_cash_applied'])) {
            } else {

                $_SESSION['cart']['wallet']['current_cash'] = $this->loadModel->getWalletAmount(null, $_SESSION['auth']['user_id']);

                if(isset($_SESSION['cart']['wallet']['used_cash'])) {

                    $current_cash = $this->loadModel->getWalletAmount(null, $_SESSION['auth']['user_id']);

                    if (empty($_SESSION['cart']['wallet']['used_cash'])) {
                        $_SESSION['cart']['wallet']['used_cash'] = 0;
                    }

                    if ($_SESSION['cart']['wallet']['used_cash'] > $current_cash) {
                        $_SESSION['cart']['wallet']['used_cash'] = $current_cash;
                    }

                    $_SESSION['cart']['wallet']['current_cash'] = $current_cash - $_SESSION['cart']['wallet']['used_cash'];
                }
            }

            if (isset($_SESSION['cart']['wallet_system'])) {
            } else {
                Registry::get('view')->assign('show_wallet', "yes");
            }
        }

    }
}
?>
