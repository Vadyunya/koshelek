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

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['bonuses']['wallet_cash_back'] = array(
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'discount_bonuses' => array('by_percentage','by_fixed'),
        'zones' => array('cart'),
        'filter' => 'floatval'
    );
 }  
return $schema;
?>