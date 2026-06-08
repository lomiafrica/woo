<?php
/**
 * Plugin Name: lomi. for WooCommerce
 * Plugin URI: https://lomi.africa
 * Description: WooCommerce payment gateway for lomi.
 * Version: 1.0.0
 * Author: lomi.
 * Author URI: https://lomi.africa
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires Plugins: woocommerce
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 9.6
 * WC tested up to: 10.7
 * Text Domain: woo-lomi
 * Domain Path: /languages
 */

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_LOMI_MAIN_FILE', __FILE__ );
define( 'WC_LOMI_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

define( 'WC_LOMI_VERSION', '1.0.0' );

/**
 * Load plugin translations.
 */
function wc_lomi_load_textdomain() {
	load_plugin_textdomain(
		'woo-lomi',
		false,
		dirname( plugin_basename( WC_LOMI_MAIN_FILE ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'wc_lomi_load_textdomain', 0 );

/**
 * Force HTTPS URL when WooCommerce is available.
 *
 * @param string $url URL.
 * @return string
 */
function wc_lomi_force_https_url( $url ) {
	return class_exists( 'WC_HTTPS' ) ? WC_HTTPS::force_https_url( $url ) : $url;
}

/**
 * Resolve payment icon URL: first existing assets/images/{slug}.{svg,png,...} or bundled placeholder.
 *
 * @param string $slug Basename without extension.
 * @return string
 */
function wc_lomi_get_payment_icon_url( $slug ) {
	$slug = strtolower( sanitize_file_name( (string) $slug ) );
	if ( '' === $slug ) {
		$slug = 'lomi';
	}
	$dir = dirname( WC_LOMI_MAIN_FILE ) . '/assets/images/';
	foreach ( array( 'svg', 'png', 'webp', 'jpg', 'jpeg' ) as $ext ) {
		$file = $dir . $slug . '.' . $ext;
		if ( is_readable( $file ) ) {
			return wc_lomi_force_https_url( plugins_url( 'assets/images/' . $slug . '.' . $ext, WC_LOMI_MAIN_FILE ) );
		}
	}
	return wc_lomi_force_https_url( plugins_url( 'assets/images/lomi-placeholder.svg', WC_LOMI_MAIN_FILE ) );
}

/**
 * Initialize lomi. for WooCommerce.
 */
function tbz_wc_lomi_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'tbz_wc_lomi_wc_missing_notice' );
		return;
	}

	add_action( 'admin_init', 'tbz_wc_lomi_testmode_notice' );

	require_once __DIR__ . '/includes/class-wc-gateway-lomi.php';

	require_once __DIR__ . '/includes/class-wc-gateway-lomi-subscriptions.php';

	require_once __DIR__ . '/includes/custom-gateways/class-wc-gateway-custom-lomi.php';

	require_once __DIR__ . '/includes/custom-gateways/gateway-one/class-wc-gateway-lomi-one.php';
	require_once __DIR__ . '/includes/custom-gateways/gateway-two/class-wc-gateway-lomi-two.php';
	require_once __DIR__ . '/includes/custom-gateways/gateway-three/class-wc-gateway-lomi-three.php';
	require_once __DIR__ . '/includes/custom-gateways/gateway-four/class-wc-gateway-lomi-four.php';
	require_once __DIR__ . '/includes/custom-gateways/gateway-five/class-wc-gateway-lomi-five.php';

	add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_lomi_gateway', 99 );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tbz_woo_lomi_plugin_action_links' );

}
add_action( 'plugins_loaded', 'tbz_wc_lomi_init', 99 );

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function tbz_woo_lomi_plugin_action_links( $links ) {

	$settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=lomi' ) . '" title="' . esc_attr__( 'View lomi. WooCommerce settings', 'woo-lomi' ) . '">' . __( 'Settings', 'woo-lomi' ) . '</a>',
	);

	return array_merge( $settings_link, $links );

}

/**
 * Add lomi. gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function tbz_wc_add_lomi_gateway( $methods ) {

	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {
		$methods[] = 'WC_Gateway_Lomi_Subscriptions';
	} else {
		$methods[] = 'WC_Gateway_Lomi';
	}

	$allowed_currencies = apply_filters( 'woocommerce_lomi_supported_currencies', array( 'XOF', 'USD', 'EUR' ) );

	if ( in_array( get_woocommerce_currency(), $allowed_currencies, true ) ) {

		$settings        = get_option( 'woocommerce_lomi_settings', '' );
		$custom_gateways = isset( $settings['custom_gateways'] ) ? $settings['custom_gateways'] : '';

		switch ( $custom_gateways ) {
			case '5':
				$methods[] = 'WC_Gateway_Lomi_One';
				$methods[] = 'WC_Gateway_Lomi_Two';
				$methods[] = 'WC_Gateway_Lomi_Three';
				$methods[] = 'WC_Gateway_Lomi_Four';
				$methods[] = 'WC_Gateway_Lomi_Five';
				break;

			case '4':
				$methods[] = 'WC_Gateway_Lomi_One';
				$methods[] = 'WC_Gateway_Lomi_Two';
				$methods[] = 'WC_Gateway_Lomi_Three';
				$methods[] = 'WC_Gateway_Lomi_Four';
				break;

			case '3':
				$methods[] = 'WC_Gateway_Lomi_One';
				$methods[] = 'WC_Gateway_Lomi_Two';
				$methods[] = 'WC_Gateway_Lomi_Three';
				break;

			case '2':
				$methods[] = 'WC_Gateway_Lomi_One';
				$methods[] = 'WC_Gateway_Lomi_Two';
				break;

			case '1':
				$methods[] = 'WC_Gateway_Lomi_One';
				break;

			default:
				break;
		}
	}

	return $methods;

}

/**
 * Display a notice if WooCommerce is not installed
 */
function tbz_wc_lomi_wc_missing_notice() {
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'lomi. requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'woo-lomi' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function tbz_wc_lomi_testmode_notice() {

	if ( ! class_exists( Notes::class ) ) {
		return;
	}

	if ( ! class_exists( WC_Data_Store::class ) ) {
		return;
	}

	if ( ! method_exists( Notes::class, 'get_note_by_name' ) ) {
		return;
	}

	$test_mode_note = Notes::get_note_by_name( 'lomi-test-mode' );

	if ( false !== $test_mode_note ) {
		return;
	}

	$lomi_settings = get_option( 'woocommerce_lomi_settings' );
	$test_mode     = $lomi_settings['testmode'] ?? '';

	if ( 'yes' !== $test_mode ) {
		Notes::delete_notes_with_name( 'lomi-test-mode' );

		return;
	}

	$note = new Note();
	$note->set_title( __( 'lomi. test mode enabled', 'woo-lomi' ) );
	$note->set_content( __( 'lomi. test mode is currently enabled. Remember to disable it when you want to start accepting live payments on your site.', 'woo-lomi' ) );
	$note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
	$note->set_layout( 'plain' );
	$note->set_is_snoozable( false );
	$note->set_name( 'lomi-test-mode' );
	$note->set_source( 'woo-lomi' );
	$note->add_action( 'disable-lomi-test-mode', __( 'Disable lomi. test mode', 'woo-lomi' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=lomi' ) );
	$note->save();
}

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Registers WooCommerce Blocks integration.
 */
function tbz_wc_gateway_lomi_woocommerce_block_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once __DIR__ . '/includes/class-wc-gateway-lomi-blocks-support.php';
		require_once __DIR__ . '/includes/custom-gateways/class-wc-gateway-custom-lomi-blocks-support.php';
		require_once __DIR__ . '/includes/custom-gateways/gateway-one/class-wc-gateway-lomi-one-blocks-support.php';
		require_once __DIR__ . '/includes/custom-gateways/gateway-two/class-wc-gateway-lomi-two-blocks-support.php';
		require_once __DIR__ . '/includes/custom-gateways/gateway-three/class-wc-gateway-lomi-three-blocks-support.php';
		require_once __DIR__ . '/includes/custom-gateways/gateway-four/class-wc-gateway-lomi-four-blocks-support.php';
		require_once __DIR__ . '/includes/custom-gateways/gateway-five/class-wc-gateway-lomi-five-blocks-support.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			static function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_Gateway_Lomi_Blocks_Support() );
				$payment_method_registry->register( new WC_Gateway_Lomi_One_Blocks_Support() );
				$payment_method_registry->register( new WC_Gateway_Lomi_Two_Blocks_Support() );
				$payment_method_registry->register( new WC_Gateway_Lomi_Three_Blocks_Support() );
				$payment_method_registry->register( new WC_Gateway_Lomi_Four_Blocks_Support() );
				$payment_method_registry->register( new WC_Gateway_Lomi_Five_Blocks_Support() );
			}
		);
	}
}
add_action( 'woocommerce_blocks_loaded', 'tbz_wc_gateway_lomi_woocommerce_block_support' );
