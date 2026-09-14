/**
 * External dependencies
 */
import { __ } from "@wordpress/i18n";
import { decodeEntities } from "@wordpress/html-entities";

const defaultLabel = __("lomi.", "woo-lomi");

const payWithLabel = __("Pay with", "woo-lomi");

const payWithImageAlt = __("Pay with lomi.", "woo-lomi");

const hostedCheckoutHint = __("Secure hosted checkout on lomi.", "woo-lomi");

const brandName = "lomi.";

export const ariaLabel = ({ title }) => {
  return decodeEntities(title) || defaultLabel;
};

const isWidePaymentIcon = (iconUrl) => {
  if (!isStringValue(iconUrl)) {
    return false;
  }

  return iconUrl.includes("apple-pay") || iconUrl.includes("google-pay");
};

const CheckoutBranding = ({ brandingImageUrl, paymentIconUrls }) => {
  function isPlainObject(value) {
    return value !== null && !Array.isArray(value) && Object(value) === value;
  }
  function isTranslationLeaf(value) {
    return value === null || value === undefined || Object(value) !== value;
  }
  function isStringValue(value) {
    return Object.prototype.toString.call(value) === "[object String]";
  }
  return (
    <div className="wc-lomi-checkout-branding">
      <div className="wc-lomi-checkout-branding__main">
        <div className="wc-lomi-checkout-branding__brand">
          {brandingImageUrl ? (
            <img
              className="wc-lomi-pay-with-image"
              src={brandingImageUrl}
              alt={payWithImageAlt}
              loading="lazy"
              decoding="async"
            />
          ) : (
            <p className="wc-lomi-checkout-branding__title">
              {payWithLabel} <strong>{brandName}</strong>
            </p>
          )}
        </div>
        {paymentIconUrls?.length > 0 && (
          <div
            className="wc-lomi-checkout-branding__methods"
            aria-hidden="true"
          >
            {paymentIconUrls.map((iconUrl, index) => (
              <div
                key={index}
                className={
                  "wc-lomi-checkout-branding__method" +
                  (isWidePaymentIcon(iconUrl)
                    ? " wc-lomi-checkout-branding__method--wide"
                    : "")
                }
              >
                <img src={iconUrl} alt="" loading="lazy" decoding="async" />
              </div>
            ))}
          </div>
        )}
      </div>
      <p className="wc-lomi-checkout-branding__hint">
        <span
          className="wc-lomi-checkout-branding__hint-icon"
          aria-hidden="true"
        />
        {hostedCheckoutHint}
      </p>
    </div>
  );
};

/**
 * Label — branding card shown in the payment method list (Blocks checkout).
 */
export const Label = ({ brandingImageUrl, paymentIconUrls }) => {
  return (
    <CheckoutBranding
      brandingImageUrl={brandingImageUrl}
      paymentIconUrls={paymentIconUrls}
    />
  );
};

/**
 * Content is empty; branding is shown in the label row.
 */
export const Content = () => {
  return null;
};
