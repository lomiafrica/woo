/**
 * External dependencies
 */
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import {Content, ariaLabel, Label} from './base';
import { PAYMENT_METHOD_NAME } from './constants';

const settings = getSetting( 'lomi_data', {} );
const label = ariaLabel({ title: settings.title });

/**
 * lomi. payment method config object.
 */
const Lomi_Gateway = {
	name: PAYMENT_METHOD_NAME,
	label: <Label title={ label } />,
	content: <Content description={ settings.description } securedBadgeUrl={ settings.secured_badge_url } paymentIconUrls={ settings.payment_icon_urls } />,
	edit: <Content description={ settings.description } securedBadgeUrl={ settings.secured_badge_url } paymentIconUrls={ settings.payment_icon_urls } />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		showSavedCards: settings.allow_saved_cards,
		showSaveOption: settings.allow_saved_cards,
		features: settings.supports,
	},
};

registerPaymentMethod( Lomi_Gateway );
