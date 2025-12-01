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
use WalletSystemModel\WalletSystemModel;
use Tygh\Addons\WalletSystem\Helper;

class BaseController {
    /**
     * @var string mode is the method of the class.
     */
    protected $mode;

    /**
     * @var string mode is the method of the class.
     */
    protected $auth;

    protected $cart;
    
    /**
     * @var string Server Request method like GET,POST is save under this variable
     */
    protected $requestMethod;

    /**
     * @var array|string $_REQUEST data is save under this variable
     */
    protected $requestParam;

    /**
     * @var array this variable stores all the mode which will be able to run under this class
     */
    protected $runMode = array();

    /**
     * @var object this variable is used to return the response.
     */
    public $response;

    /**
     * @var object this variable is used to run the model method in class.
     */
    protected $loadModel;

    protected $helper;
    
    /**
     * BaseController constructor.
     *
     * @param string $mode
     */
    public function __construct($mode) {
        $this->mode = $mode;
        $this->loadModel = new WalletSystemModel();
        $this->helper = new Helper();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestParam = $_REQUEST;
        $this->auth = $_SESSION['auth'];
        $this->cart = $_SESSION['cart'];
        Registry::get('view')->assign('helper', $this->helper);
    }

    /**
     * @param array $runMode
     * 
     * @return void
     */
    protected function setRunMode($runMode=array()) {
        if(is_array($runMode)){
            $this->runMode = array_unique($runMode);
        } else {
            array_push($this->runMode,$runMode);
            $this->runMode = array_unique($this->runMode);
        }
    }

}