=== lomi. for WooCommerce ===
Contributors: lomi
Tags: woocommerce, payment, checkout, africa, xof
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.002.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept WooCommerce payments with lomi. hosted checkout in XOF, USD, and EUR.

== Description ==

lomi. for WooCommerce connects your store to lomi. hosted checkout. Customers pay on a secure lomi. page; WooCommerce orders are completed via the return URL and signed webhooks.

* Supported store currencies: XOF, USD, EUR
* Test and live API keys
* Refunds from the WooCommerce order screen (when the payment is linked)
* WooCommerce Subscriptions: map products to lomi recurring prices for subscription checkout
* Payouts and balance: lomi. dashboard

== Installation ==

1. Download `woo-lomi.zip` from [GitHub Releases](https://github.com/lomiafrica/lomi./releases/latest/download/woo-lomi.zip).
2. In WordPress admin go to **Plugins → Add New → Upload Plugin**, choose the zip, and activate.
3. Go to **WooCommerce → Settings → Payments → lomi.** and enable the gateway.
4. Add your API keys and webhook signing secret from [dashboard.lomi.africa](https://dashboard.lomi.africa).
5. Configure the webhook URL shown in the gateway settings in your lomi. dashboard.

== Frequently Asked Questions ==

= Where do I download the plugin? =

From GitHub Releases: https://github.com/lomiafrica/lomi./releases/latest/download/woo-lomi.zip

= Can I refund from WooCommerce? =

Yes, when the order stores a lomi transaction ID (after payment confirmation). Otherwise refund from the lomi. dashboard.

= Do subscription renewals charge automatically in WooCommerce? =

No. Recurring billing is handled by lomi. Link subscription products to a lomi recurring price; renewals are managed via the lomi. subscription engine and customer portal.

== Changelog ==

= 1.002.0 =
* GitHub Releases distribution (`woo-lomi.zip`)
* Setup health panel and API connection test
* WooCommerce refunds via lomi. API
* Persist lomi transaction and subscription IDs on orders
* WooCommerce Subscriptions: lomi price ID mapping and checkout gating
* `REFUND_COMPLETED` webhook order notes

= 1.001.1 =
* Initial hosted checkout integration

== Upgrade Notice ==

= 1.002.0 =
Adds refunds, subscription price mapping, and GitHub release installs. Reconfigure webhooks for PAYMENT_SUCCEEDED and REFUND_COMPLETED.
