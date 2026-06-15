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
	 * lomi. test public key (optional).
	 *
	 * @var string
	 */
	public $test_public_key;

	/**
	 * lomi. live public key (optional).
	 *
	 * @var string
	 */
	public $live_public_key;

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
		$this->icon               = wc_lomi_get_compact_icon_url();

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
		$this->test_public_key = $this->get_option( 'test_public_key' );
		$this->live_public_key = $this->get_option( 'live_public_key' );

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

		add_action( 'woocommerce_checkout_process', array( $this, 'validate_subscription_catalog_at_checkout' ) );
	}

	/**
	 * Normalize enabled flag after WooCommerce loads settings.
	 */
	public function init_settings() {
		parent::init_settings();
		$this->enabled = ( ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ) ? 'yes' : 'no';
	}

	/**
	 * Whether the store currency is supported by lomi.
	 *
	 * @return bool
	 */
	protected function currency_is_supported() {
		$allowed = apply_filters( 'woocommerce_lomi_supported_currencies', array( 'XOF', 'USD', 'EUR' ) );

		return in_array( get_woocommerce_currency(), $allowed, true );
	}

	/**
	 * Whether test mode is enabled in settings.
	 *
	 * @return bool
	 */
	protected function is_test_mode() {
		return 'yes' === $this->get_option( 'testmode' );
	}

	/**
	 * Secret API key for the current mode (reads fresh from settings).
	 *
	 * @return string
	 */
	protected function get_active_secret_key() {
		$key = $this->is_test_mode()
			? $this->get_option( 'test_secret_key' )
			: $this->get_option( 'live_secret_key' );

		return trim( (string) $key );
	}

	/**
	 * Webhook signing secret for the current mode (reads fresh from settings).
	 *
	 * @return string
	 */
	protected function get_active_webhook_secret() {
		$secret = $this->is_test_mode()
			? $this->get_option( 'test_webhook_secret' )
			: $this->get_option( 'live_webhook_secret' );

		return trim( (string) $secret );
	}

	/**
	 * Reload credential properties from saved settings (after save or on checkout).
	 */
	protected function refresh_credentials_from_settings() {
		$this->testmode           = $this->is_test_mode();
		$this->test_secret_key    = (string) $this->get_option( 'test_secret_key' );
		$this->live_secret_key    = (string) $this->get_option( 'live_secret_key' );
		$this->test_public_key    = (string) $this->get_option( 'test_public_key' );
		$this->live_public_key    = (string) $this->get_option( 'live_public_key' );
		$this->test_webhook_secret = (string) $this->get_option( 'test_webhook_secret' );
		$this->live_webhook_secret = (string) $this->get_option( 'live_webhook_secret' );
		$this->secret_key         = $this->get_active_secret_key();
		$this->webhook_secret     = $this->get_active_webhook_secret();
	}

	/**
	 * Preserve hidden password fields when saving (WooCommerce clears empty password inputs).
	 *
	 * @return bool
	 */
	public function process_admin_options() {
		$preserve_if_empty = array(
			'test_secret_key',
			'live_secret_key',
			'test_webhook_secret',
			'live_webhook_secret',
		);

		foreach ( $preserve_if_empty as $field_key ) {
			$post_key = $this->get_field_key( $field_key );
			if ( ! isset( $_POST[ $post_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				continue;
			}
			$posted = wc_clean( wp_unslash( $_POST[ $post_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( '' === $posted ) {
				unset( $_POST[ $post_key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}
		}

		$saved = parent::process_admin_options();

		$this->init_settings();
		$this->refresh_credentials_from_settings();

		return $saved;
	}

	/**
	 * Whether this admin screen is the lomi. gateway settings page.
	 *
	 * @return bool
	 */
	protected function is_lomi_settings_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		if ( ! in_array( $screen->id, array( 'woocommerce_page_wc-settings', 'woocommerce_page_wc-admin' ), true ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['section'] ) && 'lomi' === sanitize_text_field( wp_unslash( $_GET['section'] ) ) ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['path'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_GET['path'] ) ), 'lomi' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

		if ( ! $this->currency_is_supported() ) {

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

		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=lomi' );

		if ( 'yes' !== $this->get_option( 'enabled' ) ) {
			if ( '' !== $this->get_active_secret_key() ) {
				echo '<div class="notice notice-warning"><p>' . sprintf(
					/* translators: %s: settings URL */
					esc_html__( 'lomi. credentials are saved but the gateway is disabled. Enable lomi. %shere%s for checkout.', 'woo-lomi' ),
					'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'in gateway settings', 'woo-lomi' ) . '</a>'
				) . '</p></div>';
			}
			return;
		}

		if ( '' === $this->get_active_secret_key() ) {
			echo '<div class="error"><p>' . sprintf( esc_html__( 'Please enter your lomi. secret API key %shere%s to use this gateway.', 'woo-lomi' ), '<a href="' . esc_url( $settings_url ) . '">', '</a>' ) . '</p></div>';
			return;
		}

		if ( ! $this->get_active_webhook_secret() ) {
			echo '<div class="notice notice-warning"><p>' . sprintf(
				/* translators: %s: settings URL */
				esc_html__( 'lomi. is enabled but no webhook signing secret is configured. Add one %shere%s and register the webhook URL in your lomi. dashboard.', 'woo-lomi' ),
				'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'in gateway settings', 'woo-lomi' ) . '</a>'
			) . '</p></div>';
		}

		if ( ! is_ssl() ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'lomi. recommends HTTPS for production checkout and webhooks. Your site is not served over SSL.', 'woo-lomi' ) . '</p></div>';
		}

		if ( $this->testmode ) {
			echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'lomi. test mode is active.', 'woo-lomi' ) . '</strong> ' . sprintf(
				/* translators: %s: settings URL */
				esc_html__( 'Sandbox API keys are in use. Disable test mode %swhen you go live%s.', 'woo-lomi' ),
				'<a href="' . esc_url( $settings_url ) . '">',
				'</a>'
			) . '</p></div>';
		}

	}

	/**
	 * Check if lomi. gateway is enabled.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( 'yes' !== $this->enabled ) {
			return false;
		}

		if ( ! $this->currency_is_supported() ) {
			return false;
		}

		if ( '' === $this->get_active_secret_key() ) {
			return false;
		}

		return parent::is_available();
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

			$this->render_setup_health_panel();

			$mode_class = $this->is_test_mode() ? 'wc-lomi-mode-test' : 'wc-lomi-mode-live';
			echo '<table class="form-table wc-lomi-settings-table ' . esc_attr( $mode_class ) . '">';
			$this->generate_settings_html();
			echo '</table>';

			echo '<p class="description">';
			echo esc_html__( 'Recommended webhook events: PAYMENT_SUCCEEDED and REFUND_COMPLETED.', 'woo-lomi' );
			echo ' ';
			printf(
				wp_kses_post( __( 'Configure webhooks in your <a href="%1$s" target="_blank" rel="noopener noreferrer">lomi. dashboard</a>. Payouts and balance stay in the dashboard.', 'woo-lomi' ) ),
				'https://dashboard.lomi.africa/settings/webhooks'
			);
			echo '</p>';
			echo '<p class="description">';
			echo esc_html__( 'WooCommerce Subscriptions: map each subscription product to a lomi recurring price in the product editor. Recurring billing is handled by lomi.; WooCommerce does not auto-charge renewals.', 'woo-lomi' );
			echo '</p>';

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
				'description' => __( 'Must be enabled for lomi. to appear at checkout (in addition to the Active toggle on the Payments list).', 'woo-lomi' ),
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
				'class'       => 'wc-lomi-test-field',
				'description' => __( 'Your lomi. secret API key (test).', 'woo-lomi' ),
				'default'     => '',
			),
			'test_public_key'                  => array(
				'title'       => __( 'Test Public Key', 'woo-lomi' ),
				'type'        => 'text',
				'class'       => 'wc-lomi-test-field',
				'description' => __( 'Optional. Not required for hosted checkout.', 'woo-lomi' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'test_webhook_secret'              => array(
				'title'       => __( 'Test webhook secret', 'woo-lomi' ),
				'type'        => 'password',
				'class'       => 'wc-lomi-test-field',
				'description' => __( 'Secret for verifying webhook signatures (test). Must match the secret configured on your lomi. webhook endpoint.', 'woo-lomi' ),
				'default'     => '',
			),
			'live_secret_key'                  => array(
				'title'       => __( 'Live Secret Key', 'woo-lomi' ),
				'type'        => 'password',
				'class'       => 'wc-lomi-live-field',
				'description' => __( 'Your lomi. secret API key (live).', 'woo-lomi' ),
				'default'     => '',
			),
			'live_public_key'                  => array(
				'title'       => __( 'Live Public Key', 'woo-lomi' ),
				'type'        => 'text',
				'class'       => 'wc-lomi-live-field',
				'description' => __( 'Optional. Not required for hosted checkout.', 'woo-lomi' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'live_webhook_secret'              => array(
				'title'       => __( 'Live webhook secret', 'woo-lomi' ),
				'type'        => 'password',
				'class'       => 'wc-lomi-live-field',
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

		if ( ! $this->is_lomi_settings_screen() ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$lomi_admin_params = array(
			'plugin_url' => WC_LOMI_URL,
		);

		wp_enqueue_script( 'wc_lomi_admin', plugins_url( 'assets/js/lomi-admin' . $suffix . '.js', WC_LOMI_MAIN_FILE ), array( 'jquery' ), WC_LOMI_VERSION, true );

		wp_localize_script( 'wc_lomi_admin', 'wc_lomi_admin_params', $lomi_admin_params );

		wp_register_style( 'wc_lomi_admin', false, array(), WC_LOMI_VERSION );
		wp_enqueue_style( 'wc_lomi_admin' );
		wp_add_inline_style(
			'wc_lomi_admin',
			'.wc-lomi-mode-live tr:has(.wc-lomi-test-field){display:none!important}'
			. '.wc-lomi-mode-test tr:has(.wc-lomi-live-field){display:none!important}'
		);

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

		$mapping_error = $this->validate_subscription_lomi_mapping_for_order( $order );
		if ( is_wp_error( $mapping_error ) ) {
			wc_add_notice( $mapping_error->get_error_message(), 'error' );
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
			'timeout' => 60,
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
	 * Block checkout when subscription products lack lomi price mapping.
	 */
	public function validate_subscription_catalog_at_checkout() {
		if ( ! $this->is_available() ) {
			return;
		}

		$chosen = WC()->session ? WC()->session->get( 'chosen_payment_method' ) : '';
		if ( $chosen && $chosen !== $this->id ) {
			return;
		}

		if ( ! function_exists( 'wcs_cart_contains_subscription' ) || ! wcs_cart_contains_subscription() ) {
			return;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( ! $product || ! class_exists( 'WC_Gateway_Lomi_Product_Admin' ) ) {
				continue;
			}
			if ( ! WC_Gateway_Lomi_Product_Admin::product_is_subscription_type( $product ) ) {
				wc_add_notice(
					__( 'Split subscription and one-time products into separate orders when paying with lomi.', 'woo-lomi' ),
					'error'
				);
				return;
			}
			if ( ! WC_Gateway_Lomi_Product_Admin::get_product_lomi_price_id( $product ) ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: product name */
						__( 'Link "%s" to a lomi recurring price in the product editor before checkout.', 'woo-lomi' ),
						$product->get_name()
					),
					'error'
				);
			}
		}
	}

	/**
	 * Setup health checks for the gateway settings screen.
	 *
	 * @return array<string, array{label:string,status:string,message:string}>
	 */
	protected function get_setup_health_checks() {
		$checks = array();

		$active_secret = $this->get_active_secret_key();
		$active_webhook = $this->get_active_webhook_secret();

		$checks['enabled'] = array(
			'label'   => __( 'Checkout enabled', 'woo-lomi' ),
			'status'  => 'yes' === $this->get_option( 'enabled' ) ? 'ok' : 'error',
			'message' => 'yes' === $this->get_option( 'enabled' )
				? __( 'Enabled', 'woo-lomi' )
				: __( 'Disabled — check “Enable lomi.” below to show at checkout.', 'woo-lomi' ),
		);

		$checks['secret_key'] = array(
			'label'   => __( 'API secret key', 'woo-lomi' ),
			'status'  => $active_secret ? 'ok' : 'error',
			'message' => $active_secret ? __( 'Configured', 'woo-lomi' ) : __( 'Missing — add your test or live secret key below.', 'woo-lomi' ),
		);

		$checks['currency'] = array(
			'label'   => __( 'Store currency', 'woo-lomi' ),
			'status'  => $this->is_valid_for_use() ? 'ok' : 'error',
			'message' => $this->is_valid_for_use()
				? strtoupper( get_woocommerce_currency() )
				: __( 'Must be XOF, USD, or EUR.', 'woo-lomi' ),
		);

		$checks['webhook_secret'] = array(
			'label'   => __( 'Webhook signing secret', 'woo-lomi' ),
			'status'  => $active_webhook ? 'ok' : 'warning',
			'message' => $active_webhook ? __( 'Configured', 'woo-lomi' ) : __( 'Recommended — required to verify PAYMENT_SUCCEEDED webhooks.', 'woo-lomi' ),
		);

		$checks['https'] = array(
			'label'   => __( 'HTTPS', 'woo-lomi' ),
			'status'  => is_ssl() ? 'ok' : 'warning',
			'message' => is_ssl() ? __( 'Enabled', 'woo-lomi' ) : __( 'Not detected — use HTTPS in production.', 'woo-lomi' ),
		);

		$checks['test_mode'] = array(
			'label'   => __( 'Test mode', 'woo-lomi' ),
			'status'  => $this->testmode ? 'warning' : 'ok',
			'message' => $this->testmode ? __( 'Sandbox API active', 'woo-lomi' ) : __( 'Live API active', 'woo-lomi' ),
		);

		if ( $active_secret ) {
			$this->refresh_credentials_from_settings();
			$connection = $this->test_lomi_api_connection();
			$checks['api_connection'] = array(
				'label'   => __( 'API connection', 'woo-lomi' ),
				'status'  => is_wp_error( $connection ) ? 'error' : 'ok',
				'message' => is_wp_error( $connection ) ? $connection->get_error_message() : __( 'GET /me succeeded', 'woo-lomi' ),
			);
		}

		return $checks;
	}

	/**
	 * Render setup health table on gateway settings.
	 */
	protected function render_setup_health_panel() {
		$checks = $this->get_setup_health_checks();
		echo '<h3>' . esc_html__( 'Setup health', 'woo-lomi' ) . '</h3>';
		echo '<table class="widefat striped" style="max-width:720px;margin-bottom:1.5em;">';
		echo '<thead><tr><th>' . esc_html__( 'Check', 'woo-lomi' ) . '</th><th>' . esc_html__( 'Status', 'woo-lomi' ) . '</th><th>' . esc_html__( 'Details', 'woo-lomi' ) . '</th></tr></thead><tbody>';
		foreach ( $checks as $check ) {
			$status_label = __( 'OK', 'woo-lomi' );
			if ( 'error' === $check['status'] ) {
				$status_label = __( 'Action required', 'woo-lomi' );
			} elseif ( 'warning' === $check['status'] ) {
				$status_label = __( 'Warning', 'woo-lomi' );
			}
			echo '<tr>';
			echo '<td>' . esc_html( $check['label'] ) . '</td>';
			echo '<td>' . esc_html( $status_label ) . '</td>';
			echo '<td>' . esc_html( $check['message'] ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Verify API credentials with GET /me.
	 *
	 * @return true|WP_Error
	 */
	protected function test_lomi_api_connection() {
		$cache_key = 'wc_lomi_me_' . md5( (string) $this->secret_key . ( $this->testmode ? 'test' : 'live' ) );
		$cached    = get_transient( $cache_key );
		if ( 'ok' === $cached ) {
			return true;
		}
		if ( is_string( $cached ) && 0 === strpos( $cached, 'err:' ) ) {
			return new WP_Error( 'lomi_me', substr( $cached, 4 ) );
		}

		$response = $this->lomi_api_request( 'GET', '/me', null );
		if ( is_wp_error( $response ) ) {
			set_transient( $cache_key, 'err:' . $response->get_error_message(), 5 * MINUTE_IN_SECONDS );
			return $response;
		}

		set_transient( $cache_key, 'ok', 5 * MINUTE_IN_SECONDS );
		return true;
	}

	/**
	 * Whether the order contains a new subscription purchase (not a renewal).
	 *
	 * @param WC_Order $order Order.
	 * @return bool
	 */
	protected function order_contains_new_subscription( $order ) {
		if ( ! function_exists( 'wcs_order_contains_subscription' ) ) {
			return false;
		}

		return wcs_order_contains_subscription( $order, array( 'parent', 'resubscribe', 'switch' ) );
	}

	/**
	 * Validate subscription catalog mapping for an order.
	 *
	 * @param WC_Order $order Order.
	 * @return true|WP_Error
	 */
	protected function validate_subscription_lomi_mapping_for_order( $order ) {
		if ( ! $this->order_contains_new_subscription( $order ) || ! class_exists( 'WC_Gateway_Lomi_Product_Admin' ) ) {
			return true;
		}

		$subscription_lines = 0;
		$other_lines        = 0;

		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}
			if ( WC_Gateway_Lomi_Product_Admin::product_is_subscription_type( $product ) ) {
				++$subscription_lines;
				if ( ! WC_Gateway_Lomi_Product_Admin::get_product_lomi_price_id( $product ) ) {
					return new WP_Error(
						'lomi_subscription_unmapped',
						sprintf(
							/* translators: %s: product name */
							__( 'Link "%s" to a lomi recurring price in the product editor before checkout.', 'woo-lomi' ),
							$product->get_name()
						)
					);
				}
			} else {
				++$other_lines;
			}
		}

		if ( $subscription_lines > 0 && $other_lines > 0 ) {
			return new WP_Error(
				'lomi_mixed_cart',
				__( 'Split subscription and one-time products into separate orders when paying with lomi.', 'woo-lomi' )
			);
		}

		if ( $subscription_lines > 1 ) {
			return new WP_Error(
				'lomi_multi_subscription',
				__( 'Checkout one subscription product per order when paying with lomi.', 'woo-lomi' )
			);
		}

		return true;
	}

	/**
	 * Resolve catalog checkout payload for a single subscription line.
	 *
	 * @param WC_Order $order Order.
	 * @return array|null|WP_Error
	 */
	protected function resolve_subscription_catalog_checkout( $order ) {
		$validation = $this->validate_subscription_lomi_mapping_for_order( $order );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		if ( ! $this->order_contains_new_subscription( $order ) || ! class_exists( 'WC_Gateway_Lomi_Product_Admin' ) ) {
			return null;
		}

		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$product = $item->get_product();
			if ( ! $product || ! WC_Gateway_Lomi_Product_Admin::product_is_subscription_type( $product ) ) {
				continue;
			}

			return array(
				'price_id'   => WC_Gateway_Lomi_Product_Admin::get_product_lomi_price_id( $product ),
				'product_id' => WC_Gateway_Lomi_Product_Admin::get_product_lomi_product_id( $product ),
				'quantity'   => max( 1, (int) $item->get_quantity() ),
			);
		}

		return null;
	}

	/**
	 * Extract transaction ID from webhook or API payload.
	 *
	 * @param object $data Payload object.
	 * @return string
	 */
	protected function extract_lomi_transaction_id_from_payload( $data ) {
		if ( ! is_object( $data ) ) {
			return '';
		}
		if ( ! empty( $data->transaction_id ) ) {
			return sanitize_text_field( (string) $data->transaction_id );
		}
		if ( ! empty( $data->id ) && empty( $data->checkout_session_id ) ) {
			return sanitize_text_field( (string) $data->id );
		}
		if ( ! empty( $data->id ) ) {
			return sanitize_text_field( (string) $data->id );
		}
		return '';
	}

	/**
	 * Persist lomi IDs on the Woo order for refunds and subscriptions.
	 *
	 * @param WC_Order   $order        Order.
	 * @param object|null $session_data Checkout session.
	 * @param object|null $webhook_data Webhook data payload.
	 */
	protected function persist_lomi_payment_meta( $order, $session_data = null, $webhook_data = null ) {
		if ( ! $order ) {
			return;
		}

		if ( $webhook_data ) {
			$transaction_id = $this->extract_lomi_transaction_id_from_payload( $webhook_data );
			if ( $transaction_id && ! $order->get_meta( '_lomi_transaction_id' ) ) {
				$order->update_meta_data( '_lomi_transaction_id', $transaction_id );
			}
			if ( ! empty( $webhook_data->checkout_session_id ) && ! $order->get_meta( '_lomi_checkout_session_id' ) ) {
				$order->update_meta_data( '_lomi_checkout_session_id', sanitize_text_field( (string) $webhook_data->checkout_session_id ) );
			}
			if ( ! empty( $webhook_data->subscription_id ) && ! $order->get_meta( '_lomi_subscription_id' ) ) {
				$order->update_meta_data( '_lomi_subscription_id', sanitize_text_field( (string) $webhook_data->subscription_id ) );
			}
			if ( ! empty( $webhook_data->price_id ) && ! $order->get_meta( '_lomi_price_id' ) ) {
				$order->update_meta_data( '_lomi_price_id', sanitize_text_field( (string) $webhook_data->price_id ) );
			}
			if ( ! $order->get_meta( '_lomi_paid_amount' ) ) {
				if ( isset( $webhook_data->gross_amount ) ) {
					$order->update_meta_data( '_lomi_paid_amount', (int) round( (float) $webhook_data->gross_amount ) );
				} elseif ( isset( $webhook_data->amount ) ) {
					$order->update_meta_data( '_lomi_paid_amount', (int) round( (float) $webhook_data->amount ) );
				}
			}
		}

		if ( $session_data ) {
			if ( ! empty( $session_data->subscription_id ) && ! $order->get_meta( '_lomi_subscription_id' ) ) {
				$order->update_meta_data( '_lomi_subscription_id', sanitize_text_field( (string) $session_data->subscription_id ) );
			}
			if ( ! empty( $session_data->product_id ) && ! $order->get_meta( '_lomi_product_id' ) ) {
				$order->update_meta_data( '_lomi_product_id', sanitize_text_field( (string) $session_data->product_id ) );
			}
			if ( ! empty( $session_data->price_id ) && ! $order->get_meta( '_lomi_price_id' ) ) {
				$order->update_meta_data( '_lomi_price_id', sanitize_text_field( (string) $session_data->price_id ) );
			}
			if ( isset( $session_data->amount ) && ! $order->get_meta( '_lomi_paid_amount' ) ) {
				$order->update_meta_data( '_lomi_paid_amount', (int) round( (float) $session_data->amount ) );
			}
		}

		if ( ! $order->get_meta( '_lomi_transaction_id' ) ) {
			$resolved = $this->resolve_lomi_transaction_id_for_order( $order );
			if ( $resolved ) {
				$order->update_meta_data( '_lomi_transaction_id', $resolved );
			}
		}

		$order->save();
	}

	/**
	 * Find a completed lomi transaction for a Woo order via metadata.
	 *
	 * @param WC_Order $order Order.
	 * @return string Transaction UUID or empty.
	 */
	protected function resolve_lomi_transaction_id_for_order( $order ) {
		$existing = $order->get_meta( '_lomi_transaction_id' );
		if ( $existing ) {
			return (string) $existing;
		}

		$order_id = (string) $order->get_id();
		$resp     = $this->lomi_api_request( 'GET', '/transactions?status=completed&page=1&pageSize=25', null );
		if ( is_wp_error( $resp ) ) {
			return '';
		}

		$rows = $this->lomi_unwrap_data( $resp );
		if ( ! is_array( $rows ) ) {
			if ( is_object( $rows ) ) {
				$rows = array( $rows );
			} else {
				return '';
			}
		}

		$session_id = (string) $order->get_meta( '_lomi_checkout_session_id' );

		foreach ( $rows as $row ) {
			if ( ! is_object( $row ) || empty( $row->transaction_id ) ) {
				continue;
			}
			$metadata = isset( $row->metadata ) ? $row->metadata : null;
			if ( ! is_object( $metadata ) ) {
				continue;
			}
			if ( isset( $metadata->wc_order_id ) && (string) $metadata->wc_order_id === $order_id ) {
				return sanitize_text_field( (string) $row->transaction_id );
			}
			if ( isset( $metadata->order_id ) && (string) $metadata->order_id === $order_id ) {
				return sanitize_text_field( (string) $row->transaction_id );
			}
			if ( $session_id && isset( $metadata->checkout_session_id ) && (string) $metadata->checkout_session_id === $session_id ) {
				return sanitize_text_field( (string) $row->transaction_id );
			}
		}

		return '';
	}

	/**
	 * Find Woo order by stored lomi transaction ID.
	 *
	 * @param string $transaction_id Transaction UUID.
	 * @return WC_Order|false
	 */
	protected function find_order_by_lomi_transaction_id( $transaction_id ) {
		$transaction_id = sanitize_text_field( (string) $transaction_id );
		if ( ! $transaction_id ) {
			return false;
		}

		$orders = wc_get_orders(
			array(
				'limit'        => 1,
				'meta_key'     => '_lomi_transaction_id',
				'meta_value'   => $transaction_id,
				'meta_compare' => '=',
				'return'       => 'objects',
			)
		);

		return ! empty( $orders ) ? $orders[0] : false;
	}

	/**
	 * Convert a major-unit amount to lomi. API units.
	 *
	 * @param float  $amount   Amount in major units (e.g. 9.99 USD).
	 * @param string $currency Currency code.
	 * @return int
	 */
	protected function amount_to_lomi_units( $amount, $currency ) {
		$currency = strtoupper( (string) $currency );
		$amount   = (float) $amount;

		if ( 'XOF' === $currency ) {
			return (int) round( $amount );
		}

		$decimals = $this->get_currency_minor_unit_decimals( $currency );
		return (int) round( $amount * ( 10 ** $decimals ) );
	}

	/**
	 * Format refund amount for lomi. API units.
	 *
	 * @param float  $amount   Refund amount in major units.
	 * @param string $currency Currency code.
	 * @return int
	 */
	protected function get_refund_amount_for_lomi( $amount, $currency ) {
		return $this->amount_to_lomi_units( $amount, $currency );
	}

	/**
	 * Whether checkout used a mapped lomi catalog price (subscription).
	 *
	 * @param WC_Order    $order        Order.
	 * @param object|null $session_data Checkout session.
	 * @return bool
	 */
	protected function order_uses_lomi_catalog_checkout( $order, $session_data = null ) {
		if ( $order->get_meta( '_lomi_price_id' ) ) {
			return true;
		}

		if ( $session_data && ! empty( $session_data->price_id ) && $this->order_contains_new_subscription( $order ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Subscription line total in lomi. API units (excludes shipping/tax).
	 *
	 * @param WC_Order $order Order.
	 * @return int
	 */
	protected function get_subscription_line_total_for_lomi( $order ) {
		if ( ! class_exists( 'WC_Gateway_Lomi_Product_Admin' ) ) {
			return 0;
		}

		$currency = strtoupper( $order->get_currency() );
		$total    = 0.0;

		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}

			$product = $item->get_product();
			if ( ! $product || ! WC_Gateway_Lomi_Product_Admin::product_is_subscription_type( $product ) ) {
				continue;
			}

			$total += (float) $item->get_total();
		}

		return $this->amount_to_lomi_units( $total, $currency );
	}

	/**
	 * Expected paid amount for verifying a completed checkout session.
	 *
	 * Catalog subscription sessions charge the lomi price only, not Woo shipping/tax.
	 *
	 * @param WC_Order    $order        Order.
	 * @param object|null $session_data Checkout session.
	 * @return int
	 */
	protected function get_expected_lomi_session_amount( $order, $session_data = null ) {
		if ( $this->order_uses_lomi_catalog_checkout( $order, $session_data ) ) {
			$line_total = $this->get_subscription_line_total_for_lomi( $order );
			if ( $line_total > 0 ) {
				return $line_total;
			}
		}

		return $this->get_order_amount_for_lomi( $order );
	}

	/**
	 * Maximum refundable amount in lomi. API units for this order.
	 *
	 * @param WC_Order $order Order.
	 * @return int
	 */
	protected function get_refundable_lomi_amount_ceiling( $order ) {
		$paid = $order->get_meta( '_lomi_paid_amount' );
		if ( '' !== $paid && null !== $paid ) {
			return max( 0, (int) $paid );
		}

		if ( $this->order_uses_lomi_catalog_checkout( $order ) ) {
			$line_total = $this->get_subscription_line_total_for_lomi( $order );
			if ( $line_total > 0 ) {
				return $line_total;
			}
		}

		return $this->get_order_amount_for_lomi( $order );
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

		$catalog = $this->resolve_subscription_catalog_checkout( $order );
		if ( is_wp_error( $catalog ) ) {
			return $catalog;
		}
		if ( is_array( $catalog ) && ! empty( $catalog['price_id'] ) ) {
			$body['price_id'] = $catalog['price_id'];
			$body['quantity'] = $catalog['quantity'];
			if ( ! empty( $catalog['product_id'] ) ) {
				$body['product_id'] = $catalog['product_id'];
			}
			unset( $body['amount'] );
			$order->update_meta_data( '_lomi_price_id', $catalog['price_id'] );
			if ( ! empty( $catalog['product_id'] ) ) {
				$order->update_meta_data( '_lomi_product_id', $catalog['product_id'] );
			}
			$order->save();
		}

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
			if ( $this->lomi_session_is_paid( $session_data->status ?? '' ) ) {
				$this->persist_lomi_payment_meta( $order, $session_data, null );
			}
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

		$uses_catalog = $this->order_uses_lomi_catalog_checkout( $order, $session_data );
		$expected     = $this->get_expected_lomi_session_amount( $order, $session_data );
		$paid         = isset( $session_data->amount ) ? (int) round( (float) $session_data->amount ) : 0;
		if ( $paid !== $expected ) {
			$order->update_status(
				'on-hold',
				sprintf(
					/* translators: 1: expected amount, 2: session amount */
					$uses_catalog
						? __( 'lomi.: subscription line amount mismatch (expected %1$s; session has %2$s). Align Woo subscription price with the lomi recurring price.', 'woo-lomi' )
						: __( 'lomi.: amount mismatch (order expects %1$s; session has %2$s).', 'woo-lomi' ),
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
			if ( $uses_catalog ) {
				$order->add_order_note(
					sprintf(
						/* translators: 1: session currency, 2: store currency */
						__( 'lomi.: catalog checkout currency (%1$s) differs from store currency (%2$s); verified against lomi catalog price.', 'woo-lomi' ),
						$currency_sess,
						$currency_order
					)
				);
			} else {
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
		}

		$ref = $session_ref ? $session_ref : (string) $order->get_id();
		$order->payment_complete( $ref );
		$order->add_order_note( sprintf( __( 'lomi. payment successful (checkout session: %s)', 'woo-lomi' ), $ref ) );
		if ( $this->is_autocomplete_order_enabled( $order ) ) {
			$order->update_status( 'completed' );
		}
		$this->persist_lomi_payment_meta( $order, $session_data, null );
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

		$payload = json_decode( $raw );
		$data    = is_object( $payload ) && isset( $payload->data ) ? $payload->data : null;

		if ( ! is_object( $data ) ) {
			status_header( 400 );
			exit;
		}

		if ( 'REFUND_COMPLETED' === $event ) {
			$this->handle_lomi_refund_completed_webhook( $data );
			status_header( 200 );
			exit;
		}

		if ( 'PAYMENT_SUCCEEDED' !== $event ) {
			status_header( 200 );
			exit;
		}

		$order = $this->resolve_order_from_lomi_webhook_data( $data );
		if ( ! $order ) {
			status_header( 200 );
			exit;
		}

		$this->persist_lomi_payment_meta( $order, null, $data );

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
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'lomi_refund', __( 'Order not found.', 'woo-lomi' ) );
		}

		$transaction_id = $order->get_meta( '_lomi_transaction_id' );
		if ( ! $transaction_id ) {
			$transaction_id = $this->resolve_lomi_transaction_id_for_order( $order );
			if ( $transaction_id ) {
				$order->update_meta_data( '_lomi_transaction_id', $transaction_id );
				$order->save();
			}
		}

		if ( ! $transaction_id ) {
			return new WP_Error(
				'lomi_refund',
				__( 'This payment is not linked to a lomi transaction. Process the refund from your lomi. dashboard.', 'woo-lomi' )
			);
		}

		$ceiling_units = $this->get_refundable_lomi_amount_ceiling( $order );
		if ( $ceiling_units <= 0 ) {
			$ceiling_units = $this->get_order_amount_for_lomi( $order );
		}

		if ( null === $amount || '' === $amount ) {
			$refund_units = $ceiling_units;
		} else {
			$refund_units = $this->get_refund_amount_for_lomi( $amount, $order->get_currency() );
			if ( $refund_units > $ceiling_units ) {
				$refund_units = $ceiling_units;
			}
		}

		if ( $refund_units <= 0 ) {
			return new WP_Error( 'lomi_refund', __( 'Refund amount must be greater than zero.', 'woo-lomi' ) );
		}

		$refund_type = ( $refund_units >= $ceiling_units ) ? 'full' : 'partial';

		$body = array(
			'transaction_id' => $transaction_id,
			'amount'         => $refund_units,
			'refund_type'    => $refund_type,
		);
		if ( $reason ) {
			$body['reason'] = $reason;
		}

		$response = $this->lomi_api_request( 'POST', '/refunds', $body );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$refund_row = $this->lomi_unwrap_data( $response );
		$refund_id  = is_object( $refund_row ) && ! empty( $refund_row->refund_id ) ? (string) $refund_row->refund_id : '';
		$order->add_order_note(
			$refund_id
				? sprintf(
					/* translators: 1: refund id, 2: refund type */
					__( 'lomi. refund initiated (%1$s, %2$s).', 'woo-lomi' ),
					$refund_id,
					$refund_type
				)
				: sprintf(
					/* translators: %s: refund type */
					__( 'lomi. refund initiated (%s).', 'woo-lomi' ),
					$refund_type
				)
		);
		$order->save();

		return true;
	}

	/**
	 * Resolve Woo order from webhook transaction payload.
	 *
	 * @param object $data Webhook data.
	 * @return WC_Order|false
	 */
	protected function resolve_order_from_lomi_webhook_data( $data ) {
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

		if ( ! $order_id ) {
			$transaction_id = $this->extract_lomi_transaction_id_from_payload( $data );
			if ( $transaction_id ) {
				$order = $this->find_order_by_lomi_transaction_id( $transaction_id );
				if ( $order ) {
					return $order;
				}
			}
			return false;
		}

		$order = wc_get_order( $order_id );
		return $order ? $order : false;
	}

	/**
	 * Add order note when refund completes in lomi. dashboard.
	 *
	 * @param object $data Webhook data.
	 */
	protected function handle_lomi_refund_completed_webhook( $data ) {
		$transaction_id = '';
		if ( ! empty( $data->transaction_id ) ) {
			$transaction_id = sanitize_text_field( (string) $data->transaction_id );
		} elseif ( ! empty( $data->id ) ) {
			$transaction_id = sanitize_text_field( (string) $data->id );
		}

		$order = $this->resolve_order_from_lomi_webhook_data( $data );
		if ( ! $order && $transaction_id ) {
			$order = $this->find_order_by_lomi_transaction_id( $transaction_id );
		}
		if ( ! $order ) {
			return;
		}

		$refund_id = ! empty( $data->refund_id ) ? sanitize_text_field( (string) $data->refund_id ) : '';
		$note_key  = $refund_id ? 'lomi_refund_note_' . $refund_id : 'lomi_refund_note_generic';
		if ( $order->get_meta( $note_key ) ) {
			return;
		}

		$order->add_order_note(
			$refund_id
				? sprintf(
					/* translators: %s: lomi refund id */
					__( 'lomi. refund completed (refund %s). Initiated outside WooCommerce if no matching Woo refund exists.', 'woo-lomi' ),
					$refund_id
				)
				: __( 'lomi. refund completed. Initiated outside WooCommerce if no matching Woo refund exists.', 'woo-lomi' )
		);
		$order->update_meta_data( $note_key, '1' );
		$order->save();
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
