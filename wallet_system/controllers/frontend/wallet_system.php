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

if (!defined('BOOTSTRAP')) {
   die('Access denied');
}

use Tygh\Registry;

use WalletSystemClass\FrontendWalletSystem;

$frontendWalletSystem = new FrontendWalletSystem($mode);

if(isset($frontendWalletSystem->response) && !empty($frontendWalletSystem->response)){
    return $frontendWalletSystem->response;
}
?>