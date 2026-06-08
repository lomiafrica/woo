( function () {
	'use strict';

	if ( typeof wc_lomi_checkout_params === 'undefined' ) {
		return;
	}

	const STORAGE_KEY = wc_lomi_checkout_params.storageKey || 'wc_lomi_checkout_redirect';
	const gatewayIds = wc_lomi_checkout_params.gatewayIds || [ 'lomi' ];

	function isLomiPaymentSelected() {
		const selected = document.querySelector( 'input[name="payment_method"]:checked' );
		if ( selected && gatewayIds.indexOf( selected.value ) !== -1 ) {
			return true;
		}

		if ( window.wp && window.wp.data ) {
			try {
				const method = window.wp.data.select( 'wc/store/checkout' )?.getSelectedPaymentMethod?.();
				return method && gatewayIds.indexOf( method ) !== -1;
			} catch ( error ) {
				return false;
			}
		}

		return false;
	}

	function markRedirectPending() {
		sessionStorage.setItem(
			STORAGE_KEY,
			JSON.stringify( {
				startedAt: Date.now(),
			} )
		);
	}

	function unblockCheckout() {
		if ( window.jQuery ) {
			window.jQuery( document.body ).unblock();
			window.jQuery( '.woocommerce-checkout-payment, form.checkout' ).unblock();
		}

		if ( window.wp && window.wp.data ) {
			try {
				const dispatch = window.wp.data.dispatch( 'wc/store/checkout' );
				dispatch?.setIsProcessing?.( false );
				dispatch?.setHasError?.( false );
				dispatch?.setIdle?.();
			} catch ( error ) {
				// Ignore store errors on classic checkout.
			}
		}
	}

	function handleAbandonedCheckout() {
		const raw = sessionStorage.getItem( STORAGE_KEY );
		if ( ! raw ) {
			return;
		}

		sessionStorage.removeItem( STORAGE_KEY );
		unblockCheckout();

		const abandonUrl = wc_lomi_checkout_params.abandonUrl;
		if ( ! abandonUrl ) {
			window.location.reload();
			return;
		}

		window.fetch( abandonUrl, {
			method: 'GET',
			credentials: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
		} )
			.catch( function () {
				return null;
			} )
			.finally( function () {
				window.location.reload();
			} );
	}

	function bindClassicCheckout() {
		if ( ! window.jQuery ) {
			return;
		}

		window.jQuery( document.body ).on( 'checkout_place_order', function () {
			if ( isLomiPaymentSelected() ) {
				markRedirectPending();
			}
		} );

		window.jQuery( document.body ).on( 'checkout_place_order_success', function ( event, result ) {
			if ( result && result.redirect ) {
				markRedirectPending();
			}
		} );
	}

	function bindBlocksCheckout() {
		if ( ! window.wp || ! window.wp.data || ! window.wp.data.subscribe ) {
			return;
		}

		let wasProcessing = false;

		window.wp.data.subscribe( function () {
			const store = window.wp.data.select( 'wc/store/checkout' );
			if ( ! store || ! store.isProcessing ) {
				return;
			}

			const processing = store.isProcessing();
			if ( processing && ! wasProcessing && isLomiPaymentSelected() ) {
				markRedirectPending();
			}

			wasProcessing = processing;
		} );
	}

	function shouldHandleAbandon() {
		return ! document.body.classList.contains( 'woocommerce-order-received' );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! shouldHandleAbandon() ) {
			sessionStorage.removeItem( STORAGE_KEY );
			return;
		}

		handleAbandonedCheckout();
	} );

	window.addEventListener( 'pageshow', function ( event ) {
		if ( ! shouldHandleAbandon() ) {
			sessionStorage.removeItem( STORAGE_KEY );
			return;
		}

		if ( event.persisted ) {
			handleAbandonedCheckout();
		}
	} );

	document.addEventListener( 'DOMContentLoaded', function () {
		bindClassicCheckout();
		bindBlocksCheckout();
	} );
} )();
