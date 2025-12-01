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

require_once Registry::get('config.dir.schemas') . 'block_manager/blocks.functions.php';

if (version_compare(PRODUCT_VERSION, 4.9) == '1') {
    $schema['lite_checkout_wallet_system'] = array( 
        'show_on_locations' => ['checkout'],
        'templates'         => 'addons/wallet_system/views/wallet_system/wallet_payment.tpl',
        'wrappers'          => 'blocks/lite_checkout/wrappers',
        'single_for_location' => 1,
    );
}

return $schema;