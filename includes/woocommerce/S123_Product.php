<?php
/**
 * @link https://www.invoice123.com
 * @package Saskaita123Plugin
 *
 * Class Description: Detect when user has purchased product
 */

namespace S123\Includes\Woocommerce;

use S123\Includes\Base\S123_BaseController;
use S123\Includes\Base\S123_Options;
use S123\Includes\Requests\S123_ApiRequest;

if (!defined('ABSPATH')) exit;

class S123_Product
{
    use S123_Options;

    /**
     * API request object
     *
     */
    private $apiRequest;

    /**
     * Order status when to generate invoice
     *
     */
    private $orderStatus;

    public function __construct(S123_ApiRequest $api = null)
    {
        $this->apiRequest = $api ?: new S123_ApiRequest();
        $this->orderStatus = $this->s123_get_option('use_order_status');
    }

    public function s123_register()
    {
        add_action('woocommerce_order_status_changed', array($this, 's123_createInvoice'), 99, 3);
        add_action('woocommerce_checkout_order_processed', array($this, 's123_createInvoiceOnOrderProcessed'), 1);
        add_action('invoice123_payment_success', array($this, 'i123_process_success_payment'), 1);
    }

    public function i123_process_success_payment($order_id)
    {
        $order = wc_get_order($order_id);

        $invoiceTypeMeta = get_post_meta($order->get_id(), '_i123_invoice_type', true);

        if ($invoiceTypeMeta === S123_BaseController::I123_ADVANCED_INVOICE) {
            $this->convertInvoice($order);
        }
    }

    public function s123_createInvoiceOnOrderProcessed($order_id)
    {
        if ($this->s123_get_option('create_invoice_on_order_creation') !== true) {
            return null;
        }

        $order = wc_get_order($order_id);

        $can = apply_filters('i123_can_create_invoice_on_order_creation', true, $order->get_payment_method());

        if (!$can) {
            return null;
        }

        $order->update_meta_data('_invoice_created_on_process', true);
        $order->save();

        return $this->processInvoiceCreation($order);
    }

    /*
    * Create invoices after order status change
    */
    public function s123_createInvoice($order_id, $old_status, $new_status)
    {
        if (!$order_id) {
            return null;
        }

        $order = wc_get_order($order_id);

        // skip if invoice was created on order creation for the first time
        if ((boolean)$order->get_meta('_invoice_created_on_process') === true) {
            $order->update_meta_data('_invoice_created_on_process', false);
            $order->save();
            return null;
        }

        if ($this->checkIfToGenerateInvoiceByStatus($new_status)) {
            return $this->processInvoiceCreation($order);
        } else {
            return null;
        }
    }

    private function processInvoiceCreation($order)
    {
        $invoiceObj = new S123_Invoice($order, $this->apiRequest);

        $invoice = $invoiceObj->s123_buildInvoice();

        if ($invoice === null) {
            return null;
        }

        // if: create invoice
        // else if: update invoice
        if (!$order->get_meta('_invoice_generated')) {
            $response = $this->apiRequest->s123_makeRequest($this->apiRequest->getApiUrl('invoice'), $invoice, 'POST');

            // Flag the action as done
            $this->processStoreResponse($order, $response);
        } else if ($order->get_meta('_generated_invoice_id')) {
            $invoiceId = $order->get_meta('_generated_invoice_id');

            $response = $this->apiRequest->s123_makeRequest($this->apiRequest->getApiUrl('invoice') . '/' . $invoiceId, $invoice, 'PATCH');

            $this->processUpdateResponse($order, $invoice, $response);
        }

        return $invoice;
    }

    private function processStoreResponse($order, $response)
    {
        // Flag the action as done
        if ($response['code'] === 200) {
            $order->update_meta_data('_invoice_generated', true);
            // save generated id for updating invoice
            $order->update_meta_data('_generated_invoice_id', $response['body']['data']['id']);
            $order->add_order_note(__('Invoice was generated at app.invoice123.com', 's123-invoices'), false, true);
            $order->save();
        } else {
            $message = $this->errorMessage($response);
            $order->add_order_note($message);
        }
    }

    private function convertInvoice($order)
    {
        $invoiceId = $order->get_meta('_generated_invoice_id');

        if (!$invoiceId) {
            return null;
        }

        $response = $this->apiRequest->s123_makeRequest($this->apiRequest->getApiUrl('convert') . '/' . $invoiceId, [
            'payments' => [
                [
                    'total' => number_format($order->get_total(), absint(get_option('woocommerce_price_num_decimals', 2))),
                    'date' => $order->get_date_paid()->date('Y-m-d'),
                    'type' => 'transfer',
                ]
            ]
        ], 'PATCH');

        if ($response['code'] === 200) {
            $order->add_order_note(__('Invoice was converted at app.invoice123.com', 's123-invoices'), false, true);
            update_post_meta($order->get_id(), '_i123_invoice_type', S123_BaseController::I123_SIMPLE_INVOICE);
            $order->save();
        } else {
            $message = $this->errorMessage($response);
            $order->add_order_note($message);
        }
    }

    public function processUpdateResponse($order, $invoice, $response)
    {
        if ($response['code'] === 200) {
            $order->add_order_note(__('Invoice was updated at app.invoice123.com', 's123-invoices'), false, true);
            $order->save();
        } elseif ($response['code'] === 404) {
            // if invoice not found while trying to update it, create a new one
            $order->delete_meta_data('_generated_invoice_id');
            $order->save();

            $response = $this->apiRequest->s123_makeRequest($this->apiRequest->getApiUrl('invoice'), $invoice, 'POST');

            $this->processStoreResponse($order, $response);
        } else {
            $message = $this->errorMessage($response);
            $order->add_order_note($message);
        }
    }

    /*
    * Format error message for order notes
    */
    private function errorMessage($response): string
    {
        $string = __('If you see this message, your invoice has not been generated, you can send this message to Invoice123 support', 's123-invoices') . '. ';

        if (isset($response['body']['error'])) {
            $errorMessage = $response['body']['error'];
            $string .= 'Error message: ' . $errorMessage['message'] . ' ';

            if ($errorMessage['errors']) {
                foreach ($errorMessage['errors'] as $key => $error) {
                    $string .= $key . ' => ' . json_encode($error, JSON_UNESCAPED_UNICODE) . ' ';
                }
            }
        } else if (isset($response['body']['data'])) {
            $string .= 'Error message: ' . $response['body']['data'];
        } else {
            $string .= 'Error code: ' . $response['code'] . '.';

            if (isset($response['body']['message'])) {
                $string .= '<br> Error message: ' . $response['body']['message'];
            }
        }

        return $string;
    }

    private function checkIfToGenerateInvoiceByStatus($newStatus): bool
    {
        if ($newStatus === $this->orderStatus || $newStatus === 'completed' && empty($this->orderStatus)) {
            return true;
        }

        return false;
    }
}