function isPlainObject(value) {
  return value !== null && !Array.isArray(value) && Object(value) === value;
}
function isTranslationLeaf(value) {
  return value === null || value === undefined || Object(value) !== value;
}
function isStringValue(value) {
  return Object.prototype.toString.call(value) === "[object String]";
}
jQuery(function ($) {
  "use strict";

  var wc_lomi_admin = {
    testFieldSelectors:
      "#woocommerce_lomi_test_secret_key, #woocommerce_lomi_test_public_key, #woocommerce_lomi_test_webhook_secret",
    liveFieldSelectors:
      "#woocommerce_lomi_live_secret_key, #woocommerce_lomi_live_public_key, #woocommerce_lomi_live_webhook_secret",
    advancedVisible: false,

    getSettingsTable: function () {
      return $(".wc-lomi-settings-table");
    },

    fieldRow: function ($field) {
      var $row = $field.closest("tr");
      if ($row.length) {
        return $row;
      }
      return $field
        .closest("fieldset, .form-field, .components-base-control")
        .first();
    },

    toggleModeFields: function (testMode) {
      var $table = this.getSettingsTable();

      if ($table.length) {
        $table.toggleClass("wc-lomi-mode-test", testMode);
        $table.toggleClass("wc-lomi-mode-live", !testMode);
      }

      if (testMode) {
        $(this.liveFieldSelectors).each(function () {
          wc_lomi_admin.fieldRow($(this)).hide();
        });
        $(this.testFieldSelectors).each(function () {
          wc_lomi_admin.fieldRow($(this)).show();
        });
      } else {
        $(this.testFieldSelectors).each(function () {
          wc_lomi_admin.fieldRow($(this)).hide();
        });
        $(this.liveFieldSelectors).each(function () {
          wc_lomi_admin.fieldRow($(this)).show();
        });
      }

      if (!this.advancedVisible) {
        this.hideAdvancedFields();
      }
    },

    hideAdvancedFields: function () {
      $(".wc-lomi-advanced-field").each(function () {
        wc_lomi_admin.fieldRow($(this)).hide();
      });
      $(".wc-lomi-advanced-field-row").hide();
    },

    showAdvancedFields: function () {
      $(".wc-lomi-advanced-field").each(function () {
        wc_lomi_admin.fieldRow($(this)).show();
      });
      $(".wc-lomi-advanced-field-row").show();
    },

    toggleAdvancedFields: function () {
      this.advancedVisible = !this.advancedVisible;
      if (this.advancedVisible) {
        this.showAdvancedFields();
      } else {
        this.hideAdvancedFields();
      }

      var $toggle = $(".wc-lomi-advanced-toggle");
      $toggle.attr("aria-expanded", this.advancedVisible ? "true" : "false");
      $toggle.text(
        this.advancedVisible
          ? wc_lomi_admin_params.hide_advanced || "Hide advanced settings"
          : wc_lomi_admin_params.show_advanced || "Show advanced settings",
      );
    },

    copyWebhookUrl: function (event) {
      var $button =
        event && event.currentTarget
          ? $(event.currentTarget)
          : $(".wc-lomi-copy-webhook-url").first();
      var $input = $(".wc-lomi-webhook-url-input");
      var $feedback = $(".wc-lomi-copy-feedback");
      var url = $button.attr("data-copy-url") || $input.val() || "";
      var params = wc_lomi_admin_params !== void 0 ? wc_lomi_admin_params : {};

      var onSuccess = function () {
        $feedback
          .css("color", "#007017")
          .text(params.copy_success || "Copied!")
          .show();
        window.setTimeout(function () {
          $feedback.fadeOut(200, function () {
            $(this).text("").show();
          });
        }, 2000);
      };

      var onFail = function () {
        $input.trigger("focus").trigger("select");
        $feedback
          .css("color", "#b32d2e")
          .text(
            params.copy_failed ||
              "Could not copy — select the URL and press Ctrl+C (or Cmd+C).",
          )
          .show();
      };

      var legacyCopy = function (text) {
        var textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.setAttribute("readonly", "");
        textarea.style.position = "fixed";
        textarea.style.opacity = "0";
        textarea.style.left = "-9999px";
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        textarea.setSelectionRange(0, text.length);
        var copied = false;
        try {
          copied = document.execCommand("copy");
        } catch (err) {
          copied = false;
        }
        document.body.removeChild(textarea);
        return copied;
      };

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard
          .writeText(url)
          .then(onSuccess)
          .catch(function () {
            if (legacyCopy(url)) {
              onSuccess();
            } else {
              onFail();
            }
          });
        return;
      }

      if (legacyCopy(url)) {
        onSuccess();
      } else {
        onFail();
      }
    },

    init: function () {
      var $testMode = $("#woocommerce_lomi_testmode");

      $(document.body).on("change", "#woocommerce_lomi_testmode", function () {
        wc_lomi_admin.toggleModeFields($(this).is(":checked"));
      });

      if ($testMode.length) {
        wc_lomi_admin.toggleModeFields($testMode.is(":checked"));
      } else {
        wc_lomi_admin.hideAdvancedFields();
      }

      $(document.body).on(
        "click",
        ".wc-lomi-advanced-toggle",
        function (event) {
          event.preventDefault();
          wc_lomi_admin.toggleAdvancedFields();
        },
      );

      $(document.body).on(
        "click",
        ".wc-lomi-copy-webhook-url",
        function (event) {
          event.preventDefault();
          wc_lomi_admin.copyWebhookUrl(event);
        },
      );

      $(".wc-lomi-metadata")
        .change(function () {
          if ($(this).is(":checked")) {
            $(
              ".wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products",
            )
              .closest("tr")
              .show();
          } else {
            $(
              ".wc-lomi-meta-order-id, .wc-lomi-meta-name, .wc-lomi-meta-email, .wc-lomi-meta-phone, .wc-lomi-meta-billing-address, .wc-lomi-meta-shipping-address, .wc-lomi-meta-products",
            )
              .closest("tr")
              .hide();
          }
        })
        .change();

      $(
        "#woocommerce_lomi_test_secret_key, #woocommerce_lomi_live_secret_key, #woocommerce_lomi_test_webhook_secret, #woocommerce_lomi_live_webhook_secret",
      ).after(
        '<button type="button" class="wc-lomi-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>',
      );

      $(".wc-lomi-toggle-secret").on("click", function (event) {
        event.preventDefault();

        var $dashicon = $(this).closest("button").find(".dashicons");
        var $input = $(this).closest("tr").find(".input-text");
        var inputType = $input.attr("type");

        if ("text" === inputType) {
          $input.attr("type", "password");
          $dashicon.removeClass("dashicons-hidden");
          $dashicon.addClass("dashicons-visibility");
        } else {
          $input.attr("type", "text");
          $dashicon.removeClass("dashicons-visibility");
          $dashicon.addClass("dashicons-hidden");
        }
      });
    },
  };

  wc_lomi_admin.init();
});
