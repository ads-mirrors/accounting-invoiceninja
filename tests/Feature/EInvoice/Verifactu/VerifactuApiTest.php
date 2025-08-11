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