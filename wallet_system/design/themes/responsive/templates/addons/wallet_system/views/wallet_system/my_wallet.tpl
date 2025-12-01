{capture name="mainbox"}
    <div class="wallet-system-summary">
        <h3 class="ty-subheader">{__("my_wallet")}</h3>
        <div class="wallet-system-summary__balance">
            <span class="wallet-system-summary__label">{__("balance")}: </span>
            {include file="common/price.tpl" value=$total_cash primary_currency=$primary_currency}
        </div>
        <div class="wallet-system-summary__actions">
            <a class="ty-btn ty-btn__primary" href="{"wallet_system.direct_recharge"|fn_url}">
                {__("wallet_recharge")}
            </a>
        </div>
    </div>
{/capture}
{include file="common/mainbox.tpl" title=__("my_wallet") content=$smarty.capture.mainbox}
