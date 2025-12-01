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
use WalletSystemClass\CheckoutPre;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
$checkoutPre = new CheckoutPre($mode);
if(isset($checkoutPre->response) && !empty($checkoutPre->response)) {
  return $checkoutPre->response;
}
?>