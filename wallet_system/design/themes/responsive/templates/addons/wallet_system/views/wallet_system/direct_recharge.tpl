{capture name="mainbox"}
<form action="" method="post" name="wallet_direct_recharge_form" class="wallet-system-recharge-form">
    <input type="hidden" name="dispatch" value="wallet_system.direct_recharge" />
    <div class="ty-control-group">
        <label for="ws_recharge_amount" class="ty-control-group__title">{__("wallet_recharge_amount")}</label>
        <input type="text" id="ws_recharge_amount" name="wallet_recharge[recharge_amount]" value="{$wallet_recharge.recharge_amount}" class="ty-input-text" />
        {if $recharge_limits.min || $recharge_limits.max}
            <p class="ty-help-text">
                {__("wallet_limit_is")}: {$recharge_limits.min} - {$recharge_limits.max}
            </p>
        {/if}
    </div>

    <div class="ty-control-group">
        <label class="ty-control-group__title">{__("payment_methods")}</label>
        {if $payments}
            <ul class="ty-payments-list">
                {foreach from=$payments item="payment"}
                    <li class="ty-payments-list__item">
                        <label>
                            <input type="radio" name="wallet_recharge[payment_id]" value="{$payment.payment_id}" {if $wallet_recharge.payment_id == $payment.payment_id}checked{/if} />
                            {$payment.payment}
                        </label>
                    </li>
                {/foreach}
            </ul>
        {else}
            <p class="ty-no-items">{__("no_payments_available")}</p>
        {/if}
    </div>

    <div class="ty-control-group">
        <label class="ty-control-group__title">
            <input type="checkbox" name="wallet_recharge[paid_confirmed]" value="Y" {if $wallet_recharge.paid_confirmed == "Y"}checked{/if} />
            Я оплатил
        </label>
    </div>

    <div class="buttons-container">
        <button class="ty-btn ty-btn__primary" type="submit">Готово</button>
        <a class="ty-btn" href="{"wallet_system.my_wallet"|fn_url}">{__("cancel")}</a>
    </div>
</form>
{/capture}
{include file="common/mainbox.tpl" title=__("wallet_recharge") content=$smarty.capture.mainbox}
