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

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Try direct HTTP approach instead of SOAP client
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert2.pem'),
                'ssl_key' => storage_path('aeat-key2.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', $soapXml);

        \Log::info('Request with AEAT official test data:');
        \Log::info($soapXml);
        \Log::info('Response with AEAT official test data:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());
        
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

    public function test_send_new_invoice_without_chaining()
    {
        // Create a completely new invoice without any chaining
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
                    <sum1:IDEmisorFactura>
                        <sum1:NIF>99999910G</sum1:NIF>
                        <sum1:ApellidosNombreRazonSocial>Empresa Ejemplo SL</sum1:ApellidosNombreRazonSocial>
                    </sum1:IDEmisorFactura>
                </sum1:ObligadoEmision>
                <sum1:SistemaInformatico>
                    <sum1:NIFDesarrollador>99999910G</sum1:NIFDesarrollador>
                    <sum1:Nombre>Mi Sistema</sum1:Nombre>
                    <sum1:Version>1.0</sum1:Version>
                </sum1:SistemaInformatico>
                <sum1:PeriodoImpositivo>
                    <sum1:Ejercicio>2025</sum1:Ejercicio>
                    <sum1:Periodo>06</sum1:Periodo>
                </sum1:PeriodoImpositivo>
            </sum:Cabecera>
            <sum:Factura>
                <sum1:IDFactura>
                    <sum1:IDEmisorFactura>
                        <sum1:NIF>99999910G</sum1:NIF>
                        <sum1:ApellidosNombreRazonSocial>Empresa Ejemplo SL</sum1:ApellidosNombreRazonSocial>
                    </sum1:IDEmisorFactura>
                    <sum1:NumSerieFacturaEmisor>FAC001</sum1:NumSerieFacturaEmisor>
                    <sum1:FechaExpedicionFacturaEmisor>25-06-2025</sum1:FechaExpedicionFacturaEmisor>
                </sum1:IDFactura>
                <sum1:FacturaExpedida>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:FechaOperacion>25-06-2025</sum1:FechaOperacion>
                    <sum1:Cliente>
                        <sum1:IDOtro>
                            <sum1:CodigoPais>ES</sum1:CodigoPais>
                            <sum1:IDType>02</sum1:IDType>
                            <sum1:ID>12345678A</sum1:ID>
                        </sum1:IDOtro>
                        <sum1:ApellidosNombreRazonSocial>Cliente Ejemplo</sum1:ApellidosNombreRazonSocial>
                        <sum1:NIFRepresentante>99999910G</sum1:NIFRepresentante>
                    </sum1:Cliente>
                    <sum1:DetalleFactura>
                        <sum1:DescripcionOperacion>Venta de productos</sum1:DescripcionOperacion>
                        <sum1:DetalleNoSujeta>
                            <sum1:Causa>RL</sum1:Causa>
                            <sum1:ImportePorArticulos7_14_Otros>100.00</sum1:ImportePorArticulos7_14_Otros>
                        </sum1:DetalleNoSujeta>
                        <sum1:ImporteTotal>100.00</sum1:ImporteTotal>
                        <sum1:BaseImponibleACoste>100.00</sum1:BaseImponibleACoste>
                    </sum1:DetalleFactura>
                    <sum1:Contraparte>
                        <sum1:IDOtro>
                            <sum1:CodigoPais>ES</sum1:CodigoPais>
                            <sum1:IDType>02</sum1:IDType>
                            <sum1:ID>12345678A</sum1:ID>
                        </sum1:IDOtro>
                        <sum1:ApellidosNombreRazonSocial>Cliente Ejemplo</sum1:ApellidosNombreRazonSocial>
                    </sum1:Contraparte>
                </sum1:FacturaExpedida>
            </sum:Factura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for this XML
        $xmlForHash = str_replace('PLACEHOLDER_HUELLA', '', $soapXml);
        $xmlForHash = preg_replace('/<sum1:HuellaDigital>.*?<\/sum1:HuellaDigital>/s', '', $xmlForHash);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('New invoice without chaining - Calculated hash: ' . $correctHash);

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Send the request
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert2.pem'),
                'ssl_key' => storage_path('aeat-key2.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', $soapXml);

        \Log::info('New invoice without chaining response:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());

        if (!$response->successful()) {
            \Log::error('Request failed with status: ' . $response->status());
            \Log::error('Response body: ' . $response->body());
        }

        $this->assertTrue($response->successful());
    }

    public function test_send_official_structure_with_real_data()
    {
        // Use the exact structure from the official example but with real data
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
                    <sum1:NombreRazon>Empresa Test SL</sum1:NombreRazon>
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
                    <sum1:NombreRazonEmisor>Empresa Test SL</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Venta de productos de prueba</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                            <sum1:NombreRazon>Cliente Test SL</sum1:NombreRazon>
                            <sum1:NIF>B12345678</sum1:NIF>
                        </sum1:IDDestinatario>
                    </sum1:Destinatarios>
                    <sum1:DetalleFactura>
                        <sum1:DescripcionOperacion>Venta de productos de prueba</sum1:DescripcionOperacion>
                        <sum1:DetalleNoSujeta>
                            <sum1:Causa>RL</sum1:Causa>
                            <sum1:ImportePorArticulos7_14_Otros>100.00</sum1:ImportePorArticulos7_14_Otros>
                        </sum1:DetalleNoSujeta>
                        <sum1:ImporteTotal>100.00</sum1:ImporteTotal>
                        <sum1:BaseImponibleACoste>100.00</sum1:BaseImponibleACoste>
                    </sum1:DetalleFactura>
                    <sum1:Contraparte>
                        <sum1:IDOtro>
                            <sum1:CodigoPais>ES</sum1:CodigoPais>
                            <sum1:IDType>02</sum1:IDType>
                            <sum1:ID>B12345678</sum1:ID>
                        </sum1:IDOtro>
                        <sum1:ApellidosNombreRazonSocial>Cliente Test SL</sum1:ApellidosNombreRazonSocial>
                    </sum1:Contraparte>
                    <sum1:Encadenamiento>
                        <sum1:PrimerRegistro>S</sum1:PrimerRegistro>
                    </sum1:Encadenamiento>
                    <sum1:FechaHoraHusoGenRegistro>2025-06-24T22:34:00+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for this XML
        $xmlForHash = $this->getXmlForHashCalculation($soapXml);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('Official structure with real data - Calculated hash: ' . $correctHash);

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Send the request
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert2.pem'),
                'ssl_key' => storage_path('aeat-key2.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', $soapXml);

        \Log::info('Official structure with real data response:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());

        if (!$response->successful()) {
            \Log::error('Request failed with status: ' . $response->status());
            \Log::error('Response body: ' . $response->body());
        }

        $this->assertTrue($response->successful());
    }

    public function test_send_minimal_invoice_to_aeat()
    {
        // Create the most minimal invoice possible with only required fields
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
                    <sum1:NombreRazon>Test Company</sum1:NombreRazon>
                    <sum1:NIF>99999910G</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>001</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>01-01-2024</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>Test Company</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Test</sum1:DescripcionOperacion>
                    <sum1:DetalleFactura>
                        <sum1:DescripcionOperacion>Test</sum1:DescripcionOperacion>
                        <sum1:DetalleNoSujeta>
                            <sum1:Causa>RL</sum1:Causa>
                            <sum1:ImportePorArticulos7_14_Otros>10.00</sum1:ImportePorArticulos7_14_Otros>
                        </sum1:DetalleNoSujeta>
                        <sum1:ImporteTotal>10.00</sum1:ImporteTotal>
                    </sum1:DetalleFactura>
                    <sum1:Encadenamiento>
                        <sum1:PrimerRegistro>S</sum1:PrimerRegistro>
                    </sum1:Encadenamiento>
                    <sum1:FechaHoraHusoGenRegistro>2024-01-01T12:00:00+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for this XML
        $xmlForHash = $this->getXmlForHashCalculation($soapXml);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('Minimal invoice - Calculated hash: ' . $correctHash);

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Send the request
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert2.pem'),
                'ssl_key' => storage_path('aeat-key2.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', $soapXml);

        \Log::info('Minimal invoice response:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());

        // Check if we got a different response
        $responseBody = $response->body();
        if (strpos($responseBody, 'Error interno en el servidor') === false) {
            \Log::info('SUCCESS: No server error found in response!');
        } else {
            \Log::error('Still getting server error with minimal invoice');
        }

        $this->assertTrue($response->successful());
    }

    public function test_send_ultra_minimal_invoice_to_aeat()
    {
        // Ultra minimal invoice with current date and no optional fields
        $currentDate = date('d-m-Y');
        $currentDateTime = date('Y-m-d\TH:i:sP');
        
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
                    <sum1:NombreRazon>Test</sum1:NombreRazon>
                    <sum1:NIF>99999910G</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>1</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>$currentDate</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>Test</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Test</sum1:DescripcionOperacion>
                    <sum1:DetalleFactura>
                        <sum1:DescripcionOperacion>Test</sum1:DescripcionOperacion>
                        <sum1:DetalleNoSujeta>
                            <sum1:Causa>RL</sum1:Causa>
                            <sum1:ImportePorArticulos7_14_Otros>1.00</sum1:ImportePorArticulos7_14_Otros>
                        </sum1:DetalleNoSujeta>
                        <sum1:ImporteTotal>1.00</sum1:ImporteTotal>
                    </sum1:DetalleFactura>
                    <sum1:Encadenamiento>
                        <sum1:PrimerRegistro>S</sum1:PrimerRegistro>
                    </sum1:Encadenamiento>
                    <sum1:FechaHoraHusoGenRegistro>$currentDateTime</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for this XML
        $xmlForHash = $this->getXmlForHashCalculation($soapXml);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('Ultra minimal invoice - Calculated hash: ' . $correctHash);
        \Log::info('Using date: ' . $currentDate . ' and datetime: ' . $currentDateTime);

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Send the request
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert2.pem'),
                'ssl_key' => storage_path('aeat-key2.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', $soapXml);

        \Log::info('Ultra minimal invoice response:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());

        // Check if we got a different response
        $responseBody = $response->body();
        if (strpos($responseBody, 'Error interno en el servidor') === false) {
            \Log::info('SUCCESS: No server error found in ultra minimal response!');
        } else {
            \Log::error('Still getting server error with ultra minimal invoice');
        }

        $this->assertTrue($response->successful());
    }

    public function test_send_invoice_without_encadenamiento()
    {
        // Try without Encadenamiento section
        $currentDate = date('d-m-Y');
        $currentDateTime = date('Y-m-d\TH:i:sP');
        
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
                    <sum1:NombreRazon>Test</sum1:NombreRazon>
                    <sum1:NIF>99999910G</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>1</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>$currentDate</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>Test</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Test</sum1:DescripcionOperacion>
                    <sum1:DetalleFactura>
                        <sum1:DescripcionOperacion>Test</sum1:DescripcionOperacion>
                        <sum1:DetalleNoSujeta>
                            <sum1:Causa>RL</sum1:Causa>
                            <sum1:ImportePorArticulos7_14_Otros>1.00</sum1:ImportePorArticulos7_14_Otros>
                        </sum1:DetalleNoSujeta>
                        <sum1:ImporteTotal>1.00</sum1:ImporteTotal>
                    </sum1:DetalleFactura>
                    <sum1:FechaHoraHusoGenRegistro>$currentDateTime</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>PLACEHOLDER_HUELLA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Calculate the correct hash for this XML
        $xmlForHash = $this->getXmlForHashCalculation($soapXml);
        $correctHash = strtoupper(hash('sha256', $xmlForHash));
        
        // Replace the placeholder with the correct hash
        $soapXml = str_replace('PLACEHOLDER_HUELLA', $correctHash, $soapXml);
        
        \Log::info('Invoice without Encadenamiento - Calculated hash: ' . $correctHash);

        // Sign the XML before sending
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');
        $signingService = new \App\Services\EDocument\Standards\Verifactu\Signing\SigningService($soapXml, file_get_contents($keyPath), file_get_contents($certPath));
        $soapXml = $signingService->sign();

        // Send the request
        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => storage_path('aeat-cert2.pem'),
                'ssl_key' => storage_path('aeat-key2.pem'),
                'verify' => false,
                'timeout' => 30,
            ])
            ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', $soapXml);

        \Log::info('Invoice without Encadenamiento response:');
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Headers: ' . json_encode($response->headers()));
        \Log::info('Response Body: ' . $response->body());

        // Check if we got a different response
        $responseBody = $response->body();
        if (strpos($responseBody, 'Error interno en el servidor') === false) {
            \Log::info('SUCCESS: No server error found without Encadenamiento!');
        } else {
            \Log::error('Still getting server error without Encadenamiento');
        }

        $this->assertTrue($response->successful());
    }
}