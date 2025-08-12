<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace Tests\Feature\EInvoice\Verifactu;

use App\DataMapper\InvoiceItem;
use Tests\TestCase;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Tests\MockAccountData;
use App\Models\Subscription;
use App\Models\ClientContact;
use App\Utils\Traits\MakesHash;
use App\Models\RecurringInvoice;
use App\Factory\InvoiceItemFactory;
use App\Helpers\Invoice\InvoiceSum;
use Illuminate\Support\Facades\Config;
use App\Repositories\InvoiceRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VerifactuApiTest extends TestCase
{
    use MakesHash;
    use DatabaseTransactions;
    use MockAccountData;

    public $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = \Faker\Factory::create();

        $this->makeTestData();
    }

    private function buildData()
    {

        $item = new InvoiceItem();
        $item->quantity = 1;
        $item->product_key = 'product_1';
        $item->notes = 'Product 1';
        $item->cost = 100;
        $item->discount = 0;
        $item->tax_rate1 = 21;
        $item->tax_name1 = 'IVA';

        /** @var \App\Models\Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'number' => Str::random(32),
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(100)->format('Y-m-d'),
            'status_id' => Invoice::STATUS_DRAFT,
            'is_deleted' => false,
            'tax_rate1' => 0,
            'tax_name1' => '',
            'tax_rate2' => 0,
            'tax_name2' => '',
            'tax_rate3' => 0,
            'tax_name3' => '',
            'line_items' => [$item],
            'discount' => 0,
            'uses_inclusive_taxes' => false,
            'exchange_rate' => 1,
            'partial' => 0,
            'partial_due_date' => null,
            'footer' => '',
        ]);

        $repo = new InvoiceRepository();
        $invoice = $repo->save([], $invoice);

        return $invoice;

    }

    public function test_create_modification_invoice()
    {
        
        $this->assertEquals(10, $this->client->balance);

        $settings = $this->company->settings;
        $settings->e_invoice_type = 'verifactu';

        $this->company->settings = $settings;
        $this->company->save();

        $invoice = $this->buildData();
        $invoice->service()->markSent()->save();
        
        $this->assertEquals(121, $invoice->amount);
        $this->assertEquals(121, $invoice->balance);
        $this->assertEquals(131, $this->client->fresh()->balance);

        $invoice2 = $this->buildData();
        
        $items = $invoice2->line_items;
        $items[] = $items[0];
        $invoice2->line_items = $items;
        $invoice2 = $invoice2->calc()->getInvoice();

        $invoice2->service()->markSent()->save();

        $this->assertEquals(373, $this->client->fresh()->balance);
        
        $data = $invoice2->toArray();
        $data['verifactu_modified'] = true;
        $data['modified_invoice_id'] = $invoice->hashed_id;
        $data['number'] = null;
        $data['client_id'] = $this->client->hashed_id;

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices', $data);

        $response->assertStatus(200);

        $arr = $response->json();

        $this->assertEquals($arr['data']['status_id'], Invoice::STATUS_SENT);
        $this->assertEquals($arr['data']['amount'], 242);
        $this->assertEquals($arr['data']['balance'], 242);
        $this->assertEquals($arr['data']['backup']['parent_invoice_id'], $invoice->hashed_id);

        $invoice = $invoice->fresh();

        $this->assertEquals(Invoice::STATUS_REPLACED, $invoice->status_id);
        $this->assertTrue($invoice->backup->child_invoice_ids->contains($arr['data']['id']));

        $this->assertEquals(615, $this->client->fresh()->balance);

        //now create another modification invoice reducing the amounts

        $data = $invoice2->toArray();
        $data['verifactu_modified'] = true;
        $data['modified_invoice_id'] = $arr['data']['id'];
        $data['number'] = null;
        $data['client_id'] = $this->client->hashed_id;
        $data['line_items'] = $invoice2->line_items;

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices', $data);

        $response->assertStatus(200);

        $this->assertEquals(494, $this->client->fresh()->balance);

    }

    public function test_create_modification_invoice_validation_fails()
    {
        $invoice = $this->buildData();;

        $data = $invoice->toArray();
        $data['verifactu_modified'] = true;

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices', $data);

        $response->assertStatus(422);
        
    }

    public function test_create_modification_invoice_validation_fails2()
    {
        $invoice = $this->buildData();;

        $data = $invoice->toArray();
        $data['verifactu_modified'] = true;
        $data['modified_invoice_id'] = "XXX";

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices', $data);

        $response->assertStatus(422);
        
    }

    public function test_create_modification_invoice_validation_fails3()
    {
        $invoice = $this->buildData();;

        $invoice2 = $this->buildData();
        $invoice2->service()->markPaid()->save();

        $data = $invoice->toArray();
        $data['verifactu_modified'] = true;
        $data['modified_invoice_id'] = $invoice2->hashed_id;

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices', $data);

        $response->assertStatus(422);
        
    }

    public function test_create_modification_invoice_validation_fails4()
    {

        $settings = $this->company->settings;
        $settings->e_invoice_type = 'verifactu';

        $this->company->settings = $settings;
        $this->company->save();

        $invoice = $this->buildData();;

        $invoice2 = $this->buildData();
        $invoice2->service()->markSent()->save();

        $data = $invoice->toArray();
        $data['verifactu_modified'] = true;
        $data['modified_invoice_id'] = $invoice2->hashed_id;
        $data['client_id'] = $this->client->hashed_id;
        $data['number'] = null;

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices', $data);

        $response->assertStatus(200);
        
    }

    public function test_cancel_invoice_response()
    {

       $invoice = $this->buildData();

        $invoice->service()->markSent()->save();

        $this->assertEquals($invoice->status_id, Invoice::STATUS_SENT);
        $this->assertEquals($invoice->balance, 121);
        $this->assertEquals($invoice->amount, 121);
        
        $settings = $this->company->settings;
        $settings->e_invoice_type = 'verifactu';

        $this->company->settings = $settings;
        $this->company->save();

        
        $data = [
            'action' => 'cancel',
            'ids' => [$invoice->hashed_id],
            'reason' => 'R3'
        ];

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices/bulk', $data);

        $response->assertStatus(200);

        $arr = $response->json();
// nlog($arr);

        $this->assertEquals($arr['data'][0]['status_id'], Invoice::STATUS_CANCELLED);
        $this->assertEquals($arr['data'][0]['balance'], 121);
        $this->assertEquals($arr['data'][0]['amount'], 121);
        $this->assertNotNull($arr['data'][0]['backup']['child_invoice_ids'][0]);
        

        $credit_invoice = Invoice::find($this->decodePrimaryKey($arr['data'][0]['backup']['child_invoice_ids'][0]));

        $this->assertNotNull($credit_invoice);
        $this->assertEquals($credit_invoice->status_id, Invoice::STATUS_SENT);
        $this->assertEquals($credit_invoice->balance, -121);
        $this->assertEquals($credit_invoice->amount, -121);
        $this->assertEquals($credit_invoice->backup->parent_invoice_id, $invoice->hashed_id);
        $this->assertEquals($credit_invoice->backup->parent_invoice_number, $invoice->number);
    }

    public function test_restore_invoice_validation()
    {
                
        $settings = $this->company->settings;
        $settings->e_invoice_type = 'verifactu';

        $this->company->settings = $settings;
        $this->company->save();

        $data = [
            'action' => 'delete',
            'ids' => [$this->invoice->hashed_id]
        ];
        
        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices/bulk', $data);

        $response->assertStatus(200);

        $arr = $response->json();

        $this->assertTrue($arr['data'][0]['is_deleted']);
        
        $data = [
            'action' => 'restore',
            'ids' => [$this->invoice->hashed_id]
        ];

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices/bulk', $data);

        $response->assertStatus(422);

    }
    

    public function test_restore_invoice_that_is_archived()
    {
                
        $settings = $this->company->settings;
        $settings->e_invoice_type = 'verifactu';

        $this->company->settings = $settings;
        $this->company->save();

        $data = [
            'action' => 'archive',
            'ids' => [$this->invoice->hashed_id]
        ];
        
        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices/bulk', $data);

        $response->assertStatus(200);

        $arr = $response->json();

        $this->assertFalse($arr['data'][0]['is_deleted']);
        
        $data = [
            'action' => 'restore',
            'ids' => [$this->invoice->hashed_id]
        ];

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/invoices/bulk', $data);

        $response->assertStatus(200);

    }

    /**
     * test_update_company_settings
     *
     * Verifactu we do not allow the user to change from the verifactu system nor, do we allow changing the locking feature of invoices
     * @return void
     */
    public function test_update_company_settings()
    {
        // Ensure LARAVEL_START is defined for the middleware
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        Config::set('ninja.environment', 'hosted');
        
        $settings = $this->company->settings;
        $settings->e_invoice_type = 'verifactu';
        $this->company->settings = $settings;
        $this->company->save();

        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->putJson('/api/v1/companies/'.$this->company->hashed_id, $this->company->toArray())
        ->assertStatus(200);


        $settings = $this->company->settings;
        $settings->e_invoice_type = 'Facturae_3.2.2';
        $this->company->settings = $settings;


        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->putJson('/api/v1/companies/'.$this->company->hashed_id, $this->company->toArray())
        ->assertStatus(200);


        $arr = $response->json();

        $this->assertEquals($arr['data']['settings']['e_invoice_type'], 'verifactu');
        $this->assertEquals($arr['data']['settings']['lock_invoices'], 'when_sent');
    }
}