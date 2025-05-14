<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\PaymentDrivers;

use App\Models\Payment;
use App\Models\SystemLog;
use App\Models\GatewayType;
use App\Models\PaymentHash;
use App\Models\ClientGatewayToken;
use App\Jobs\Mail\PaymentFailedMailer;
use App\PaymentDrivers\Authorize\AuthorizeACH;
use net\authorize\api\constants\ANetEnvironment;
use App\PaymentDrivers\Authorize\AuthorizeCustomer;
use App\PaymentDrivers\Authorize\RefundTransaction;
use App\Http\Requests\Payments\PaymentWebhookRequest;
use App\PaymentDrivers\Authorize\AuthorizeCreditCard;
use App\PaymentDrivers\Authorize\AuthorizePaymentMethod;
use net\authorize\api\contract\v1\GetMerchantDetailsRequest;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller\GetMerchantDetailsController;

/**
 * Class BaseDriver.
 */
class AuthorizePaymentDriver extends BaseDriver
{
    public $merchant_authentication;

    public $token_billing = true;

    public $can_authorise_credit_card = true;

    public static $methods = [
        GatewayType::CREDIT_CARD => AuthorizeCreditCard::class,
        GatewayType::BANK_TRANSFER => AuthorizeACH::class,
    ];

    public const SYSTEM_LOG_TYPE = SystemLog::TYPE_AUTHORIZE;

    public function setPaymentMethod($payment_method_id)
    {
        $class = self::$methods[$payment_method_id];

        $this->payment_method = new $class($this);

        return $this;
    }

    /**
     * Returns the gateway types.
     */
    public function gatewayTypes(): array
    {
        $types = [];

        $types[] = GatewayType::CREDIT_CARD;
        $types[] = GatewayType::BANK_TRANSFER;

        return $types;
    }

    public function getClientRequiredFields(): array
    {
        $data = [
            // ['name' => 'client_name', 'label' => ctrans('texts.name'), 'type' => 'text', 'validation' => 'required|min:2'],
            ['name' => 'client_phone', 'label' => ctrans('texts.phone'), 'type' => 'text', 'validation' => 'required'],
            ['name' => 'contact_email', 'label' => ctrans('texts.email'), 'type' => 'text', 'validation' => 'required|email:rfc'],
            ['name' => 'client_address_line_1', 'label' => ctrans('texts.address1'), 'type' => 'text', 'validation' => 'required'],
            ['name' => 'client_city', 'label' => ctrans('texts.city'), 'type' => 'text', 'validation' => 'required'],
            ['name' => 'client_state', 'label' => ctrans('texts.state'), 'type' => 'text', 'validation' => 'required'],
            ['name' => 'client_postal_code', 'label' => ctrans('texts.postal_code'), 'type' => 'text', 'validation' => 'required'],
            ['name' => 'client_country_id', 'label' => ctrans('texts.country'), 'type' => 'select', 'validation' => 'required'],
        ];

        $fields = [];

        if ($this->company_gateway->require_shipping_address) {
            $fields[] = ['name' => 'client_shipping_address_line_1', 'label' => ctrans('texts.shipping_address1'), 'type' => 'text', 'validation' => 'required'];
            $fields[] = ['name' => 'client_shipping_city', 'label' => ctrans('texts.shipping_city'), 'type' => 'text', 'validation' => 'required'];
            $fields[] = ['name' => 'client_shipping_state', 'label' => ctrans('texts.shipping_state'), 'type' => 'text', 'validation' => 'required'];
            $fields[] = ['name' => 'client_shipping_postal_code', 'label' => ctrans('texts.shipping_postal_code'), 'type' => 'text', 'validation' => 'required'];
            $fields[] = ['name' => 'client_shipping_country_id', 'label' => ctrans('texts.shipping_country'), 'type' => 'text', 'validation' => 'required'];
        }

        if ($this->company_gateway->require_custom_value1) {
            $fields[] = ['name' => 'client_custom_value1', 'label' => $this->helpers->makeCustomField($this->client->company->custom_fields, 'client1'), 'type' => 'text', 'validation' => 'required'];
        }


        if ($this->company_gateway->require_custom_value2) {
            $fields[] = ['name' => 'client_custom_value2', 'label' => $this->helpers->makeCustomField($this->client->company->custom_fields, 'client2'), 'type' => 'text', 'validation' => 'required'];
        }


        if ($this->company_gateway->require_custom_value3) {
            $fields[] = ['name' => 'client_custom_value3', 'label' => $this->helpers->makeCustomField($this->client->company->custom_fields, 'client3'), 'type' => 'text', 'validation' => 'required'];
        }

        if ($this->company_gateway->require_custom_value4) {
            $fields[] = ['name' => 'client_custom_value4', 'label' => $this->helpers->makeCustomField($this->client->company->custom_fields, 'client4'), 'type' => 'text', 'validation' => 'required'];
        }

        return array_merge($data, $fields);
    }

    public function authorizeView($payment_method)
    {
        return (new AuthorizePaymentMethod($this))->authorizeView();
    }

    public function authorizeResponse($request)
    {
        return (new AuthorizePaymentMethod($this))->authorizeResponseView($request);
    }

    public function processPaymentView($data)
    {
        return $this->payment_method->processPaymentView($data);
    }

    public function processPaymentResponse($request)
    {
        return $this->payment_method->processPaymentResponse($request);
    }

    public function refund(Payment $payment, $refund_amount, $return_client_response = false)
    {
        return (new RefundTransaction($this))->refundTransaction($payment, $refund_amount);
    }

    public function tokenBilling(ClientGatewayToken $cgt, PaymentHash $payment_hash)
    {
        $this->init();

        //Universal token billing.
        $this->setPaymentMethod($cgt->gateway_type_id);

        return $this->payment_method->tokenBilling($cgt, $payment_hash);
    }

    public function init()
    {
        error_reporting(E_ALL & ~E_DEPRECATED);

        $this->merchant_authentication = new MerchantAuthenticationType();
        $this->merchant_authentication->setName($this->company_gateway->getConfigField('apiLoginId'));
        $this->merchant_authentication->setTransactionKey($this->company_gateway->getConfigField('transactionKey'));

        return $this;
    }

    public function getPublicClientKey()
    {
        $request = new GetMerchantDetailsRequest();
        $request->setMerchantAuthentication($this->merchant_authentication);

        $controller = new GetMerchantDetailsController($request);
        $response = $controller->executeWithApiResponse($this->mode());

        return $response->getPublicClientKey();
    }

    public function mode()
    {
        if ($this->company_gateway->getConfigField('testMode')) {
            return  ANetEnvironment::SANDBOX;
        }

        return $env = ANetEnvironment::PRODUCTION;
    }

    public function validationMode()
    {
        return $this->company_gateway->getConfigField('testMode') ? 'testMode' : 'liveMode';
    }

    public function findClientGatewayRecord(): ?ClientGatewayToken
    {
        return ClientGatewayToken::where('client_id', $this->client->id)
                                 ->where('company_gateway_id', $this->company_gateway->id)
                                 ->first();
    }

    /**
     * Detach payment method from Authorize.net.
     *
     * @param ClientGatewayToken $token
     * @return void
     */
    public function detach(ClientGatewayToken $token)
    {
        return (new AuthorizePaymentMethod($this))->deletePaymentProfile($token->gateway_customer_reference, $token->token);
    }

    public function import()
    {
        $this->init();

        nlog("starting import auth.net");

        return (new AuthorizeCustomer($this))->importCustomers();
    }

    public function importCustomers()
    {
        return $this->import();
    }

    public function auth(): string
    {
        return $this->init()->getPublicClientKey() ? 'ok' : 'error';
    }

    public function processWebhookRequest(PaymentWebhookRequest $request)
    {

        $payload = file_get_contents('php://input');
        $headers = getallheaders();

        $signatureKey = $this->company_gateway->getConfigField('signatureKey');

        function isValidSignature($payload, $headers, $signatureKey)
        {
            // Normalize headers to uppercase for consistent lookup
            $normalizedHeaders = array_change_key_case($headers, CASE_UPPER);
            
            if (!isset($normalizedHeaders['X-ANET-SIGNATURE'])) {
                return false;
            }

            $receivedSignature = $normalizedHeaders['X-ANET-SIGNATURE'];
            
            // Remove 'sha512=' prefix if it exists
            $receivedHash = str_replace('sha512=', '', $receivedSignature);

            // Make sure signatureKey is a valid hex string and convert to binary
            if (!ctype_xdigit($signatureKey)) {
                return false;
            }

            // Calculate HMAC exactly as Authorize.net does
            $expectedHash = strtoupper(hash_hmac('sha512', $payload, $signatureKey));
                        
            return hash_equals($receivedHash, $expectedHash);
        }

        if (!isValidSignature($payload, $headers, $signatureKey)) {
            return response()->noContent();
        }

        $data = json_decode($payload, true);

        // Check event type
        $eventType = $data['eventType'] ?? null;
        $transactionId = $data['payload']['id'] ?? 'unknown';

        switch ($eventType) {
            case 'net.authorize.payment.void.created':
                $this->voidPayment($data);
                break;
                
            default:
                // Other webhook event types can be handled here
                nlog("ℹ️ Unhandled event type: $eventType");
                break;
        }

        return response()->noContent();

    }

// array (
//   'notificationId' => '2ebb25fa-a814-4c53-8e1c-013423214f00',
//   'eventType' => 'net.authorize.payment.void.created',
//   'eventDate' => '2025-05-14T04:09:10.2193293Z',
//   'webhookId' => '95c72ffd-635d-43a7-97b6-8096078cb11a',
//   'payload' => 
//   array (
//     'responseCode' => 1,
//     'avsResponse' => 'P',
//     'authAmount' => 13.85,
//     'merchantReferenceId' => 'ref1747192172',
//     'invoiceNumber' => '0082',
//     'entityName' => 'transaction',
//     'id' => '80040995616',
//   ),
// )  
    private function voidPayment($data)
    {

        $payment = Payment::withTrashed()
                        ->where('company_id', $this->company_gateway->company_id)        
                        ->where('transaction_reference', $data['payload']['id'])
                        ->first();

        if($payment){
            
            if($payment->status_id != Payment::STATUS_COMPLETED)
                return;

            $payment->service()->deletePayment();
            $payment->status_id = Payment::STATUS_FAILED;
            $payment->save();

            $payment_hash = PaymentHash::query()->where('payment_id', $payment->id)->first();

            if ($payment_hash) {
                $error = ctrans('texts.client_payment_failure_body', [
                    'invoice' => implode(',', $payment->invoices->pluck('number')->toArray()),
                    'amount' => array_sum(array_column($payment_hash->invoices(), 'amount')) + $payment_hash->fee_total, ]);
            } else {
                $error = 'Payment for '.$payment->client->present()->name()." for {$payment->amount} failed";
            }

            
            PaymentFailedMailer::dispatch(
                $payment_hash,
                $payment->client->company,
                $payment->client,
                $error
            );

        }
    }
}
