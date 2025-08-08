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
use App\Helpers\Invoice\Taxer;
use App\DataMapper\Tax\BaseRule;
use App\Services\AbstractService;
use App\Helpers\Invoice\InvoiceSum;
use App\Utils\Traits\NumberFormatter;
use App\Helpers\Invoice\InvoiceSumInclusive;
use App\Services\EDocument\Standards\Verifactu\Models\Desglose;
use App\Services\EDocument\Standards\Verifactu\Models\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Models\RegistroAnterior;
use App\Services\EDocument\Standards\Verifactu\Models\SistemaInformatico;
use App\Services\EDocument\Standards\Verifactu\Models\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\RegistroModificacion;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice as VerifactuInvoice;
use App\Services\EDocument\Standards\Verifactu\Models\InvoiceModification;
use App\Services\EDocument\Standards\Verifactu\AeatClient;

class Verifactu extends AbstractService
{

    private AeatClient $aeat_client;
    
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

        $v_logs = $this->invoice->verifactu_logs;

        //determine the current status of the invoice.
        $document = new RegistroAlta($this->invoice);

        $huella = '';

        //1. new => RegistraAlta
        if($v_logs->count() >= 1){
            $v_log = $v_logs->first();
            $huella = $v_log->hash;
            $document = InvoiceModification::fromInvoice($this->invoice, $v_log->deserialize());    
        }

        //3. cancelled => RegistroAnulacion

        $new_huella = $this->calculateHash($document, $huella); // careful with this! we'll need to reference this later
        $document->setHuella($new_huella);

        $soapXml = $document->toSoapEnvelope();
        
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
        $idEmisorFactura = $document->getIdEmisorFactura();
        $numSerieFactura = $document->getNumSerieFactura();
        $fechaExpedicionFactura = $document->getFechaExpedicionFactura();
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
        return $this->aeat_client->send($soapXml);
    }
}