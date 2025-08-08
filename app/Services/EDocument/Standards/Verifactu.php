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

class Verifactu extends AbstractService
{

    public function __construct(public Invoice $invoice)
    {  
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

        //1. new => RegistraAlta
        if($v_logs->count() >= 1){
            $v_log = $v_logs->first();
            
            $document = InvoiceModification::fromInvoice($this->invoice, $v_log->deserialize());    
        }


        //3. cancelled => RegistroAnulacion
    }
    
}