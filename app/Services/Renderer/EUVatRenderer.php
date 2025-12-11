<?php

namespace Webmakerr\App\Services\Renderer;


use Webmakerr\Api\StoreSettings;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Modules\Tax\TaxModule;
use Webmakerr\App\Services\Localization\LocalizationManager;

class EUVatRenderer
{
    protected $isEUCountry = false;
    protected $taxSettings = [];
    protected $taxApplicableCountry = '';

    public function __construct($isEUCountry = false, $taxApplicableCountry = '')
    {
        $this->taxSettings = (new TaxModule())->getSettings();
        $this->isEUCountry = $isEUCountry;
        $this->taxApplicableCountry = $taxApplicableCountry;
    }

    public function render($cart)
    {
        $euVatEnabled = Arr::get($this->taxSettings, 'eu_vat_settings.require_vat_number', 'no');

        if ($this->isEUCountry && $euVatEnabled === 'yes') {
                $vatNumber = Arr::get($cart->checkout_data, 'tax_data.vat_number', '');
                ?>
                <div class="fct_checkout_form_section" role="region" aria-labelledby="eu-vat-heading">
                    <div class="fct_form_section_header">
                        <h4 id="eu-vat-heading" class="fct_form_section_header_label">
                            <?php echo esc_html__('EU VAT', 'webmakerr-cart'); ?>
                        </h4>
                    </div>
                    <div class="fct_form_section_body">
                        <div class="fct_tax_field">
                            <div data-fluent-cart-checkout-page-form-input-wrapper class="fct_tax_input_wrapper"
                                 id="fct_billing_tax_id_wrapper"
                            >
                                <label for="fct_billing_tax_id" class="sr-only">
                                    <?php echo esc_html__('VAT Number', 'webmakerr-cart'); ?>
                                </label>

                                <input
                                    data-fluent-cart-checkout-page-tax-id
                                    type="text"
                                    name="fct_billing_tax_id"
                                    autocomplete="tax-id"
                                    placeholder="<?php echo esc_html__('Enter Tax ID', 'webmakerr-cart'); ?>"
                                    id="fct_billing_tax_id"
                                    value="<?php echo esc_attr($vatNumber) ?? ''; ?>"
                                    aria-describedby="fct_billing_tax_id_error"
                                />

                                <button
                                    type="button"
                                    data-fluent-cart-checkout-page-tax-apply-btn
                                    aria-label="<?php echo esc_attr__('Apply VAT number', 'webmakerr-cart'); ?>"
                                >
                                    <?php echo esc_html__('Apply', 'webmakerr-cart'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                    <span
                        data-fluent-cart-checkout-page-tax-loading
                        class="fct_tax_loading"
                        role="status"
                        aria-live="polite"
                        aria-label="<?php echo esc_attr__('Validating VAT number', 'webmakerr-cart'); ?>">
                    </span>

                    <span
                        data-fluent-cart-checkout-page-form-error
                        class="fct_form_error"
                        id="fct_billing_tax_id_error"
                        role="alert"
                        aria-live="assertive">
                    </span>

                   <?php $this->renderValidNote($cart->checkout_data); ?>
              </div>
            <?php
        } else {
            return '';
        }

    }

    public function renderValidNote($checkoutData)
    {
        $isValid = Arr::get($checkoutData, 'tax_data.valid', false);
        $name = Arr::get($checkoutData, 'tax_data.name', '');
        $taxTotal = Arr::get($checkoutData, 'tax_data.tax_total', 0);

        ?>

        <div
            class="fct_vat_valid_note <?php echo !$isValid ? 'is-hidden' : ''; ?>"
            data-fluent-cart-tax-valid-note-wrapper role="status"
            aria-live="polite"
            <?php echo $isValid ? '' : 'aria-hidden="true"'; ?>
        >
                <span data-fluent-cart-tax-valid-note >
                    <span class="sr-only">
                       <?php echo esc_html__('Valid VAT number for:', 'webmakerr-cart'); ?>
                    </span>
                    <?php echo esc_html($name); ?>

                    <?php if ($taxTotal != 0): ?>
                        <span style="color: #ffa500;">
                            <?php echo esc_html__('(Reverse Charge not applied)', 'webmakerr-cart'); ?>
                        </span>
                    <?php endif; ?>
                </span>

            <button
                type="button"
                data-fluent-cart-tax-remove-btn
                aria-label="<?php echo esc_attr__('Remove VAT number', 'webmakerr-cart'); ?>"
            >
                <?php echo esc_html__('Remove', 'webmakerr-cart'); ?>
            </button>
        </div>

        <?php
    }
}