<?php

namespace App\Services\EDocument\Standards;

use App\Services\AbstractService;
use App\Services\EDocument\Standards\Verifactu\VerifactuClient;

class Verifactu extends AbstractService
{

    public function run()
    {

        $client = new VerifactuClient(VerifactuClient::MODE_PROD);
        $response = $client->sendRegistroFactura('<sum:RegFactuSistemaFacturacion xmlns:sum="https://...">...</sum:RegFactuSistemaFacturacion>');
        var_dump($response);

    }
}
