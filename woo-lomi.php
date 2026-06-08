<?php
/**
 * Plugin Name: lomi. for WooCommerce
 * Plugin URI: https://lomi.africa
 * Description: WooCommerce payment gateway for lomi.
 * Version: 1.001.1
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

define( 'WC_LOMI_VERSION', '1.001.1' );

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
		return '';
	}

	$dir = dirname( WC_LOMI_MAIN_FILE ) . '/assets/images/';
	foreach ( array( 'webp', 'svg', 'png', 'jpg', 'jpeg' ) as $ext ) {
		$file = $dir . $slug . '.' . $ext;
		if ( is_readable( $file ) ) {
			return wc_lomi_force_https_url( plugins_url( 'assets/images/' . $slug . '.' . $ext, WC_LOMI_MAIN_FILE ) );
		}
	}

	return '';
}

/**
 * Brand logo URL for checkout (lomi. wordmark on light backgrounds).
 *
 * @return string
 */
function wc_lomi_get_brand_logo_url() {
	$url = wc_lomi_get_payment_icon_url( 'lomi_l' );
	if ( $url ) {
		return $url;
	}

	return wc_lomi_force_https_url( plugins_url( 'assets/images/lomi_d.webp', WC_LOMI_MAIN_FILE ) );
}

/**
 * Compact lomi. icon for the payment method title row.
 *
 * @return string
 */
function wc_lomi_get_compact_icon_url() {
	$url = wc_lomi_get_payment_icon_url( 'icon' );
	if ( $url ) {
		return $url;
	}

	return wc_lomi_get_brand_logo_url();
}

/**
 * Default payment method icons shown on checkout (bundled assets).
 *
 * Temporary until checkout session exposes merchant-specific methods.
 *
 * @return string[]
 */
function wc_lomi_get_default_payment_icon_slugs() {
	return array( 'wave', 'mtn', 'apple-pay', 'google-pay', 'spi' );
}

/**
 * Payment icon slugs to render on WooCommerce checkout.
 *
 * @return string[]
 */
function wc_lomi_get_checkout_payment_icon_slugs() {
	return apply_filters( 'wc_lomi_checkout_payment_icon_slugs', wc_lomi_get_default_payment_icon_slugs() );
}

/**
 * Payment icon URLs to render on WooCommerce checkout.
 *
 * @return string[]
 */
function wc_lomi_get_checkout_payment_icon_urls() {
	$urls = array();

	foreach ( wc_lomi_get_checkout_payment_icon_slugs() as $slug ) {
		$url = wc_lomi_get_payment_icon_url( $slug );
		if ( $url ) {
			$urls[] = $url;
		}
	}

	return apply_filters( 'wc_lomi_checkout_payment_icon_urls', $urls );
}

/**
 * Composite checkout branding image (legacy asset; optional override).
 *
 * @return string
 */
function wc_lomi_get_checkout_branding_image_url() {
	$url = wc_lomi_get_payment_icon_url( 'pay-with-lomi' );

	return apply_filters( 'wc_lomi_checkout_branding_image_url', $url );
}

/**
 * Whether checkout uses the structured branding card instead of the title.
 *
 * @return bool
 */
function wc_lomi_uses_checkout_branding_card() {
	return true;
}

/**
 * Enqueue checkout branding styles on the checkout page.
 *
 * @return void
 */
function wc_lomi_enqueue_checkout_branding_styles() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	wp_enqueue_style(
		'wc-lomi-checkout-branding',
		plugins_url( 'assets/css/checkout-branding.css', WC_LOMI_MAIN_FILE ),
		array(),
		WC_LOMI_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'wc_lomi_enqueue_checkout_branding_styles' );

/**
 * Checkout branding card: title, trust badge, and payment method icons.
 *
 * @return string
 */
function wc_lomi_get_checkout_branding_html() {
	$icons = array();

	foreach ( wc_lomi_get_checkout_payment_icon_slugs() as $slug ) {
		$url = wc_lomi_get_payment_icon_url( $slug );
		if ( $url ) {
			$icons[] = array(
				'slug' => $slug,
				'url'  => $url,
			);
		}
	}

	ob_start();
	?>
	<div class="wc-lomi-checkout-branding">
		<div class="wc-lomi-checkout-branding__header">
			<span class="wc-lomi-checkout-branding__badge">
				<?php
				$secured_by_image_url = wc_lomi_get_checkout_branding_image_url();
				if ( $secured_by_image_url ) :
					?>
					<img
						class="wc-lomi-secured-by-image"
						src="<?php echo esc_url( $secured_by_image_url ); ?>"
						alt="<?php echo esc_attr__( 'Secured by lomi.', 'woo-lomi' ); ?>"
						loading="lazy"
						decoding="async"
					/>
				<?php else : ?>
					<?php
					echo wp_kses(
						__( 'Secured by <strong>lomi.</strong>', 'woo-lomi' ),
						array( 'strong' => array() )
					);
					?>
				<?php endif; ?>
			</span>
		</div>
		<?php if ( ! empty( $icons ) ) : ?>
		<div class="wc-lomi-checkout-branding__methods">
			<?php foreach ( $icons as $icon ) : ?>
			<div class="wc-lomi-checkout-branding__method<?php echo 'spi' === $icon['slug'] ? ' wc-lomi-checkout-branding__method--wide' : ''; ?>">
				<img src="<?php echo esc_url( $icon['url'] ); ?>" alt="" loading="lazy" decoding="async" />
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render checkout branding (classic checkout payment box).
 *
 * @return void
 */
function wc_lomi_render_checkout_branding() {
	echo wc_lomi_get_checkout_branding_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Map a lomi. provider code to a bundled checkout icon slug.
 *
 * @param string $provider_code Provider code from GET /providers.
 * @return string
 */
function wc_lomi_map_provider_code_to_icon_slug( $provider_code ) {
	$map = array(
		'WAVE'   => 'wave',
		'MTN'    => 'mtn',
		'ORANGE' => 'orange',
		'SPI'    => 'spi',
		'STRIPE' => 'cards',
	);

	$code = strtoupper( (string) $provider_code );

	return isset( $map[ $code ] ) ? $map[ $code ] : '';
}

/**
 * Transient cache key for connected provider icons.
 *
 * @param string $secret_key API secret key.
 * @param bool   $testmode   Whether test mode is active.
 * @return string
 */
function wc_lomi_provider_icons_cache_key( $secret_key, $testmode ) {
	return 'wc_lomi_providers_' . ( $testmode ? 'test' : 'live' ) . '_' . md5( (string) $secret_key );
}

/**
 * Clear cached provider icons for a given API key.
 *
 * @param string $secret_key API secret key.
 * @param bool   $testmode   Whether test mode is active.
 * @return void
 */
function wc_lomi_clear_provider_icons_cache( $secret_key, $testmode ) {
	if ( empty( $secret_key ) ) {
		return;
	}

	delete_transient( wc_lomi_provider_icons_cache_key( $secret_key, $testmode ) );
}

/**
 * Fetch icon slugs for payment providers connected in the lomi. dashboard.
 *
 * Results are cached for one hour to avoid API calls on every checkout page load.
 *
 * @param string $secret_key API secret key.
 * @param bool   $testmode   Whether test mode is active.
 * @param bool   $force_refresh Bypass cache when true.
 * @return string[]
 */
function wc_lomi_fetch_connected_provider_icon_slugs( $secret_key, $testmode, $force_refresh = false ) {
	if ( empty( $secret_key ) ) {
		return array();
	}

	$cache_key = wc_lomi_provider_icons_cache_key( $secret_key, $testmode );

	if ( ! $force_refresh ) {
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$base_url = $testmode ? 'https://sandbox.api.lomi.africa' : 'https://api.lomi.africa';
	$response = wp_remote_get(
		$base_url . '/providers',
		array(
			'timeout' => 10,
			'headers' => array(
				'X-API-KEY' => $secret_key,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array();
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	$body   = json_decode( wp_remote_retrieve_body( $response ), true );

	$providers = array();
	if ( ! empty( $body['data'] ) && is_array( $body['data'] ) ) {
		$providers = $body['data'];
	} elseif ( is_array( $body ) && isset( $body[0]['provider_code'] ) ) {
		$providers = $body;
	}

	if ( 200 !== $status || empty( $providers ) ) {
		return array();
	}

	$icons = array();
	foreach ( $providers as $provider ) {
		if ( empty( $provider['is_connected'] ) || empty( $provider['provider_code'] ) ) {
			continue;
		}

		$slug = wc_lomi_map_provider_code_to_icon_slug( $provider['provider_code'] );
		if ( $slug && ! in_array( $slug, $icons, true ) ) {
			$icons[] = $slug;
		}
	}

	$order = array( 'wave', 'mtn', 'apple-pay', 'google-pay', 'spi' );
	usort(
		$icons,
		function ( $a, $b ) use ( $order ) {
			$pos_a = array_search( $a, $order, true );
			$pos_b = array_search( $b, $order, true );
			$pos_a = false === $pos_a ? PHP_INT_MAX : $pos_a;
			$pos_b = false === $pos_b ? PHP_INT_MAX : $pos_b;

			return $pos_a - $pos_b;
		}
	);

	set_transient( $cache_key, $icons, HOUR_IN_SECONDS );

	return $icons;
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

	add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_lomi_gateway', 99 );
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tbz_woo_lomi_plugin_action_links' );

}
add_action( 'plugins_loaded', 'tbz_wc_lomi_init', 99 );

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 * @return array
 */
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
 * @return array
 */
function tbz_wc_add_lomi_gateway( $methods ) {

	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway' ) ) {
		$methods[] = 'WC_Gateway_Lomi_Subscriptions';
	} else {
		$methods[] = 'WC_Gateway_Lomi';
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
 */
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
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			static function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_Gateway_Lomi_Blocks_Support() );
			}
		);
	}
}
add_action( 'woocommerce_blocks_loaded', 'tbz_wc_gateway_lomi_woocommerce_block_support' );
