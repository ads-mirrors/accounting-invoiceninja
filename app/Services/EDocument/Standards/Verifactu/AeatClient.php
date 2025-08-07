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

namespace App\Services\EDocument\Standards\Verifactu;

use Illuminate\Support\Facades\Http;
use App\Services\EDocument\Standards\Verifactu\ResponseProcessor;

class AeatClient
{
    private string $base_url;

    private string $sandbox_url = 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';

    public function __construct(private ?string $certificate = null, private ?string $ssl_key = null)
    {
        $this->init();
    }
    
    /**
     * initialize the certificates
     *
     * @return self
     */
    private function init(): self
    {
        $this->certificate = $this->certificate ?? file_get_contents(config('services.verifactu.certificate'));
        $this->ssl_key = $this->ssl_key ?? file_get_contents(config('services.verifactu.ssl_key'));

        return $this;
    }
    
    /**
     * setTestMode
     *
     * @return self
     */
    public function setTestMode(): self
    {
        $this->base_url = $this->sandbox_url;

        return $this;
    }
    
    /**
     * run
     *
     * @param  mixed $entity
     * @return void
     */
    public function run($entity): void
    {
        // build the payload

        // harvest any previous hashes

        // send the payload to the AEAT

        // await the response and insert new row into the verifactu_logs table

        // write an activity (success or failure)

        // on success, add a reference to invoice->backup->guid
    }


    private function buildPayload($entity): string
    {
        return '';
    }

    private function harvestPreviousHashes($entity): array
    {
        return [];
    }

    private function send($xml)
    {
                        
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => $this->certificate,
                'ssl_key' => $this->ssl_key,
                'verify' => false,
                'timeout' => 30,
            ])
            ->withBody($xml, 'text/xml')
            ->post($this->base_url);

        $success = $response->successful();

        $responseProcessor = new ResponseProcessor();

        $parsedResponse = $responseProcessor->processResponse($response->body());

        nlog($parsedResponse);

        if($parsedResponse['success']){

            //write the success activity
        }
        else {
            //handle the failure
        }
        
        
    }
}