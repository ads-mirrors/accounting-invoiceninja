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
use App\Models\ClientContact;
use App\DataMapper\InvoiceItem;
use App\DataMapper\ClientSettings;
use App\DataMapper\CompanySettings;
use App\Factory\CompanyUserFactory;
use App\Services\EDocument\Standards\Verifactu\Models\InvoiceCancellation;

class InvoiceCancellationTest extends TestCase
{
    private $user;
    private $company;
    private $token;
    private $client;
    private $faker;

    private string $test_company_nif = 'A39200019';
    private string $test_client_nif = 'A39200019';

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();
    }

    private function buildTestInvoice(): Invoice
    {
        $account = Account::factory()->create([
            'hosted_client_count' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $company = Company::factory()->create([
            'account_id' => $account->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $company_settings = CompanySettings::defaults();
        $company_settings->currency_id = '3';
        $company_settings->country_id = '724';
        $company_settings->vat_number = $this->test_company_nif;

        $company->settings = $company_settings;
        $company->save();

        $this->company = $company;

        $user = User::factory()->create([
            'account_id' => $account->id,
            'email' => $this->faker->unique()->safeEmail(),
            'confirmation_code' => $this->faker->unique()->uuid(),
        ]);

        $this->user = $user;

        $user->companies()->attach($company->id, [
            'account_id' => $account->id,
            'is_owner' => 1,
            'is_admin' => 1,
            'is_locked' => 0,
            'notifications' => CompanySettings::notificationDefaults(),
            'settings' => null,
        ]);


        $company_token = new CompanyToken();
        $company_token->user_id = $user->id;
        $company_token->company_id = $company->id;
        $company_token->account_id = $account->id;
        $company_token->token = $this->faker->unique()->sha1();
        $company_token->name = $this->faker->word();
        $company_token->is_system = 0;

        $company_token->save();

        $client_settings = ClientSettings::defaults();
        $client_settings->currency_id = '3';

        $this->client = Client::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'name' => 'Test Client',
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

        $line_items = [];

        $item = new InvoiceItem();
        $item->product_key = '1234567890';
        $item->qty = 1;
        $item->cost = 100;
        $item->notes = 'Test item';
        $item->tax_name1 = 'IVA';
        $item->tax_rate1 = 21;

        $line_items[] = $item;
        
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'number' => 'INV-2024-001',
            'date' => '2024-01-15',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status_id' => Invoice::STATUS_DRAFT,
            'amount' => 121.00,
            'balance' => 121.00,
            'line_items' => $line_items,
        ]);

        $invoice = $invoice->calc()
                        ->getInvoice()
                        ->service()
                        ->markSent()
                        ->save();
                        
        return $invoice;
    }

    public function testInvoiceCancellationCreation()
    {
        $invoice = $this->buildTestInvoice();
        
        $huella = 'ABCD1234EF5678901234567890ABCDEF1234567890ABCDEF1234567890ABCDEF12';
        
        $cancellation = InvoiceCancellation::fromInvoice($invoice, $huella);
        
        $this->assertInstanceOf(InvoiceCancellation::class, $cancellation);
        $this->assertEquals('INV-2024-001', $cancellation->getNumSerieFacturaEmisor());
        $this->assertEquals('15-01-2024', $cancellation->getFechaExpedicionFacturaEmisor());
        $this->assertEquals($this->test_company_nif, $cancellation->getNifEmisor());
        $this->assertEquals($huella, $cancellation->getHuellaFactura());
        $this->assertEquals('02', $cancellation->getEstado());
        $this->assertEquals('Factura anulada por error', $cancellation->getDescripcionEstado());

        nlog($cancellation->toXmlString());
    }

    public function testInvoiceCancellationXmlGeneration()
    {
        $invoice = $this->buildTestInvoice();
        
        $huella = 'ABCD1234EF5678901234567890ABCDEF1234567890ABCDEF1234567890ABCDEF12';
        
        $cancellation = InvoiceCancellation::fromInvoice($invoice, $huella);
        
        $xmlString = $cancellation->toXmlString();
        
        // Verify XML structure
        $this->assertNotEmpty($xmlString);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xmlString);
        $this->assertStringContainsString('SuministroLRFacturas', $xmlString);
        $this->assertStringContainsString('xmlns:ds="http://www.w3.org/2000/09/xmldsig#"', $xmlString);
        $this->assertStringContainsString('Version="1.1"', $xmlString);
        
        // Verify required elements
        $this->assertStringContainsString('LRFacturaEntrada', $xmlString);
        $this->assertStringContainsString('IDFactura', $xmlString);
        $this->assertStringContainsString('IDEmisorFactura', $xmlString);
        $this->assertStringContainsString('NumSerieFacturaEmisor', $xmlString);
        $this->assertStringContainsString('FechaExpedicionFacturaEmisor', $xmlString);
        $this->assertStringContainsString('NIFEmisor', $xmlString);
        $this->assertStringContainsString('HuellaFactura', $xmlString);
        $this->assertStringContainsString('EstadoFactura', $xmlString);
        $this->assertStringContainsString('Estado', $xmlString);
        $this->assertStringContainsString('DescripcionEstado', $xmlString);
        
        // Verify specific values
        $this->assertStringContainsString('INV-2024-001', $xmlString);
        $this->assertStringContainsString('15-01-2024', $xmlString);
        $this->assertStringContainsString($this->test_company_nif, $xmlString);
        $this->assertStringContainsString($huella, $xmlString);
        $this->assertStringContainsString('02', $xmlString);
        $this->assertStringContainsString('Factura anulada por error', $xmlString);
    }


    public function testInvoiceCancellationSoapEnvelope()
    {
        $invoice = $this->buildTestInvoice();
        
        $huella = 'ABCD1234EF5678901234567890ABCDEF1234567890ABCDEF1234567890ABCDEF12';
        
        $cancellation = InvoiceCancellation::fromInvoice($invoice, $huella);
        
        $soapEnvelope = $cancellation->toSoapEnvelope();
        
        // Verify SOAP structure
        $this->assertNotEmpty($soapEnvelope);
        $this->assertStringContainsString('soapenv:Envelope', $soapEnvelope);
        $this->assertStringContainsString('xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"', $soapEnvelope);
        $this->assertStringContainsString('xmlns:sum="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd"', $soapEnvelope);
        $this->assertStringContainsString('xmlns:sum1="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd"', $soapEnvelope);
        
        // Verify SOAP body structure
        $this->assertStringContainsString('soapenv:Header', $soapEnvelope);
        $this->assertStringContainsString('soapenv:Body', $soapEnvelope);
        $this->assertStringContainsString('sum:RegFactuSistemaFacturacion', $soapEnvelope);
        $this->assertStringContainsString('sum:Cabecera', $soapEnvelope);
        $this->assertStringContainsString('sum1:ObligadoEmision', $soapEnvelope);
        $this->assertStringContainsString('sum:RegistroFactura', $soapEnvelope);
        
        // Verify the cancellation XML is embedded in SOAP
        $this->assertStringContainsString('SuministroLRFacturas', $soapEnvelope);
        $this->assertStringContainsString('LRFacturaEntrada', $soapEnvelope);
    }


    public function testInvoiceCancellationCustomValues()
    {
        $cancellation = new InvoiceCancellation();
        
        $cancellation->setNumSerieFacturaEmisor('CUSTOM-INV-001')
                    ->setFechaExpedicionFacturaEmisor('2025-01-20')
                    ->setNifEmisor('B87654321')
                    ->setHuellaFactura('CUSTOM_HASH_1234567890ABCDEF')
                    ->setEstado('01') // Different status
                    ->setDescripcionEstado('Factura anulada por solicitud del cliente');
        
        $xmlString = $cancellation->toXmlString();
        
        // Verify custom values are in XML
        $this->assertStringContainsString('CUSTOM-INV-001', $xmlString);
        $this->assertStringContainsString('2025-01-20', $xmlString);
        $this->assertStringContainsString('B87654321', $xmlString);
        $this->assertStringContainsString('CUSTOM_HASH_1234567890ABCDEF', $xmlString);
                $this->assertStringContainsString('01', $xmlString);
        $this->assertStringContainsString('Factura anulada por solicitud del cliente', $xmlString);

    }

    public function testInvoiceCancellationSerialization()
    {
        $invoice = $this->buildTestInvoice();
        
        $cancellation = InvoiceCancellation::fromInvoice($invoice, 'TEST_HASH');
        
        // Serialize
        $serialized = $cancellation->serialize();
        $this->assertNotEmpty($serialized);
        $this->assertIsString($serialized);
        
        // Deserialize
        $deserialized = InvoiceCancellation::unserialize($serialized);
        $this->assertInstanceOf(InvoiceCancellation::class, $deserialized);
        
        // Verify all properties are preserved
        $this->assertEquals($cancellation->getNumSerieFacturaEmisor(), $deserialized->getNumSerieFacturaEmisor());
        $this->assertEquals($cancellation->getFechaExpedicionFacturaEmisor(), $deserialized->getFechaExpedicionFacturaEmisor());
        $this->assertEquals($cancellation->getNifEmisor(), $deserialized->getNifEmisor());
        $this->assertEquals($cancellation->getHuellaFactura(), $deserialized->getHuellaFactura());
        $this->assertEquals($cancellation->getEstado(), $deserialized->getEstado());
                $this->assertEquals($cancellation->getDescripcionEstado(), $deserialized->getDescripcionEstado());

    }

    public function testInvoiceCancellationFromXml()
    {
        $invoice = $this->buildTestInvoice();
        
        $originalCancellation = InvoiceCancellation::fromInvoice($invoice, 'ORIGINAL_HASH');
        $originalCancellation->setEstado('03')
                           ->setDescripcionEstado('Factura anulada por duplicado');
        
        $xmlString = $originalCancellation->toXmlString();
        
        // Parse from XML
        $parsedCancellation = InvoiceCancellation::fromXml($xmlString);
        
        // Verify all properties are correctly parsed
        $this->assertEquals($originalCancellation->getNumSerieFacturaEmisor(), $parsedCancellation->getNumSerieFacturaEmisor());
        $this->assertEquals($originalCancellation->getFechaExpedicionFacturaEmisor(), $parsedCancellation->getFechaExpedicionFacturaEmisor());
        $this->assertEquals($originalCancellation->getNifEmisor(), $parsedCancellation->getNifEmisor());
        $this->assertEquals($originalCancellation->getHuellaFactura(), $parsedCancellation->getHuellaFactura());
        $this->assertEquals($originalCancellation->getEstado(), $parsedCancellation->getEstado());
                $this->assertEquals($originalCancellation->getDescripcionEstado(), $parsedCancellation->getDescripcionEstado());

    }

    public function testInvoiceCancellationXmlValidation()
    {
        $invoice = $this->buildTestInvoice();
        
        $cancellation = InvoiceCancellation::fromInvoice($invoice, 'VALIDATION_HASH');
        
        $xmlString = $cancellation->toXmlString();
        
        // Verify XML is well-formed
        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($xmlString), 'Generated XML should be well-formed');
        
        // Verify required namespaces
        $doc->loadXML($xmlString);
        $root = $doc->documentElement;
        
        $this->assertEquals('SuministroLRFacturas', $root->nodeName);
        $this->assertEquals('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd', $root->getAttribute('xmlns'));
        $this->assertEquals('http://www.w3.org/2000/09/xmldsig#', $root->getAttribute('xmlns:ds'));
                $this->assertEquals('1.1', $root->getAttribute('Version'));

    }

    public function testInvoiceCancellationDifferentStatusCodes()
    {
        $invoice = $this->buildTestInvoice();
        
        $statusCodes = [
            '01' => 'Factura anulada por solicitud del cliente',
            '02' => 'Factura anulada por error',
            '03' => 'Factura anulada por duplicado',
            '04' => 'Factura anulada por otros motivos'
        ];
        
        foreach ($statusCodes as $code => $description) {
            $cancellation = InvoiceCancellation::fromInvoice($invoice, 'STATUS_HASH_' . $code);
            $cancellation->setEstado($code)
                        ->setDescripcionEstado($description);
            
            $xmlString = $cancellation->toXmlString();
            
                        $this->assertStringContainsString($code, $xmlString);
            $this->assertStringContainsString($description, $xmlString);
        }

    }

    public function testInvoiceCancellationWithNullValues()
    {
        $cancellation = new InvoiceCancellation();
        
        // Test with minimal required values
        $cancellation->setNumSerieFacturaEmisor('MINIMAL-INV')
                    ->setFechaExpedicionFacturaEmisor('2025-01-01')
                    ->setNifEmisor('B12345678')
                    ->setHuellaFactura('MINIMAL_HASH');
        
        $xmlString = $cancellation->toXmlString();
        
        // Should still generate valid XML with default values
        $this->assertNotEmpty($xmlString);
        $this->assertStringContainsString('MINIMAL-INV', $xmlString);
        $this->assertStringContainsString('2025-01-01', $xmlString);
        $this->assertStringContainsString('B12345678', $xmlString);
        $this->assertStringContainsString('MINIMAL_HASH', $xmlString);
                $this->assertStringContainsString('02', $xmlString); // Default estado
        $this->assertStringContainsString('Factura anulada por error', $xmlString); // Default description

    }

    public function testInvoiceCancellationIntegrationWithVerifactu()
    {
        $invoice = $this->buildTestInvoice();
        
        // Simulate the integration with the main Verifactu class
        $cancellation = InvoiceCancellation::fromInvoice($invoice, 'INTEGRATION_HASH');
        
        // Test XML generation
        $xmlString = $cancellation->toXmlString();
        $this->assertNotEmpty($xmlString);
        
        // Test SOAP envelope generation
        $soapEnvelope = $cancellation->toSoapEnvelope();
        $this->assertNotEmpty($soapEnvelope);
        
        // Test serialization for storage
        $serialized = $cancellation->serialize();
        $this->assertNotEmpty($serialized);
        
        // Test that the cancellation can be stored and retrieved
        $deserialized = InvoiceCancellation::unserialize($serialized);
        $this->assertInstanceOf(InvoiceCancellation::class, $deserialized);
        
                // Verify the deserialized object can still generate XML
        $newXmlString = $deserialized->toXmlString();
        $this->assertNotEmpty($newXmlString);
        $this->assertEquals($xmlString, $newXmlString);

    }

    public function testInvoiceCancellationExactXmlFormat()
    {
        $invoice = $this->buildTestInvoice();
        
        $cancellation = InvoiceCancellation::fromInvoice($invoice, 'ABCD1234EF5678901234567890ABCDEF1234567890ABCDEF1234567890ABCDEF12');
        
        $xmlString = $cancellation->toXmlString();
        
        // Verify the exact XML structure matches the required format
        $expectedElements = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<SuministroLRFacturas',
            'xmlns="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/suministro/FacturaLR.xsd"',
            'xmlns:ds="http://www.w3.org/2000/09/xmldsig#"',
            'Version="1.1"',
            '<LRFacturaEntrada>',
            '<IDFactura>',
            '<IDEmisorFactura>',
            '<NumSerieFacturaEmisor>INV-2024-001</NumSerieFacturaEmisor>',
            '<FechaExpedicionFacturaEmisor>15-01-2024</FechaExpedicionFacturaEmisor>',
            '<NIFEmisor>A39200019</NIFEmisor>',
            '<HuellaFactura>ABCD1234EF5678901234567890ABCDEF1234567890ABCDEF1234567890ABCDEF12</HuellaFactura>',
            '</IDEmisorFactura>',
            '</IDFactura>',
            '<EstadoFactura>',
            '<Estado>02</Estado>',
            '<DescripcionEstado>Factura anulada por error</DescripcionEstado>',
            '</EstadoFactura>',
            '</LRFacturaEntrada>',
            '</SuministroLRFacturas>'
        ];
        
        foreach ($expectedElements as $element) {
            $this->assertStringContainsString($element, $xmlString, "XML should contain: $element");
        }
        
        // Verify XML is properly formatted and indented
        $this->assertStringContainsString('  <LRFacturaEntrada>', $xmlString);
        $this->assertStringContainsString('    <IDFactura>', $xmlString);
                $this->assertStringContainsString('      <IDEmisorFactura>', $xmlString);
        $this->assertStringContainsString('        <NumSerieFacturaEmisor>', $xmlString);
    }
} 