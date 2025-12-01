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


use WalletSystemClass\IndexPost;
use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\DateTimeHelper;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$indexPost = new IndexPost($mode);

if(isset($indexPost->response) && !empty($indexPost->response)){
    return $indexPost->response;
}