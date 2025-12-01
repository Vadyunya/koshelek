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

namespace Tygh\Addons\WalletSystem;

use Tygh\Core\ApplicationInterface;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;

class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    /** @inheritDoc */
    public function boot(ApplicationInterface $app)
    {
        $app->register(new ServiceProvider());
    }

    /** @inheritDoc */
    public function getHookHandlerMap()
    {
        return [
            /** @see \Tygh\Addons\PdfDocuments\HookHandlers\OrdersHookHandler::printOrderInvoicesNormalizeParameters() */
            'calculate_cart'=> [
                'addons.wallet_system.hook_handlers.calculate_cart',
                'calculateCart',
            ],

            'is_cart_empty'=> [
                'addons.wallet_system.hook_handlers.cart',
                'isCartEmpty',
            ],

            'pre_add_to_cart'=> [
                'addons.wallet_system.hook_handlers.preaddtocart',
                'preAddToCart',
            ],

            'order_placement_routines'=> [
                'addons.wallet_system.hook_handlers.order_placement_routines',
                'orderPlacementRoutines',
            ],

            'place_order'=> [
                'addons.wallet_system.hook_handlers.place_order',
                'placeOrder',
            ],

            'change_order_status'=> [
                'addons.wallet_system.hook_handlers.change_order_status',
                'changeOrderStatus',
            ],

            'get_order_info'=> [
                'addons.wallet_system.hook_handlers.get_order_info',
                'getOrderInfo',
            ],

            'allow_place_order'=> [
                'addons.wallet_system.hook_handlers.allow_place_order',
                'allowPlaceOrder',
            ],

            'pre_update_order'=> [
                'addons.wallet_system.hook_handlers.pre_update_order',
                'preUpdateOrder',
            ],
            
            'place_suborders'=> [
                'addons.wallet_system.hook_handlers.place_suborders',
                'placeSuborders',
            ],
            
            'mve_place_order'=> [
                'addons.wallet_system.hook_handlers.mve_place_order',
                'mvePlaceOrder',
            ],

            'get_orders_post'=> [
                'addons.wallet_system.hook_handlers.get_orders_post',
                'getOrdersPost',
            ],

            'send_order_notification'=> [
                'addons.wallet_system.hook_handlers.send_order_notification',
                'sendOrderNotification',
            ],

            'get_external_discounts'=> [
                'addons.wallet_system.hook_handlers.get_external_discounts',
                'getExternalDiscounts',
            ],

            'update_profile'=> [
                'addons.wallet_system.hook_handlers.update_profile',
                'updateProfile',
            ],

            'checkout_place_order_before_check_amount_in_stock'=> [
                'addons.wallet_system.hook_handlers.checkout_place_order_before_check_amount_in_stock',
                'checkoutPlaceOrderBeforeCheckAmountInStock',
            ],

            'pre_place_order'=> [
                'addons.wallet_system.hook_handlers.pre_place_order',
                'prePlaceOrder',
            ],

            'get_users_pre'=> [
                'addons.wallet_system.hook_handlers.get_users_pre',
                'getUsersPre',
            ],

            'change_order_status_post'=> [
                'addons.wallet_system.hook_handlers.change_order_status_post',
                'changeOrderStatusPost',
            ],

            'prepare_checkout_payment_methods'=> [
                'addons.wallet_system.hook_handlers.prepare_checkout_payment_methods',
                'prepareChecoutPaymentMethodsHookTest',
            ],
            
            'checkout_select_default_payment_method'=> [
                'addons.wallet_system.hook_handlers.checkout_select_default_payment_method',
                'checkoutSelectDefaultPaymentMethodHookTest',
            ],

            'get_products_post'=> [
                'addons.wallet_system.hook_handlers.get_products_post',
                'getProductsPost',
            ],

            'get_product_data_post'=> [
                'addons.wallet_system.hook_handlers.get_product_data_post',
                'getProductDataPost',
            ],
            
        ];
    }
}
