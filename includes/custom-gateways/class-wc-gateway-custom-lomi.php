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
	 * Payment Icons.
	 */
	public function payment_icons() {

		return array(
			'verve'         => __( 'Verve', 'woo-lomi' ),
			'visa'          => __( 'Visa', 'woo-lomi' ),
			'mastercard'    => __( 'Mastercard', 'woo-lomi' ),
			'lomiwhite'     => __( 'Secured by lomi. White', 'woo-lomi' ),
			'lomiblue'      => __( 'Secured by lomi. Blue', 'woo-lomi' ),
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
			$icon_html .= '<img class="wc-lomi-payment-icon" src="' . esc_url( wc_lomi_get_payment_icon_url( $i ) ) . '" alt="' . esc_attr( (string) $i ) . '" style="height: 24px; width: auto; max-height: 24px; max-width: 80px; vertical-align: middle; margin-left: 0.35em;" />';
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
