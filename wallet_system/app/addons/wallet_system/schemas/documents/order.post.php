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

$schema['WalletSystem'] = array(
    'class' => '\Tygh\Addons\WalletSystem\Documents\Order\WalletSystemVariable',
    'arguments' => array('#context', '@formatter'),
);

return $schema;