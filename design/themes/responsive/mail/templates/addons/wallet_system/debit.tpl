<p>{{ __("dear") }} {{ wallet_data.user_name|default("customer") }},</p>
<p>{{ __("wallet_debit_details") }}: {{ wallet_data.amount }}</p>
<p>{{ __("balance") }}: {{ wallet_data.total_cash }}</p>
{% if wallet_data.order_id %}
<p>{{ __("order") }} #: {{ wallet_data.order_id }}</p>
{% endif %}
