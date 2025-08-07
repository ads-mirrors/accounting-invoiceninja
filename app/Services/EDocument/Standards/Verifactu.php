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
use App\Services\EDocument\Standards\Verifactu\Models\RegistroAnterior;
use App\Services\EDocument\Standards\Verifactu\Models\SistemaInformatico;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice as VerifactuInvoice;

class Verifactu extends AbstractService
{
    use Taxer;
    use NumberFormatter;

    private Company $company;

    private InvoiceSum | InvoiceSumInclusive $calc;

    private VerifactuInvoice $v_invoice;

    private array $tax_map = [];

    private float $allowance_total = 0;

    private array $errors = [];

    private string $current_timestamp;

    private array $impuesto_codes = [
        '01' => 'IVA (Impuesto sobre el Valor Añadido)', // Value Added Tax - Standard Spanish VAT
        '02' => 'IPSI (Impuesto sobre la Producción, los Servicios y la Importación)', // Production, Services and Import Tax - Ceuta and Melilla
        '03' => 'IGIC (Impuesto General Indirecto Canario)', // Canary Islands General Indirect Tax
        '05' => 'Otros (Others)' // Other taxes
    ];

    private array $clave_regimen_codes = [
        '01' => 'Régimen General', // General Regime - Standard VAT regime for most businesses
        '02' => 'Régimen Simplificado', // Simplified Regime - For small businesses with simplified accounting
        '03' => 'Régimen Especial de Agrupaciones de Módulos', // Special Module Grouping Regime - For agricultural activities
        '04' => 'Régimen Especial del Recargo de Equivalencia', // Special Equivalence Surcharge Regime - For retailers
        '05' => 'Régimen Especial de las Agencias de Viajes', // Special Travel Agencies Regime
        '06' => 'Régimen Especial de los Bienes Usados', // Special Used Goods Regime
        '07' => 'Régimen Especial de los Objetos de Arte', // Special Art Objects Regime
        '08' => 'Régimen Especial de las Antigüedades', // Special Antiques Regime
        '09' => 'Régimen Especial de los Objetos de Colección', // Special Collectibles Regime
        '10' => 'Régimen Especial de los Bienes de Inversión', // Special Investment Goods Regime
        '11' => 'Régimen Especial de los Servicios', // Special Services Regime
        '12' => 'Régimen Especial de los Bienes de Inversión y Servicios', // Special Investment Goods and Services Regime
        '13' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo)', // Special Investment Goods and Services Regime (Reverse Charge)
        '14' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo - Bienes de Inversión)', // Special Investment Goods and Services Regime (Reverse Charge - Investment Goods)
        '15' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo - Servicios)', // Special Investment Goods and Services Regime (Reverse Charge - Services)
        '16' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo - Bienes de Inversión y Servicios)', // Special Investment Goods and Services Regime (Reverse Charge - Investment Goods and Services)
        '17' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo - Bienes de Inversión y Servicios - Inversión del Sujeto Pasivo)', // Special Investment Goods and Services Regime (Reverse Charge - Investment Goods and Services - Reverse Charge)
        '18' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo - Bienes de Inversión y Servicios - Inversión del Sujeto Pasivo - Bienes de Inversión)', // Special Investment Goods and Services Regime (Reverse Charge - Investment Goods and Services - Reverse Charge - Investment Goods)
        '19' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo - Bienes de Inversión y Servicios - Inversión del Sujeto Pasivo - Servicios)', // Special Investment Goods and Services Regime (Reverse Charge - Investment Goods and Services - Reverse Charge - Services)
        '20' => 'Régimen Especial de los Bienes de Inversión y Servicios (Inversión del Sujeto Pasivo - Bienes de Inversión y Servicios - Inversión del Sujeto Pasivo - Bienes de Inversión y Servicios)' // Special Investment Goods and Services Regime (Reverse Charge - Investment Goods and Services - Reverse Charge - Investment Goods and Services)
    ];

    private array $calificacion_operacion_codes = [
        'S1' => 'OPERACIÓN SUJETA Y NO EXENTA - SIN INVERSIÓN DEL SUJETO PASIVO', // Subject and Non-Exempt Operation - Without Reverse Charge
        'S2' => 'OPERACIÓN SUJETA Y NO EXENTA - CON INVERSIÓN DEL SUJETO PASIVO', // Subject and Non-Exempt Operation - With Reverse Charge
        'N1' => 'OPERACIÓN NO SUJETA ARTÍCULO 7, 14, OTROS', // Non-Subject Operation Article 7, 14, Others
        'N2' => 'OPERACIÓN NO SUJETA POR REGLAS DE LOCALIZACIÓN' // Non-Subject Operation by Location Rules
    ];

    public function __construct(public Invoice $invoice)
    {
        $this->company = $invoice->company;
        $this->calc = $this->invoice->calc();
        $this->v_invoice = new VerifactuInvoice();
    }

    /**
     * Entry point for building document
     *
     * @return self
     */
    public function run(): self
    {

        $this->current_timestamp = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:s');

        $this->v_invoice
            ->setIdVersion('1.0')
            ->setIdFactura($this->invoice->number) //invoice number
            ->setNombreRazonEmisor($this->company->present()->name()) //company name
            ->setTipoFactura($this->calculateInvoiceType()) //invoice type
            ->setDescripcionOperacion('')// Not manadatory - max chars 500
            ->setCuotaTotal($this->invoice->total_taxes) //total taxes
            ->setImporteTotal($this->invoice->amount) //total invoice amount
            ->setFechaHoraHusoGenRegistro($this->current_timestamp) //creation/submission timestamp
            ->setTipoHuella('01') //sha256
            ->setHuella('PLACEHOLDER_HUELLA');

        /** The business entity that is issuing the invoice */
        $emisor = new PersonaFisicaJuridica();
        $emisor->setNif($this->company->settings->vat_number)
                ->setNombreRazon($this->invoice->company->present()->name());

        $this->v_invoice->setTercero($emisor);

        /** The business entity (Client) that is receiving the invoice */
        $destinatarios = [];
        $destinatario = new PersonaFisicaJuridica();

        $destinatario
            ->setNif($this->invoice->client->vat_number)
            ->setNombreRazon($this->invoice->client->present()->name());

        $destinatarios[] = $destinatario;

        $this->v_invoice->setDestinatarios($destinatarios);
        
        // The tax breakdown
        $desglose = new Desglose();

        //Combine the line taxes with invoice taxes here to get a total tax amount
        $taxes = $calc->getTaxMap();

        $desglose_iva = [];

        foreach ($taxes as $tax) {

            $desglose_iva[] = [
                'Impuesto' => $this->calculateTaxType($tax['name']), //tax type
                'ClaveRegimen' => '01', //tax regime classification code
                'CalificacionOperacion' => 'S1', //operation classification code
                'BaseImponibleOimporteNoSujeto' => $tax['base_amount'] ?? $this->calc->getNetSubtotal(), // taxable base amount
                'TipoImpositivo' => $tax['tax_rate'], // Tax Rate
                'CuotaRepercutida' => $tax['total'] // Tax Amount
            ];

        };

        $desglose->setDesgloseIVA($desglose_iva);

        $this->v_invoice->setDesglose($desglose);

        // Encadenamiento
        $encadenamiento = new Encadenamiento();

        // Get the previous invoice log
        $v_log = $this->company->verifactu_logs()->first();

        // We chain the previous hash to the current invoice to ensure consistency
        if($v_log){

            $registro_anterior = new RegistroAnterior();
            $registro_anterior->setIDEmisorFactura($v_log->nif);
            $registro_anterior->setNumSerieFactura($v_log->invoice_number);
            $registro_anterior->setFechaExpedicionFactura($v_log->date->format('d-m-Y'));
            $registro_anterior->setHuella($v_log->hash);

            $encadenamiento->setRegistroAnterior($registro_anterior);
            
        }
        else {

            $encadenamiento->setPrimerRegistro('S');
            
        }

        $this->v_invoice->setEncadenamiento($encadenamiento);

        //Sending system information - We automatically generate the obligado emision from this later
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Invoice Ninja')
            ->setNif(config('services.verifactu.sender_nif'))
            ->setNombreSistemaInformatico('Invoice Ninja')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('01')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');

        $this->v_invoice->setSistemaInformatico($sistema);


        return $this;
    }

    private function calculateTaxType(string $tax_name): string
    {
        if(stripos($tax_name, 'iva') !== false) {
            return '01';
        }

        if(stripos($tax_name, 'igic') !== false) {
            return '03';
        }

        if(stripos($tax_name, 'ipsi') !== false) {
            return '02';
        }

        if(stripos($tax_name, 'otros') !== false) {
            return '05';
        }

        return '01';
    }

    private function calculateInvoiceType(): string
    {
        //tipofactua
    }

    
}