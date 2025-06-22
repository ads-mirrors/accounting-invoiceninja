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
                    <sum1:NIF>89890001K</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>89890001K</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>12345678-G66</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>03-02-2025</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>Certificate One Telematics</sum1:NombreRazonEmisor>
                    <sum1:Correccion>N</sum1:Correccion>
                    <sum1:RechazoPrevio>N</sum1:RechazoPrevio>
                    <sum1:TipoFactura>R3</sum1:TipoFactura>
                    <sum1:TipoRectificativa>I</sum1:TipoRectificativa>
                    <sum1:FacturasRectificadas>
                        <sum1:IDFacturaRectificada>
                            <sum1:IDEmisorFactura>89890001K</sum1:IDEmisorFactura>
                            <sum1:NumSerieFactura>12345600-G66</sum1:NumSerieFactura>
                            <sum1:FechaExpedicionFactura>01-04-2024</sum1:FechaExpedicionFactura>
                        </sum1:IDFacturaRectificada>
                    </sum1:FacturasRectificadas>
                    <sum1:FechaOperacion>03-02-2025</sum1:FechaOperacion>
                    <sum1:DescripcionOperacion>delivery date</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                            <sum1:NombreRazon>Certificate Two Telematics</sum1:NombreRazon>
                            <sum1:NIF>89890002E</sum1:NIF>
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
                        <sum1:DetalleDesglose>
                            <sum1:ClaveRegimen>05</sum1:ClaveRegimen>
                            <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                            <sum1:TipoImpositivo>10</sum1:TipoImpositivo>
                            <sum1:BaseImponibleOimporteNoSujeto>100.00</sum1:BaseImponibleOimporteNoSujeto>
                            <sum1:CuotaRepercutida>10.00</sum1:CuotaRepercutida>
                        </sum1:DetalleDesglose>
                    </sum1:Desglose>
                    <sum1:CuotaTotal>31.40</sum1:CuotaTotal>
                    <sum1:ImporteTotal>241.40</sum1:ImporteTotal>
                    <sum1:Encadenamiento>
                        <sum1:RegistroAnterior>
                            <sum1:IDEmisorFactura>89890001K</sum1:IDEmisorFactura>
                            <sum1:NumSerieFactura>12345677-G33</sum1:NumSerieFactura>
                            <sum1:FechaExpedicionFactura>15-04-2024</sum1:FechaExpedicionFactura>
                            <sum1:Huella>C9AF4AF1EF5EBBA700350DE3EEF12C2D355C56AC56F13DB2A25E0031BD2B7ED5</sum1:Huella>
                        </sum1:RegistroAnterior>
                    </sum1:Encadenamiento>
                    <sum1:SistemaInformatico>
                        <sum1:NombreRazon>CERTIFICATE ONE TELEMATICAS</sum1:NombreRazon>
                        <sum1:NIF>89890001K</sum1:NIF>
                        <sum1:NombreSistemaInformatico>SystemName</sum1:NombreSistemaInformatico>
                        <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
                        <sum1:Version>1.0.03</sum1:Version>
                        <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
                        <sum1:TipoUsoPosibleSoloVerifactu>S</sum1:TipoUsoPosibleSoloVerifactu>
                        <sum1:TipoUsoPosibleMultiOT>N</sum1:TipoUsoPosibleMultiOT>
                        <sum1:IndicadorMultiplesOT>N</sum1:IndicadorMultiplesOT>
                    </sum1:SistemaInformatico>
                    <sum1:FechaHoraHusoGenRegistro>2025-02-03T14:30:00+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>FF954378B64ED331A9B2366AD317D86E9DEC1716B12DD0ACCB172A6DC4C105AA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        $endpoint = 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';
        $certPath = storage_path('aeat-cert2.pem');
        $keyPath = storage_path('aeat-key2.pem');

        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => $certPath,
                'ssl_key' => $keyPath,
                'verify' => false, // Optional: disable CA verification for testing
            ])
            ->post($endpoint, $soapXml);

        \Log::info('Request with AEAT official test data:');
        \Log::info($soapXml);
        \Log::info('Response with AEAT official test data:');
        \Log::info($response->body());
        $this->assertTrue($response->successful());
    }

    public function test_send_aeat_example_without_cert()
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
                    <sum1:NombreRazon>XXXXX</sum1:NombreRazon>
                    <sum1:NIF>99999910G</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>99999910G</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>12345</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>13-09-2024</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>XXXXX</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F1</sum1:TipoFactura>
                    <sum1:DescripcionOperacion>Descripc</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                            <sum1:NombreRazon>YYYY</sum1:NombreRazon>
                            <sum1:NIF>BBBB</sum1:NIF>
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
                    <sum1:Encadenamiento>
                        <sum1:PrimerRegistro>S</sum1:PrimerRegistro>
                    </sum1:Encadenamiento>
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
                    <sum1:FechaHoraHusoGenRegistro>2024-09-13T19:20:30+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>Huella</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        $endpoint = 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';

        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'verify' => false, // Optional: disable CA verification for testing
            ])
            ->post($endpoint, $soapXml);

        \Log::info('Request without certificate:');
        \Log::info($soapXml);
        \Log::info('Response without certificate:');
        \Log::info($response->body());
        \Log::info('Status code: ' . $response->status());
        
        // This might fail, but we want to see the response
        $this->assertTrue(true); // Just to see the response
    }

    public function test_send_aeat_example_to_alternative_endpoint()
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
                    <sum1:NIF>89890001K</sum1:NIF>
                </sum1:ObligadoEmision>
            </sum:Cabecera>
            <sum:RegistroFactura>
                <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>89890001K</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>12345678-G66</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>03-02-2025</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>Certificate One Telematics</sum1:NombreRazonEmisor>
                    <sum1:Correccion>N</sum1:Correccion>
                    <sum1:RechazoPrevio>N</sum1:RechazoPrevio>
                    <sum1:TipoFactura>R3</sum1:TipoFactura>
                    <sum1:TipoRectificativa>I</sum1:TipoRectificativa>
                    <sum1:FacturasRectificadas>
                        <sum1:IDFacturaRectificada>
                            <sum1:IDEmisorFactura>89890001K</sum1:IDEmisorFactura>
                            <sum1:NumSerieFactura>12345600-G66</sum1:NumSerieFactura>
                            <sum1:FechaExpedicionFactura>01-04-2024</sum1:FechaExpedicionFactura>
                        </sum1:IDFacturaRectificada>
                    </sum1:FacturasRectificadas>
                    <sum1:FechaOperacion>03-02-2025</sum1:FechaOperacion>
                    <sum1:DescripcionOperacion>delivery date</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                            <sum1:NombreRazon>Certificate Two Telematics</sum1:NombreRazon>
                            <sum1:NIF>89890002E</sum1:NIF>
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
                        <sum1:DetalleDesglose>
                            <sum1:ClaveRegimen>05</sum1:ClaveRegimen>
                            <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                            <sum1:TipoImpositivo>10</sum1:TipoImpositivo>
                            <sum1:BaseImponibleOimporteNoSujeto>100.00</sum1:BaseImponibleOimporteNoSujeto>
                            <sum1:CuotaRepercutida>10.00</sum1:CuotaRepercutida>
                        </sum1:DetalleDesglose>
                    </sum1:Desglose>
                    <sum1:CuotaTotal>31.40</sum1:CuotaTotal>
                    <sum1:ImporteTotal>241.40</sum1:ImporteTotal>
                    <sum1:Encadenamiento>
                        <sum1:RegistroAnterior>
                            <sum1:IDEmisorFactura>89890001K</sum1:IDEmisorFactura>
                            <sum1:NumSerieFactura>12345677-G33</sum1:NumSerieFactura>
                            <sum1:FechaExpedicionFactura>15-04-2024</sum1:FechaExpedicionFactura>
                            <sum1:Huella>C9AF4AF1EF5EBBA700350DE3EEF12C2D355C56AC56F13DB2A25E0031BD2B7ED5</sum1:Huella>
                        </sum1:RegistroAnterior>
                    </sum1:Encadenamiento>
                    <sum1:SistemaInformatico>
                        <sum1:NombreRazon>CERTIFICATE ONE TELEMATICAS</sum1:NombreRazon>
                        <sum1:NIF>89890001K</sum1:NIF>
                        <sum1:NombreSistemaInformatico>SystemName</sum1:NombreSistemaInformatico>
                        <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
                        <sum1:Version>1.0.03</sum1:Version>
                        <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
                        <sum1:TipoUsoPosibleSoloVerifactu>S</sum1:TipoUsoPosibleSoloVerifactu>
                        <sum1:TipoUsoPosibleMultiOT>N</sum1:TipoUsoPosibleMultiOT>
                        <sum1:IndicadorMultiplesOT>N</sum1:IndicadorMultiplesOT>
                    </sum1:SistemaInformatico>
                    <sum1:FechaHoraHusoGenRegistro>2025-02-03T14:30:00+01:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>FF954378B64ED331A9B2366AD317D86E9DEC1716B12DD0ACCB172A6DC4C105AA</sum1:Huella>
                </sum1:RegistroAlta>
            </sum:RegistroFactura>
        </sum:RegFactuSistemaFacturacion>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        // Try the alternative test endpoint (prewww10.aeat.es)
        $endpoint = 'https://prewww10.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';
        $certPath = storage_path('aeat-cert.pem');
        $keyPath = storage_path('aeat-key.pem');

        $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])
            ->withOptions([
                'cert' => $certPath,
                'ssl_key' => $keyPath,
                'verify' => false, // Optional: disable CA verification for testing
            ])
            ->post($endpoint, $soapXml);

        \Log::info('Request to alternative endpoint (prewww10.aeat.es):');
        \Log::info($soapXml);
        \Log::info('Response from alternative endpoint:');
        \Log::info($response->body());
        \Log::info('Status code: ' . $response->status());
        $this->assertTrue($response->successful());
    }
}