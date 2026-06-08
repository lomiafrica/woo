jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle lomi. admin functions.
	 */
	var wc_lomi_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			$( document.body ).on( 'change', '#woocommerce_lomi_testmode', function() {
				var test_secret_key = $( '#woocommerce_lomi_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					test_webhook_secret = $( '#woocommerce_lomi_test_webhook_secret' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_lomi_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_webhook_secret = $( '#woocommerce_lomi_live_webhook_secret' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					test_secret_key.show();
					test_webhook_secret.show();
					live_secret_key.hide();
					live_webhook_secret.hide();
				} else {
					test_secret_key.hide();
					test_webhook_secret.hide();
					live_secret_key.show();
					live_webhook_secret.show();
				}
			} );

			$( '#woocommerce_lomi_testmode' ).change();

			$( '.wc-lomi-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products' ).closest( 'tr' ).hide();
				}
			} ).change();

			$( '#woocommerce_lomi_test_secret_key, #woocommerce_lomi_live_secret_key, #woocommerce_lomi_test_webhook_secret, #woocommerce_lomi_live_webhook_secret' ).after(
				'<button class="wc-lomi-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>'
			);

			$( '.wc-lomi-toggle-secret' ).on( 'click', function( event ) {
				event.preventDefault();

				var $dashicon = $( this ).closest( 'button' ).find( '.dashicons' );
				var $input = $( this ).closest( 'tr' ).find( '.input-text' );
				var inputType = $input.attr( 'type' );

				if ( 'text' === inputType ) {
					$input.attr( 'type', 'password' );
					$dashicon.removeClass( 'dashicons-hidden' );
					$dashicon.addClass( 'dashicons-visibility' );
				} else {
					$input.attr( 'type', 'text' );
					$dashicon.removeClass( 'dashicons-visibility' );
					$dashicon.addClass( 'dashicons-hidden' );
				}
			} );
		}
	};

	wc_lomi_admin.init();

} );
