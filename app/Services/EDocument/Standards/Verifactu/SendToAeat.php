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

use Mail;
use App\Utils\Ninja;
use App\Models\Company;
use App\Models\Invoice;
use App\Libraries\MultiDB;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\EDocument\Standards\Verifactu;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SendToAeat implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 5;

    public $deleteWhenMissingModels = true;
    
    /**
     * Modification Invoices - (modify) 
     *  - If Amount > 0 - We generates a F3 document which replaces the original invoice. And becomes the new invoice.
     *  - If Amount < 0 - We generate a R2 document which is a negative modification on the original invoice.
     * Create Invoices - (create) Generates a F1 document.
     * Cancellation Invoices - (cancel) Generates a R3 document with full negative values of the original invoice.
     */

    /**
     * __construct
     *
     * @param  int $invoice_id
     * @param  Company $company
     * @param  string $action create, modify, cancel
     * @return void
     */
    public function __construct(private int $invoice_id, private Company $company, private string $action)
    {
    }

    public function backoff()
    {
        return [5, 30, 240, 3600, 7200];
    }

    public function handle()
    {
        MultiDB::setDB($this->company->db);

        $invoice = Invoice::withTrashed()->find($this->invoice_id);

        switch($this->action) {
            case 'modify':
                $this->modifyInvoice($invoice);
                break;
            case 'create':
                $this->createInvoice($invoice);
                break;
            case 'cancel':
                $this->cancelInvoice($invoice);
                break;
        }

    }
    
    /**
     * modifyInvoice
     *
     * Two code paths here:
     * 1. F3 - we are replacing the invoice with a new one: ie. invoice->amount >=0
     * 2. R2 - we are modifying the invoice with a negative amount: ie. invoice->amount < 0
     * @param  Invoice $invoice
     * @return void
     */
    public function modifyInvoice(Invoice $invoice)
    {
                
        $verifactu = new Verifactu($invoice);
        $verifactu->run();

        $envelope = $verifactu->getEnvelope();

        $response = $verifactu->send($envelope);

        nlog($response);
        
        // if($invoice->amount >= 0) {
        //     $document = (new RegistroAlta($invoice))->run()->getInvoice();
        // }
        // else {
        //     $document = (new RegistroRectificacion($invoice))->run()->getInvoice();
        // }
        
    }

    public function createInvoice(Invoice $invoice)
    {
        $verifactu = new Verifactu($invoice);
        $verifactu->run();

        $envelope = $verifactu->getEnvelope();

        $response = $verifactu->send($envelope);

        nlog($response);

    }

    public function cancelInvoice(Invoice $invoice)
    {

        $verifactu = new Verifactu($invoice);
        $document = (new RegistroAlta($invoice))->run()->getInvoice();

        $last_hash = $invoice->company->verifactu_logs()->first();

        $huella = $this->cancellationHash($document, $last_hash->hash);

        $cancellation = $document->createCancellation();
        $cancellation->setHuella($huella);

        $soapXml = $cancellation->toSoapEnvelope();

        $response = $verifactu->send($soapXml);

        nlog($response);
    }

    public function middleware()
    {
        return [new WithoutOverlapping("send_to_aeat_{$this->company->company_key}")];
    }

    public function failed($exception = null)
    {
        nlog($exception);
    }


    
    /**
     * cancellationHash
     *
     * @param  mixed $document
     * @param  string $huella
     * @return string
     */
    private function cancellationHash($document, string $huella): string
    {

        $idEmisorFacturaAnulada = $document->getIdFactura()->getIdEmisorFactura();
        $numSerieFacturaAnulada = $document->getIdFactura()->getNumSerieFactura();
        $fechaExpedicionFacturaAnulada = $document->getIdFactura()->getFechaExpedicionFactura();
        $fechaHoraHusoGenRegistro = $document->getFechaHoraHusoGenRegistro();

        $hashInput = "IDEmisorFacturaAnulada={$idEmisorFacturaAnulada}&" .
            "NumSerieFacturaAnulada={$numSerieFacturaAnulada}&" .
            "FechaExpedicionFacturaAnulada={$fechaExpedicionFacturaAnulada}&" .
            "Huella={$huella}&" .
            "FechaHoraHusoGenRegistro={$fechaHoraHusoGenRegistro}";

        return strtoupper(hash('sha256', $hashInput));

    }
}