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
            
            nlog('SNS Webhook received', [
                'headers' => $headers,
                'payload_size' => strlen($payload)
            ]);

            // Parse the SNS payload
            $snsData = json_decode($payload, true);
            
            if (!$snsData) {
                nlog('SNS Webhook: Invalid JSON payload');
                return response()->json(['error' => 'Invalid JSON payload'], 400);
            }

            // Verify SNS signature for security (skip for subscription confirmation)
            $snsMessageType = $headers['x-amz-sns-message-type'][0] ?? null;
            
            if ($snsMessageType === 'Notification') {
                $signatureValid = $this->verifySNSSignature($request, $payload);
                if (!$signatureValid) {
                    nlog('SNS Webhook: Invalid signature - potential security threat');
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            if (!$snsMessageType) {
                nlog('SNS Webhook: Missing x-amz-sns-message-type header');
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
                nlog('SNS Unsubscribe confirmation received', ['topic_arn' => $snsData['TopicArn'] ?? 'unknown']);
                return response()->json(['status' => 'unsubscribe_confirmed']);
            }

            nlog('SNS Webhook: Unknown message type', ['type' => $snsMessageType]);
            return response()->json(['error' => 'Unknown message type'], 400);

        } catch (\Exception $e) {
            nlog('SNS Webhook: Error processing request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verify SNS message signature
     * 
     * @param Request $request
     * @param string $payload
     * @return bool
     */
    private function verifySNSSignature(Request $request, string $payload): bool
    {
        try {
            // Get required headers for signature verification
            $signatureVersion = $request->header('x-amz-sns-message-type') ? 
                $request->header('x-amz-sns-message-type') : '1';
            
            // For now, we'll implement basic verification
            // In production, you should implement full AWS SNS signature verification
            // This requires fetching the signing certificate from AWS and verifying the signature
            
            // Basic checks
            $requiredHeaders = [
                'x-amz-sns-message-type',
                'x-amz-sns-message-id',
                'x-amz-sns-topic-arn'
            ];
            
            foreach ($requiredHeaders as $header) {
                if (!$request->header($header)) {
                    nlog('SNS: Missing required header for signature verification', ['header' => $header]);
                    return false;
                }
            }
            
            // Check if the payload contains valid AWS SNS structure
            $snsData = json_decode($payload, true);
            if (!isset($snsData['Type']) || !isset($snsData['MessageId']) || !isset($snsData['TopicArn'])) {
                nlog('SNS: Invalid SNS message structure for signature verification');
                return false;
            }
            
            // For production, implement full signature verification:
            // 1. Extract the signing certificate URL from the message
            // 2. Fetch the certificate from AWS
            // 3. Verify the signature using the certificate
            // 4. Check the signature timestamp for replay attacks
            
            // For now, we'll do basic validation and log that full verification is needed
            nlog('SNS: Basic signature validation passed - consider implementing full AWS SNS signature verification for production');
            
            return true;
            
        } catch (\Exception $e) {
            nlog('SNS: Error during signature verification', ['error' => $e->getMessage()]);
            return false;
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
            nlog('SNS Subscription confirmation: Missing SubscribeURL');
            return response()->json(['error' => 'Missing SubscribeURL'], 400);
        }

        nlog('SNS Subscription confirmation received', [
            'topic_arn' => $snsData['TopicArn'] ?? 'unknown',
            'subscribe_url' => $subscribeUrl
        ]);

        // You can optionally make an HTTP request to confirm the subscription
        // This is required by AWS to complete the SNS subscription setup
        try {
            $response = file_get_contents($subscribeUrl);
            nlog('SNS Subscription confirmed', ['response' => $response]);
        } catch (\Exception $e) {
            nlog('SNS Subscription confirmation failed', ['error' => $e->getMessage()]);
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
            nlog('SNS Notification: Missing Message content');
            return response()->json(['error' => 'Missing Message content'], 400);
        }

        // Parse the SES message (it's JSON encoded as a string)
        $sesData = json_decode($message, true);
        
        if (!$sesData) {
            nlog('SNS Notification: Invalid SES message format');
            return response()->json(['error' => 'Invalid SES message format'], 400);
        }

        // Validate the SES payload structure
        $validationResult = $this->validateSESPayload($sesData);
        if (!$validationResult['valid']) {
            nlog('SNS Notification: SES payload validation failed', [
                'errors' => $validationResult['errors'],
                'payload' => $sesData
            ]);
            return response()->json(['error' => 'Invalid SES payload', 'details' => $validationResult['errors']], 400);
        }

        nlog('SNS Notification: Processing SES data', [
            'notification_type' => $sesData['notificationType'] ?? $sesData['eventType'] ?? 'unknown',
            'message_id' => $sesData['mail']['messageId'] ?? 'unknown'
        ]);

        // Extract company key from SES data
        $companyKey = $this->extractCompanyKeyFromSES($sesData);
        
        if (!$companyKey) {
            nlog('SNS Notification: No company key found in SES data', [
                'ses_data' => $sesData
            ]);
            return response()->json(['error' => 'No company key found'], 400);
        }

        // Dispatch the SES webhook job for processing
        try {
            SESWebhook::dispatch($sesData);
            
            nlog('SNS Notification: SES webhook job dispatched successfully', [
                'company_key' => $companyKey,
                'message_id' => $sesData['mail']['messageId'] ?? 'unknown'
            ]);
            
            return response()->json(['status' => 'webhook_processed']);
            
        } catch (\Exception $e) {
            nlog('SNS Notification: Failed to dispatch SES webhook job', [
                'error' => $e->getMessage(),
                'company_key' => $companyKey
            ]);
            
            return response()->json(['error' => 'Failed to process webhook'], 500);
        }
    }

    /**
     * Validate SES payload structure and required fields
     * 
     * @param array $sesData
     * @return array ['valid' => bool, 'errors' => array]
     */
    private function validateSESPayload(array $sesData): array
    {
        $errors = [];
        
        // Check if required top-level fields exist
        if (!isset($sesData['mail'])) {
            $errors[] = 'Missing required field: mail';
        }
        
        if (!isset($sesData['eventType']) && !isset($sesData['notificationType'])) {
            $errors[] = 'Missing required field: eventType or notificationType';
        }
        
        // Validate mail object structure
        if (isset($sesData['mail'])) {
            $mail = $sesData['mail'];
            
            if (!isset($mail['messageId'])) {
                $errors[] = 'Missing required field: mail.messageId';
            }
            
            if (!isset($mail['timestamp'])) {
                $errors[] = 'Missing required field: mail.timestamp';
            }
            
            if (!isset($mail['source'])) {
                $errors[] = 'Missing required field: mail.source';
            }
            
            if (!isset($mail['destination']) || !is_array($mail['destination']) || empty($mail['destination'])) {
                $errors[] = 'Missing or invalid field: mail.destination';
            }
            
            // Validate headers structure if present
            if (isset($mail['headers']) && !is_array($mail['headers'])) {
                $errors[] = 'Invalid field: mail.headers must be an array';
            }
            
            // Validate commonHeaders structure if present
            if (isset($mail['commonHeaders']) && !is_array($mail['commonHeaders'])) {
                $errors[] = 'Invalid field: mail.commonHeaders must be an array';
            }
        }
        
        // Validate event-specific data based on event type
        $eventType = $sesData['eventType'] ?? $sesData['notificationType'] ?? '';
        
        switch (strtolower($eventType)) {
            case 'delivery':
                if (!isset($sesData['delivery'])) {
                    $errors[] = 'Missing required field: delivery for delivery event';
                } else {
                    $delivery = $sesData['delivery'];
                    if (!isset($delivery['timestamp'])) {
                        $errors[] = 'Missing required field: delivery.timestamp';
                    }
                    if (!isset($delivery['recipients']) || !is_array($delivery['recipients'])) {
                        $errors[] = 'Missing or invalid field: delivery.recipients';
                    }
                }
                break;
                
            case 'bounce':
                if (!isset($sesData['bounce'])) {
                    $errors[] = 'Missing required field: bounce for bounce event';
                } else {
                    $bounce = $sesData['bounce'];
                    if (!isset($bounce['timestamp'])) {
                        $errors[] = 'Missing required field: bounce.timestamp';
                    }
                    if (!isset($bounce['bounceType'])) {
                        $errors[] = 'Missing required field: bounce.bounceType';
                    }
                    if (!isset($bounce['bouncedRecipients']) || !is_array($bounce['bouncedRecipients'])) {
                        $errors[] = 'Missing or invalid field: bounce.bouncedRecipients';
                    }
                }
                break;
                
            case 'complaint':
                if (!isset($sesData['complaint'])) {
                    $errors[] = 'Missing required field: complaint for complaint event';
                } else {
                    $complaint = $sesData['complaint'];
                    if (!isset($complaint['timestamp'])) {
                        $errors[] = 'Missing required field: complaint.timestamp';
                    }
                    if (!isset($complaint['complainedRecipients']) || !is_array($complaint['complainedRecipients'])) {
                        $errors[] = 'Missing or invalid field: complaint.complainedRecipients';
                    }
                }
                break;
                
            case 'open':
                // Open events might not have additional data beyond the mail object
                break;
                
            default:
                if (!empty($eventType)) {
                    $errors[] = "Unknown event type: {$eventType}";
                }
                break;
        }
        
        // Validate timestamp format if present
        if (isset($sesData['mail']['timestamp'])) {
            $timestamp = $sesData['mail']['timestamp'];
            if (!$this->isValidISOTimestamp($timestamp)) {
                $errors[] = 'Invalid timestamp format: mail.timestamp must be ISO 8601 format';
            }
        }
        
        // Validate messageId format (should be a valid string)
        if (isset($sesData['mail']['messageId'])) {
            $messageId = $sesData['mail']['messageId'];
            if (!is_string($messageId) || strlen(trim($messageId)) === 0) {
                $errors[] = 'Invalid messageId: must be a non-empty string';
            }
        }
        
        // Check for suspicious patterns
        if ($this->containsSuspiciousContent($sesData)) {
            $errors[] = 'Payload contains suspicious content patterns';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Check if timestamp is in valid ISO 8601 format
     * 
     * @param string $timestamp
     * @return bool
     */
    private function isValidISOTimestamp(string $timestamp): bool
    {
        try {
            $date = new \DateTime($timestamp);
            return $date !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check for suspicious content patterns in the payload
     * 
     * @param array $sesData
     * @return bool
     */
    private function containsSuspiciousContent(array $sesData): bool
    {
        $suspiciousPatterns = [
            'javascript:',
            'data:text/html',
            'vbscript:',
            'onload=',
            'onerror=',
            'onclick=',
            '<script',
            '<?php',
            'eval(',
            'document.cookie'
        ];
        
        $payloadString = json_encode($sesData);
        
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($payloadString, $pattern) !== false) {
                nlog('SNS: Suspicious content pattern detected', ['pattern' => $pattern]);
                return true;
            }
        }
        
        return false;
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
            $companyKey = $sesData['mail']['tags']['company_key'];
            if ($this->isValidCompanyKey($companyKey)) {
                nlog('SNS: Found company key in mail tags', ['value' => $companyKey]);
                return $companyKey;
            }
        }

        // Check custom headers - specifically X-Tag which contains the company key
        if (isset($sesData['mail']['headers'])) {
            nlog('SNS: Checking mail headers for X-Tag', [
                'headers_count' => count($sesData['mail']['headers']),
                'headers' => $sesData['mail']['headers']
            ]);
            
            foreach ($sesData['mail']['headers'] as $header) {
                if (isset($header['name']) && $header['name'] === 'X-Tag' && isset($header['value'])) {
                    $companyKey = $header['value'];
                    if ($this->isValidCompanyKey($companyKey)) {
                        nlog('SNS: Found X-Tag header', ['value' => $companyKey]);
                        return $companyKey;
                    }
                }
            }
            
            nlog('SNS: X-Tag header not found in mail headers');
        }

        // Check if company key is in the main SES data
        if (isset($sesData['company_key'])) {
            $companyKey = $sesData['company_key'];
            if ($this->isValidCompanyKey($companyKey)) {
                nlog('SNS: Found company key in main SES data', ['value' => $companyKey]);
                return $companyKey;
            }
        }

        // Check bounce data
        if (isset($sesData['bounce']) && isset($sesData['bounce']['tags']['company_key'])) {
            $companyKey = $sesData['bounce']['tags']['company_key'];
            if ($this->isValidCompanyKey($companyKey)) {
                nlog('SNS: Found company key in bounce tags', ['value' => $companyKey]);
                return $companyKey;
            }
        }

        // Check complaint data
        if (isset($sesData['complaint']) && isset($sesData['complaint']['tags']['company_key'])) {
            $companyKey = $sesData['complaint']['tags']['company_key'];
            if ($this->isValidCompanyKey($companyKey)) {
                nlog('SNS: Found company key in complaint tags', ['value' => $companyKey]);
                return $companyKey;
            }
        }

        // Check delivery data
        if (isset($sesData['delivery']) && isset($sesData['delivery']['tags']['company_key'])) {
            $companyKey = $sesData['delivery']['tags']['company_key'];
            if ($this->isValidCompanyKey($companyKey)) {
                nlog('SNS: Found company key in delivery tags', ['value' => $companyKey]);
                return $companyKey;
            }
        }

        nlog('SNS: No company key found in any location', [
            'mail_headers_exists' => isset($sesData['mail']['headers']),
            'mail_common_headers_exists' => isset($sesData['mail']['commonHeaders']),
            'bounce_exists' => isset($sesData['bounce']),
            'complaint_exists' => isset($sesData['complaint']),
            'delivery_exists' => isset($sesData['delivery'])
        ]);

        return null;
    }

    /**
     * Validate company key format
     * 
     * @param string $companyKey
     * @return bool
     */
    private function isValidCompanyKey(string $companyKey): bool
    {
        // Company key should be a non-empty string
        if (empty(trim($companyKey))) {
            return false;
        }
        
        // Company key should be a reasonable length (Invoice Ninja uses 32 character keys)
        if (strlen($companyKey) < 10 || strlen($companyKey) > 100) {
            return false;
        }
        
        // Company key should only contain alphanumeric characters and common symbols
        if (!preg_match('/^[a-zA-Z0-9\-_\.]+$/', $companyKey)) {
            return false;
        }
        
        return true;
    }
}
