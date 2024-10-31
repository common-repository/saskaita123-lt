<?php

use S123\Includes\Base\S123_BaseController;

if (!defined('ABSPATH')) exit;

$vats = $this->s123_getAvailableTaxRates();
$apiVats = $this->apiRequest->s123_makeGetRequest($this->apiRequest->getApiUrl('vats'))['body'];
$orderStatuses = wc_get_order_statuses();
$mailer = WC()->mailer();
$nonce = wp_create_nonce('s123_security');
?>

<div class="tab-container">
    <div>
        <h3><?php echo __("Invoice123 Invoice Settings", "s123-invoices") ?></h3>
    </div>

    <div class="info box">
        <?php echo __("All basic woocommerce settings are set after installing the woocommerce module on app.invoice123.com", "s123-invoices") ?>
    </div>

    <form method="post" id="invoiceSettingsSubmitForm">
        <div class="s123-form__group" style="display: flex; flex-direction: column;">
            <label class="i123-font-weight-midbold"
                   for="orderStatuses"><?php echo __("Select when invoice will be generated if order status changes (default: Completed)", "s123-invoices") ?></label>
            <select id="orderStatuses" name="use_order_status" class="s123-form__control" style="max-width: 250px;">
                <?php foreach ($orderStatuses as $key => $status) : ?>
                    <option
                            value="<?php echo esc_attr(str_replace('wc-', '', $key)) ?>"
                        <?php echo esc_attr(str_replace('wc-', '', $key)) === esc_attr($this->s123_get_option("use_order_status")) ||
                        (esc_attr(str_replace('wc-', '', $key)) === 'completed' && esc_attr($this->s123_get_option("use_order_status")) === '') ? 'selected' : '' ?>
                    >
                        <?php echo esc_html($status); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($vats && count($vats) > 0) : ?>
            <span class="i123-font-weight-midbold"><?php echo __("Link woocommerce VAT types with app.invoice123.com VAT types if you use VAT.", "s123-invoices") ?></span>
            <div class="s123_vats margin-top">
                <?php foreach ($vats as $key => $vat) : ?>
                    <div>
                        <label style="width: 70px"
                               for="vats<?php echo $key ?>"><?php echo $vat->tax_rate_country . ' ' . $vat->tax_rate; ?></label>
                        <select id="vats<?php echo $key ?>" name="api_vats[]" class="s123-form__control">
                            <option hidden disabled
                                    selected><?php echo __("-- Select option --", "s123-invoices") ?></option>
                            <?php if ($apiVats && $apiVats["data"]) : ?>
                                <?php foreach ($apiVats["data"] as $datum) : ?>
                                    <option
                                            value="<?php echo esc_attr($datum["id"]) . '-' . esc_attr($vat->tax_rate_id); ?>"
                                        <?php echo esc_attr($vat->s123_tax_id) === esc_attr($datum["id"]) ? 'selected' : '' ?>
                                    >
                                        <?php echo esc_html($datum["vat_code"] . ' - ' . substr($datum['tariff'], 2) / 100 . '%'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h3 class="mb-0"><?php echo __("Extra settings", "s123-invoices") ?></h3>

        <div class="s123-form__group margin-top">
            <input
                    type="checkbox"
                    name="default_advanced_invoices"
                    id="default_advanced_invoices"
                <?php echo $this->s123_get_option("default_advanced_invoices") === true ? 'checked' : '' ?>
                    style="margin-top: 2px;"
            >
            <label for="default_advanced_invoices" class="mx-2">
                <?php echo __("By default create advanced invoices", "s123-invoices") ?>
            </label>
        </div>

        <div class="s123-form__group margin-top">
            <input
                    type="checkbox"
                    name="create_invoice_on_order_creation"
                    id="create_invoice_on_order_creation"
                <?php echo $this->s123_get_option("create_invoice_on_order_creation") === true ? 'checked' : '' ?>
                    style="margin-top: 2px;"
            >
            <label for="create_invoice_on_order_creation" class="mx-2">
                <?php echo __("Create an invoice upon order creation", "s123-invoices") ?>
            </label>
        </div>

        <div class="s123-form__group margin-top">
            <input
                    type="checkbox"
                    name="create_unpaid_invoices"
                    id="create_unpaid_invoices"
                <?php echo $this->s123_get_option("create_unpaid_invoices") === true ? 'checked' : '' ?>
                    style="margin-top: 2px;"
            >
            <label for="create_unpaid_invoices" class="mx-2">
                <?php echo __("By default create unpaid invoices", "s123-invoices") ?>
                <span id="create_unpaid_invoices_tooltip" class="i123-info-icon">
                    <?php
                    echo file_get_contents($this->plugin_path . 'admin/partials/components/icons/info.svg');
                    ?>
                </span>
            </label>
        </div>

        <div class="s123-form__group margin-top">
            <input
                    type="checkbox"
                    name="use_custom_inputs"
                    id="use_custom_inputs"
                <?php echo $this->s123_get_option("use_custom_inputs") === true ? 'checked' : '' ?>
                    style="margin-top: 2px;"
            >
            <label for="use_custom_inputs" class="mx-2">
                <?php echo __("Add custom inputs to checkout for clients to provide company requisites", "s123-invoices") ?>
                <span id="custom_inputs_tooltip" class="i123-info-icon">
                    <?php
                    echo file_get_contents($this->plugin_path . 'admin/partials/components/icons/info.svg');
                    ?>
                </span>
            </label>
        </div>

        <input type="hidden" name="action" value="s123_submit_invoice_settings">
        <input type="hidden" name="s123_security" value="<?php echo esc_attr($nonce); ?>">
        <button type="submit" class="s123-btn s123-btn__primary margin-top"><?php echo __("Save", "s123-invoices") ?></button>
    </form>
</div>

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
  tippy('#custom_inputs_tooltip', {
    content: <?php echo json_encode(__("This is used to create invoices for companies", "s123-invoices")); ?>
  });

  tippy('#create_unpaid_invoices_tooltip', {
    content: <?php echo json_encode(__("Created invoices will be without payment", "s123-invoices")); ?>
  });
</script>