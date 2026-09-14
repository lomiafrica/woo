function isPlainObject(value) {
  return value !== null && !Array.isArray(value) && Object(value) === value;
}
function isTranslationLeaf(value) {
  return value === null || value === undefined || Object(value) !== value;
}
function isStringValue(value) {
  return Object.prototype.toString.call(value) === "[object String]";
}
(() => {
  "use strict";
  var e = {
    462(e, n, o) {
      var i = o(609),
        a = Symbol.for("react.element"),
        t = (Symbol.for("react.fragment"), Object.prototype.hasOwnProperty),
        r =
          i.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED
            .ReactCurrentOwner,
        s = { key: !0, ref: !0, __self: !0, __source: !0 };
      function c(e, n, o) {
        var i,
          c = {},
          l = null,
          d = null;
        for (i in (void 0 !== o && (l = "" + o),
        void 0 !== n.key && (l = "" + n.key),
        void 0 !== n.ref && (d = n.ref),
        n))
          t.call(n, i) && !s.hasOwnProperty(i) && (c[i] = n[i]);
        if (e && e.defaultProps)
          for (i in (n = e.defaultProps)) void 0 === c[i] && (c[i] = n[i]);
        return {
          $$typeof: a,
          type: e,
          key: l,
          ref: d,
          props: c,
          _owner: r.current,
        };
      }
      (n.jsx = c), (n.jsxs = c);
    },
    70(e, n, o) {
      e.exports = o(462);
    },
    609(e) {
      e.exports = window.React;
    },
  };
  const n = {},
    o = window.wc.wcBlocksRegistry,
    i = window.wc.wcSettings,
    a = window.wp.i18n,
    t = window.wp.htmlEntities;
  var r = (function o(i) {
    const a = n[i];
    if (void 0 !== a) return a.exports;
    const t = (n[i] = { exports: {} });
    return e[i](t, t.exports, o), t.exports;
  })(70);
  const s = (0, a.__)("lomi.", "woo-lomi"),
    c = (0, a.__)("Pay with", "woo-lomi"),
    l = (0, a.__)("Pay with lomi.", "woo-lomi"),
    d = (0, a.__)("Secure hosted checkout on lomi.", "woo-lomi"),
    m = (e) =>
      Object.prototype.toString.call(e) === "[object String]" &&
      (e.includes("apple-pay") || e.includes("google-pay")),
    _ = ({ brandingImageUrl: e, paymentIconUrls: n }) =>
      (0, r.jsxs)("div", {
        className: "wc-lomi-checkout-branding",
        children: [
          (0, r.jsxs)("div", {
            className: "wc-lomi-checkout-branding__main",
            children: [
              (0, r.jsx)("div", {
                className: "wc-lomi-checkout-branding__brand",
                children: e
                  ? (0, r.jsx)("img", {
                      className: "wc-lomi-pay-with-image",
                      src: e,
                      alt: l,
                      loading: "lazy",
                      decoding: "async",
                    })
                  : (0, r.jsxs)("p", {
                      className: "wc-lomi-checkout-branding__title",
                      children: [
                        c,
                        " ",
                        (0, r.jsx)("strong", { children: "lomi." }),
                      ],
                    }),
              }),
              n?.length > 0 &&
                (0, r.jsx)("div", {
                  className: "wc-lomi-checkout-branding__methods",
                  "aria-hidden": "true",
                  children: n.map((e, n) =>
                    (0, r.jsx)(
                      "div",
                      {
                        className:
                          "wc-lomi-checkout-branding__method" +
                          (m(e)
                            ? " wc-lomi-checkout-branding__method--wide"
                            : ""),
                        children: (0, r.jsx)("img", {
                          src: e,
                          alt: "",
                          loading: "lazy",
                          decoding: "async",
                        }),
                      },
                      n,
                    ),
                  ),
                }),
            ],
          }),
          (0, r.jsxs)("p", {
            className: "wc-lomi-checkout-branding__hint",
            children: [
              (0, r.jsx)("span", {
                className: "wc-lomi-checkout-branding__hint-icon",
                "aria-hidden": "true",
              }),
              d,
            ],
          }),
        ],
      }),
    w = ({ brandingImageUrl: e, paymentIconUrls: n }) =>
      (0, r.jsx)(_, { brandingImageUrl: e, paymentIconUrls: n }),
    p = () => null,
    g = (0, i.getSetting)("lomi_data", {}),
    h = (({ title: e }) => (0, t.decodeEntities)(e) || s)({ title: g.title }),
    u = {
      name: "lomi",
      label: (0, r.jsx)(w, {
        title: h,
        brandingImageUrl: g.branding_image_url,
        paymentIconUrls: g.payment_icon_urls,
      }),
      content: (0, r.jsx)(p, {}),
      edit: (0, r.jsx)(p, {}),
      canMakePayment: () => !0,
      ariaLabel: h,
      supports: {
        showSavedCards: g.allow_saved_cards,
        showSaveOption: g.allow_saved_cards,
        features: g.supports,
      },
    };
  (0, o.registerPaymentMethod)(u);
})();
