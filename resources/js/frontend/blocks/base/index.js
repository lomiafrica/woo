/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

const defaultLabel = __(
    'lomi. ',
    'woo-lomi'
);

export const ariaLabel = ({ title }) => {
    return decodeEntities( title ) || defaultLabel;
}

/**
 * Content component
 */
export const Content = ({ description }) => {
    return decodeEntities( description || '' );
};

const PaymentIcons = ({ logoUrls, label }) => {
    return (
        <div style={{ display: 'flex', flexDirection: 'row', gap: '0.35rem', flexWrap: 'wrap', alignItems: 'center' }}>
            {logoUrls.map((logoUrl, index) => (
                <img key={index} src={logoUrl} alt={label} style={{ height: '24px', width: 'auto', maxWidth: '80px', objectFit: 'contain' }} />
            ))}
        </div>
    );
};

export const Label = ({ logoUrls, title }) => {
    return (
        <>
            <div style={{ display: 'flex', flexDirection: 'row', gap: '0.5rem', alignItems: 'center' }}>
                <div>
                    { ariaLabel( { title: title } ) }
                </div>
                <PaymentIcons logoUrls={ logoUrls } label={ ariaLabel( { title: title } ) } />
            </div>
        </>
    );
};
