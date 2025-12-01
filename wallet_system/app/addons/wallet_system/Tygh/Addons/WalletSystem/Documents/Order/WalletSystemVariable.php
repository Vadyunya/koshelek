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


namespace Tygh\Addons\WalletSystem\Documents\Order;


use Tygh\Template\Document\Order\Context;
use Tygh\Template\IActiveVariable;
use Tygh\Template\IVariable;
use Tygh\Tools\Formatter;

/**
 * Class WalletSystemVariable
 * @package Tygh\Addons\WalletSystem\Documents\Order
 */
class WalletSystemVariable implements IVariable
{
    public $paid_by_wallet;
    
    public function __construct(Context $context, Formatter $formatter)
    {
        $order = $context->getOrder();
        if (!empty($order->data['pay_by_wallet_amount'])) {
            $this->paid_by_wallet = $formatter->asPrice($order->data['pay_by_wallet_amount']); 
        }
    }

    /**
     * @inheritDoc
     */
   
}