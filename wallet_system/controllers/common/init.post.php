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


use Tygh\Registry;
use Tygh\Addons\WalletSystem\Helper;

$helper = new Helper();

Registry::get('view')->assign('helper', $helper);

?>