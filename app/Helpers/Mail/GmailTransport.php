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

namespace App\Helpers\Mail;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

/**
 * GmailTransport.
 */
class GmailTransport extends AbstractTransport
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        nlog("In Do Send");

        /** @var \Symfony\Component\Mime\Email $message */
        $message = MessageConverter::toEmail($message->getOriginalMessage()); //@phpstan-ignore-line

        //ensure utf-8 encoding of subject
        $subject = $message->getSubject();

        if (!mb_check_encoding($subject, 'UTF-8') || preg_match('/Ã.|â.|Â./', $subject)) {

            $possible_encodings = ['Windows-1252', 'ISO-8859-1', 'ISO-8859-15'];
            
            foreach ($possible_encodings as $encoding) {
                $converted = mb_convert_encoding($subject, 'UTF-8', $encoding);
                
                if (mb_check_encoding($converted, 'UTF-8') && !preg_match('/Ã.|â.|Â./', $converted)) {
                    $subject = $converted;
                    break;
                }
            }
        }

        $message->subject($subject);

        /** @phpstan-ignore-next-line **/
        $token = $message->getHeaders()->get('gmailtoken')->getValue(); // @phpstan-ignore-line
        $message->getHeaders()->remove('gmailtoken');

        $client = new Client();
        $client->setClientId(config('ninja.auth.google.client_id'));
        $client->setClientSecret(config('ninja.auth.google.client_secret'));
        $client->setAccessToken($token);

        $service = new Gmail($client);

        $body = new Message();

        $bccs = $message->getHeaders()->get('Bcc');

        $bcc_list = '';

        if ($bccs) {
            $bcc_list = 'Bcc: ';

            foreach ($bccs->getAddresses() as $address) {

                $bcc_list .= $address->getAddress() .',';
            }

            $bcc_list = rtrim($bcc_list, ",") . "\r\n";
        }

        $body->setRaw($this->base64_encode($bcc_list.$message->toString()));

        $service->users_messages->send('me', $body, []);

    }

    private function base64_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        // return rtrim(strtr(base64_encode($data), ['+' => '-', '/' => '_']), '=');
    }

    public function __toString(): string
    {
        return 'gmail';
    }
}
