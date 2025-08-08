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

class VerifactuFeatureTest extends TestCase
{
    private $account;
    private $company;
    private $user;
    private $cu;
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
            'name' => 'bob',
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
            'date' => now()->addSeconds($this->client->timezone_offset())->format('Y-m-d'),
            'next_send_date' => null,
            'due_date' => now()->addSeconds($this->client->timezone_offset())->addDays(5)->format('Y-m-d'),
            'last_sent_date' => now()->addSeconds($this->client->timezone_offset()),
            'reminder_last_sent' => null,
            'status_id' => Invoice::STATUS_DRAFT,
            'amount' => 10,
            'balance' => 10,
            'line_items' => $line_items,
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

        $invoice = $this->buildData();

        $this->assertNotNull($invoice);
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
    }
}