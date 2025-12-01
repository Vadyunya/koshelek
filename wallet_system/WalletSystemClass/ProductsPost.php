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



namespace WalletSystemClass;
use Tygh\Registry;
use Tygh\Tygh;

class ProductsPost extends BaseController {
    /**
     * ProductsPost constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        parent::__construct($mode);

        $this->setRunMode('view');
        if(in_array($this->mode,$this->runMode)){
            $this->$mode();
        }
        
    }

    public function view() {

        $product_id = $_REQUEST['product_id'];

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
                                    Registry::get('view')->assign('product_promotion', $promotion_bonuses['1']['discount_value']);
                                    Registry::get('view')->assign('product_promotion_type', $promotion_bonuses['1']['discount_bonus']);
                                    Registry::get('view')->assign('product_promotion_quantity', $promotion_product['amount']);
                                    return;
                                }
                            }
                        }
                    
                    }
                }
            }
        }
        
    }

}