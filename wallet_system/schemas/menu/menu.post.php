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

$schema['central']['marketing']['items']['wallet_system'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'wallet_system.wallet_transaction',
    'position' => 50,
    'subitems' => array(
	        'wallet_system' => array(
	            'href' => 'wallet_system.wallet_transaction',
	            'position' => 100,
	        ),
	        'wallet_bank_transfer' => array(
	            'href' => 'wallet_system.wallet_bank_transfer',
	            'position' => 200,
	        ),       
    ),
);

return $schema;