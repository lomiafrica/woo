jQuery( function( $ ) {
	'use strict';

	var formatlomi = {};

	/**
	 * Object to handle lomi. admin functions.
	 */
	var wc_lomi_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_lomi_testmode', function() {
				var test_secret_key = $( '#woocommerce_lomi_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					test_public_key = $( '#woocommerce_lomi_test_public_key' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_lomi_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_lomi_live_public_key' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					test_secret_key.show();
					test_public_key.show();
					live_secret_key.hide();
					live_public_key.hide();
				} else {
					test_secret_key.hide();
					test_public_key.hide();
					live_secret_key.show();
					live_public_key.show();
				}
			} );

			$( '#woocommerce_lomi_testmode' ).change();

			// Toggle Custom Metadata settings.
			$( '.wc-lomi-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products' ).closest( 'tr' ).hide();
				}
			} ).change();

			$( ".wc-lomi-payment-icons" ).select2( {
				templateResult: formatlomi.PaymentIcons,
				templateSelection: formatlomi.PaymentIconDisplay
			} );

			$( '#woocommerce_lomi_test_secret_key, #woocommerce_lomi_live_secret_key' ).after(
				'<button class="wc-lomi-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>'
			);

			$( '.wc-lomi-toggle-secret' ).on( 'click', function( event ) {
				event.preventDefault();

				let $dashicon = $( this ).closest( 'button' ).find( '.dashicons' );
				let $input = $( this ).closest( 'tr' ).find( '.input-text' );
				let inputType = $input.attr( 'type' );

				if ( 'text' == inputType ) {
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

	formatlomi.PaymentIcons = function( payment_method ) {
		if ( !payment_method.id ) {
			return payment_method.text;
		}

		var $payment_method = $(
			'<span><img src="' + wc_lomi_admin_params.plugin_url + '/assets/images/lomi-placeholder.svg" class="img-flag" style="height: 15px; width:18px;" alt="" /> ' + payment_method.text + '</span>'
		);

		return $payment_method;
	};

	formatlomi.PaymentIconDisplay = function( payment_method ) {
		return payment_method.text;
	};

	wc_lomi_admin.init();

} );
