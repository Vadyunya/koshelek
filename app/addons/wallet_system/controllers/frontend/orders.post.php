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

use WalletSystemClass\FrontEndOrderPost;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$frontEndOrderPost = new FrontEndOrderPost($mode);
if(isset($frontEndOrderPost->response) && !empty($frontEndOrderPost->response)){
    return $frontEndOrderPost->response;
}
?>