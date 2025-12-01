<?php
/******************************************************************
# Wallet - Wallet                                                 *
# ----------------------------------------------------------------*
# author    Webkul                                                *
# copyright Copyright (C) 2010 webkul.com. All Rights Reserved.   *
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL     *
# Websites: http://webkul.com                                     *
*******************************************************************
*/ 

use Tygh\Registry;
use Tygh\SESSION;
use WalletSystemClass\CheckoutPost;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$checkoutPost = new CheckoutPost($mode);
if(isset($checkoutPost->response) && !empty($checkoutPost->response)){
   return $checkoutPost->response;
}
?>