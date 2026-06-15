jQuery( function( $ ) {
	'use strict';

	var wc_lomi_admin = {
		testFieldSelectors: '#woocommerce_lomi_test_secret_key, #woocommerce_lomi_test_public_key, #woocommerce_lomi_test_webhook_secret',
		liveFieldSelectors: '#woocommerce_lomi_live_secret_key, #woocommerce_lomi_live_public_key, #woocommerce_lomi_live_webhook_secret',

		getSettingsTable: function() {
			return $( '.wc-lomi-settings-table' );
		},

		fieldRow: function( $field ) {
			var $row = $field.closest( 'tr' );
			if ( $row.length ) {
				return $row;
			}
			return $field.closest( 'fieldset, .form-field, .components-base-control' ).first();
		},

		toggleModeFields: function( testMode ) {
			var $table = this.getSettingsTable();

			if ( $table.length ) {
				$table.toggleClass( 'wc-lomi-mode-test', testMode );
				$table.toggleClass( 'wc-lomi-mode-live', ! testMode );
			}

			if ( testMode ) {
				$( this.liveFieldSelectors ).each( function() {
					wc_lomi_admin.fieldRow( $( this ) ).hide();
				} );
				$( this.testFieldSelectors ).each( function() {
					wc_lomi_admin.fieldRow( $( this ) ).show();
				} );
			} else {
				$( this.testFieldSelectors ).each( function() {
					wc_lomi_admin.fieldRow( $( this ) ).hide();
				} );
				$( this.liveFieldSelectors ).each( function() {
					wc_lomi_admin.fieldRow( $( this ) ).show();
				} );
			}
		},

		init: function() {
			var $testMode = $( '#woocommerce_lomi_testmode' );

			$( document.body ).on( 'change', '#woocommerce_lomi_testmode', function() {
				wc_lomi_admin.toggleModeFields( $( this ).is( ':checked' ) );
			} );

			if ( $testMode.length ) {
				wc_lomi_admin.toggleModeFields( $testMode.is( ':checked' ) );
			}

			$( '.wc-lomi-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products' ).closest( 'tr' ).hide();
				}
			} ).change();

			$( '#woocommerce_lomi_test_secret_key, #woocommerce_lomi_live_secret_key, #woocommerce_lomi_test_webhook_secret, #woocommerce_lomi_live_webhook_secret' ).after(
				'<button type="button" class="wc-lomi-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>'
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
