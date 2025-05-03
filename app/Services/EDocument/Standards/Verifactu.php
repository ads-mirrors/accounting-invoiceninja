<?php

namespace App\Services\EDocument\Standards;

use App\Services\AbstractService;
use App\Services\EDocument\Standards\Verifactu\InvoiceninjaToVerifactuMapper;
use App\Services\EDocument\Standards\Verifactu\VerifactuClient;

class Verifactu extends AbstractService
{

    public function run()
    {

        $client = new VerifactuClient(VerifactuClient::MODE_PROD);
        $mapper = new InvoiceninjaToVerifactuMapper();
        $invoice = null; //TODO fetch actual invoice
        $regFacAlta = $mapper->mapRegistroFacturacionAlta($invoice);
        $response = $client->sendRegistroAlta($regFacAlta);
        var_dump($response);

    }
}
