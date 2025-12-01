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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Registry;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers add-on services.
     *
     * @param Container $app Application instance
     *
     * @return void
     */
    public function register(Container $app)
    {   
        $app['addons.wallet_system.hook_handlers.calculate_cart'] = static function () {
            return new HookHandlers\CalculateCartHookHandler();
        };

        $app['addons.wallet_system.hook_handlers.cart'] = static function () {
            return new HookHandlers\CartEmptyHookHandler();
        };

        $app['addons.wallet_system.hook_handlers.preaddtocart'] = static function () {
            return new HookHandlers\PreAddToCartHookHandler();
        };

        $app['addons.wallet_system.hook_handlers.order_placement_routines'] = static function () {
            return new HookHandlers\OrderPlacementRoutinesHookHandler();
        };

        $app['addons.wallet_system.hook_handlers.place_order'] = static function () {
            return new HookHandlers\PlaceOrderHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.change_order_status'] = static function () {
            return new HookHandlers\ChangeOrderStatusHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.get_order_info'] = static function () {
            return new HookHandlers\GetOrderInfoHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.allow_place_order'] = static function () {
            return new HookHandlers\AllowPlaceOrderHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.pre_update_order'] = static function () {
            return new HookHandlers\PreUpdateOrderHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.place_suborders'] = static function () {
            return new HookHandlers\PlaceSubordersHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.mve_place_order'] = static function () {
            return new HookHandlers\MvePlaceOrderHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.get_orders_post'] = static function () {
            return new HookHandlers\GetOrdersPostHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.send_order_notification'] = static function () {
            return new HookHandlers\SendOrderNotificationHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.get_external_discounts'] = static function () {
            return new HookHandlers\GetExternalDiscountsHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.update_profile'] = static function () {
            return new HookHandlers\UpdateProfileHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.checkout_place_order_before_check_amount_in_stock'] = static function () {
            return new HookHandlers\CheckoutPlaceOrderBeforeCheckAmountInStockHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.pre_place_order'] = static function () {
            return new HookHandlers\PrePlaceOrderHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.get_users_pre'] = static function () {
            return new HookHandlers\GetUsersPreHookHandler();
        };
        
        $app['addons.wallet_system.hook_handlers.change_order_status_post'] = static function () {
            return new HookHandlers\ChangeOrderStatusPostHookHandler();
        };
        $app['addons.wallet_system.hook_handlers.prepare_checkout_payment_methods'] = static function () {
            return new HookHandlers\PrepareChecoutPaymentMethodsHookTestHookHandler();
        };
        $app['addons.wallet_system.hook_handlers.checkout_select_default_payment_method'] = static function () {
            return new HookHandlers\CheckoutSelectDefaultPaymentMethodHookTestHookHandler();
        };
        $app['addons.wallet_system.hook_handlers.get_products_post'] = static function () {
            return new HookHandlers\GetProductsPost();
        };
        $app['addons.wallet_system.hook_handlers.get_product_data_post'] = static function () {
            return new HookHandlers\GetProductDataPost();
        };

    }
}
