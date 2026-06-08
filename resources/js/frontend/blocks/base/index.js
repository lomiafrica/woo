/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

const defaultLabel = __(
    'lomi. ',
    'woo-lomi'
);

const securedBadgeLabel = __(
    'Secured by lomi.',
    'woo-lomi'
);

const securedBadgeStyles = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    padding: '7px 16px',
    borderRadius: '9999px',
    background: 'rgba(255, 255, 255, 0.55)',
    backdropFilter: 'blur(12px)',
    WebkitBackdropFilter: 'blur(12px)',
    border: '1px solid rgba(15, 23, 42, 0.08)',
    boxShadow: '0 1px 2px rgba(15, 23, 42, 0.06)',
    width: 'fit-content',
};

export const ariaLabel = ({ title }) => {
    return decodeEntities( title ) || defaultLabel;
}

const PaymentIcons = ({ logoUrls }) => {
    if ( ! logoUrls?.length ) {
        return null;
    }

    return (
        <div className="wc-lomi-payment-icons" style={{ display: 'flex', flexDirection: 'row', gap: '0.5rem', flexWrap: 'wrap', alignItems: 'center' }}>
            {logoUrls.map((logoUrl, index) => (
                <img key={index} src={logoUrl} alt="" style={{ height: '28px', width: 'auto', maxWidth: '72px', objectFit: 'contain' }} />
            ))}
        </div>
    );
};

const SecuredBadge = ({ securedBadgeUrl }) => {
    return (
        <div className="wc-lomi-secured-badge" style={ securedBadgeStyles }>
            { securedBadgeUrl ? (
                <img
                    src={ securedBadgeUrl }
                    alt={ securedBadgeLabel }
                    style={{ height: '18px', width: 'auto', maxWidth: '180px', objectFit: 'contain' }}
                />
            ) : (
                <span style={{ fontSize: '12px', fontWeight: 600, letterSpacing: '0.01em', color: '#0f172a' }}>
                    { securedBadgeLabel }
                </span>
            ) }
        </div>
    );
};

/**
 * Content — "Pay with lomi.", secured badge, then payment method icons.
 */
export const Content = ({ description, securedBadgeUrl, paymentIconUrls }) => {
    return (
        <div className="wc-lomi-checkout-branding" style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
            { description ? (
                <div className="wc-lomi-checkout-description">{ decodeEntities( description ) }</div>
            ) : null }
            <SecuredBadge securedBadgeUrl={ securedBadgeUrl } />
            <PaymentIcons logoUrls={ paymentIconUrls } />
        </div>
    );
};

export const Label = ({ title }) => {
    return <span>{ ariaLabel( { title: title } ) }</span>;
};
