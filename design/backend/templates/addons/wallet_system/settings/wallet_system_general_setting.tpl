<div class="control-group">
    <label class="control-label" for="ws_status_wallet_transfer">{__("enable_wallet_transfers")}</label>
    <div class="controls">
        <input type="hidden" name="wallet_system_data[status_wallet_transfer]" value="N" />
        <input type="checkbox" id="ws_status_wallet_transfer" name="wallet_system_data[status_wallet_transfer]" value="Y" {if $wallet_system_data.status_wallet_transfer == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_status_bank_transfer">{__("enable_bank_transfers")}</label>
    <div class="controls">
        <input type="hidden" name="wallet_system_data[status_bank_transfer]" value="N" />
        <input type="checkbox" id="ws_status_bank_transfer" name="wallet_system_data[status_bank_transfer]" value="Y" {if $wallet_system_data.status_bank_transfer == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_min_recharge">{__("wallet_min_recharge")}</label>
    <div class="controls">
        <input type="text" id="ws_min_recharge" name="wallet_system_data[min_recharge_amount]" value="{$wallet_system_data.min_recharge_amount}" class="input-medium" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_max_recharge">{__("wallet_max_recharge")}</label>
    <div class="controls">
        <input type="text" id="ws_max_recharge" name="wallet_system_data[max_recharge_amount]" value="{$wallet_system_data.max_recharge_amount}" class="input-medium" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_min_transfer">{__("wallet_min_transfer")}</label>
    <div class="controls">
        <input type="text" id="ws_min_transfer" name="wallet_system_data[min_transfer_amount]" value="{$wallet_system_data.min_transfer_amount}" class="input-medium" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_max_transfer">{__("wallet_max_transfer")}</label>
    <div class="controls">
        <input type="text" id="ws_max_transfer" name="wallet_system_data[max_transfer_amount]" value="{$wallet_system_data.max_transfer_amount}" class="input-medium" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_reward_commission">{__("wallet_reward_points_commission")}</label>
    <div class="controls">
        <input type="text" id="ws_reward_commission" name="wallet_system_data[reward_points_commission]" value="{$wallet_system_data.reward_points_commission}" class="input-medium" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_min_reward">{__("wallet_min_reward_points")}</label>
    <div class="controls">
        <input type="text" id="ws_min_reward" name="wallet_system_data[min_reward_points]" value="{$wallet_system_data.min_reward_points}" class="input-medium" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_max_reward">{__("wallet_max_reward_points")}</label>
    <div class="controls">
        <input type="text" id="ws_max_reward" name="wallet_system_data[max_reward_points]" value="{$wallet_system_data.max_reward_points}" class="input-medium" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_registration_amount">{__("wallet_registration_bonus")}</label>
    <div class="controls">
        <input type="text" id="ws_registration_amount" name="wallet_system_data[new_registration_amount]" value="{$wallet_system_data.new_registration_amount}" class="input-medium" />
        <label class="checkbox inline">
            <input type="hidden" name="wallet_system_data[new_registration_cash_back]" value="N" />
            <input type="checkbox" name="wallet_system_data[new_registration_cash_back]" value="Y" {if $wallet_system_data.new_registration_cash_back == "Y"}checked="checked"{/if} />
            {__("wallet_enable_registration_bonus")}
        </label>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_refund_min">{__("wallet_min_refund")}</label>
    <div class="controls">
        <input type="text" id="ws_refund_min" name="wallet_system_data[min_refund_amount]" value="{$wallet_system_data.min_refund_amount}" class="input-medium" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_refund_max">{__("wallet_max_refund")}</label>
    <div class="controls">
        <input type="text" id="ws_refund_max" name="wallet_system_data[max_refund_amount]" value="{$wallet_system_data.max_refund_amount}" class="input-medium" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_transfer_bank">{__("wallet_allow_bank_withdraw")}</label>
    <div class="controls">
        <input type="hidden" name="wallet_system_data[status_transfer_wallet_to_bank]" value="N" />
        <input type="checkbox" id="ws_transfer_bank" name="wallet_system_data[status_transfer_wallet_to_bank]" value="Y" {if $wallet_system_data.status_transfer_wallet_to_bank == "Y"}checked="checked"{/if} />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_transfer_all">{__("wallet_transfer_for_all_customers")}</label>
    <div class="controls">
        <input type="hidden" name="wallet_system_data[transfer_for_all_customer]" value="N" />
        <input type="checkbox" id="ws_transfer_all" name="wallet_system_data[transfer_for_all_customer]" value="Y" {if $wallet_system_data.transfer_for_all_customer == "Y"}checked="checked"{/if} />
        <p class="muted description">{__("wallet_transfer_for_all_customers_notice")}</p>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_customers">{__("wallet_allowed_customers")}</label>
    <div class="controls">
        <input type="text" id="ws_customers" name="wallet_system_data[customers]" value="{$wallet_system_data.customers}" class="input-xxlarge" placeholder="{__("enter_customer_ids_comma")}" />
        <p class="muted description">{__("wallet_customer_ids_hint")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ws_bank_min">{__("wallet_min_bank_transfer")}</label>
    <div class="controls">
        <input type="text" id="ws_bank_min" name="wallet_system_data[min_bank_transfer_amount]" value="{$wallet_system_data.min_bank_transfer_amount}" class="input-medium" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="ws_bank_max">{__("wallet_max_bank_transfer")}</label>
    <div class="controls">
        <input type="text" id="ws_bank_max" name="wallet_system_data[max_bank_transfer_amount]" value="{$wallet_system_data.max_bank_transfer_amount}" class="input-medium" />
    </div>
</div>
