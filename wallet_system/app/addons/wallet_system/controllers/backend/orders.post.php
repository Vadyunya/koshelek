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

use Tygh\Notifications\EventIdProviders\OrderProvider;
use Tygh\Pdf;
use Tygh\Registry;
use Tygh\Shippings\Shippings;
use Tygh\Storage;
use Tygh\Tygh;
use Tygh\Tools\Url;
use WalletSystemClass\OrdersPost;

$ordersPost = new OrdersPost($mode);

if(isset($ordersPost->response) && !empty($ordersPost->response)){
    return $ordersPost->response;
}

?>