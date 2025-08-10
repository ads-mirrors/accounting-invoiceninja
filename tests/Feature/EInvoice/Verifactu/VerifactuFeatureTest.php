<?php

namespace Tests\Feature\EInvoice\Verifactu;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Account;
use App\Models\Company;
use App\Models\Invoice;
use Faker\Factory as Faker;
use App\Models\CompanyToken;
use App\Models\VerifactuLog;
use App\Models\ClientContact;
use App\DataMapper\InvoiceItem;
use App\DataMapper\ClientSettings;
use App\DataMapper\CompanySettings;
use App\Factory\CompanyUserFactory;
use Illuminate\Support\Facades\Http;
use App\Services\EDocument\Standards\Verifactu;
use App\Services\EDocument\Standards\Verifactu\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\Models\Desglose;
use App\Services\EDocument\Standards\Verifactu\Models\IDFactura;
use App\Services\EDocument\Standards\Verifactu\ResponseProcessor;
use App\Services\EDocument\Standards\Verifactu\Models\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Models\RegistroAnterior;
use App\Services\EDocument\Standards\Validation\VerifactuDocumentValidator;
use App\Services\EDocument\Standards\Verifactu\Models\FacturaRectificativa;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice as VerifactuInvoice;

class VerifactuFeatureTest extends TestCase
{
    private $account;
    private $company;
    private $user;
    private $cu;
    private $token;
    private $client;
    private $faker;

    // private string $nombre_razon = 'CERTIFICADO ENTIDAD PRUEBAS'; //must match the cert name
    private string $nombre_razon = 'CERTIFICADO FISICA PRUEBAS'; //must match the cert name

    private string $test_company_nif = 'A39200019';

    private string $test_client_nif = 'A39200019';

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();
    }

    private function buildData($settings = null)
    {
        $this->account = Account::factory()->create([
            'hosted_client_count' => 1000,
            'hosted_company_count' => 1000,
        ]);

        $this->account->num_users = 3;
        $this->account->save();

        $this->user = User::factory()->create([
            'account_id' => $this->account->id,
            'confirmation_code' => 'xyz123',
            'email' => $this->faker->unique()->safeEmail(),
        ]);

        if(!$settings) {
            $settings = CompanySettings::defaults();
            $settings->client_online_payment_notification = false;
            $settings->client_manual_payment_notification = false;
            $settings->country_id = 724;
            $settings->currency_id = 3;
            $settings->address1 = 'Calle Mayor 123'; // Main Street 123
            $settings->city = 'Madrid';
            $settings->state = 'Madrid';
            $settings->postal_code = '28001';
            $settings->vat_number = 'B12345678'; // Spanish VAT number format
            $settings->payment_terms = '10';
            $settings->vat_number = $this->test_company_nif;
            $settings->name = $this->nombre_razon;
        }

        $this->company = Company::factory()->create([
            'account_id' => $this->account->id,
            'settings' => $settings,
        ]);

        $this->company->settings = $settings;
        $this->company->save();

        $this->cu = CompanyUserFactory::create($this->user->id, $this->company->id, $this->account->id);
        $this->cu->is_owner = true;
        $this->cu->is_admin = true;
        $this->cu->is_locked = false;
        $this->cu->save();

        $this->token = \Illuminate\Support\Str::random(64);

        $company_token = new CompanyToken();
        $company_token->user_id = $this->user->id;
        $company_token->company_id = $this->company->id;
        $company_token->account_id = $this->account->id;
        $company_token->name = 'test token';
        $company_token->token = $this->token;
        $company_token->is_system = true;

        $company_token->save();

        $client_settings = ClientSettings::defaults();
        $client_settings->currency_id = '3';

        $this->client = Client::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'is_deleted' => 0,
            'name' => $this->nombre_razon,
            'address1' => 'Calle Mayor 123',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'postal_code' => '28001',
            'country_id' => 724,
            'vat_number' => $this->test_client_nif,
            'balance' => 0,
            'paid_to_date' => 0,
            'settings' => $client_settings,
        ]);

        ClientContact::factory()->create([
                'user_id' => $this->user->id,
                'client_id' => $this->client->id,
                'company_id' => $this->company->id,
                'is_primary' => 1,
                'first_name' => 'john',
                'last_name' => 'doe',
                'email' => 'john@doe.com',
                'send_email' => true,
            ]);

        $line_items = [];

        $item = new InvoiceItem();
        $item->product_key = '1234567890';
        $item->quantity = 1;
        $item->cost = 100;
        $item->notes = 'Test item';
        $item->tax_name1 = 'IVA';
        $item->tax_rate1 = 21;
        $item->discount =0;

        $line_items[] = $item;
        
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'date' => now()->format('Y-m-d'),
            'next_send_date' => null,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'last_sent_date' => now(),
            'reminder_last_sent' => null,
            'uses_inclusive_taxes' => false,
            'discount' => 0,
            'is_amount_discount' => false,
            'status_id' => Invoice::STATUS_DRAFT,
            'amount' => 10,
            'balance' => 10,
            'line_items' => $line_items,
            'tax_rate1' => 0,
            'tax_rate2' => 0,
            'tax_rate3' => 0,
            'tax_name1' => '',
            'tax_name2' => '',
            'tax_name3' => '',
        ]);

        $invoice = $invoice->calc()
                        ->getInvoice()
                        ->service()
                        ->markSent()
                        ->save();
                        
        return $invoice;
    }

    public function test_construction_and_validation()
    {
// - current previous hash - 10C643EDC7DC727FAC6BAEBAAC7BEA67B5C1369A5A5ED74E5AD3149FC30A3C8C
//BE95547AA8B973A3D6A860B36833FBDE3C8AB853F4B8F05872574A5DA7314A23
// - current previous invoice number - TEST0033343443

        $invoice = $this->buildData();

        $invoice->number = 'TEST0033343460';
        $invoice->save();

        $this->assertNotNull($invoice);

        $_inv = Invoice::factory()->create([
            'user_id' => $invoice->user_id,
            'company_id' => $invoice->company_id,
            'client_id' => $invoice->client_id,
            'date' => '2025-08-10',
            'status_id' => Invoice::STATUS_SENT,
            'uses_inclusive_taxes' => false,
        ]);

        $xx = VerifactuLog::create([
            'invoice_id' => $_inv->id,
            'company_id' => $invoice->company_id,
            'invoice_number' => 'TEST0033343459',
            'date' => '2025-08-10',
            'hash' => 'E5A23515881D696FCD1CA8EE4902632BFC6D892BA8EB79CB656A5F84963079D3',
            'nif' => 'A39200019',
            'previous_hash' => 'E5A23515881D696FCD1CA8EE4902632BFC6D892BA8EB79CB656A5F84963079D3',
        ]);

        $verifactu = new Verifactu($invoice);
        $verifactu->run();
        $verifactu->setTestMode()
                ->setPreviousHash('E5A23515881D696FCD1CA8EE4902632BFC6D892BA8EB79CB656A5F84963079D3');

        $validator = new \App\Services\EDocument\Standards\Validation\VerifactuDocumentValidator($verifactu->getEnvelope());
        $validator->validate();
        $errors = $validator->getVerifactuErrors();


        if (!empty($errors)) {

            nlog('Verifactu Validation Errors:');
            nlog($xml);
            nlog($errors);
        }

        $this->assertCount(0, $errors);

        $this->assertNotEmpty($verifactu->getEnvelope());

        $envelope = $verifactu->getEnvelope();
        
        $this->assertNotEmpty($envelope);

        // In test mode, we don't actually send to the service
        // The envelope generation and validation is what we're testing
        $this->assertNotEmpty($envelope);
        $this->assertStringContainsString('soapenv:Envelope', $envelope);
        $this->assertStringContainsString('RegFactuSistemaFacturacion', $envelope);

        // Test the send method (in test mode it should return a response structure)
        $response = $verifactu->send($envelope);
        nlog($response);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        // In test mode, the response might not be successful, but the structure should be correct

        $xx->forceDelete();
    }

    public function testBuildInvoiceCancellation()
    {
        $invoice = $this->buildData();

        $invoice->number = 'TEST0033343459';
        $invoice->save();

        $_inv = Invoice::factory()->create([
            'user_id' => $invoice->user_id,
            'company_id' => $invoice->company_id,
            'client_id' => $invoice->client_id,
            'date' => '2025-08-10',
            'status_id' => Invoice::STATUS_SENT,
            'uses_inclusive_taxes' => false,
        ]);

        $xx = VerifactuLog::create([
            'invoice_id' => $_inv->id,
            'company_id' => $invoice->company_id,
            'invoice_number' => 'TEST0033343459',
            'date' => '2025-08-10',
            'hash' => 'CEF610A3C24D4106ABE4A836C48B0F5251600F44EEE05A90EBD7185FA753553F',
            'nif' => 'A39200019',
            'previous_hash' => 'CEF610A3C24D4106ABE4A836C48B0F5251600F44EEE05A90EBD7185FA753553F',
        ]);

        $verifactu = new Verifactu($invoice);
        $document = (new RegistroAlta($invoice))->run()->getInvoice();
        $huella = $this->cancellationHash($document, $xx->hash);

        $cancellation = $document->createCancellation();
        // $cancellation->setFechaHoraHusoGenRegistro('2025-08-09T23:57:25+00:00');
        $cancellation->setHuella($huella);

        $soapXml = $cancellation->toSoapEnvelope();

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

        $r = new ResponseProcessor();
        $rx = $r->processResponse($response->body());
        $this->assertTrue($rx['success']);

        $xx->forceDelete();


    }

    private function cancellationHash($document, $huella)
    {

    $idEmisorFacturaAnulada = $document->getIdFactura()->getIdEmisorFactura();
    $numSerieFacturaAnulada = $document->getIdFactura()->getNumSerieFactura();
    $fechaExpedicionFacturaAnulada = $document->getIdFactura()->getFechaExpedicionFactura();
    $fechaHoraHusoGenRegistro = $document->getFechaHoraHusoGenRegistro();

    $hashInput = "IDEmisorFacturaAnulada={$idEmisorFacturaAnulada}&" .
        "NumSerieFacturaAnulada={$numSerieFacturaAnulada}&" .
        "FechaExpedicionFacturaAnulada={$fechaExpedicionFacturaAnulada}&" .
        "Huella={$huella}&" .
        "FechaHoraHusoGenRegistro={$fechaHoraHusoGenRegistro}";

        nlog("Cancellation Huella: " . $hashInput);

    return strtoupper(hash('sha256', $hashInput));



    }


    public function test_invoice_invoice_modification()
    {
        $invoice = $this->buildData();
        $invoice->number = 'TEST0033343460-R2';
        $invoice->save();

        $previous_huella = '1FB6B4EF72DD2A07CC23B3F9D74EE5749C8E86B34B9B1DFFFC8C3E46ACA87E21';

        $xx = VerifactuLog::create([
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'invoice_number' => 'TEST0033343459',
            'date' => '2025-08-10',
            'hash' => $previous_huella,
            'nif' => 'A39200019',
            'previous_hash' => $previous_huella,
        ]);

        $verifactu = new Verifactu($invoice);
        $document = $verifactu->setTestMode()
                ->setPreviousHash($previous_huella)
                ->run()
                ->getInvoice();

        nlog($document->toSoapEnvelope());

        $response = $verifactu->send($document->toSoapEnvelope());
       
        $this->assertNotNull($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
    }

        public function test_rectification_invoice()
        {
        $soapXml = <<<XML
                    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd" xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <sum:RegFactuSistemaFacturacion>
                        <sum:Cabecera>
                            <sum1:ObligadoEmision>
                            <sum1:NombreRazon>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazon>
                            <sum1:NIF>A39200019</sum1:NIF>
                            </sum1:ObligadoEmision>
                        </sum:Cabecera>
                        <sum:RegistroFactura>
                            <sum1:RegistroAlta>
                    <sum1:IDVersion>1.0</sum1:IDVersion>
                    <sum1:IDFactura>
                        <sum1:IDEmisorFactura>A39200019</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>TEST0033343460-R1</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>10-08-2025</sum1:FechaExpedicionFactura>
                    </sum1:IDFactura>
                    <sum1:NombreRazonEmisor>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazonEmisor>
                    <sum1:TipoFactura>F3</sum1:TipoFactura>
                    <sum1:FacturasSustituidas>
                        <sum1:IDFacturaSustituida>
                        <sum1:IDEmisorFactura>A39200019</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>TEST0033343460</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>10-08-2025</sum1:FechaExpedicionFactura>
                        </sum1:IDFacturaSustituida>
                    </sum1:FacturasSustituidas>
                    <sum1:DescripcionOperacion>Alta</sum1:DescripcionOperacion>
                    <sum1:Destinatarios>
                        <sum1:IDDestinatario>
                        <sum1:NombreRazon>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazon>
                        <sum1:NIF>A39200019</sum1:NIF>
                        </sum1:IDDestinatario>
                    </sum1:Destinatarios>
                    <sum1:Desglose>
                        <sum1:DetalleDesglose>
                        <sum1:Impuesto>01</sum1:Impuesto>
                        <sum1:ClaveRegimen>01</sum1:ClaveRegimen>
                        <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
                        <sum1:TipoImpositivo>21.00</sum1:TipoImpositivo>
                        <sum1:BaseImponibleOimporteNoSujeto>100.00</sum1:BaseImponibleOimporteNoSujeto>
                        <sum1:CuotaRepercutida>21.00</sum1:CuotaRepercutida>
                        </sum1:DetalleDesglose>
                    </sum1:Desglose>
                    <sum1:CuotaTotal>21</sum1:CuotaTotal>
                    <sum1:ImporteTotal>121</sum1:ImporteTotal>
                    <sum1:Encadenamiento>
                        <sum1:RegistroAnterior>
                        <sum1:IDEmisorFactura>A39200019</sum1:IDEmisorFactura>
                        <sum1:NumSerieFactura>TEST0033343459</sum1:NumSerieFactura>
                        <sum1:FechaExpedicionFactura>10-08-2025</sum1:FechaExpedicionFactura>
                        <sum1:Huella>1FB6B4EF72DD2A07CC23B3F9D74EE5749C8E86B34B9B1DFFFC8C3E46ACA87E21</sum1:Huella>
                        </sum1:RegistroAnterior>
                    </sum1:Encadenamiento>
                    <sum1:SistemaInformatico>
                        <sum1:NombreRazon>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazon>
                        <sum1:NIF>A39200019</sum1:NIF>
                        <sum1:NombreSistemaInformatico>InvoiceNinja</sum1:NombreSistemaInformatico>
                        <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
                        <sum1:Version>1.0.03</sum1:Version>
                        <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
                        <sum1:TipoUsoPosibleSoloVerifactu>N</sum1:TipoUsoPosibleSoloVerifactu>
                        <sum1:TipoUsoPosibleMultiOT>S</sum1:TipoUsoPosibleMultiOT>
                        <sum1:IndicadorMultiplesOT>S</sum1:IndicadorMultiplesOT>
                    </sum1:SistemaInformatico>
                    <sum1:FechaHoraHusoGenRegistro>2025-08-10T05:02:18+00:00</sum1:FechaHoraHusoGenRegistro>
                    <sum1:TipoHuella>01</sum1:TipoHuella>
                    <sum1:Huella>BC61C7CB7CB09917C076CAE7D066B3E2CF521A3B8B501D0C83250B5EB4A4B40D</sum1:Huella>
                    </sum1:RegistroAlta>
                        </sum:RegistroFactura>
                        </sum:RegFactuSistemaFacturacion>
                    </soapenv:Body>
                    </soapenv:Envelope>

        XML;


        $xslt = new VerifactuDocumentValidator($soapXml);
        $xslt->validate();
        $errors = $xslt->getVerifactuErrors();

        if(count($errors) > 0) {
            nlog('Errors:');
            nlog($errors);
            nlog('Errors:');
        }

        $this->assertCount(0, $errors);

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

        $r = new ResponseProcessor();
        $rx = $r->processResponse($response->body());

        nlog($rx);

        }


    public function testInvoiceCancellation()
    {
        // Create a sample invoice
        $invoice = $this->buildData();
        
        // Create cancellation from invoice
        $cancellation = \App\Services\EDocument\Standards\Verifactu\Models\InvoiceCancellation::fromInvoice(
            $invoice, 
            'ABCD1234EF5678901234567890ABCDEF1234567890ABCDEF1234567890ABCDEF12'
        );
        
        // Set custom cancellation details
        $cancellation->setEstado('02') // 02 = Invoice cancelled
                    ->setDescripcionEstado('Factura anulada por error');
        
        // Generate XML
        $xmlString = $cancellation->toXmlString();
        
        // Verify XML structure
        $this->assertNotEmpty($xmlString);
        $this->assertStringContainsString('SuministroLRFacturas', $xmlString);
        $this->assertStringContainsString('LRFacturaEntrada', $xmlString);
        $this->assertStringContainsString('IDFactura', $xmlString);
        $this->assertStringContainsString('EstadoFactura', $xmlString);
        $this->assertStringContainsString('Estado', $xmlString);
        $this->assertStringContainsString('02', $xmlString); // Cancelled status
        
        // Generate SOAP envelope
        $soapEnvelope = $cancellation->toSoapEnvelope();
        
        // Verify SOAP structure
        $this->assertNotEmpty($soapEnvelope);
        $this->assertStringContainsString('soapenv:Envelope', $soapEnvelope);
        $this->assertStringContainsString('RegFactuSistemaFacturacion', $soapEnvelope);
        
        // Test serialization
        $serialized = $cancellation->serialize();
        $this->assertNotEmpty($serialized);
        
        // Test deserialization
        $deserialized = \App\Services\EDocument\Standards\Verifactu\Models\InvoiceCancellation::unserialize($serialized);
        $this->assertEquals($cancellation->getNumSerieFacturaEmisor(), $deserialized->getNumSerieFacturaEmisor());
        $this->assertEquals($cancellation->getEstado(), $deserialized->getEstado());
        
        // Test from XML
        $fromXml = \App\Services\EDocument\Standards\Verifactu\Models\InvoiceCancellation::fromXml($xmlString);
        $this->assertEquals($cancellation->getNumSerieFacturaEmisor(), $fromXml->getNumSerieFacturaEmisor());
        $this->assertEquals($cancellation->getEstado(), $fromXml->getEstado());

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
           ->withBody($soapEnvelope, 'text/xml')
           ->post('https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP');

        nlog('Request with AEAT official test data:');
        nlog($soapEnvelope);
        nlog('Response with AEAT official test data:');
        nlog('Response Status: ' . $response->status());
        nlog('Response Headers: ' . json_encode($response->headers()));
        nlog('Response Body: ' . $response->body());

        $r = new ResponseProcessor();
        $rx = $r->processResponse($response->body());

        nlog($rx);

    }

    /**
     * Test that R1 invoice XML structure is exactly as expected with proper element order
     */
    public function test_r1_invoice_xml_structure_exact_match(): void
    {
        // Create a complete R1 invoice with all required elements matching the exact XML structure
        $invoice = new VerifactuInvoice();
        
        // Set required properties using setter methods to match the expected XML exactly
        $invoice->setIdVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIdEmisorFactura('A39200019');
        $idFactura->setNumSerieFactura('TEST0033343444');
        $idFactura->setFechaExpedicionFactura('09-08-2025');
        $invoice->setIdFactura($idFactura);
        
        $invoice->setNombreRazonEmisor('CERTIFICADO FISICA PRUEBAS');
        $invoice->setTipoFactura(VerifactuInvoice::TIPO_FACTURA_RECTIFICATIVA);
        $invoice->setTipoRectificativa('S');
        $invoice->setDescripcionOperacion('Rectificación por error en factura anterior');
        $invoice->setCuotaTotal(47.05);
        $invoice->setImporteTotal(144.05);
        $invoice->setFechaHoraHusoGenRegistro('2025-08-09T23:18:44+02:00');
        $invoice->setTipoHuella('01');
        $invoice->setHuella('E7558C33FE3496551F38FEB582F4879B1D9F6C073489628A8DC275E12298941F');

        // Set up rectification details exactly as in the expected XML
        $invoice->setRectifiedInvoice('A39200019', 'TEST0033343443', '09-08-2025');

        
        $importeRectificacion = [
            'BaseRectificada' => 100.00,
            'CuotaRectificada' => 21.00,
            'CuotaRecargoRectificado' => 0.00
        ];

        $invoice->setRectificationAmounts($importeRectificacion);

        // Set up desglose exactly as in the expected XML
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'TipoImpositivo' => 21.00,
            'BaseImponible' => 97.00,
            'Cuota' => 20.37
        ]);
        $invoice->setDesglose($desglose);

        // Generate SOAP envelope XML
        $soapXml = $invoice->toSoapEnvelope();

        // Verify the XML structure exactly matches the expected format
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $soapXml);
        $this->assertStringContainsString('<soapenv:Envelope', $soapXml);
        $this->assertStringContainsString('xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"', $soapXml);
        $this->assertStringContainsString('xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"', $soapXml);
        $this->assertStringContainsString('xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd"', $soapXml);
        
        // Verify SOAP structure
        $this->assertStringContainsString('<soapenv:Header/>', $soapXml);
        $this->assertStringContainsString('<soapenv:Body>', $soapXml);
        $this->assertStringContainsString('<sum:RegFactuSistemaFacturacion>', $soapXml);
        $this->assertStringContainsString('<sum:Cabecera>', $soapXml);
        $this->assertStringContainsString('<sum1:ObligadoEmision>', $soapXml);
        $this->assertStringContainsString('<sum:RegistroFactura>', $soapXml);
        $this->assertStringContainsString('<sum1:RegistroAlta>', $soapXml);
        
        // Verify elements are in exact order as per the expected XML
        $expectedOrder = [
            'IDVersion',
            'IDFactura',
            'NombreRazonEmisor',
            'TipoFactura',
            'TipoRectificativa',
            'FacturasRectificadas',
            'ImporteRectificacion',
            'DescripcionOperacion',
            'Destinatarios',
            'Desglose',
            'CuotaTotal',
            'ImporteTotal',
            'Encadenamiento',
            'SistemaInformatico',
            'FechaHoraHusoGenRegistro',
            'TipoHuella',
            'Huella'
        ];

        $xmlLines = explode("\n", $soapXml);
        $currentIndex = 0;

        foreach ($expectedOrder as $elementName) {
            $found = false;
            for ($i = $currentIndex; $i < count($xmlLines); $i++) {
                if (strpos($xmlLines[$i], "<sum1:{$elementName}") !== false || strpos($xmlLines[$i], "</sum1:{$elementName}") !== false) {
                    $currentIndex = $i;
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Element {$elementName} not found in expected order");
        }

        // Verify specific structure for FacturasRectificadas
        $this->assertStringContainsString('<sum1:FacturasRectificadas>', $soapXml);
        $this->assertStringContainsString('<sum1:IDFacturaRectificada>', $soapXml);
        $this->assertStringContainsString('<sum1:IDEmisorFactura>A39200019</sum1:IDEmisorFactura>', $soapXml);
        $this->assertStringContainsString('<sum1:NumSerieFactura>TEST0033343443</sum1:NumSerieFactura>', $soapXml);
        $this->assertStringContainsString('<sum1:FechaExpedicionFactura>09-08-2025</sum1:FechaExpedicionFactura>', $soapXml);
        $this->assertStringContainsString('</sum1:IDFacturaRectificada>', $soapXml);
        $this->assertStringContainsString('</sum1:FacturasRectificadas>', $soapXml);

        // Verify ImporteRectificacion structure
        $this->assertStringContainsString('<sum1:ImporteRectificacion>', $soapXml);
        $this->assertStringContainsString('<sum1:BaseRectificada>100.00</sum1:BaseRectificada>', $soapXml);
        $this->assertStringContainsString('<sum1:CuotaRectificada>21.00</sum1:CuotaRectificada>', $soapXml);
        $this->assertStringContainsString('<sum1:CuotaRecargoRectificado>0.00</sum1:CuotaRecargoRectificado>', $soapXml);
        $this->assertStringContainsString('</sum1:ImporteRectificacion>', $soapXml);

        // Verify Destinatarios structure
        $this->assertStringContainsString('<sum1:Destinatarios>', $soapXml);
        $this->assertStringContainsString('<sum1:IDDestinatario>', $soapXml);
        $this->assertStringContainsString('<sum1:NombreRazon>Test Recipient Company</sum1:NombreRazon>', $soapXml);
        $this->assertStringContainsString('<sum1:NIF>A39200019</sum1:NIF>', $soapXml);
        $this->assertStringContainsString('</sum1:IDDestinatario>', $soapXml);
        $this->assertStringContainsString('</sum1:Destinatarios>', $soapXml);

        // Verify Desglose structure
        $this->assertStringContainsString('<sum1:Desglose>', $soapXml);
        $this->assertStringContainsString('<sum1:DetalleDesglose>', $soapXml);
        $this->assertStringContainsString('<sum1:Impuesto>01</sum1:Impuesto>', $soapXml);
        $this->assertStringContainsString('<sum1:ClaveRegimen>01</sum1:ClaveRegimen>', $soapXml);
        $this->assertStringContainsString('<sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>', $soapXml);
        $this->assertStringContainsString('<sum1:TipoImpositivo>21.00</sum1:TipoImpositivo>', $soapXml);
        $this->assertStringContainsString('<sum1:BaseImponibleOimporteNoSujeto>97.00</sum1:BaseImponibleOimporteNoSujeto>', $soapXml);
        $this->assertStringContainsString('<sum1:CuotaRepercutida>20.37</sum1:CuotaRepercutida>', $soapXml);
        $this->assertStringContainsString('</sum1:DetalleDesglose>', $soapXml);
        $this->assertStringContainsString('</sum1:Desglose>', $soapXml);

        // Verify Encadenamiento structure
        $this->assertStringContainsString('<sum1:Encadenamiento>', $soapXml);
        $this->assertStringContainsString('<sum1:PrimerRegistro>S</sum1:PrimerRegistro>', $soapXml);
        $this->assertStringContainsString('</sum1:Encadenamiento>', $soapXml);

        // Verify SistemaInformatico structure
        $this->assertStringContainsString('<sum1:SistemaInformatico>', $soapXml);
        $this->assertStringContainsString('<sum1:NombreRazon>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazon>', $soapXml);
        $this->assertStringContainsString('<sum1:NIF>A39200019</sum1:NIF>', $soapXml);
        $this->assertStringContainsString('<sum1:NombreSistemaInformatico>InvoiceNinja</sum1:NombreSistemaInformatico>', $soapXml);
        $this->assertStringContainsString('<sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>', $soapXml);
        $this->assertStringContainsString('<sum1:Version>1.0.03</sum1:Version>', $soapXml);
        $this->assertStringContainsString('<sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>', $soapXml);
        $this->assertStringContainsString('<sum1:TipoUsoPosibleSoloVerifactu>N</sum1:TipoUsoPosibleSoloVerifactu>', $soapXml);
        $this->assertStringContainsString('<sum1:TipoUsoPosibleMultiOT>S</sum1:TipoUsoPosibleMultiOT>', $soapXml);
        $this->assertStringContainsString('<sum1:IndicadorMultiplesOT>S</sum1:IndicadorMultiplesOT>', $soapXml);
        $this->assertStringContainsString('</sum1:SistemaInformatico>', $soapXml);

        // Verify closing tags
        $this->assertStringContainsString('</sum1:RegistroAlta>', $soapXml);
        $this->assertStringContainsString('</sum:RegistroFactura>', $soapXml);
        $this->assertStringContainsString('</sum:RegFactuSistemaFacturacion>', $soapXml);
        $this->assertStringContainsString('</soapenv:Body>', $soapXml);
        $this->assertStringContainsString('</soapenv:Envelope>', $soapXml);
    }
}




// $soapXml = <<<XML
// <?xml version="1.0" encoding="UTF-8"?>
// <soapenv:Envelope 
//   xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
//   xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd" 
//   xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd">
//   <soapenv:Header/>
//   <soapenv:Body>
//     <sum:RegFactuSistemaFacturacion>
//       <sum:Cabecera>
//         <sum1:ObligadoEmision>
//           <sum1:NombreRazon>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazon>
//           <sum1:NIF>A39200019</sum1:NIF>
//         </sum1:ObligadoEmision>
//       </sum:Cabecera>
//       <sum:RegistroFactura>
//         <sum1:RegistroAlta>
//           <sum1:IDVersion>1.0</sum1:IDVersion>
//           <sum1:IDFactura>
//             <sum1:IDEmisorFactura>A39200019</sum1:IDEmisorFactura>
//             <sum1:NumSerieFactura>TEST0033343444</sum1:NumSerieFactura>
//             <sum1:FechaExpedicionFactura>09-08-2025</sum1:FechaExpedicionFactura>
//           </sum1:IDFactura>
//           <sum1:NombreRazonEmisor>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazonEmisor>
//           <sum1:TipoFactura>F3</sum1:TipoFactura>
//           <sum1:TipoRectificativa>S</sum1:TipoRectificativa>
//           <sum1:FacturasSustituidas>
//             <sum1:IDFacturaSustituida>
//                 <sum1:IDEmisorFactura>A39200019</sum1:IDEmisorFactura>
//                 <sum1:NumSerieFactura>TEST0033343443</sum1:NumSerieFactura>
//                 <sum1:FechaExpedicionFactura>09-08-2025</sum1:FechaExpedicionFactura>
//             </sum1:IDFacturaSustituida>
//           </sum1:FacturasSustituidas>
//           <sum1:ImporteRectificacion>
//             <sum1:BaseRectificada>100.00</sum1:BaseRectificada>
//             <sum1:CuotaRectificada>21.00</sum1:CuotaRectificada>
//             <sum1:CuotaRecargoRectificado>0.00</sum1:CuotaRecargoRectificado>
//           </sum1:ImporteRectificacion>
//           <sum1:DescripcionOperacion>Rectificación por error en factura anterior</sum1:DescripcionOperacion>
//           <sum1:Desglose>
//             <sum1:DetalleDesglose>
//               <sum1:Impuesto>01</sum1:Impuesto>
//               <sum1:ClaveRegimen>01</sum1:ClaveRegimen>
//               <sum1:CalificacionOperacion>S1</sum1:CalificacionOperacion>
//               <sum1:TipoImpositivo>21.00</sum1:TipoImpositivo>
//               <sum1:BaseImponibleOimporteNoSujeto>97.00</sum1:BaseImponibleOimporteNoSujeto>
//               <sum1:CuotaRepercutida>20.37</sum1:CuotaRepercutida>
//             </sum1:DetalleDesglose>
//           </sum1:Desglose>
//           <sum1:CuotaTotal>47.05</sum1:CuotaTotal>
//           <sum1:ImporteTotal>144.05</sum1:ImporteTotal>
//           <sum1:Encadenamiento>
//             <sum1:PrimerRegistro>S</sum1:PrimerRegistro>
//           </sum1:Encadenamiento>
//           <sum1:SistemaInformatico>
//             <sum1:NombreRazon>CERTIFICADO FISICA PRUEBAS</sum1:NombreRazon>
//             <sum1:NIF>A39200019</sum1:NIF>
//             <sum1:NombreSistemaInformatico>InvoiceNinja</sum1:NombreSistemaInformatico>
//             <sum1:IdSistemaInformatico>77</sum1:IdSistemaInformatico>
//             <sum1:Version>1.0.03</sum1:Version>
//             <sum1:NumeroInstalacion>383</sum1:NumeroInstalacion>
//             <sum1:TipoUsoPosibleSoloVerifactu>N</sum1:TipoUsoPosibleSoloVerifactu>
//             <sum1:TipoUsoPosibleMultiOT>S</sum1:TipoUsoPosibleMultiOT>
//             <sum1:IndicadorMultiplesOT>S</sum1:IndicadorMultiplesOT>
//           </sum1:SistemaInformatico>
//           <sum1:FechaHoraHusoGenRegistro>2025-08-09T23:18:44+02:00</sum1:FechaHoraHusoGenRegistro>
//           <sum1:TipoHuella>01</sum1:TipoHuella>
//           <sum1:Huella>E7558C33FE3496551F38FEB582F4879B1D9F6C073489628A8DC275E12298941F</sum1:Huella>
//         </sum1:RegistroAlta>
//       </sum:RegistroFactura>
//     </sum:RegFactuSistemaFacturacion>
//   </soapenv:Body>
// </soapenv:Envelope>
// XML;
