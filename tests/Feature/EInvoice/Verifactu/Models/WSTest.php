<?php

namespace Tests\Feature\EInvoice\Verifactu\Models;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\EDocument\Standards\Verifactu\AeatAuthority;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice;
use App\Services\EDocument\Standards\Verifactu\Models\Desglose;
use App\Services\EDocument\Standards\Verifactu\Models\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Models\SistemaInformatico;
use App\Services\EDocument\Standards\Verifactu\Response\ResponseProcessor;
use App\Services\EDocument\Standards\Verifactu\Models\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\Models\InvoiceModification;


class WSTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // if (config('ninja.is_travis')) {
            $this->markTestSkipped('Deliberately skipping Verifactu tests - otherwise we will break the hash chain !!!');
        // }

    }

    //@todo - need to test that the user has granted power of attorney to the system 
    //@todo - data must be written to the database to confirm this.
    public function test_verifactu_authority()
    {
        $authority = new AeatAuthority();
        $authority->setTestMode();
        $success = $authority->run('A39200019');

        $this->assertTrue($success);
    }


//@todo - need to confirm that building the xml and sending works.
    public function test_verifactu_invoice_model_can_build_xml()
    {
                    
        // Generate current timestamp in the correct format
        $currentTimestamp = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:s');

        nlog($currentTimestamp);

        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC2023002')
            ->setFechaExpedicionFactura('02-01-2025')
            ->setRefExterna('REF-123')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Venta de productos varios')
            ->setCuotaTotal(210.00)
            ->setImporteTotal(1000.00)
            ->setFechaHoraHusoGenRegistro($currentTimestamp)
            ->setTipoHuella('01')
            ->setHuella('PLACEHOLDER_HUELLA');
        // Add emitter
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('A39200019')
            ->setRazonSocial('Empresa Ejemplo SL');
        $invoice->setTercero($emisor);

        // Add breakdown
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponibleOimporteNoSujeto' => 1000.00,
            'TipoImpositivo' => 21,
            'CuotaRepercutida' => 210.00
        ]);
        $invoice->setDesglose($desglose);


$destinatarios = [];
$destinatario = new PersonaFisicaJuridica();

$destinatario
    ->setNif('A39200020')
    ->setNombreRazon('Empresa Ejemplo SL VV');

$destinatarios[] = $destinatario;

$invoice->setDestinatarios($destinatarios);

        // Add information system
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        // Add chain
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        $soapXml = $invoice->toSoapEnvelope();

        $this->assertNotNull($soapXml);

     nlog($soapXml);
    }

    //@todo - need to confirm that building the xml and sending works.
    public function test_generated_invoice_xml_can_send_to_web_service()
    {
                    
        // Generate current timestamp in the correct format
        $currentTimestamp = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:s');

        // $currentTimestamp = \Carbon\Carbon::parse('2023-01-01')->format('Y-m-d\TH:i:s');
        // $currentTimestamp = now()->subDays(5)->format('Y-m-d\TH:i:s');

        nlog($currentTimestamp);

        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC2023002')
            ->setFechaExpedicionFactura('02-01-2025')
            ->setRefExterna('REF-123')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Venta de productos varios')
            ->setCuotaTotal(210.00)
            ->setImporteTotal(1000.00)
            ->setFechaHoraHusoGenRegistro($currentTimestamp)
            ->setTipoHuella('01')
            ->setHuella('PLACEHOLDER_HUELLA');

        // Add emitter
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('A39200019')
            ->setRazonSocial('Empresa Ejemplo SL');
        $invoice->setTercero($emisor);




        // Add breakdown
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponibleOimporteNoSujeto' => 1000.00,
            'TipoImpositivo' => 21,
            'CuotaRepercutida' => 210.00
        ]);
        $invoice->setDesglose($desglose);

        // Add information system
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        // Add chain
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        $soapXml = $invoice->toSoapEnvelope();

        $this->assertNotNull($soapXml);

        $correctHash = $this->calculateVerifactuHash(
            $invoice->getTercero()->getNif(),           // IDEmisorFactura
            $invoice->getIdFactura(), // NumSerieFactura
            $invoice->getFechaHoraHusoGenRegistro(),          // FechaExpedicionFactura
            $invoice->getTipoFactura(),                  // TipoFactura
            $invoice->getCuotaTotal(),               // CuotaTotal
            $invoice->getImporteTotal(),              // ImporteTotal
            '',                    // Huella (empty for first calculation)
            $currentTimestamp      // FechaHoraHusoGenRegistro (current time)
        );

        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);

        nlog("test_generated_invoice_xml_can_send_to_web_service");
        nlog('Calculated hash for XML: ' . $correctHash);

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert5.pem');
        $keyPath = storage_path('aeat-key5.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Try direct HTTP approach instead of SOAP client
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert5.pem'),
                'ssl_key' => storage_path('aeat-key5.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->withBody($soapXml, 'text/xml')
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP');

        nlog('Request with AEAT official test data:');
        nlog($soapXml);
        nlog('Response with AEAT official test data:');
        nlog('Response Status: ' . $response->status());
        nlog('Response Headers: ' . json_encode($response->headers()));
        nlog('Response Body: ' . $response->body());

        if (!$response->successful()) {
            \Log::error('Request failed with status: ' . $response->status());
            \Log::error('Response body: ' . $response->body());
        }

        $this->assertTrue($response->successful());

    }


    //Confirmed, this works. requires us to track the previous hash for each company to be used in subsequent calls.
    public function test_send_aeat_example_to_verifactu()
    {
        // Generate current timestamp in the correct format
        // $currentTimestamp = date('Y-m-d\TH:i:sP');
                
        $currentTimestamp = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');
        $invoice_number = 'TEST0033343443';
        $previous_invoice_number = 'TEST0033343442';
        $invoice_date = '02-07-2025';
        $previous_hash = '10C643EDC7DC727FAC6BAEBAAC7BEA67B5C1369A5A5ED74E5AD3149FC30A3C8C';
        $nif = 'A39200019';

                $soapXml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"
            xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd">
            <soapenv:Header/>
            <soapenv:Body>
                <sum:RegFactuSistemaFacturacion>
                    <sum:Cabecera>
                        <!-- ObligadoEmision: The computer system submitting on behalf of the invoice issuer -->
                        <sum1:ObligadoEmision>
                            <sum1:NombreRazon>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazon>
                            <sum1:NIF>{$nif}</sum1:NIF>
                        </sum1:ObligadoEmision>
                    </sum:Cabecera>
                    <sum:RegistroFactura>
                        <sum1:RegistroAlta>
                            <sum1:IDVersion>1.0</sum1:IDVersion>
                            <!-- IDFactura: The actual invoice issuer (using same test NIF) -->
                            <sum1:IDFactura>
                                <sum1:IDEmisorFactura>{$nif}</sum1:IDEmisorFactura>
                                <sum1:NumSerieFactura>{$invoice_number}</sum1:NumSerieFactura>
                                <sum1:FechaExpedicionFactura>{$invoice_date}</sum1:FechaExpedicionFactura>
                            </sum1:IDFactura>
                            <!-- NombreRazonEmisor: The actual business that issued the invoice -->
                            <sum1:NombreRazonEmisor>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazonEmisor>
                            <sum1:TipoFactura>F1</sum1:TipoFactura>
                            <sum1:DescripcionOperacion>Test invoice submitted by computer system on behalf of business</sum1:DescripcionOperacion>
                            <sum1:Destinatarios>
                                <sum1:IDDestinatario>
                                    <sum1:NombreRazon>Test Recipient Company</sum1:NombreRazon>
                                    <sum1:NIF>A39200019</sum1:NIF>
                                </sum1:IDDestinatario>
                            </sum1:Destinatarios>
                            <sum1:Desglose>
                                <sum1:DetalleDesglose>
                                    <sum1:ClaveRegimen>01</sum1:ClaveRegimen>
                                    <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                                    <sum1:TipoImpositivo>21</sum1:TipoImpositivo>
                                    <sum1:BaseImponibleOimporteNoSujeto>100.00</sum1:BaseImponibleOimporteNoSujeto>
                                    <sum1:CuotaRepercutida>21.00</sum1:CuotaRepercutida>
                                </sum1:DetalleDesglose>
                            </sum1:Desglose>
                            <sum1:CuotaTotal>21.00</sum1:CuotaTotal>
                            <sum1:ImporteTotal>121.00</sum1:ImporteTotal>
                            <!-- Encadenamiento: Required chaining information -->
                            <sum1:Encadenamiento>
                                <sum1:RegistroAnterior>
                                    <sum1:IDEmisorFactura>{$nif}</sum1:IDEmisorFactura>
                                    <sum1:NumSerieFactura>{$previous_invoice_number}</sum1:NumSerieFactura>
                                    <sum1:FechaExpedicionFactura>02-07-2025</sum1:FechaExpedicionFactura>
                                    <sum1:Huella>{$previous_hash}</sum1:Huella>
                                </sum1:RegistroAnterior>
                            </sum1:Encadenamiento>
                            <!-- SistemaInformatico: The computer system details (same as ObligadoEmision) -->
                            <sum1:SistemaInformatico>
                                <sum1:NombreRazon>Sistema de Facturación</sum1:NombreRazon>
                                <sum1:NIF>A39200019</sum1:NIF>
                                <sum1:NombreSistemaInformatico>InvoiceNinja</sum1:NombreSistemaInformatico>
                                <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
                                <sum1:Version>1.0.03</sum1:Version>
                                <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
                                <sum1:TipoUsoPosibleSoloVerifactu>N</sum1:TipoUsoPosibleSoloVerifactu>
                                <sum1:TipoUsoPosibleMultiOT>S</sum1:TipoUsoPosibleMultiOT>
                                <sum1:IndicadorMultiplesOT>S</sum1:IndicadorMultiplesOT>
                            </sum1:SistemaInformatico>
                            <sum1:FechaHoraHusoGenRegistro>{$currentTimestamp}</sum1:FechaHoraHusoGenRegistro>
                            <sum1:TipoHuella>01</sum1:TipoHuella>
                            <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                        </sum1:RegistroAlta>
                    </sum:RegistroFactura>
                </sum:RegFactuSistemaFacturacion>
            </soapenv:Body>
        </soapenv:Envelope>
        XML;

        // Calculate the correct hash using AEAT's specified format
        $correctHash = $this->calculateVerifactuHash(
            $nif,           // IDEmisorFactura
            $invoice_number,            // NumSerieFactura  
            $invoice_date,          // FechaExpedicionFactura
            'F1',                  // TipoFactura
            '21.00',               // CuotaTotal
            '121.00',              // ImporteTotal
            $previous_hash,                    // Huella (empty for first calculation)
            $currentTimestamp      // FechaHoraHusoGenRegistro (current time)
        );  
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        nlog('Calculated hash for XML: ' . $correctHash);

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert5.pem');
        $keyPath = storage_path('aeat-key5.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Try direct HTTP approach instead of SOAP client
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert5.pem'),
                'ssl_key' => storage_path('aeat-key5.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->withBody($soapXml, 'text/xml')
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP');

        nlog('Request with AEAT official test data:');
        nlog($soapXml);
        nlog('Response with AEAT official test data:');
        nlog('Response Status: ' . $response->status());
        nlog('Response Headers: ' . json_encode($response->headers()));
        nlog('Response Body: ' . $response->body());
        
        if (!$response->successful()) {
            \Log::error('Request failed with status: ' . $response->status());
            \Log::error('Response body: ' . $response->body());
        }
        
        $this->assertTrue($response->successful());


        $responseProcessor = new ResponseProcessor();
        $responseProcessor->processResponse($response->body());

        nlog($responseProcessor->getSummary());

        $this->assertTrue($responseProcessor->getSummary()['success']);

    }

    //@todo - need to test that cancelling an invoice works.
    public function test_cancel_existing_invoice()
    {
        //@todo - need to test that cancelling an invoice works.
    }

    //@todo - Need to test that modifying an invoice works.
    public function test_cancel_and_modify_existing_invoice()
    {
        $currentTimestamp = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');
        $invoice_number = 'TEST0033343436';
        $invoice_date = '02-07-2025';
        $nif = '99999910G';

        // Create original invoice (the one to be cancelled)
        $originalInvoice = new Invoice();
        $originalInvoice
            ->setIdVersion('1.0')
            ->setIdFactura($invoice_number)
            ->setNombreRazonEmisor('Original Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Original invoice')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro($currentTimestamp)
            ->setTipoHuella('01')
            ->setHuella('ORIGINAL_HASH');

        // Add emitter to original invoice
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif($nif)
            ->setRazonSocial('Original Company');
        $originalInvoice->setTercero($emisor);

        // Add sistema informatico to original invoice
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('InvoiceNinja')
            ->setIdSistemaInformatico('77')
            ->setVersion('1.0.03')
            ->setNumeroInstalacion('383');
        $originalInvoice->setSistemaInformatico($sistema);

        // Create modified invoice (the replacement)
        $modifiedInvoice = new Invoice();
        $modifiedInvoice
            ->setIdVersion('1.0')
            ->setIdFactura($invoice_number)
            ->setNombreRazonEmisor('CERTIFICADO FISICA PRUEBAS')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Test invoice submitted by computer system on behalf of business')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro($currentTimestamp)
            ->setTipoHuella('01')
            ->setHuella('PLACEHOLDER_HUELLA');

        // Add emitter to modified invoice
        $emisorModificado = new PersonaFisicaJuridica();
        $emisorModificado
            ->setNif($nif)
            ->setRazonSocial('CERTIFICADO FISICA PRUEBAS');
        $modifiedInvoice->setTercero($emisorModificado);

        // Add sistema informatico to modified invoice
        $modifiedInvoice->setSistemaInformatico($sistema);

        // Add destinatarios to modified invoice
        $destinatario = new PersonaFisicaJuridica();
        $destinatario
            ->setNombreRazon('Test Recipient Company')
            ->setNif('A39200019');
        $modifiedInvoice->setDestinatarios([$destinatario]);

        // Add desglose to modified invoice
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'TipoImpositivo' => '21',
            'BaseImponibleOimporteNoSujeto' => '100.00',
            'CuotaRepercutida' => '21.00'
        ]);
        $modifiedInvoice->setDesglose($desglose);

        // Add encadenamiento to modified invoice
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $modifiedInvoice->setEncadenamiento($encadenamiento);

        // Create modification using the new models
        $modification = InvoiceModification::createFromInvoice($originalInvoice, $modifiedInvoice);

        // Calculate the correct hash using AEAT's specified format
        $correctHash = $this->calculateVerifactuHash(
            $nif,           // IDEmisorFactura
            $invoice_number, // NumSerieFactura
            $invoice_date,   // FechaExpedicionFactura
            'F1',           // TipoFactura
            '21.00',        // CuotaTotal
            '121.00',       // ImporteTotal
            '',             // Huella (empty for first calculation)
            $currentTimestamp // FechaHoraHusoGenRegistro (current time)
        );

        // Update the modification record with the correct hash
        $modification->getRegistroModificacion()->setHuella($correctHash);

        nlog('Calculated hash for XML: ' . $correctHash);

        // Generate SOAP envelope
        $soapXml = $modification->toSoapEnvelope();

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert5.pem');
        $keyPath = storage_path('aeat-key5.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Try direct HTTP approach instead of SOAP client
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert5.pem'),
                'ssl_key' => storage_path('aeat-key5.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->withBody($soapXml, 'text/xml')
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP');

        nlog('Request with AEAT official test data:');
        nlog($soapXml);
        nlog('Response with AEAT official test data:');
        nlog('Response Status: ' . $response->status());
        nlog('Response Headers: ' . json_encode($response->headers()));
        nlog('Response Body: ' . $response->body());

        if (!$response->successful()) {
            \Log::error('Request failed with status: ' . $response->status());
            \Log::error('Response body: ' . $response->body());
        }

        $this->assertTrue($response->successful());

        $responseProcessor = new ResponseProcessor();
        $responseProcessor->processResponse($response->body());

        nlog($responseProcessor->getSummary());

        $this->assertTrue($responseProcessor->getSummary()['success']);
    }


    /**
     * Calculate Verifactu hash using AEAT's specified format
     * Based on AEAT response showing the exact format they use
     */
    private function calculateVerifactuHash(
        string $idEmisorFactura,
        string $numSerieFactura, 
        string $fechaExpedicionFactura,
        string $tipoFactura,
        string $cuotaTotal,
        string $importeTotal,
        string $huella,
        string $fechaHoraHusoGenRegistro
    ): string {
        // Build the hash input string exactly as AEAT expects it
        $hashInput = "IDEmisorFactura={$idEmisorFactura}&" .
                    "NumSerieFactura={$numSerieFactura}&" .
                    "FechaExpedicionFactura={$fechaExpedicionFactura}&" .
                    "TipoFactura={$tipoFactura}&" .
                    "CuotaTotal={$cuotaTotal}&" .
                    "ImporteTotal={$importeTotal}&" .
                    "Huella={$huella}&" .
                    "FechaHoraHusoGenRegistro={$fechaHoraHusoGenRegistro}";
        
        nlog('Hash input string: ' . $hashInput);
        
        // Calculate SHA256 hash and return in uppercase
        return strtoupper(hash('sha256', $hashInput));
    }




}