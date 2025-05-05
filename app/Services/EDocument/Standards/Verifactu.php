<?php

namespace App\Services\EDocument\Standards;

use App\Models\Invoice;
use App\Services\AbstractService;
use App\Services\EDocument\Standards\Verifactu\VerifactuClient;
use App\Services\EDocument\Standards\Verifactu\InvoiceninjaToVerifactuMapper;

class Verifactu extends AbstractService
{
    public function __construct(private Invoice $invoice)
    {
    }

    public function run()
    {
        $client = new VerifactuClient(VerifactuClient::MODE_PROD);
        $mapper = new InvoiceninjaToVerifactuMapper();
        $regFacAlta = $mapper->mapRegistroFacturacionAlta($this->invoice);
        $response = $client->sendRegistroAlta($regFacAlta);
        var_dump($response);
    }
}
