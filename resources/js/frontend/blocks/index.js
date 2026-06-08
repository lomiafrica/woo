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
	label: <Label title={ label } brandingImageUrl={ settings.branding_image_url } paymentIconUrls={ settings.payment_icon_urls } />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		showSavedCards: settings.allow_saved_cards,
		showSaveOption: settings.allow_saved_cards,
		features: settings.supports,
	},
};

registerPaymentMethod( Lomi_Gateway );
