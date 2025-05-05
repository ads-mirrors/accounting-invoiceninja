<?php

declare(strict_types=1);

namespace App\Services\EDocument\Standards\Verifactu;

use Carbon\Carbon;
use App\Models\Invoice;
use App\DataMapper\Tax\BaseRule;
use App\Services\EDocument\Standards\Verifactu\Types\IDOtro;
use App\Services\EDocument\Standards\Verifactu\Types\Detalle;
use App\Services\EDocument\Standards\Verifactu\Types\Desglose;
use App\Services\EDocument\Standards\Verifactu\Types\IDFactura;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\Types\Destinatarios;
use App\Services\EDocument\Standards\Verifactu\Types\IDDestinatario;
use App\Services\EDocument\Standards\Verifactu\Types\IDFacturaExpedida;
use App\Services\EDocument\Standards\Verifactu\Types\PersonaFisicaJuridica;

class InvoiceninjaToVerifactuMapper
{
    public array $rechazo_previo = [
        'S', // No previous rejection by AEAT
        'N', // Previous rejection
        'X', // No previous rejection but the registration does not exist
    ];

    public array $subsanacion = [
        'S', // Correction
        'N', // No correction
    ];

    /**
     * F series invoices are for the ORIGINAL / INITIAL version of the invoice.
     * R series invoices are for the CORRECTED version of the invoice.
     * 
     * F1 is a standard invoice. Where the full customer details are provided.
     * F2 is a simplified invoice. Where the customer details are not provided.
     * F3 is a substitute invoice. Used to replace F2 invoices - we will not implement this!
     * 
     * R1 Corrective invoice for errors in the original invoice.
     * R2 Used when customer enters bankruptcy during the invoice lifetime.
     * R3 Bad debt invoice for VAT refund.
     * R4 General purpose corrective invoice
     * R5 Corrective invoice for F2 type invoices.
     * 
     * @var array
     */
    public array $invoice_types = [
        'F1', // Invoice
        'F2', // Simplified Invoice
        'F3', // Substitute Invoice
        'R1', // Rectification Invoice
        'R2', // Rectification Invoice
        'R3', // Rectification Invoice
        'R4', // Rectification Invoice
        'R5', // Rectification Invoice
    ];

    /**
     * When generateing R type invoices, we will always use values
     * that substitute the original invoice, this requires settings
     * 
     * $registroAlta->setTipoRectificativa('S'); // for Substitutive
     */ 

    public function mapRegistroAlta(Invoice $invoice): RegistroAlta // Registration Entry
    {
        $registroAlta = new RegistroAlta(); // Registration Entry

        // Set version
        $registroAlta->setIDVersion('1.0');

        // Set invoice ID (IDFactura)
        $idFactura = new IDFactura(); // Issued Invoice ID
        $idFactura->setIDEmisorFactura($invoice->company->settings->vat_number); // Invoice Issuer ID
        $idFactura->setNumSerieFactura($invoice->number); // Invoice Serial Number
        $idFactura->setFechaExpedicionFactura(\Carbon\Carbon::parse($invoice->date)->format('d-m-Y')); // Invoice Issue Date
        $registroAlta->setIDFactura($idFactura);

        // Set external reference (RefExterna) - The clients reference for this document - typically the PO Number, only apply if we have one.
        if(strlen($invoice->po_number) > 1) {
            $registroAlta->setRefExterna($invoice->po_number);
        }

        // Set issuer name (NombreRazonEmisor)
        $registroAlta->setNombreRazonEmisor($invoice->company->present()->name());

        // Set correction and previous rejection (Subsanacion y RechazoPrevio)
        //@todo we need to have logic surrounding these two fields if the are applicable to the current doc
        //@todo these _are_ optional fields 
        // $registroAlta->setSubsanacion('Subsanacion::VALUE_N'); // Correction
        // $registroAlta->setRechazoPrevio('RechazoPrevio::VALUE_N'); // Previous Rejection

        // Set invoice type (TipoFactura)
        $registroAlta->setTipoFactura($this->getInvoiceType($invoice));

        // Delivery Date of the goods or services (we force invoice->date for this.)
        $registroAlta->setFechaOperacion(\Carbon\Carbon::parse($invoice->date)->format('d-m-Y'));

        // Description of the operation (we use invoice->public_notes) BUT only if it's not empty
        if(strlen($invoice->public_notes ?? '') > 0) {
            $registroAlta->setDescripcionOperacion($invoice->public_notes);
        }

        // Set recipients (Destinatarios)
        $destinatarios = new Destinatarios(); // Recipients
        $destinatario = new IDDestinatario(); // Natural/Legal Person
        $destinatario->setNombreRazon($invoice->client->present()->name()); // Business Name

        // For Spanish clients with a VAT, we just need to set the NIF
        if (strlen($invoice->client->vat_number ?? '') > 2 && $invoice->client->country->iso_3166_2 === 'ES') {
            $destinatario->setNIF($invoice->client->vat_number); // Tax ID Number
        } else {
            // For all other clients, we need to set the IDOtro
            // this requires some logic to build
            $idOtro = $this->buildIdOtro($invoice);
            $destinatario->setIDOtro($idOtro);
        }

        $destinatarios->addIDDestinatario($destinatario);
        $registroAlta->setDestinatarios($destinatarios);

        // Set breakdown (Desglose) MAXIMUM 12 Line items!!!!!!!!
        $desglose = new Desglose(); // Breakdown

        foreach($invoice->line_items as $item) {
            $detalle = new Detalle(); // Detail
            $detalle->setImpuesto('01'); // Tax (IVA)  //@todo, need to implement logic for the other tax codes
            $detalle->setTipoImpositivo($item->tax_rate1);
            $detalle->setBaseImponibleOimporteNoSujeto($item->line_total); // Taxable Base or Non-Taxable Amount
            $detalle->setCuotaRepercutida($item->tax_amount); // Charged Tax Amount
            $desglose->addToDetalleDesglose($detalle);
        }

        $registroAlta->setDesglose($desglose);

        // Set total amounts (CuotaTotal e ImporteTotal)
        $registroAlta->setCuotaTotal($invoice->total_taxes); //@todo this is not correct
        $registroAlta->setImporteTotal($invoice->amount); //@todo this is not correct

        // Set fingerprint type and value (TipoHuella y Huella)
        $registroAlta->setTipoHuella('01');
        
        // Set generation date (FechaHoraHusoGenRegistro)
        $registroAlta->setFechaHoraHusoGenRegistro(\Carbon\Carbon::now()->format('Y-m-d\TH:i:sP')); //@todo set the timezone to the company locale

        $registroAlta->setHuella($this->getHash($invoice, $registroAlta)); // Digital Fingerprint

        return $registroAlta;
    }
        
    /**
     * getHash
     *
     * 1. High Billing Record
     * The fields to include in the string to calculate the footprint are: 
     * 
     * IDEmisorFactura : Identification of the invoice issuer.
     * NumSerieFactura : Serial number of the invoice.
     * InvoiceIssueDate : Date the invoice was issued.
     * TipoFactura : Invoice type code.
     * TotalQuota : Total amount of tax quotas.
     * TotalAmount : Total amount of the invoice.
     * Fingerprint (previous record) : Hash of the immediately preceding billing record (if any).
     * DateTimeZoneGenRecord : Date and time of record generation.
     * 
     * 2. Cancellation Billing Record
     * In this case, the fields used to generate the hash are: 
     *  
     * IDEmisorFacturaAnulada : Identification of the issuer of the cancelled invoice.
     * NumSerieFacturaAnulada : Serial number of the cancelled invoice.
     * CancelledInvoiceIssueDate : Date of issue of the canceled invoice.
     * Fingerprint (previous record) : Hash of the cancelled invoice.
     * DateTimeZoneGenRecord : Date and time of record generation.
     * 
     * 3. Event Registration
     * For event logs, the data string to be processed includes: 
     * NIF of the issuer and the person obliged to issue .
     * Event ID .
     * Identification of the computer system .
     * Billing software version .
     * Installation number .
     * Event type .
     * Trace of the previous event (if applicable).
     * Date and time of event generation .
     * 
     * Based on the type of record, the hash will need to be calculated differently.
     * 
     * @param  Invoice $invoice
     * @param  RegistroAlta $registroAlta
     * @return string
     */
    private function getHash(Invoice $invoice, RegistroAlta $registroAlta): string
    {
        // $hash = '';
        // Tipo de factura	Invoice type
        // Número de factura	Invoice number
        // Fecha de emisión	Date of issue
        // NIF del emisor	Issuer's Tax Identification Number (NIF)
        // NIF del receptor	Recipient's Tax Identification Number (NIF)
        // Importe total	Total amount
        // Base imponible	Taxable base
        // IVA aplicado	Applied VAT
        // Tipo impositivo	Tax rate
        // Fecha operación	Transaction date
        // Descripción operación	Description of the transaction
        // Serie	Invoice series
        // Concepto	Concept or description of the invoice

        $hash = "IDEmisorFactura=" . $registroAlta->getIDFactura()->getIDEmisorFactura() .
            "&NumSerieFactura=" . $registroAlta->getIDFactura()->getNumSerieFactura() .
            "&FechaExpedicionFactura=" . $registroAlta->getIDFactura()->getFechaExpedicionFactura() .
            "&TipoFactura=" . $registroAlta->getTipoFactura() .
            "&CuotaTotal=" . $registroAlta->getCuotaTotal() .
            "&ImporteTotal=" . $registroAlta->getImporteTotal() .
            "&Huella=" . $registroAlta->getHuella() . // Fingerprint of the previous record
            "&FechaHoraHusoGenRegistro=" . $registroAlta->getFechaHoraHusoGenRegistro();

        $hash = utf8_encode($hash);

        $hash = strtoupper(hash('sha256', $hash));

        return $hash;

    }

    /**
     * Generate hash for cancellation records
     * 
     * The fields used to generate the hash are:
     * - IDEmisorFacturaAnulada: Identification of the issuer of the cancelled invoice
     * - NumSerieFacturaAnulada: Serial number of the cancelled invoice
     * - FechaExpedicionFacturaAnulada: Date of issue of the canceled invoice
     * - Huella: Hash of the cancelled invoice
     * - FechaHoraHusoGenRegistro: Date and time of record generation
     */
    // private function getHashForCancellation(RegistroFacturacionAnulacion $registroAnulacion): string
    // {
    //     $hash = "IDEmisorFacturaAnulada=" . $registroAnulacion->getIDFactura()->getIDEmisorFactura() .
    //         "&NumSerieFacturaAnulada=" . $registroAnulacion->getIDFactura()->getNumSerieFactura() .
    //         "&FechaExpedicionFacturaAnulada=" . $registroAnulacion->getIDFactura()->getFechaExpedicionFactura() .
    //         "&Huella=" . $registroAnulacion->getHuella() . // Hash of the cancelled invoice //@todo, when we init the doc, we need to set this!!
    //         "&FechaHoraHusoGenRegistro=" . $registroAnulacion->getFechaHoraHusoGenRegistro();

    //     $hash = utf8_encode($hash);
    
    //     return strtoupper(hash('sha256', $hash));
    // }
    /**
     * getInvoiceType
     *
     * We do not yet have any UI for this. We'll need to implement UI
     * functionality that allows the user to initially select F1/F2
     * 
     * and then on editting, they'll be able to select R1/R2/R3/R4/R5   
     *  be able to select R1/R2/R3/R4/R5
     * @return string
     */
    private function getInvoiceType(Invoice $invoice): string
    {
        //@todo we need to have logic surrounding these two fields if the are applicable to the current doc
        return match($invoice->status_id) {
            Invoice::STATUS_DRAFT => 'F1',
            Invoice::STATUS_SENT => 'R4',
            Invoice::STATUS_PAID => 'R4',
            Invoice::STATUS_OVERDUE => 'R4',
            Invoice::STATUS_CANCELLED => 'R4',
            default => 'F1',
        };
    }
    
    /**
     * buildIdOtro
     *
     * Client Identifier mapping
     * @param  Invoice $invoice
     * @return IDOtro
     */
    private function buildIdOtro(Invoice $invoice): IDOtro
    {
        $idOtro = new IDOtro(); // Other ID
        
        $br = new BaseRule();
        $eu_countries = $br->eu_country_codes;

        $client_country_code = $invoice->client->country->iso_3166_2;

        if(in_array($client_country_code, $eu_countries)) {

            // Is this B2C or B2B?
            if(strlen($invoice->client->vat_number ?? '') > 2) {
                $idOtro->setIDType('02'); // VAT Number
                $idOtro->setID($invoice->client->vat_number);
            } else {
                $idOtro->setIDType('04'); // Legal Entity ID
                $idOtro->setID($invoice->client->id_number);
            }
        }
        else {
            //foreign country
            $idOtro->setIDType('03');
            $idOtro->setID(strlen($invoice->client->vat_number ?? '') > 2 ? $invoice->client->vat_number : $invoice->client->id_number);
        }
        
        return $idOtro;
    }
}
