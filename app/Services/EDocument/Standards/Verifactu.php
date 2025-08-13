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

namespace App\Services\EDocument\Standards;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\VerifactuLog;
use App\Helpers\Invoice\Taxer;
use App\DataMapper\Tax\BaseRule;
use App\Services\AbstractService;
use App\Helpers\Invoice\InvoiceSum;
use App\Utils\Traits\NumberFormatter;
use App\Helpers\Invoice\InvoiceSumInclusive;
use App\Services\EDocument\Standards\Verifactu\AeatClient;
use App\Services\EDocument\Standards\Verifactu\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\Models\Desglose;
use App\Services\EDocument\Standards\Verifactu\Models\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Models\RegistroAnterior;
use App\Services\EDocument\Standards\Verifactu\Models\SistemaInformatico;
use App\Services\EDocument\Standards\Verifactu\Models\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice as VerifactuInvoice;

class Verifactu extends AbstractService
{

    private AeatClient $aeat_client;

    private string $soapXml;
    
    //store the current document state
    private VerifactuInvoice $_document;
    
    //store the current huella
    private string $_huella;

    private string $_previous_huella;
    
    public function __construct(public Invoice $invoice)
    {  
        $this->aeat_client = new AeatClient();
    }

    /**
     * Entry point for building document
     *
     * @return self
     */
    public function run(): self
    {

        $v_logs = $this->invoice->company->verifactu_logs;

        $i_logs = $this->invoice->verifactu_logs;

        $document = (new RegistroAlta($this->invoice))->run();
        
        if($this->invoice->amount < 0) {
            $document = $document->setRectification();
        }
        
        $document = $document->getInvoice();
    
        //keep this state for logging later on successful send
        $this->_document = $document;

        $this->_previous_huella = '';

        if($v_logs->count() >= 1){
            $v_log = $v_logs->first();
            $this->_previous_huella = $v_log->hash;
        }

        $this->_huella = $this->calculateHash($document, $this->_previous_huella); // careful with this! we'll need to reference this later
        $document->setHuella($this->_huella);

        $this->setEnvelope($document->toSoapEnvelope());

        return $this;
        
    }
            
    /**
     * setHuella
     * We need this for cancellation documents.
     * 
     * @param  string $huella
     * @return self
     */
    public function setHuella(string $huella): self
    {
        $this->_huella = $huella;
        return $this;
    }
    
    public function getInvoice()
    {
        return $this->_document;
    }

    public function setInvoice(VerifactuInvoice $invoice): self
    {
        $this->_document = $invoice;
        return $this;
    }

    public function getEnvelope(): string
    {
        return $this->soapXml;
    }
    
    public function setTestMode(): self
    {
        $this->aeat_client->setTestMode();
        return $this;
    }
    /**
     * setPreviousHash
     *
     * **only used for testing**
     * @param  string $previous_hash
     * @return self
     */
    public function setPreviousHash(string $previous_hash): self
    {
        $this->_previous_huella = $previous_hash;
        return $this;
    }

    private function setEnvelope(string $soapXml): self
    {
        $this->soapXml = $soapXml;
        return $this;
    }

    public function writeLog(array $response)
    {
        VerifactuLog::create([
            'invoice_id' => $this->invoice->id,
            'company_id' => $this->invoice->company_id,
            'invoice_number' => $this->invoice->number,
            'date' => $this->invoice->date,
            'hash' => $this->_huella,
            'nif' => $this->_document->getIdFactura()->getIdEmisorFactura(),
            'previous_hash' => $this->_previous_huella,
            'state' => $this->_document->serialize(),
            'response' => $response,
        ]);
    }
    /**
     * calculateHash
     *
     * @param  mixed $document
     * @param  string $huella
     * @return string
     */
    public function calculateHash($document, string $huella): string
    {
        
        $idEmisorFactura = $document->getIdFactura()->getIdEmisorFactura();
        $numSerieFactura = $document->getIdFactura()->getNumSerieFactura();
        $fechaExpedicionFactura = $document->getIdFactura()->getFechaExpedicionFactura();
        $tipoFactura = $document->getTipoFactura();
        $cuotaTotal = $document->getCuotaTotal();
        $importeTotal = $document->getImporteTotal();
        $fechaHoraHusoGenRegistro = $document->getFechaHoraHusoGenRegistro();
        
        $hashInput = "IDEmisorFactura={$idEmisorFactura}&" .
            "NumSerieFactura={$numSerieFactura}&" .
            "FechaExpedicionFactura={$fechaExpedicionFactura}&" .
            "TipoFactura={$tipoFactura}&" .
            "CuotaTotal={$cuotaTotal}&" .
            "ImporteTotal={$importeTotal}&" .
            "Huella={$huella}&" .
            "FechaHoraHusoGenRegistro={$fechaHoraHusoGenRegistro}";

        return strtoupper(hash('sha256', $hashInput));
    }
    
    public function send(string $soapXml): array
    {
        nlog(["sending", $soapXml]);

        $response =  $this->aeat_client->send($soapXml);

        if($response['success'] || $response['status'] == 'ParcialmenteCorrecto'){
            $this->writeLog($response);
        }

        return $response;
    }
}