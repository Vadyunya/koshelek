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

use Tygh\Addons\InstallerInterface;
use Tygh\Core\ApplicationInterface;
use Tygh\Enum\NotificationSeverity;
use Tygh\Tygh;
use WalletSystemModel\WalletSystemModel;

class Installer implements InstallerInterface
{

     /**
     * @var object WalletSystemModel class.
     */
    public $loadModel;

    /**
     * @inheritDoc
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function onBeforeInstall()
    {
    }

    /**
     * @inheritDoc
     */
    public function onInstall()
    {

        $addon_name = fn_get_lang_var('wallet_system');
        
        Tygh::$app['view']->assign('mode', 'notification');
        fn_set_notification(
            'S', __('well_done'), __(
                'wk_webkul_user_guide_content', array(
                    '[support_link]' => 'https://webkul.uvdesk.com/en/customer/create-ticket/',
                    '[user_guide]' => 'https://webkul.com/blog/cs-cart-wallet-system/',
                    '[addon_name]' => $addon_name,
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function onUninstall()
    {
        $this->loadModel = new WalletSystemModel();
        $this->loadModel->delete('settings_objects', ['name' => 'wallet_system_tpl_data']);

        $this->loadModel->delete('images_links', ['object_type' => 'transaction_image_banner']);

    }
}
