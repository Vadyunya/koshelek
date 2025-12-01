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

use \Tygh\Enum\Addons\Rma\ReturnOperationStatuses;

use WalletSystemClass\RmaPost;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $rmaPost = new RmaPost($mode);

  if(isset($rmaPost->response) && !empty($rmaPost->response)){
      return $rmaPost->response;
  }
}
?>