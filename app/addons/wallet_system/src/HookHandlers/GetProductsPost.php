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

namespace Tygh\Addons\WalletSystem\HookHandlers;
use WalletSystemModel\WalletSystemModel;

class GetProductsPost{

    /**
     * @var object this variable is used to run the model method in class.
     */
    protected $loadModel;

    /**
     * @var string mode is the method of the class.
     */
    protected $auth;


    function getProductsPost(&$products, $params, $lang_code) {

        foreach ($products as $product_id => $product) {    

            $promotion_datas = fn_get_promotions([])[0];
            

            foreach($promotion_datas as $promotion_data){
                if($promotion_data['status'] == 'A'){
                    $promotion_conditions = unserialize($promotion_data['conditions']);
                    $promotion_bonuses = unserialize($promotion_data['bonuses']);

                    
                    if(isset($promotion_bonuses[1]) && isset($promotion_bonuses[1]['bonus']) && $promotion_bonuses[1]['bonus'] == 'wallet_cash_back'){


                        foreach($promotion_conditions['conditions'] as $promotion_condition){
                            if($promotion_condition['condition'] == 'products'){
                                foreach($promotion_condition['value'] as $promotion_product){
                                    if($product_id == $promotion_product['product_id']){
                                        $products[$product_id]['product_promotion'] = $promotion_bonuses['1']['discount_value'];
                                        $products[$product_id]['product_promotion_type'] = $promotion_bonuses['1']['discount_bonus'];
                                        $products[$product_id]['product_promotion_quantity'] = $promotion_product['amount'];
                                        
                                    }
                                }
                            }
                        
                        }
                    }
                }
            }

        }
    }

    
}