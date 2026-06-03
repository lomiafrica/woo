<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shared base for additional lomi. gateways (hosted checkout; settings inherit from main lomi. gateway).
 */
class WC_Gateway_Custom_Lomi extends WC_Gateway_Lomi_Subscriptions {

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
 
		$this->form_fields = array(
			'enabled'                          => array(
				'title'       => __( 'Enable/Disable', 'woo-lomi' ),
				/* translators: payment method title */
				'label'       => sprintf( __( 'Enable lomi. - %s', 'woo-lomi' ), $this->title ),
				'type'        => 'checkbox',
				'description' => __( 'Enable this gateway as a payment option on the checkout page.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __( 'Title', 'woo-lomi' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-lomi' ),
				'desc_tip'    => true,
				'default'     => __( 'lomi.', 'woo-lomi' ),
			),
			'description'                      => array(
				'title'       => __( 'Description', 'woo-lomi' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the payment method description which the user sees during checkout.', 'woo-lomi' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'payment_page'                     => array(
				'title'       => __( 'Payment Option', 'woo-lomi' ),
				'type'        => 'select',
				'description' => __( 'Legacy setting. Customers are always sent to lomi. hosted checkout.', 'woo-lomi' ),
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''         => __( 'Select One', 'woo-lomi' ),
					'inline'   => __( 'Popup', 'woo-lomi' ),
					'redirect' => __( 'Redirect', 'woo-lomi' ),
				),
			),
			'autocomplete_order'               => array(
				'title'       => __( 'Autocomplete Order After Payment', 'woo-lomi' ),
				'label'       => __( 'Autocomplete Order', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-autocomplete-order',
				'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'remove_cancel_order_button'       => array(
				'title'       => __( 'Remove Cancel Order & Restore Cart Button', 'woo-lomi' ),
				'label'       => __( 'Remove the cancel order & restore cart button on the pay for order page', 'woo-lomi' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'split_payment'                    => array(
				'title'       => __( 'Split Payment', 'woo-lomi' ),
				'label'       => __( 'Enable Split Payment', 'woo-lomi' ),
				'type'        => 'checkbox',
				'description' => '',
				'class'       => 'woocommerce_lomi_split_payment',
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'subaccount_code'                  => array(
				'title'       => __( 'Subaccount Code', 'woo-lomi' ),
				'type'        => 'text',
				'description' => __( 'Enter the subaccount code here.', 'woo-lomi' ),
				'class'       => 'woocommerce_lomi_subaccount_code',
				'default'     => '',
			),
			'split_payment_transaction_charge' => array(
				'title'             => __( 'Split Payment Transaction Charge', 'woo-lomi' ),
				'type'              => 'number',
				'description'       => __( 'A flat fee to charge the subaccount for this transaction, in Naira (&#8358;). This overrides the split percentage set when the subaccount was created. Ideally, you will need to use this if you are splitting in flat rates (since subaccount creation only allows for percentage split). e.g. 100 for a &#8358;100 flat fee.', 'woo-lomi' ),
				'class'             => 'woocommerce_lomi_split_payment_transaction_charge',
				'default'           => '',
				'custom_attributes' => array(
					'min'  => 1,
					'step' => 0.1,
				),
				'desc_tip'          => false,
			),
			'split_payment_charge_account'     => array(
				'title'       => __( 'lomi. Charges Bearer', 'woo-lomi' ),
				'type'        => 'select',
				'description' => __( 'Who bears lomi. charges?', 'woo-lomi' ),
				'class'       => 'woocommerce_lomi_split_payment_charge_account',
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''           => __( 'Select One', 'woo-lomi' ),
					'account'    => __( 'Account', 'woo-lomi' ),
					'subaccount' => __( 'Subaccount', 'woo-lomi' ),
				),
			),
			'payment_channels'                 => array(
				'title'             => __( 'Payment Channels', 'woo-lomi' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-lomi-payment-channels',
				'description'       => __( 'The payment channels enabled for this gateway', 'woo-lomi' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->channels(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select payment channels', 'woo-lomi' ),
				),
			),
			'cards_allowed'                    => array(
				'title'             => __( 'Allowed Card Brands', 'woo-lomi' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-lomi-cards-allowed',
				'description'       => __( 'The card brands allowed for this gateway. This filter only works with the card payment channel.', 'woo-lomi' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->card_types(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select card brands', 'woo-lomi' ),
				),
			),
			'banks_allowed'                    => array(
				'title'             => __( 'Allowed Banks Card', 'woo-lomi' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-lomi-banks-allowed',
				'description'       => __( 'The banks whose card should be allowed for this gateway. This filter only works with the card payment channel.', 'woo-lomi' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->banks(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select banks', 'woo-lomi' ),
				),
			),
			'payment_icons'                    => array(
				'title'             => __( 'Payment Icons', 'woo-lomi' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-lomi-payment-icons',
				'description'       => __( 'The payment icons to be displayed on the checkout page.', 'woo-lomi' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->payment_icons(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select payment icons', 'woo-lomi' ),
				),
			),
			'custom_metadata'                  => array(
				'title'       => __( 'Custom Metadata', 'woo-lomi' ),
				'label'       => __( 'Enable Custom Metadata', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-metadata',
				'description' => __( 'If enabled, you will be able to send more information about the order to lomi..', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_order_id'                    => array(
				'title'       => __( 'Order ID', 'woo-lomi' ),
				'label'       => __( 'Send Order ID', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-meta-order-id',
				'description' => __( 'If checked, the Order ID will be sent to lomi.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_name'                        => array(
				'title'       => __( 'Customer Name', 'woo-lomi' ),
				'label'       => __( 'Send Customer Name', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-meta-name',
				'description' => __( 'If checked, the customer full name will be sent to lomi.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_email'                       => array(
				'title'       => __( 'Customer Email', 'woo-lomi' ),
				'label'       => __( 'Send Customer Email', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-meta-email',
				'description' => __( 'If checked, the customer email address will be sent to lomi.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_phone'                       => array(
				'title'       => __( 'Customer Phone', 'woo-lomi' ),
				'label'       => __( 'Send Customer Phone', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-meta-phone',
				'description' => __( 'If checked, the customer phone will be sent to lomi.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_billing_address'             => array(
				'title'       => __( 'Order Billing Address', 'woo-lomi' ),
				'label'       => __( 'Send Order Billing Address', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-meta-billing-address',
				'description' => __( 'If checked, the order billing address will be sent to lomi.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_shipping_address'            => array(
				'title'       => __( 'Order Shipping Address', 'woo-lomi' ),
				'label'       => __( 'Send Order Shipping Address', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-meta-shipping-address',
				'description' => __( 'If checked, the order shipping address will be sent to lomi.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_products'                    => array(
				'title'       => __( 'Product(s) Purchased', 'woo-lomi' ),
				'label'       => __( 'Send Product(s) Purchased', 'woo-lomi' ),
				'type'        => 'checkbox',
				'class'       => 'wc-lomi-meta-products',
				'description' => __( 'If checked, the product(s) purchased will be sent to lomi.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
		);

	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {

		$lomi_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=lomi' );
		$checkout_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
		?>

		<h2>
			<?php
			/* translators: payment method title */
			printf( __( 'lomi. - %s', 'woo-lomi' ), esc_attr( $this->title ) );
			?>
			<?php
			if ( function_exists( 'wc_back_link' ) ) {
				wc_back_link( __( 'Return to payments', 'woo-lomi' ), $checkout_settings_url );
			}
			?>
		</h2>

		<h4>
			<?php
			/* translators: link to lomi. developers settings page */
			printf( __( 'Important: configure your webhook in the <a href="%s" target="_blank" rel="noopener noreferrer">lomi. dashboard</a> using the URL below.', 'woo-lomi' ), 'https://dashboard.lomi.africa' );
			?>
		</h4>

		<p style="color: red">
			<code><?php echo esc_url( WC()->api_request_url( 'Tbz_WC_Lomi_Webhook' ) ); ?></code>
		</p>

		<p>
			<?php
			/* translators: link to lomi. general settings page */
			printf( __( 'To configure your lomi. API keys and enable/disable test mode, do that <a href="%s">here</a>', 'woo-lomi' ), esc_url( $lomi_settings_url ) );
			?>
		</p>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {

			/* translators: disabled message */
			echo '<div class="inline error"><p><strong>' . sprintf( __( 'lomi. Payment Gateway Disabled: %s', 'woo-lomi' ), esc_attr( $this->msg ) ) . '</strong></p></div>';

		}

	}

	/**
	 * Payment Channels.
	 */
	public function channels() {

		return array(
			'card'          => __( 'Cards', 'woo-lomi' ),
			'bank'          => __( 'Pay with Bank', 'woo-lomi' ),
			'ussd'          => __( 'USSD', 'woo-lomi' ),
			'qr'            => __( 'QR', 'woo-lomi' ),
			'bank_transfer' => __( 'Bank Transfer', 'woo-lomi' ),
		);

	}

	/**
	 * Card Types.
	 */
	public function card_types() {

		return array(
			'visa'       => __( 'Visa', 'woo-lomi' ),
			'verve'      => __( 'Verve', 'woo-lomi' ),
			'mastercard' => __( 'Mastercard', 'woo-lomi' ),
		);

	}

	/**
	 * Banks.
	 */
	public function banks() {

		return array(
			'044'  => __( 'Access Bank', 'woo-lomi' ),
			'035A' => __( 'ALAT by WEMA', 'woo-lomi' ),
			'401'  => __( 'ASO Savings and Loans', 'woo-lomi' ),
			'023'  => __( 'Citibank Nigeria', 'woo-lomi' ),
			'063'  => __( 'Access Bank (Diamond)', 'woo-lomi' ),
			'050'  => __( 'Ecobank Nigeria', 'woo-lomi' ),
			'562'  => __( 'Ekondo Microfinance Bank', 'woo-lomi' ),
			'084'  => __( 'Enterprise Bank', 'woo-lomi' ),
			'070'  => __( 'Fidelity Bank', 'woo-lomi' ),
			'011'  => __( 'First Bank of Nigeria', 'woo-lomi' ),
			'214'  => __( 'First City Monument Bank', 'woo-lomi' ),
			'058'  => __( 'Guaranty Trust Bank', 'woo-lomi' ),
			'030'  => __( 'Heritage Bank', 'woo-lomi' ),
			'301'  => __( 'Jaiz Bank', 'woo-lomi' ),
			'082'  => __( 'Keystone Bank', 'woo-lomi' ),
			'014'  => __( 'MainStreet Bank', 'woo-lomi' ),
			'526'  => __( 'Parallex Bank', 'woo-lomi' ),
			'076'  => __( 'Polaris Bank Limited', 'woo-lomi' ),
			'101'  => __( 'Providus Bank', 'woo-lomi' ),
			'221'  => __( 'Stanbic IBTC Bank', 'woo-lomi' ),
			'068'  => __( 'Standard Chartered Bank', 'woo-lomi' ),
			'232'  => __( 'Sterling Bank', 'woo-lomi' ),
			'100'  => __( 'Suntrust Bank', 'woo-lomi' ),
			'032'  => __( 'Union Bank of Nigeria', 'woo-lomi' ),
			'033'  => __( 'United Bank For Africa', 'woo-lomi' ),
			'215'  => __( 'Unity Bank', 'woo-lomi' ),
			'035'  => __( 'Wema Bank', 'woo-lomi' ),
			'057'  => __( 'Zenith Bank', 'woo-lomi' ),
		);

	}

	/**
	 * Payment Icons.
	 */
	public function payment_icons() {

		return array(
			'verve'         => __( 'Verve', 'woo-lomi' ),
			'visa'          => __( 'Visa', 'woo-lomi' ),
			'mastercard'    => __( 'Mastercard', 'woo-lomi' ),
			'lomiwhite' => __( 'Secured by lomi. White', 'woo-lomi' ),
			'lomiblue'  => __( 'Secured by lomi. Blue', 'woo-lomi' ),
			'lomi-wc'   => __( 'lomi. Nigeria', 'woo-lomi' ),
			'lomi-gh'   => __( 'lomi. Ghana', 'woo-lomi' ),
			'access'        => __( 'Access Bank', 'woo-lomi' ),
			'alat'          => __( 'ALAT by WEMA', 'woo-lomi' ),
			'aso'           => __( 'ASO Savings and Loans', 'woo-lomi' ),
			'citibank'      => __( 'Citibank Nigeria', 'woo-lomi' ),
			'diamond'       => __( 'Access Bank (Diamond)', 'woo-lomi' ),
			'ecobank'       => __( 'Ecobank Nigeria', 'woo-lomi' ),
			'ekondo'        => __( 'Ekondo Microfinance Bank', 'woo-lomi' ),
			'enterprise'    => __( 'Enterprise Bank', 'woo-lomi' ),
			'fidelity'      => __( 'Fidelity Bank', 'woo-lomi' ),
			'firstbank'     => __( 'First Bank of Nigeria', 'woo-lomi' ),
			'fcmb'          => __( 'First City Monument Bank', 'woo-lomi' ),
			'gtbank'        => __( 'Guaranty Trust Bank', 'woo-lomi' ),
			'heritage'      => __( 'Heritage Bank', 'woo-lomi' ),
			'jaiz'          => __( 'Jaiz Bank', 'woo-lomi' ),
			'keystone'      => __( 'Keystone Bank', 'woo-lomi' ),
			'mainstreet'    => __( 'MainStreet Bank', 'woo-lomi' ),
			'parallex'      => __( 'Parallex Bank', 'woo-lomi' ),
			'polaris'       => __( 'Polaris Bank Limited', 'woo-lomi' ),
			'providus'      => __( 'Providus Bank', 'woo-lomi' ),
			'stanbic'       => __( 'Stanbic IBTC Bank', 'woo-lomi' ),
			'standard'      => __( 'Standard Chartered Bank', 'woo-lomi' ),
			'sterling'      => __( 'Sterling Bank', 'woo-lomi' ),
			'suntrust'      => __( 'Suntrust Bank', 'woo-lomi' ),
			'union'         => __( 'Union Bank of Nigeria', 'woo-lomi' ),
			'uba'           => __( 'United Bank For Africa', 'woo-lomi' ),
			'unity'         => __( 'Unity Bank', 'woo-lomi' ),
			'wema'          => __( 'Wema Bank', 'woo-lomi' ),
			'zenith'        => __( 'Zenith Bank', 'woo-lomi' ),
		);

	}

	/**
	 * Display the selected payment icon.
	 */
	public function get_icon() {
		$icons = $this->payment_icons;
		if ( ! is_array( $icons ) || empty( $icons ) ) {
			$icons = array( 'lomi' );
		}
		$icon_html = '';
		foreach ( $icons as $i ) {
			$icon_html .= '<img src="' . esc_url( wc_lomi_get_payment_icon_url( $i ) ) . '" alt="' . esc_attr( (string) $i ) . '" style="height: 40px; margin-right: 0.4em;margin-bottom: 0.6em;" />';
		}
		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
	}

	/**
	 * Hosted checkout only (see parent).
	 */
	public function payment_scripts() {
	}

	/**
	 * Add custom gateways to the checkout page.
	 *
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	public function add_gateway_to_checkout( $available_gateways ) {

		if ( $this->enabled == 'no' ) {
			unset( $available_gateways[ $this->id ] );
		}

		return $available_gateways;

	}

	/**
	 * Check if the custom lomi. gateway is enabled.
	 *
	 * @return bool
	 */
	public function is_available() {

		if ( 'yes' == $this->enabled ) {

			if ( ! $this->secret_key ) {

				return false;

			}

			return true;

		}

		return false;
	}
}
