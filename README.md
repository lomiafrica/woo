# lomi. for WooCommerce

Accept WooCommerce payments with lomi. hosted checkout in XOF, USD, and EUR. Merchant revenue is credited in XOF.

## Overview

lomi. for WooCommerce connects your WooCommerce store to lomi. hosted checkout.

The plugin lets merchants accept customer payments in XOF, USD, and EUR while merchant revenue is credited in XOF. Customers are sent to a secure lomi. checkout session to complete payment, and WooCommerce order status is updated when the payment is confirmed.

## Current Payment Model

- Supported customer payment currencies: XOF, USD, EUR
- Merchant revenue credited in XOF
- Secure lomi. hosted checkout
- Test and live API key configuration
- Webhook-based payment confirmation
- Optional checkout metadata
- Optional additional lomi. payment methods with custom labels and card-brand filters

## Important Notes

- This plugin does not support stores configured with currencies outside XOF, USD, and EUR unless the supported-currency filter is customized.
- Automatic refunds from WooCommerce are not available. Process refunds from the lomi. dashboard.
- Saved cards and automatic subscription renewals are not charged in WooCommerce with lomi. hosted checkout.
- WooCommerce Subscriptions can still be used to manage subscription products, schedules, and renewal orders.

## Requirements

- WordPress 6.2 or later
- WooCommerce 9.6 or later
- PHP 7.4 or later

## Installation

1. Upload the plugin folder to `wp-content/plugins/` or install it from the WordPress admin plugin screen.
2. Activate the plugin from `WordPress Admin > Plugins`.
3. Go to `WooCommerce > Settings > Payments`.
4. Select `lomi.`.
5. Enable the gateway.
6. Add your lomi. test or live API keys.
7. Configure the webhook URL shown in the settings screen from your lomi. dashboard.
8. Save changes.

## Configuration

### Main Settings

- Enable or disable lomi. on checkout.
- Set the checkout title customers see during payment.
- Set the checkout description customers see during payment.
- Enable test mode while testing.
- Add live and test API credentials.
- Add live and test webhook signing secrets.

### Webhooks

Configure the webhook URL shown in the lomi. settings screen from your lomi. dashboard.

The webhook signing secret in WooCommerce must match the secret configured for the webhook endpoint in lomi. The plugin uses webhooks to confirm successful payments and update WooCommerce orders.

### Additional Payment Methods

The plugin can create additional lomi. payment methods that reuse the main gateway API keys.

Use additional payment methods when you need separate checkout labels or card-brand filters in WooCommerce. These payment methods still use lomi. hosted checkout.

### Supported Currencies

The default supported store currencies are:

- XOF
- USD
- EUR

Stores using another currency will see the gateway disabled by default.

Developers can customize the supported currency list with the `woocommerce_lomi_supported_currencies` filter.

## FAQ

### Which currencies does this plugin support?

The plugin supports XOF, USD, and EUR by default.

### What currency is merchant revenue credited in?

Merchant revenue is credited in XOF.

### Does this plugin use hosted checkout?

Yes. Customers complete payment through lomi. hosted checkout.

### Are automatic WooCommerce refunds supported?

No. Process refunds from the lomi. dashboard.

### Are saved cards supported?

No. Saved card payments are not available with lomi. hosted checkout in this plugin.

### Are automatic subscription renewals charged by WooCommerce?

No. WooCommerce Subscriptions can manage subscription products and renewal orders, but automatic renewal charges are not processed in WooCommerce through lomi. hosted checkout.

### How do I test payments?

Enable test mode, enter your test API credentials and test webhook signing secret, then run checkout using a test order.

## Changelog

### 1.0.0

- Initial lomi. for WooCommerce release.
- Support lomi. hosted checkout for XOF, USD, and EUR.
- Credit merchant revenue in XOF.
- Add webhook-based order confirmation.
- Add test and live API credential settings.
- Add WooCommerce Blocks checkout support.
- Add French translation files.
