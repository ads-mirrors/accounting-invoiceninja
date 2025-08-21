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

namespace App\Http\Controllers;

use App\Jobs\SES\SESWebhook;
use App\Jobs\PostMark\ProcessPostmarkWebhook;
use App\Libraries\MultiDB;
use App\Services\InboundMail\InboundMail;
use App\Services\InboundMail\InboundMailEngine;
use App\Utils\TempFile;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class SNSController.
 * 
 * Handles Amazon SNS webhook notifications that contain SES email event data.
 * SNS acts as an intermediary between SES and your application.
 */
class SNSController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * Handle SNS webhook notifications
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        try {
            // Get the raw request body for SNS signature verification
            $payload = $request->getContent();
            $headers = $request->headers->all();
            
            Log::info('SNS Webhook received', [
                'headers' => $headers,
                'payload_size' => strlen($payload)
            ]);

            // Parse the SNS payload
            $snsData = json_decode($payload, true);
            
            if (!$snsData) {
                Log::error('SNS Webhook: Invalid JSON payload');
                return response()->json(['error' => 'Invalid JSON payload'], 400);
            }

            // Get SNS message type from headers (AWS SNS specific)
            $snsMessageType = $headers['x-amz-sns-message-type'][0] ?? null;
            
            if (!$snsMessageType) {
                Log::error('SNS Webhook: Missing x-amz-sns-message-type header');
                return response()->json(['error' => 'Missing SNS message type'], 400);
            }

            // Handle SNS subscription confirmation
            if ($snsMessageType === 'SubscriptionConfirmation') {
                return $this->handleSubscriptionConfirmation($snsData);
            }

            // Handle SNS notification (contains SES data)
            if ($snsMessageType === 'Notification') {
                return $this->handleSESNotification($snsData);
            }

            // Handle unsubscribe confirmation
            if ($snsMessageType === 'UnsubscribeConfirmation') {
                Log::info('SNS Unsubscribe confirmation received', ['topic_arn' => $snsData['TopicArn'] ?? 'unknown']);
                return response()->json(['status' => 'unsubscribe_confirmed']);
            }

            Log::warning('SNS Webhook: Unknown message type', ['type' => $snsMessageType]);
            return response()->json(['error' => 'Unknown message type'], 400);

        } catch (\Exception $e) {
            Log::error('SNS Webhook: Error processing request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle SNS subscription confirmation
     * 
     * @param array $snsData
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleSubscriptionConfirmation(array $snsData)
    {
        $subscribeUrl = $snsData['SubscribeURL'] ?? null;
        
        if (!$subscribeUrl) {
            Log::error('SNS Subscription confirmation: Missing SubscribeURL');
            return response()->json(['error' => 'Missing SubscribeURL'], 400);
        }

        Log::info('SNS Subscription confirmation received', [
            'topic_arn' => $snsData['TopicArn'] ?? 'unknown',
            'subscribe_url' => $subscribeUrl
        ]);

        // You can optionally make an HTTP request to confirm the subscription
        // This is required by AWS to complete the SNS subscription setup
        try {
            $response = file_get_contents($subscribeUrl);
            Log::info('SNS Subscription confirmed', ['response' => $response]);
        } catch (\Exception $e) {
            Log::error('SNS Subscription confirmation failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'subscription_confirmed']);
    }

    /**
     * Handle SES notification from SNS
     * 
     * @param array $snsData
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleSESNotification(array $snsData)
    {
        $message = $snsData['Message'] ?? null;
        
        if (!$message) {
            Log::error('SNS Notification: Missing Message content');
            return response()->json(['error' => 'Missing Message content'], 400);
        }

        // Parse the SES message (it's JSON encoded as a string)
        $sesData = json_decode($message, true);
        
        if (!$sesData) {
            Log::error('SNS Notification: Invalid SES message format');
            return response()->json(['error' => 'Invalid SES message format'], 400);
        }

        Log::info('SNS Notification: Processing SES data', [
            'notification_type' => $sesData['notificationType'] ?? $sesData['eventType'] ?? 'unknown',
            'message_id' => $sesData['mail']['messageId'] ?? 'unknown'
        ]);

        // Extract company key from SES data
        $companyKey = $this->extractCompanyKeyFromSES($sesData);
        
        if (!$companyKey) {
            Log::warning('SNS Notification: No company key found in SES data', [
                'ses_data' => $sesData
            ]);
            return response()->json(['error' => 'No company key found'], 400);
        }

        // Dispatch the SES webhook job for processing
        try {
            SESWebhook::dispatch($sesData);
            
            Log::info('SNS Notification: SES webhook job dispatched successfully', [
                'company_key' => $companyKey,
                'message_id' => $sesData['mail']['messageId'] ?? 'unknown'
            ]);
            
            return response()->json(['status' => 'webhook_processed']);
            
        } catch (\Exception $e) {
            Log::error('SNS Notification: Failed to dispatch SES webhook job', [
                'error' => $e->getMessage(),
                'company_key' => $companyKey
            ]);
            
            return response()->json(['error' => 'Failed to process webhook'], 500);
        }
    }

    /**
     * Extract company key from SES data
     * 
     * @param array $sesData
     * @return string|null
     */
    private function extractCompanyKeyFromSES(array $sesData): ?string
    {
        // Check various possible locations for company key in SES data
        
        // Check mail tags
        if (isset($sesData['mail']['tags']['company_key'])) {
            Log::info('SNS: Found company key in mail tags', ['value' => $sesData['mail']['tags']['company_key']]);
            return $sesData['mail']['tags']['company_key'];
        }

        // Check custom headers - specifically X-Tag which contains the company key
        if (isset($sesData['mail']['headers'])) {
            Log::info('SNS: Checking mail headers for X-Tag', [
                'headers_count' => count($sesData['mail']['headers']),
                'headers' => $sesData['mail']['headers']
            ]);
            
            foreach ($sesData['mail']['headers'] as $header) {
                if (isset($header['name']) && $header['name'] === 'X-Tag' && isset($header['value'])) {
                    Log::info('SNS: Found X-Tag header', ['value' => $header['value']]);
                    return $header['value'];
                }
            }
            
            Log::warning('SNS: X-Tag header not found in mail headers');
        }

        // Check if company key is in the main SES data
        if (isset($sesData['company_key'])) {
            Log::info('SNS: Found company key in main SES data', ['value' => $sesData['company_key']]);
            return $sesData['company_key'];
        }

        // Check bounce data
        if (isset($sesData['bounce']) && isset($sesData['bounce']['tags']['company_key'])) {
            Log::info('SNS: Found company key in bounce tags', ['value' => $sesData['bounce']['tags']['company_key']]);
            return $sesData['bounce']['tags']['company_key'];
        }

        // Check complaint data
        if (isset($sesData['complaint']) && isset($sesData['complaint']['tags']['company_key'])) {
            Log::info('SNS: Found company key in complaint tags', ['value' => $sesData['complaint']['tags']['company_key']]);
            return $sesData['complaint']['tags']['company_key'];
        }

        // Check delivery data
        if (isset($sesData['delivery']) && isset($sesData['delivery']['tags']['company_key'])) {
            Log::info('SNS: Found company key in delivery tags', ['value' => $sesData['delivery']['tags']['company_key']]);
            return $sesData['delivery']['tags']['company_key'];
        }

        Log::warning('SNS: No company key found in any location', [
            'mail_headers_exists' => isset($sesData['mail']['headers']),
            'mail_common_headers_exists' => isset($sesData['mail']['commonHeaders']),
            'bounce_exists' => isset($sesData['bounce']),
            'complaint_exists' => isset($sesData['complaint']),
            'delivery_exists' => isset($sesData['delivery'])
        ]);

        return null;
    }
}
