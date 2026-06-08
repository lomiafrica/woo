<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Lomi extends WC_Payment_Gateway {

	/**
	 * Is test mode active?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * Should orders be marked as complete after payment?
	 *
	 * @var bool
	 */
	public $autocomplete_order;

	/**
	 * Should the cancel & remove order button be removed on the pay for order page.
	 *
	 * @var bool
	 */
	public $remove_cancel_order_button;

	/**
	 * lomi. test secret key.
	 *
	 * @var string
	 */
	public $test_secret_key;

	/**
	 * lomi. live secret key.
	 *
	 * @var string
	 */
	public $live_secret_key;

	/**
	 * Should custom metadata be enabled?
	 *
	 * @var bool
	 */
	public $custom_metadata;

	/**
	 * Should the order id be sent as a custom metadata to lomi.?
	 *
	 * @var bool
	 */
	public $meta_order_id;

	/**
	 * Should the customer name be sent as a custom metadata to lomi.?
	 *
	 * @var bool
	 */
	public $meta_name;

	/**
	 * Should the billing email be sent as a custom metadata to lomi.?
	 *
	 * @var bool
	 */
	public $meta_email;

	/**
	 * Should the billing phone be sent as a custom metadata to lomi.?
	 *
	 * @var bool
	 */
	public $meta_phone;

	/**
	 * Should the billing address be sent as a custom metadata to lomi.?
	 *
	 * @var bool
	 */
	public $meta_billing_address;

	/**
	 * Should the shipping address be sent as a custom metadata to lomi.?
	 *
	 * @var bool
	 */
	public $meta_shipping_address;

	/**
	 * Should the order items be sent as a custom metadata to lomi.?
	 *
	 * @var bool
	 */
	public $meta_products;

	/**
	 * API secret key
	 *
	 * @var string
	 */
	public $secret_key;

	/**
	 * Webhook signing secret (test).
	 *
	 * @var string
	 */
	public $test_webhook_secret;

	/**
	 * Webhook signing secret (live).
	 *
	 * @var string
	 */
	public $live_webhook_secret;

	/**
	 * Active webhook secret for HMAC verification.
	 *
	 * @var string
	 */
	public $webhook_secret;

	/**
	 * Gateway disabled message
	 *
	 * @var string
	 */
	public $msg;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'lomi';
		$this->method_title       = __( 'lomi.', 'woo-lomi' );
		$this->method_description = sprintf( __( 'Accept payments with lomi. Secure hosted checkout. <a href="%1$s" target="_blank">Create an account</a> and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-lomi' ), 'https://lomi.africa', 'https://dashboard.lomi.africa/settings/access-tokens' );
		$this->has_fields         = false;

		$this->supports = array(
			'products',
			'refunds',
			'subscriptions',
			'multiple_subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings
		$this->init_settings();

		// Get setting values

		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->enabled            = $this->get_option( 'enabled' );
		$this->testmode           = $this->get_option( 'testmode' ) === 'yes' ? true : false;
		$this->autocomplete_order = $this->get_option( 'autocomplete_order' ) === 'yes' ? true : false;

		$this->test_secret_key = $this->get_option( 'test_secret_key' );
		$this->live_secret_key = $this->get_option( 'live_secret_key' );

		$this->remove_cancel_order_button = $this->get_option( 'remove_cancel_order_button' ) === 'yes' ? true : false;

		$this->custom_metadata = $this->get_option( 'custom_metadata' ) === 'yes' ? true : false;

		$this->meta_order_id         = $this->get_option( 'meta_order_id' ) === 'yes' ? true : false;
		$this->meta_name             = $this->get_option( 'meta_name' ) === 'yes' ? true : false;
		$this->meta_email            = $this->get_option( 'meta_email' ) === 'yes' ? true : false;
		$this->meta_phone            = $this->get_option( 'meta_phone' ) === 'yes' ? true : false;
		$this->meta_billing_address  = $this->get_option( 'meta_billing_address' ) === 'yes' ? true : false;
		$this->meta_shipping_address = $this->get_option( 'meta_shipping_address' ) === 'yes' ? true : false;
		$this->meta_products         = $this->get_option( 'meta_products' ) === 'yes' ? true : false;

		$this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;

		$this->test_webhook_secret = $this->get_option( 'test_webhook_secret' );
		$this->live_webhook_secret = $this->get_option( 'live_webhook_secret' );
		$this->webhook_secret      = $this->testmode ? $this->test_webhook_secret : $this->live_webhook_secret;

		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wc_gateway_lomi', array( $this, 'verify_lomi_checkout_session' ) );
		add_action( 'woocommerce_api_wc_gateway_lomi_cancel', array( $this, 'handle_lomi_checkout_cancel' ) );
		add_action( 'woocommerce_api_wc_gateway_lomi_abandon', array( $this, 'handle_lomi_checkout_abandon' ) );

		// Webhook listener/API hook.
		add_action( 'woocommerce_api_tbz_wc_lomi_webhook', array( $this, 'process_lomi_webhooks' ) );

		// Check if the gateway can be used.
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

		$allowed = apply_filters( 'woocommerce_lomi_supported_currencies', array( 'XOF', 'USD', 'EUR' ) );

		if ( ! in_array( get_woocommerce_currency(), $allowed, true ) ) {

			$this->msg = sprintf(
				/* translators: %s: settings URL */
				__( 'lomi. supports XOF, USD, and EUR only. Set your store currency <a href="%s">here</a>.', 'woo-lomi' ),
				admin_url( 'admin.php?page=wc-settings&tab=general' )
			);

			return false;

		}

		return true;

	}

	/**
	 * Display lomi payment icon.
	 */
	public function get_icon() {
		$icon = wc_lomi_get_checkout_branding_html();
		if ( ! $icon ) {
			return apply_filters( 'woocommerce_gateway_icon', '', $this->id );
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	/**
	 * Hide title text when the composite branding image is used.
	 *
	 * @return string
	 */
	public function get_title() {
		if ( wc_lomi_uses_checkout_branding_card() ) {
			return '';
		}

		return $this->title;
	}

	/**
	 * Check if lomi. merchant details is filled.
	 */
	public function admin_notices() {

		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields.
		if ( ! $this->secret_key ) {
			echo '<div class="error"><p>' . sprintf( esc_html__( 'Please enter your lomi. secret API key %shere%s to use this gateway.', 'woo-lomi' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=lomi' ) ) . '">', '</a>' ) . '</p></div>';
			return;
		}

	}

	/**
	 * Check if lomi. gateway is enabled.
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

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {

		?>

		<h2><?php echo esc_html( $this->get_method_title() ); ?>
		<?php
		if ( function_exists( 'wc_back_link' ) ) {
			wc_back_link( __( 'Return to payments', 'woo-lomi' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
		}
		?>
		</h2>

		<h4>
			<strong><?php
			printf(
				wp_kses_post( __( 'Configure a webhook in your <a href="%1$s" target="_blank" rel="noopener noreferrer">lomi. dashboard</a> with URL: <code style="color:red">%2$s</code> and the same signing secret as below.', 'woo-lomi' ) ),
				'https://dashboard.lomi.africa',
				esc_html( WC()->api_request_url( 'Tbz_WC_Lomi_Webhook' ) )
			);
			?></strong>
		</h4>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {
			?>
			<div class="inline error"><p><strong><?php esc_html_e( 'lomi. payment gateway disabled', 'woo-lomi' ); ?></strong>: <?php echo wp_kses_post( $this->msg ); ?></p></div>

			<?php
		}

	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$form_fields = array(
			'enabled'                          => array(
				'title'       => __( 'Enable/Disable', 'woo-lomi' ),
				'label'       => __( 'Enable lomi.', 'woo-lomi' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable lomi. on the checkout page.', 'woo-lomi' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __( 'Title', 'woo-lomi' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-lomi' ),
				'default'     => __( 'lomi.', 'woo-lomi' ),
				'desc_tip'    => true,
			),
			'description'                      => array(
				'title'       => __( 'Description', 'woo-lomi' ),
				'type'        => 'textarea',
				'description' => __( 'Optional extra text below the payment method. Leave empty to use the default lomi. branding card.', 'woo-lomi' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'testmode'                         => array(
				'title'       => __( 'Test mode', 'woo-lomi' ),
				'label'       => __( 'Enable Test Mode', 'woo-lomi' ),
				'type'        => 'checkbox',
				'description' => __( 'Test mode uses the sandbox API. Disable for live payments.', 'woo-lomi' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'test_secret_key'                  => array(
				'title'       => __( 'Test Secret Key', 'woo-lomi' ),
				'type'        => 'password',
				'description' => __( 'Your lomi. secret API key (test).', 'woo-lomi' ),
				'default'     => '',
			),
			'live_secret_key'                  => array(
				'title'       => __( 'Live Secret Key', 'woo-lomi' ),
				'type'        => 'password',
				'description' => __( 'Your lomi. secret API key (live).', 'woo-lomi' ),
				'default'     => '',
			),
			'test_webhook_secret'              => array(
				'title'       => __( 'Test webhook secret', 'woo-lomi' ),
				'type'        => 'password',
				'description' => __( 'Secret for verifying webhook signatures (test). Must match the secret configured on your lomi. webhook endpoint.', 'woo-lomi' ),
				'default'     => '',
			),
			'live_webhook_secret'              => array(
				'title'       => __( 'Live webhook secret', 'woo-lomi' ),
				'type'        => 'password',
				'description' => __( 'Secret for verifying webhook signatures (live).', 'woo-lomi' ),
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

		$this->form_fields = $form_fields;

	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
	}

	/**
	 * Enqueue checkout assets for branding and hosted-checkout abandon recovery.
	 */
	public function payment_scripts() {
		if ( ! is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) ) {
			return;
		}

		wp_enqueue_style(
			'wc-lomi-checkout',
			plugins_url( 'assets/css/lomi-checkout.css', WC_LOMI_MAIN_FILE ),
			array(),
			WC_LOMI_VERSION
		);

		if ( ! $this->is_available() || ! is_checkout() || is_order_received_page() ) {
			return;
		}

		wp_enqueue_script(
			'wc-lomi-checkout-abandon',
			plugins_url( 'assets/js/checkout-abandon.js', WC_LOMI_MAIN_FILE ),
			array( 'jquery' ),
			WC_LOMI_VERSION,
			true
		);

		wp_localize_script(
			'wc-lomi-checkout-abandon',
			'wc_lomi_checkout_params',
			array(
				'storageKey' => 'wc_lomi_checkout_redirect',
				'abandonUrl' => WC()->api_request_url( 'wc_gateway_lomi_abandon' ),
				'gatewayIds' => $this->get_lomi_gateway_ids(),
			)
		);
	}

	/**
	 * Gateway IDs that use lomi. hosted checkout.
	 *
	 * @return string[]
	 */
	protected function get_lomi_gateway_ids() {
		return array_values( array_unique( apply_filters( 'wc_lomi_gateway_ids', array( $this->id ) ) ) );
	}

	/**
	 * Remember the pending order while the shopper is on hosted checkout.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	protected function set_lomi_pending_checkout_session( WC_Order $order ) {
		if ( ! WC()->session ) {
			return;
		}

		WC()->session->set( 'lomi_pending_order_id', $order->get_id() );
		WC()->session->set( 'lomi_pending_order_key', $order->get_order_key() );
	}

	/**
	 * Clear pending hosted checkout pointers from the shopper session.
	 *
	 * @return void
	 */
	protected function clear_lomi_pending_checkout_session() {
		if ( ! WC()->session ) {
			return;
		}

		WC()->session->set( 'lomi_pending_order_id', null );
		WC()->session->set( 'lomi_pending_order_key', null );
	}

	/**
	 * Restore a pending order back into the cart.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	protected function restore_order_items_to_cart( WC_Order $order ) {
		if ( ! function_exists( 'wc_load_cart' ) ) {
			return;
		}

		wc_load_cart();

		if ( ! WC()->cart ) {
			return;
		}

		WC()->cart->empty_cart( false );

		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}

			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$quantity     = $item->get_quantity();
			$variations   = array();

			foreach ( $item->get_meta_data() as $meta ) {
				if ( taxonomy_is_product_attribute( $meta->key ) || meta_is_product_attribute( $meta->key, $meta->value, $product_id ) ) {
					$variations[ 'attribute_' . sanitize_title( $meta->key ) ] = $meta->value;
				}
			}

			$cart_item_data = apply_filters(
				'woocommerce_order_again_cart_item_data',
				array(),
				$item,
				$order
			);

			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data );
		}

		WC()->cart->calculate_totals();
	}

	/**
	 * Cancel a pending hosted checkout order and restore the cart.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $note  Order note.
	 * @return bool
	 */
	protected function abandon_lomi_checkout_order( WC_Order $order, $note = '' ) {
		if ( ! $order instanceof WC_Order ) {
			return false;
		}

		$abandonable_statuses = apply_filters(
			'wc_lomi_abandonable_order_statuses',
			array( 'pending', 'failed', 'on-hold' )
		);

		if ( ! $order->has_status( $abandonable_statuses ) ) {
			return false;
		}

		if ( ! $note ) {
			$note = __( 'lomi.: customer left hosted checkout before completing payment.', 'woo-lomi' );
		}

		$this->restore_order_items_to_cart( $order );
		$order->update_status( 'cancelled', $note );
		$order->delete_meta_data( '_lomi_checkout_session_id' );
		$order->save();

		return true;
	}

	/**
	 * Hosted checkout cancel URL for a specific order.
	 *
	 * @param WC_Order $order Order.
	 * @return string
	 */
	protected function get_lomi_checkout_cancel_url( WC_Order $order ) {
		return add_query_arg(
			array(
				'order_id' => $order->get_id(),
				'key'      => $order->get_order_key(),
			),
			WC()->api_request_url( 'wc_gateway_lomi_cancel' )
		);
	}

	/**
	 * Load admin scripts.
	 */
	public function admin_scripts() {

		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$lomi_admin_params = array(
			'plugin_url' => WC_LOMI_URL,
		);

		wp_enqueue_script( 'wc_lomi_admin', plugins_url( 'assets/js/lomi-admin' . $suffix . '.js', WC_LOMI_MAIN_FILE ), array(), WC_LOMI_VERSION, true );

		wp_localize_script( 'wc_lomi_admin', 'wc_lomi_admin_params', $lomi_admin_params );

	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {
		return $this->start_lomi_checkout_redirect( $order_id );
	}

	/**
	 * Process a redirect payment option payment.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return array|void
	 */
	public function process_redirect_payment_option( $order_id ) {

		return $this->start_lomi_checkout_redirect( $order_id );

	}

	/**
	 * Create checkout session and return Woo redirect payload.
	 *
	 * @param int $order_id Order ID.
	 * @return array|void
	 */
	protected function start_lomi_checkout_redirect( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			wc_add_notice( __( 'Order not found.', 'woo-lomi' ), 'error' );
			return;
		}

		$session = $this->create_lomi_checkout_session( $order );

		if ( is_wp_error( $session ) ) {
			wc_add_notice( $session->get_error_message(), 'error' );
			return;
		}

		$this->set_lomi_pending_checkout_session( $order );

		return array(
			'result'   => 'success',
			'redirect' => $session['checkout_url'],
		);

	}

	/**
	 * Process a token payment.
	 *
	 * @param $token
	 * @param $order_id
	 *
	 * @return bool
	 */

	/**
	 * Order total formatted for lomi. API amount.
	 *
	 * XOF is sent in whole francs. USD and EUR are sent in minor units (cents).
	 *
	 * @param WC_Order $order Order.
	 * @return int
	 */
	protected function get_order_amount_for_lomi( $order ) {
		$currency = strtoupper( $order->get_currency() );
		$total    = (float) $order->get_total();

		if ( 'XOF' === $currency ) {
			return (int) round( $total );
		}

		$decimals = $this->get_currency_minor_unit_decimals( $currency );

		return (int) round( $total * ( 10 ** $decimals ) );
	}

	/**
	 * Normalize customer phone for lomi. API validation.
	 *
	 * @param WC_Order $order Order.
	 * @return string
	 */
	protected function get_lomi_customer_phone( WC_Order $order ) {
		$phone = trim( (string) $order->get_billing_phone() );
		if ( '' === $phone ) {
			return '';
		}

		$phone = preg_replace( '/[\s().-]+/', '', $phone );
		if ( is_string( $phone ) && 0 === strpos( $phone, '00' ) ) {
			$phone = '+' . substr( $phone, 2 );
		}

		return is_string( $phone ) ? $phone : '';
	}

	/**
	 * Currency minor unit precision expected by the lomi. API.
	 *
	 * @param string $currency Currency code.
	 * @return int
	 */
	protected function get_currency_minor_unit_decimals( $currency ) {
		$currency = strtoupper( $currency );
		$decimals = array(
			'XOF' => 0,
			'USD' => 2,
			'EUR' => 2,
		);

		return (int) apply_filters(
			'woocommerce_lomi_currency_minor_unit_decimals',
			isset( $decimals[ $currency ] ) ? $decimals[ $currency ] : 2,
			$currency
		);
	}

	/**
	 * lomi. API base URL.
	 *
	 * @return string
	 */
	protected function get_lomi_api_base_url() {
		return $this->testmode ? 'https://sandbox.api.lomi.africa' : 'https://api.lomi.africa';
	}

	/**
	 * JSON request to lomi. API.
	 *
	 * @param string          $method HTTP method.
	 * @param string          $path   Path after /v1.
	 * @param array|null      $body   Request body.
	 * @return object|WP_Error Decoded JSON or WP_Error.
	 */
	protected function lomi_api_request( $method, $path, $body = null ) {
		$url  = $this->get_lomi_api_base_url() . $path;
		$args = array(
			'method'  => $method,
			'timeout' => 20,
			'headers' => array(
				'X-API-KEY'    => $this->secret_key,
				'Content-Type' => 'application/json',
			),
		);
		if ( null !== $body && in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) ) {
			$args['body'] = wp_json_encode( $body );
		}
		$request = wp_remote_request( $url, $args );
		if ( is_wp_error( $request ) ) {
			$this->log_lomi_api_error(
				$request->get_error_message(),
				array(
					'method' => $method,
					'url'    => $url,
				)
			);

			return new WP_Error(
				'lomi_http_request',
				sprintf(
					/* translators: %s: WordPress HTTP API error message */
					__( 'lomi. API request failed before receiving a response: %s', 'woo-lomi' ),
					$request->get_error_message()
				)
			);
		}
		$code = wp_remote_retrieve_response_code( $request );
		$raw  = wp_remote_retrieve_body( $request );
		$json = json_decode( $raw );
		if ( $code < 200 || $code >= 300 ) {
			$detail = __( 'No response body.', 'woo-lomi' );
			if ( is_object( $json ) && ! empty( $json->message ) ) {
				$detail = (string) $json->message;
			} elseif ( is_object( $json ) && ! empty( $json->error ) ) {
				$detail = is_string( $json->error ) ? (string) $json->error : wp_json_encode( $json->error );
			} elseif ( ! empty( $raw ) ) {
				$detail = wp_strip_all_tags( (string) $raw );
			}

			$msg = sprintf(
				/* translators: 1: HTTP status code, 2: API response detail */
				__( 'lomi. API request failed (HTTP %1$d): %2$s', 'woo-lomi' ),
				$code,
				$detail
			);

			$this->log_lomi_api_error(
				$msg,
				array(
					'method'        => $method,
					'url'           => $url,
					'status_code'   => $code,
					'response_body' => $raw,
					'request_body'  => isset( $args['body'] ) ? $args['body'] : '',
				)
			);

			return new WP_Error( 'lomi_api', $msg );
		}
		return $json;
	}

	/**
	 * Log API errors without exposing credentials.
	 *
	 * @param string $message Error message.
	 * @param array  $context Log context.
	 * @return void
	 */
	protected function log_lomi_api_error( $message, $context = array() ) {
		if ( ! function_exists( 'wc_get_logger' ) ) {
			return;
		}

		$context['source'] = 'woo-lomi';
		wc_get_logger()->error( $message, $context );
	}

	/**
	 * Unwrap { data: ... } if present.
	 *
	 * @param object $json Response.
	 * @return object
	 */
	protected function lomi_unwrap_data( $json ) {
		$payload = $json;
		if ( is_object( $json ) && isset( $json->data ) ) {
			$payload = $json->data;
		}
		if ( is_array( $payload ) && ! empty( $payload[0] ) && is_object( $payload[0] ) ) {
			$payload = $payload[0];
		}
		return $payload;
	}

	/**
	 * Normalize API value to a session row object (for GET/POST checkout-sessions).
	 *
	 * @param mixed $json Decoded JSON.
	 * @return object|null
	 */
	protected function lomi_normalize_checkout_session_payload( $json ) {
		$unwrapped = $this->lomi_unwrap_data( $json );
		return is_object( $unwrapped ) ? $unwrapped : null;
	}

	/**
	 * Build metadata for checkout session.
	 *
	 * @param WC_Order $order Order.
	 * @return array
	 */
	protected function build_lomi_session_metadata( WC_Order $order ) {
		$meta = array(
			'wc_order_id'  => (string) $order->get_id(),
			'wc_order_key' => $order->get_order_key(),
			'plugin'       => 'woo-lomi',
		);
		if ( $this->custom_metadata ) {
			if ( $this->meta_order_id ) {
				$meta['order_id'] = (string) $order->get_id();
			}
			if ( $this->meta_name ) {
				$meta['customer_name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			}
			if ( $this->meta_email ) {
				$meta['customer_email'] = $order->get_billing_email();
			}
			if ( $this->meta_phone ) {
				$meta['customer_phone'] = $order->get_billing_phone();
			}
			if ( $this->meta_billing_address ) {
				$meta['billing_address'] = wp_strip_all_tags( str_replace( '<br/>', ', ', $order->get_formatted_billing_address() ) );
			}
			if ( $this->meta_shipping_address ) {
				$ship = $order->get_formatted_shipping_address();
				if ( ! $ship ) {
					$ship = $order->get_formatted_billing_address();
				}
				$meta['shipping_address'] = wp_strip_all_tags( str_replace( '<br/>', ', ', $ship ) );
			}
			if ( $this->meta_products ) {
				$lines = array();
				foreach ( $order->get_items() as $item ) {
					$lines[] = $item->get_name() . ' x' . $item->get_quantity();
				}
				$meta['products'] = implode( ' | ', $lines );
			}
		}
		$cf = $this->get_custom_fields( $order->get_id() );
		if ( is_array( $cf ) ) {
			foreach ( $cf as $row ) {
				if ( isset( $row['variable_name'], $row['value'] ) ) {
					$meta[ 'cart_' . $row['variable_name'] ] = (string) $row['value'];
				}
			}
		}
		return $meta;
	}

	/**
	 * Create checkout session on lomi.
	 *
	 * @param WC_Order $order Order.
	 * @return array|WP_Error { checkout_url, checkout_session_id } or error.
	 */
	protected function create_lomi_checkout_session( WC_Order $order ) {
		$callback = add_query_arg(
			array(
				'order_id' => $order->get_id(),
				'key'      => $order->get_order_key(),
			),
			WC()->api_request_url( 'WC_Gateway_Lomi' )
		);
		$cancel = $this->get_lomi_checkout_cancel_url( $order );
		$body = array(
			'currency_code'           => strtoupper( $order->get_currency() ),
			'amount'                  => $this->get_order_amount_for_lomi( $order ),
			'integration_source'      => 'woocommerce',
			'success_url'             => esc_url_raw( $callback ),
			'cancel_url'              => esc_url_raw( $cancel ),
			'customer_email'          => $order->get_billing_email(),
			'customer_name'           => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
			'customer_phone'          => $this->get_lomi_customer_phone( $order ),
			'customer_city'           => $order->get_billing_city(),
			'customer_country'        => $order->get_billing_country(),
			'customer_address'        => trim( $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() ),
			'customer_postal_code'    => $order->get_billing_postcode(),
			'require_billing_address' => false,
			'title'                   => sprintf( __( 'Order %s', 'woo-lomi' ), $order->get_order_number() ),
			'metadata'                => $this->build_lomi_session_metadata( $order ),
		);
		$resp = $this->lomi_api_request( 'POST', '/checkout-sessions', $body );
		if ( is_wp_error( $resp ) ) {
			$order->add_order_note(
				sprintf(
					/* translators: %s: API error message */
					__( 'lomi.: failed to create checkout session (%s)', 'woo-lomi' ),
					$resp->get_error_message()
				)
			);
			$order->save();
			return $resp;
		}
		$data = $this->lomi_normalize_checkout_session_payload( $resp );
		$checkout_url = '';
		if ( $data && ! empty( $data->checkout_url ) ) {
			$checkout_url = (string) $data->checkout_url;
		} elseif ( $data && ! empty( $data->url ) ) {
			$checkout_url = (string) $data->url;
		}

		if ( ! $data || empty( $data->checkout_session_id ) || empty( $checkout_url ) ) {
			$this->log_lomi_api_error(
				'Unexpected checkout session response from lomi.',
				array(
					'response_body' => wp_json_encode( $resp ),
				)
			);
			$order->add_order_note( __( 'lomi.: checkout session response missing checkout_session_id or checkout_url.', 'woo-lomi' ) );
			$order->save();
			return new WP_Error( 'lomi_bad_response', __( 'Unexpected response from lomi. when creating checkout session.', 'woo-lomi' ) );
		}
		$order->update_meta_data( '_lomi_checkout_session_id', (string) $data->checkout_session_id );
		$order->save();
		return array(
			'checkout_url'        => $checkout_url,
			'checkout_session_id' => (string) $data->checkout_session_id,
		);
	}

	/**
	 * GET checkout session by ID.
	 *
	 * @param string $session_id UUID.
	 * @return object|false
	 */
	protected function fetch_lomi_checkout_session( $session_id ) {
		$path = '/checkout-sessions/' . rawurlencode( $session_id );
		$resp = $this->lomi_api_request( 'GET', $path, null );
		if ( is_wp_error( $resp ) ) {
			return false;
		}
		return $this->lomi_normalize_checkout_session_payload( $resp );
	}

	/**
	 * Whether session status means paid (hosted checkout uses checkout_session_status.completed).
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	protected function lomi_session_is_paid( $status ) {
		return 'completed' === strtolower( (string) $status );
	}

	/**
	 * Mark order paid if session matches.
	 *
	 * @param WC_Order $order       Order.
	 * @param object   $session_data Session DTO object.
	 * @return void
	 */
	protected function maybe_complete_order_from_lomi_session( $order, $session_data, $notify_customer = false ) {
		if ( ! $order || ! $session_data ) {
			return;
		}
		$session_ref = isset( $session_data->checkout_session_id ) ? (string) $session_data->checkout_session_id : '';
		$raw_status  = strtolower( (string) ( $session_data->status ?? '' ) );

		if ( in_array( $order->get_status(), array( 'processing', 'completed' ), true ) ) {
			return;
		}

		if ( ! $this->lomi_session_is_paid( $session_data->status ?? '' ) ) {
			$order->add_order_note(
				sprintf(
					/* translators: 1: session status, 2: checkout session id */
					__( 'lomi.: session not paid yet (status: %1$s, session: %2$s).', 'woo-lomi' ),
					$raw_status ? $raw_status : __( 'unknown', 'woo-lomi' ),
					$session_ref ? $session_ref : '—'
				)
			);
			if ( 'expired' === $raw_status ) {
				$order->update_status(
					'on-hold',
					__( 'lomi. checkout session expired before payment; review or cancel the order.', 'woo-lomi' )
				);
			}
			$order->save();
			if ( $notify_customer && in_array( $raw_status, array( 'open', 'expired' ), true ) ) {
				wc_add_notice(
					__( 'Your lomi. payment was not completed. You can try again from the order payment page.', 'woo-lomi' ),
					'notice'
				);
			}
			return;
		}

		$expected = $this->get_order_amount_for_lomi( $order );
		$paid     = isset( $session_data->amount ) ? (int) round( (float) $session_data->amount ) : 0;
		if ( $paid !== $expected ) {
			$order->update_status(
				'on-hold',
				sprintf(
					/* translators: 1: expected amount, 2: session amount */
					__( 'lomi.: amount mismatch (order expects %1$s; session has %2$s).', 'woo-lomi' ),
					$expected,
					$paid
				)
			);
			$order->save();
			if ( $notify_customer ) {
				wc_add_notice( __( 'We could not confirm the payment amount with lomi.. Please contact the store.', 'woo-lomi' ), 'error' );
			}
			return;
		}

		$currency_order = strtoupper( $order->get_currency() );
		$currency_sess  = isset( $session_data->currency_code ) ? strtoupper( (string) $session_data->currency_code ) : '';
		if ( $currency_sess && $currency_sess !== $currency_order ) {
			$order->update_status(
				'on-hold',
				sprintf(
					/* translators: 1: order currency, 2: session currency */
					__( 'lomi.: currency mismatch (order %1$s vs session %2$s).', 'woo-lomi' ),
					$currency_order,
					$currency_sess
				)
			);
			$order->save();
			if ( $notify_customer ) {
				wc_add_notice( __( 'Currency mismatch while verifying payment with lomi.. Please contact the store.', 'woo-lomi' ), 'error' );
			}
			return;
		}

		$ref = $session_ref ? $session_ref : (string) $order->get_id();
		$order->payment_complete( $ref );
		$order->add_order_note( sprintf( __( 'lomi. payment successful (checkout session: %s)', 'woo-lomi' ), $ref ) );
		if ( $this->is_autocomplete_order_enabled( $order ) ) {
			$order->update_status( 'completed' );
		}
		$order->save();
		if ( function_exists( 'WC' ) && WC()->cart ) {
			WC()->cart->empty_cart();
		}
	}

	public function process_token_payment( $token, $order_id ) {
		wc_add_notice( __( 'Saved card payments are not available with lomi.', 'woo-lomi' ), 'error' );
		return false;
	}

	/**
	 * Show new card can only be added when placing an order notice.
	 */
	public function add_payment_method() {

		wc_add_notice( __( 'You can only add a new card when placing an order.', 'woo-lomi' ), 'error' );

		return;

	}

	/**
	 * Displays the payment page.
	 *
	 * @param int $order_id Order ID.
	 */
	public function receipt_page( $order_id ) {

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$session = $this->create_lomi_checkout_session( $order );
		if ( is_wp_error( $session ) ) {
			echo '<p class="woocommerce-error">' . esc_html( $session->get_error_message() ) . '</p>';
			return;
		}
		echo '<p>' . esc_html__( 'Thank you for your order. Pay securely with lomi. using the button below.', 'woo-lomi' ) . '</p>';
		echo '<p><a class="button button-alt" href="' . esc_url( $session['checkout_url'] ) . '">' . esc_html__( 'Pay with lomi.', 'woo-lomi' ) . '</a></p>';
		if ( ! $this->remove_cancel_order_button ) {
			echo '<p><a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . esc_html__( 'Cancel order & restore cart', 'woo-lomi' ) . '</a></p>';
		}
	}

	/**
	 * Resolve the pending hosted checkout order from the shopper session.
	 *
	 * @return WC_Order|false
	 */
	protected function get_pending_lomi_checkout_order() {
		if ( ! WC()->session ) {
			return false;
		}

		$order_id = absint( WC()->session->get( 'lomi_pending_order_id' ) );
		$order_key = (string) WC()->session->get( 'lomi_pending_order_key' );

		if ( ! $order_id || ! $order_key ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_order_key() !== $order_key ) {
			return false;
		}

		return $order;
	}

	/**
	 * Customer cancelled hosted checkout from lomi.
	 */
	public function handle_lomi_checkout_cancel() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';

		@ob_clean();

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_order_key() !== $key ) {
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}

		$this->abandon_lomi_checkout_order(
			$order,
			__( 'lomi.: customer cancelled hosted checkout.', 'woo-lomi' )
		);
		$this->clear_lomi_pending_checkout_session();
		wc_add_notice(
			__( 'Payment was cancelled. Your cart has been restored — you can place your order again.', 'woo-lomi' ),
			'notice'
		);
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}

	/**
	 * Browser returned to checkout without completing hosted payment.
	 */
	public function handle_lomi_checkout_abandon() {
		@ob_clean();

		$order = $this->get_pending_lomi_checkout_order();
		if ( ! $order ) {
			wp_send_json_success( array( 'abandoned' => false ) );
		}

		$abandoned = $this->abandon_lomi_checkout_order( $order );
		$this->clear_lomi_pending_checkout_session();

		if ( $abandoned ) {
			wc_add_notice(
				__( 'Payment was cancelled. Your cart has been restored — you can place your order again.', 'woo-lomi' ),
				'notice'
			);
		}

		wp_send_json_success(
			array(
				'abandoned' => $abandoned,
			)
		);
	}

	/**
	 * Return handler after lomi. checkout.
	 */
	public function verify_lomi_checkout_session() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';

		@ob_clean();

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_order_key() !== $key ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
		$session_id = $order->get_meta( '_lomi_checkout_session_id' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['lomi_checkout_session_id'] ) ) {
			$session_id = sanitize_text_field( wp_unslash( $_GET['lomi_checkout_session_id'] ) );
		}
		if ( ! $session_id ) {
			$order->add_order_note( __( 'lomi.: return URL missing checkout session id in order meta.', 'woo-lomi' ) );
			$order->save();
			wc_add_notice( __( 'Missing lomi. session. Please try again or contact the store.', 'woo-lomi' ), 'error' );
			wp_safe_redirect( $order->get_checkout_payment_url() );
			exit;
		}
		$data = $this->fetch_lomi_checkout_session( $session_id );
		if ( ! $data ) {
			$order->add_order_note( __( 'lomi.: could not fetch checkout session from API after customer return.', 'woo-lomi' ) );
			$order->save();
			wc_add_notice( __( 'Could not verify payment with lomi..', 'woo-lomi' ), 'error' );
			wp_safe_redirect( $order->get_checkout_payment_url() );
			exit;
		}
		$this->maybe_complete_order_from_lomi_session( $order, $data, true );
		$this->clear_lomi_pending_checkout_session();

		if ( $this->lomi_session_is_paid( $data->status ?? '' ) ) {
			wp_safe_redirect( $this->get_return_url( $order ) );
			exit;
		}

		$this->abandon_lomi_checkout_order(
			$order,
			__( 'lomi.: hosted checkout returned without a completed payment.', 'woo-lomi' )
		);
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}

	/**
	 * Process webhook from lomi.
	 */
	public function process_lomi_webhooks() {

		if ( strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) !== 'POST' ) {
			status_header( 405 );
			exit;
		}

		$raw       = file_get_contents( 'php://input' );
		$signature = isset( $_SERVER['HTTP_X_LOMI_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_LOMI_SIGNATURE'] ) ) : '';
		$event     = isset( $_SERVER['HTTP_X_LOMI_EVENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_LOMI_EVENT'] ) ) : '';

		if ( ! $signature || ! $this->webhook_secret ) {
			status_header( 401 );
			exit;
		}

		$expected = hash_hmac( 'sha256', $raw, $this->webhook_secret );
		if ( ! hash_equals( $expected, $signature ) ) {
			status_header( 401 );
			exit;
		}

		if ( 'PAYMENT_SUCCEEDED' !== $event ) {
			status_header( 200 );
			exit;
		}

		$payload = json_decode( $raw );
		$data    = is_object( $payload ) && isset( $payload->data ) ? $payload->data : null;

		if ( ! is_object( $data ) ) {
			status_header( 400 );
			exit;
		}

		$order_id = 0;
		if ( isset( $data->metadata->wc_order_id ) ) {
			$order_id = absint( $data->metadata->wc_order_id );
		}
		if ( ! $order_id && ! empty( $data->checkout_session_id ) ) {
			$orders = wc_get_orders(
				array(
					'limit'        => 1,
					'meta_key'     => '_lomi_checkout_session_id',
					'meta_value'   => $data->checkout_session_id,
					'meta_compare' => '=',
					'return'       => 'objects',
				)
			);
			if ( ! empty( $orders ) ) {
				$order_id = $orders[0]->get_id();
			}
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			status_header( 200 );
			exit;
		}
		$session_id = isset( $data->checkout_session_id ) ? (string) $data->checkout_session_id : $order->get_meta( '_lomi_checkout_session_id' );
		$session    = $session_id ? $this->fetch_lomi_checkout_session( $session_id ) : false;
		if ( ! $session ) {
			$order->add_order_note( __( 'lomi.: PAYMENT_SUCCEEDED webhook received but session could not be fetched from API.', 'woo-lomi' ) );
			$order->save();
		} else {
			$this->maybe_complete_order_from_lomi_session( $order, $session, false );
		}

		status_header( 200 );
		exit;
	}

	/**
	 * Save Customer Card Details.
	 *
	 * @param $lomi_response
	 * @param $user_id
	 * @param $order_id
	 */
	public function save_card_details( $lomi_response, $user_id, $order_id ) {
		// lomi. hosted checkout does not store cards in WooCommerce.
	}

	/**
	 * Save payment token to the order for automatic renewal for further subscription payment.
	 *
	 * @param $order_id
	 * @param $lomi_response
	 */
	public function save_subscription_payment_token( $order_id, $lomi_response ) {
		// Automatic renewals via saved authorization codes are not supported with lomi.
	}

	/**
	 * Get custom fields to pass to lomi..
	 *
	 * @param int $order_id WC Order ID
	 *
	 * @return array
	 */
	public function get_custom_fields( $order_id ) {

		$order = wc_get_order( $order_id );

		$custom_fields = array();

		$custom_fields[] = array(
			'display_name'  => 'Plugin',
			'variable_name' => 'plugin',
			'value'         => 'woo-lomi',
		);
		
		if ( $this->custom_metadata ) {

			if ( $this->meta_order_id ) {

				$custom_fields[] = array(
					'display_name'  => 'Order ID',
					'variable_name' => 'order_id',
					'value'         => $order_id,
				);

			}

			if ( $this->meta_name ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Name',
					'variable_name' => 'customer_name',
					'value'         => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				);

			}

			if ( $this->meta_email ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Email',
					'variable_name' => 'customer_email',
					'value'         => $order->get_billing_email(),
				);

			}

			if ( $this->meta_phone ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Phone',
					'variable_name' => 'customer_phone',
					'value'         => $order->get_billing_phone(),
				);

			}

			if ( $this->meta_products ) {

				$line_items = $order->get_items();

				$products = '';

				foreach ( $line_items as $item_id => $item ) {
					$name     = $item['name'];
					$quantity = $item['qty'];
					$products .= $name . ' (Qty: ' . $quantity . ')';
					$products .= ' | ';
				}

				$products = rtrim( $products, ' | ' );

				$custom_fields[] = array(
					'display_name'  => 'Products',
					'variable_name' => 'products',
					'value'         => $products,
				);

			}

			if ( $this->meta_billing_address ) {

				$billing_address = $order->get_formatted_billing_address();
				$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

				$custom_fields[] = array(
					'display_name'  => 'Billing Address',
					'variable_name' => 'billing_address',
					'value'         => $billing_address,
				);

			}

			if ( $this->meta_shipping_address ) {

				$shipping_address = $order->get_formatted_shipping_address();
				$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

				if ( empty( $shipping_address ) ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$shipping_address = $billing_address;

				}
				$custom_fields[] = array(
					'display_name'  => 'Shipping Address',
					'variable_name' => 'shipping_address',
					'value'         => $shipping_address,
				);

			}

		}

		return $custom_fields;
	}

	/**
	 * Process a refund request from the Order details screen.
	 *
	 * @param int $order_id WC Order ID.
	 * @param float|null $amount Refund Amount.
	 * @param string $reason Refund Reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return new WP_Error(
			'lomi_refund',
			__( 'Automatic refunds from WooCommerce are not available for lomi.. Process refunds in your lomi. dashboard.', 'woo-lomi' )
		);
	}

	/**
	 * Checks if WC version is less than passed in version.
	 *
	 * @param string $version Version to check against.
	 *
	 * @return bool
	 */
	public function is_wc_lt( $version ) {
		return version_compare( WC_VERSION, $version, '<' );
	}

	/**
	 * Checks if autocomplete order is enabled for the payment method.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	protected function is_autocomplete_order_enabled( $order ) {
		$autocomplete_order = false;

		$payment_method = $order->get_payment_method();

		$lomi_settings = get_option( 'woocommerce_' . $payment_method . '_settings' );

		if ( isset( $lomi_settings['autocomplete_order'] ) && 'yes' === $lomi_settings['autocomplete_order'] ) {
			$autocomplete_order = true;
		}

		return $autocomplete_order;
	}

	public function get_logo_url() {

		$url = wc_lomi_get_brand_logo_url();

		return apply_filters( 'wc_lomi_gateway_icon_url', $url, $this->id );
	}

	/**
	 * Payment icon URLs for checkout branding.
	 *
	 * @return string[]
	 */
	public function get_payment_icon_urls() {
		return wc_lomi_get_checkout_payment_icon_urls();
	}

	/**
	 * Check if an order contains a subscription.
	 *
	 * @param int $order_id WC Order ID.
	 *
	 * @return bool
	 */
	public function order_contains_subscription( $order_id ) {

		return function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) );

	}
}
