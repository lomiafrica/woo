/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

const defaultLabel = __(
    'lomi. ',
    'woo-lomi'
);

const payWithLabel = __(
    'Pay with',
    'woo-lomi'
);

const securedByLabel = __(
    'Secured by',
    'woo-lomi'
);

const securedByImageAlt = __(
    'Secured by lomi.',
    'woo-lomi'
);

const brandName = 'lomi.';

export const ariaLabel = ({ title }) => {
    return decodeEntities( title ) || defaultLabel;
}

const isWidePaymentIcon = (iconUrl) => {
    return typeof iconUrl === 'string' && iconUrl.includes( 'spi' );
};

const SecuredByBadge = ({ brandingImageUrl }) => {
    if ( brandingImageUrl ) {
        return (
            <span className="wc-lomi-checkout-branding__badge">
                <img
                    className="wc-lomi-secured-by-image"
                    src={ brandingImageUrl }
                    alt={ securedByImageAlt }
                    loading="lazy"
                    decoding="async"
                />
            </span>
        );
    }

    return (
        <span className="wc-lomi-checkout-branding__badge">
            { securedByLabel } <strong>{ brandName }</strong>
        </span>
    );
};

const CheckoutBranding = ({ brandingImageUrl, paymentIconUrls }) => {
    return (
        <div className="wc-lomi-checkout-branding">
            <div className="wc-lomi-checkout-branding__header">
                <p className="wc-lomi-checkout-branding__title">
                    { payWithLabel } <strong>{ brandName }</strong>
                </p>
                <SecuredByBadge brandingImageUrl={ brandingImageUrl } />
            </div>
            { paymentIconUrls?.length > 0 && (
                <div className="wc-lomi-checkout-branding__methods">
                    { paymentIconUrls.map( ( iconUrl, index ) => (
                        <div
                            key={ index }
                            className={
                                'wc-lomi-checkout-branding__method' +
                                ( isWidePaymentIcon( iconUrl ) ? ' wc-lomi-checkout-branding__method--wide' : '' )
                            }
                        >
                            <img src={ iconUrl } alt="" loading="lazy" decoding="async" />
                        </div>
                    ) ) }
                </div>
            ) }
        </div>
    );
};

/**
 * Label — compact branding card with payment method icons.
 */
export const Label = ({ title, brandingImageUrl, paymentIconUrls }) => {
    return (
        <CheckoutBranding
            brandingImageUrl={ brandingImageUrl }
            paymentIconUrls={ paymentIconUrls }
        />
    );
};

/**
 * Content is empty; branding is shown in the label row.
 */
export const Content = () => {
    return null;
};
