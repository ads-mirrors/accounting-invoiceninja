<?php

namespace Tests\Feature\EInvoice\Verifactu\Models;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WSTest extends TestCase
{
    public function test_send_aeat_example_to_verifactu()
    {
        $soapXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"
    xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd">
    <soapenv:Header/>
    <soapenv:Body>
        <sum:RegFactuSistemaFacturacion>
            <sum:Cabecera>
                <sum1:ObligadoEmision>
                    <sum1:NombreRazon>Certificate One Telematics</sum1:NombreRazon>
                    <sum1:NIF>99999910G</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>TEST-001</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>24-06-2025</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>Certificate One Telematics</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Test invoice</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                            <sum1:NombreRazon>Test Recipient</sum1:NombreRazon>
                            <sum1:NIF>99999999A</sum1:NIF>
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
                    <sum1:SistemaInformatico>
                        <sum1:NombreRazon>SSSS</sum1:NombreRazon>
                        <sum1:NIF>99999910G</sum1:NIF>
                        <sum1:NombreSistemaInformatico>NombreSistemaInformatico</sum1:NombreSistemaInformatico>
                        <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
                        <sum1:Version>1.0.03</sum1:Version>
                        <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
                        <sum1:TipoUsoPosibleSoloVerifactu>N</sum1:TipoUsoPosibleSoloVerifactu>
                        <sum1:TipoUsoPosibleMultiOT>S</sum1:TipoUsoPosibleMultiOT>
                        <sum1:IndicadorMultiplesOT>S</sum1:IndicadorMultiplesOT>
                    </sum1:SistemaInformatico>
                    <sum1:FechaHoraHusoGenRegistro>2025-06-24T22:34:00+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for the XML content (excluding the signature)
        $xmlForHash = $this->getXmlForHashCalculation($soapXml);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('Calculated hash for XML: ' . $correctHash);

        // Try the Requerimiento endpoint instead of Verifactu
        // $endpoint = 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/RequerimientoSOAP';
        $endpoint = 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');

        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath),file_get_contents($certPath));
        $soapXml = $signingService->sign();

        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'RegFactuSistemaFacturacion',
            ])
            ->withOptions([
                'cert' => $certPath,
                'ssl_key' => $keyPath,
                'verify' => false, // Optional: disable CA verification for testing
                'timeout' => 30, // Increase timeout
            ])
            ->post($endpoint, $soapXml);

        \Log::info('Request with AEAT official test data:');
        \Log::info($soapXml);
        \Log::info('Response with AEAT official test data:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());
        
        // Don't assert success yet, let's see what the actual response is
        if (!$response->successful()) {
            \Log::error('Request failed with status: ' . $response->status());
            \Log::error('Response body: ' . $response->body());
        }
        
        $this->assertTrue($response->successful());
    }

    /**
     * Extract the XML content that should be used for hash calculation
     * This excludes the signature and focuses on the business data
     */
    private function getXmlForHashCalculation(string $fullXml): string
    {
        $doc = new \DOMDocument();
        $doc->loadXML($fullXml);
        
        // Find the RegistroAlta element
        $registroAlta = $doc->getElementsByTagNameNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'RegistroAlta')->item(0);
        
        if (!$registroAlta) {
            throw new \Exception('RegistroAlta element not found');
        }
        
        // Create a new document with just the RegistroAlta content
        $hashDoc = new \DOMDocument('1.0', 'UTF-8');
        $hashDoc->preserveWhiteSpace = false;
        $hashDoc->formatOutput = false;
        
        // Import the RegistroAlta node
        $importedNode = $hashDoc->importNode($registroAlta, true);
        $hashDoc->appendChild($importedNode);
        
        // Return the XML string for hash calculation
        return $hashDoc->saveXML();
    }

    public function test_send_brand_new_invoice_to_verifactu()
    {
        // Create a completely new invoice with fresh data
        $soapXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"
    xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd">
    <soapenv:Header/>
    <soapenv:Body>
        <sum:RegFactuSistemaFacturacion>
            <sum:Cabecera>
                <sum1:ObligadoEmision>
                    <sum1:NombreRazon>NUEVA EMPRESA TEST SL</sum1:NombreRazon>
                    <sum1:NIF>99999910G</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>FAC-2025-001</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>24-06-2025</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>NUEVA EMPRESA TEST SL</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Venta de servicios informáticos</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                            <sum1:NombreRazon>CLIENTE TEST SA</sum1:NombreRazon>
                            <sum1:NIF>B12345678</sum1:NIF>
                        </sum1:IDDestinatario>
                    </sum1:Destinatarios>
                    <sum1:Desglose>
                        <sum1:DetalleDesglose>
                            <sum1:ClaveRegimen>01</sum1:ClaveRegimen>
                            <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                            <sum1:TipoImpositivo>21</sum1:TipoImpositivo>
                            <sum1:BaseImponibleOimporteNoSujeto>500.00</sum1:BaseImponibleOimporteNoSujeto>
                            <sum1:CuotaRepercutida>105.00</sum1:CuotaRepercutida>
                        </sum1:DetalleDesglose>
                    </sum1:Desglose>
                    <sum1:CuotaTotal>105.00</sum1:CuotaTotal>
                    <sum1:ImporteTotal>605.00</sum1:ImporteTotal>
                    <sum1:SistemaInformatico>
                        <sum1:NombreRazon>INVOICE NINJA</sum1:NombreRazon>
                        <sum1:NIF>99999910G</sum1:NIF>
                        <sum1:NombreSistemaInformatico>InvoiceNinja</sum1:NombreSistemaInformatico>
                        <sum1:IdSistemaInformatico>001</sum1:IdSistemaInformatico>
                        <sum1:Version>5.0</sum1:Version>
                        <sum1:NumeroInstalacion>001</sum1:NumeroInstalacion>
                        <sum1:TipoUsoPosibleSoloVerifactu>S</sum1:TipoUsoPosibleSoloVerifactu>
                        <sum1:TipoUsoPosibleMultiOT>N</sum1:TipoUsoPosibleMultiOT>
                        <sum1:IndicadorMultiplesOT>N</sum1:IndicadorMultiplesOT>
                    </sum1:SistemaInformatico>
                    <sum1:FechaHoraHusoGenRegistro>2025-06-24T22:40:00+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for the XML content
        $xmlForHash = $this->getXmlForHashCalculation($soapXml);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('Brand new invoice - Calculated hash: ' . $correctHash);

        // Try the Sello endpoint for certificate-based access
        $endpoint = 'https://prewww10.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');

        // Sign the XML
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Send the request
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => $certPath,
                'ssl_key' => $keyPath,
                'verify' => false,
                'timeout' => 30,
            ])
            ->post($endpoint, $soapXml);

        \Log::info('Brand new invoice request:');
        \Log::info($soapXml);
        \Log::info('Brand new invoice response:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());
        
        // Check if the response contains an error
        $responseBody = $response->body();
        if (strpos($responseBody, 'Error interno en el servidor') !== false) {
            \Log::error('SOAP response contains server error: ' . $responseBody);
            $this->fail('SOAP response contains server error: ' . $responseBody);
        }
        
        // Check if the response contains a success message
        if (strpos($responseBody, 'RegFactuSistemaFacturacionResponse') !== false) {
            \Log::info('SOAP response contains success message');
        }
        
        $this->assertTrue($response->successful());
    }

    public function test_send_official_example_to_verifactu()
    {
        // Use the exact structure from the official AEAT example
        $soapXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"
    xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd">
    <soapenv:Header/>
    <soapenv:Body>
        <sum:RegFactuSistemaFacturacion>
            <sum:Cabecera>
                <sum1:ObligadoEmision>
                    <sum1:NombreRazon>99999910G</sum1:NombreRazon>
                    <sum1:NIF>99999910G</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>12345</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>24-06-2025</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>99999910G</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Test invoice following official example</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                            <sum1:NombreRazon>B12345678</sum1:NombreRazon>
                            <sum1:NIF>B12345678</sum1:NIF>
                        </sum1:IDDestinatario>
                    </sum1:Destinatarios>
                    <sum1:Desglose>
                        <sum1:DetalleDesglose>
                            <sum1:ClaveRegimen>01</sum1:ClaveRegimen>
                            <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                            <sum1:TipoImpositivo>4</sum1:TipoImpositivo>
                            <sum1:BaseImponibleOimporteNoSujeto>10.00</sum1:BaseImponibleOimporteNoSujeto>
                            <sum1:CuotaRepercutida>0.40</sum1:CuotaRepercutida>
                        </sum1:DetalleDesglose>
                        <sum1:DetalleDesglose>
                            <sum1:ClaveRegimen>01</sum1:ClaveRegimen>
                            <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                            <sum1:TipoImpositivo>21</sum1:TipoImpositivo>
                            <sum1:BaseImponibleOimporteNoSujeto>100.00</sum1:BaseImponibleOimporteNoSujeto>
                            <sum1:CuotaRepercutida>21.00</sum1:CuotaRepercutida>
                        </sum1:DetalleDesglose>
                    </sum1:Desglose>
                    <sum1:CuotaTotal>21.40</sum1:CuotaTotal>
                    <sum1:ImporteTotal>131.40</sum1:ImporteTotal>
                    <sum1:Encadenamiento>
                        <sum1:RegistroAnterior>
                            <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                            <sum1:NumSerieFactura>44</sum1:NumSerieFactura>
                            <sum1:FechaExpedicionFactura>24-06-2025</sum1:FechaExpedicionFactura>
                            <sum1:Huella>HuellaRegistroAnterior</sum1:Huella>
                        </sum1:RegistroAnterior>
                    </sum1:Encadenamiento>
                    <sum1:SistemaInformatico>
                        <sum1:NombreRazon>99999910G</sum1:NombreRazon>
                        <sum1:NIF>99999910G</sum1:NIF>
                        <sum1:NombreSistemaInformatico>InvoiceNinja</sum1:NombreSistemaInformatico>
                        <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
                        <sum1:Version>1.0.03</sum1:Version>
                        <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
                        <sum1:TipoUsoPosibleSoloVerifactu>N</sum1:TipoUsoPosibleSoloVerifactu>
                        <sum1:TipoUsoPosibleMultiOT>S</sum1:TipoUsoPosibleMultiOT>
                        <sum1:IndicadorMultiplesOT>S</sum1:IndicadorMultiplesOT>
                    </sum1:SistemaInformatico>
                    <sum1:FechaHoraHusoGenRegistro>2025-06-24T22:40:00+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for the XML content
        $xmlForHash = $this->getXmlForHashCalculation($soapXml);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('Official example - Calculated hash: ' . $correctHash);

        // Try the standard Verifactu endpoint
        $endpoint = 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');

        // Sign the XML
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Send the request
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => $certPath,
                'ssl_key' => $keyPath,
                'verify' => false,
                'timeout' => 30,
            ])
            ->post($endpoint, $soapXml);

        \Log::info('Official example request:');
        \Log::info($soapXml);
        \Log::info('Official example response:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());
        
        // Check if the response contains an error
        $responseBody = $response->body();
        if (strpos($responseBody, 'Error interno en el servidor') !== false) {
            \Log::error('SOAP response contains server error: ' . $responseBody);
            $this->fail('SOAP response contains server error: ' . $responseBody);
        }
        
        // Check if the response contains a success message
        if (strpos($responseBody, 'RegFactuSistemaFacturacionResponse') !== false) {
            \Log::info('SOAP response contains success message');
        }
        
        $this->assertTrue($response->successful());
    }
}