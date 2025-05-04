<?php

declare(strict_types=1);

namespace App\Services\EDocument\Standards\Verifactu;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Services\EDocument\Standards\Verifactu\Types\IDOtro;
use App\Services\EDocument\Standards\Verifactu\Types\Detalle;
use App\Services\EDocument\Standards\Verifactu\Types\Desglose;
use App\Services\EDocument\Standards\Verifactu\Types\Destinatarios;
use App\Services\EDocument\Standards\Verifactu\Types\IDFacturaExpedida;
use App\Services\EDocument\Standards\Verifactu\Types\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFacturacionAlta;

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

    public function mapRegistroFacturacionAlta(Invoice $invoice): RegistroFacturacionAlta // Registration Entry
    {
        $registroFacturacionAlta = new RegistroFacturacionAlta(); // Registration Entry

        // Set version
        $registroFacturacionAlta->setIDVersion('');

        // Set invoice ID (IDFactura)
        $idFactura = new IDFacturaExpedida(); // Issued Invoice ID
        $idFactura->setIDEmisorFactura($invoice->company->settings->vat_number); // Invoice Issuer ID
        $idFactura->setNumSerieFactura($invoice->number); // Invoice Serial Number
        $idFactura->setFechaExpedicionFactura(\Carbon\Carbon::parse($invoice->date)->format('d-m-Y')); // Invoice Issue Date
        $registroFacturacionAlta->setIDFactura($idFactura);

        // Set external reference (RefExterna) - The clients reference for this document - typically the PO Number, only apply if we have one.
        if(strlen($invoice->po_number) > 1) {
            $registroFacturacionAlta->setRefExterna($invoice->po_number);
        }

        // Set issuer name (NombreRazonEmisor)
        $registroFacturacionAlta->setNombreRazonEmisor($invoice->company->present()->name());

        // Set correction and previous rejection (Subsanacion y RechazoPrevio)
        //@todo we need to have logic surrounding these two fields if the are applicable to the current doc
        //@todo these _are_ optional fields 
        // $registroFacturacionAlta->setSubsanacion('Subsanacion::VALUE_N'); // Correction
        // $registroFacturacionAlta->setRechazoPrevio('RechazoPrevio::VALUE_N'); // Previous Rejection

        // Set invoice type (TipoFactura)
        $registroFacturacionAlta->setTipoFactura(ClaveTipoFactura::VALUE_F_1);

        // Set operation date and description (FechaOperacion y DescripcionOperacion)
        $registroFacturacionAlta->setFechaOperacion(\Carbon\Carbon::parse($invoice->date)->format('d-m-Y'));
        $registroFacturacionAlta->setDescripcionOperacion($invoice->public_notes ?? '');

        // Set recipients (Destinatarios)
        $destinatarios = new Destinatarios(); // Recipients
        $destinatario = new PersonaFisicaJuridica(); // Natural/Legal Person
        $destinatario->setNombreRazon($invoice->client->present()->name()); // Business Name

        if ($invoice->client->vat_number) {
            $destinatario->setNIF($invoice->client->vat_number); // Tax ID Number
        } else {
            $idOtro = new IDOtro(); // Other ID
            $idOtro->setID('07'); // Not registered in census (No censado)
            $idOtro->setID($invoice->client->id_number);
            $destinatario->setIDOtro($idOtro);
        }

        $destinatarios->addToIDDestinatario($destinatario);
        $registroFacturacionAlta->setDestinatarios($destinatarios);

        // Set breakdown (Desglose)
        $desglose = new Desglose(); // Breakdown
        $detalle = new Detalle(); // Detail
        $detalle->setImpuesto(''); // Tax (IVA)
        $detalle->setTipoImpositivo($invoice->tax_rate); //@todo this is not correct
        $detalle->setBaseImponibleOimporteNoSujeto($invoice->amount); // Taxable Base or Non-Taxable Amount
        $detalle->setCuotaRepercutida($invoice->tax_amount); // Charged Tax Amount
        $desglose->addToDetalleDesglose($detalle);
        $registroFacturacionAlta->setDesglose($desglose);

        // Set total amounts (CuotaTotal e ImporteTotal)
        $registroFacturacionAlta->setCuotaTotal((string)$invoice->tax_amount); //@todo this is not correct
        $registroFacturacionAlta->setImporteTotal((string)$invoice->total); //@todo this is not correct

        // Set fingerprint type and value (TipoHuella y Huella)
        $registroFacturacionAlta->setTipoHuella('');
        $registroFacturacionAlta->setHuella(hash('sha256', $invoice->number)); // Digital Fingerprint

        // Set generation date (FechaHoraHusoGenRegistro)
        $registroFacturacionAlta->setFechaHoraHusoGenRegistro(Carbon::now()->format('Y-m-d\TH:i:s')); //@todo set the timezone to the company locale

        return $registroFacturacionAlta;
    }
}
