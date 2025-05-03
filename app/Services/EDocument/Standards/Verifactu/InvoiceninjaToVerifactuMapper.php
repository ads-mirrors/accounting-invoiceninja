<?php

declare(strict_types=1);

namespace App\Services\EDocument\Standards\Verifactu;

use App\Models\Invoice;
use App\Services\EDocument\Standards\Verifactu\Types\Desglose;
use App\Services\EDocument\Standards\Verifactu\Types\Destinatarios;
use App\Services\EDocument\Standards\Verifactu\Types\Detalle;
use App\Services\EDocument\Standards\Verifactu\Types\IDFacturaExpedida;
use App\Services\EDocument\Standards\Verifactu\Types\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFacturacionAlta;
use Carbon\Carbon;

class InvoiceninjaToVerifactuMapper
{
    public function mapRegistroFacturacionAlta(Invoice $invoice): RegistroFacturacionAlta
    {
        $registroFacturacionAlta = new RegistroFacturacionAlta();

        // Set version
        $registroFacturacionAlta->setIDVersion('');

        // Set invoice ID
        $idFactura = new IDFacturaExpedida();
        $idFactura->setIDEmisorFactura($invoice->company->vat_number);
        $idFactura->setNumSerieFactura($invoice->number);
        $idFactura->setFechaExpedicionFactura($invoice->date->format('d-m-Y'));
        $registroFacturacionAlta->setIDFactura($idFactura);

        // Set external reference
        $registroFacturacionAlta->setRefExterna($invoice->number);

        // Set issuer name
        $registroFacturacionAlta->setNombreRazonEmisor($invoice->company->name);

        // Set subsanacion and rechazo previo
        $registroFacturacionAlta->setSubsanacion('Subsanacion::VALUE_N');
        $registroFacturacionAlta->setRechazoPrevio('RechazoPrevio::VALUE_N');

        // Set invoice type
        $registroFacturacionAlta->setTipoFactura(ClaveTipoFactura::VALUE_F_1);

        // Set operation date and description
        $registroFacturacionAlta->setFechaOperacion($invoice->date->format('d-m-Y'));
        $registroFacturacionAlta->setDescripcionOperacion($invoice->public_notes);

        // Set recipients
        $destinatarios = new Destinatarios();
        $destinatario = new PersonaFisicaJuridica();
        $destinatario->setNombreRazon($invoice->client->name);

        if ($invoice->client->vat_number) {
            $destinatario->setNIF($invoice->client->vat_number);
        } else {
            $idOtro = new IDOtro();
            $idOtro->setID('07'); // No censado
            $idOtro->setID($invoice->client->id_number);
            $destinatario->setIDOtro($idOtro);
        }

        $destinatarios->addToIDDestinatario($destinatario);
        $registroFacturacionAlta->setDestinatarios($destinatarios);

        // Set breakdown
        $desglose = new Desglose();
        $detalle = new Detalle();
        $detalle->setImpuesto(''); // IVA
        $detalle->setTipoImpositivo($invoice->tax_rate);
        $detalle->setBaseImponibleOimporteNoSujeto($invoice->amount);
        $detalle->setCuotaRepercutida($invoice->tax_amount);
        $desglose->addToDetalleDesglose($detalle);
        $registroFacturacionAlta->setDesglose($desglose);

        // Set total amounts
        $registroFacturacionAlta->setCuotaTotal((string)$invoice->tax_amount);
        $registroFacturacionAlta->setImporteTotal((string)$invoice->total);

        // Set fingerprint type and value
        $registroFacturacionAlta->setTipoHuella('');
        $registroFacturacionAlta->setHuella(hash('sha256', $invoice->number));

        // Set generation date
        $registroFacturacionAlta->setFechaHoraHusoGenRegistro(Carbon::now()->format('Y-m-d\TH:i:s'));

        return $registroFacturacionAlta;
    }
}
