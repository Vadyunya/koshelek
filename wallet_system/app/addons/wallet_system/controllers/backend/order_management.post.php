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

use WalletSystemClass\OrderManagementPost;
use Tygh\Registry;

$orderManagementPost = new OrderManagementPost($mode);

if(isset($orderManagementPost->response) && !empty($orderManagementPost->response)){
    return $orderManagementPost->response;
}
?>





