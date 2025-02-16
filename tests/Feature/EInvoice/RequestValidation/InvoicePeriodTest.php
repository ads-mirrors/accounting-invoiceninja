<?php

namespace Tests\Feature\EInvoice\RequestValidation;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\MockAccountData;

class InvoicePeriodTest extends TestCase
{
    use MockAccountData;

    protected UpdateInvoiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(
            ThrottleRequests::class
        );

        $this->makeTestData();

    }

    public function testERecurringInvoicePeriodValidationPasses()
    {

        $r = \App\Models\RecurringInvoice::factory()->create(
            [
                'user_id' => $this->user->id,
                'company_id' => $this->company->id,
                'client_id' => $this->client->id,
            ]
        );
            
        $data = $r->toArray();

        $data['client_id'] = $this->client->hashed_id;

        $data['e_invoice'] = [
            'Invoice' => [
             'InvoicePeriod' => [
                [
                    'StartDate' => '2025-01-01',
                    'EndDate' => '2025-01-01',
                    'Description' => 'first day of this month|last day of this month'
                ]    
             ]
            ]
        ];
        
        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->putJson('/api/v1/recurring_invoices/'.$r->hashed_id, $data);

        $response->assertStatus(200);

        $arr = $response->json();

        $this->assertEquals($arr['data']['e_invoice']['Invoice']['InvoicePeriod'][0]['Description'], 'first day of this month|last day of this month');

        $r = $r->fresh();

        $invoice = \App\Factory\RecurringInvoiceToInvoiceFactory::create($r, $r->client);

        $this->assertEquals($invoice->e_invoice->Invoice->InvoicePeriod[0]->StartDate->date, now()->setTimezone($r->client->timezone()->name)->startOfMonth()->startOfDay()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($invoice->e_invoice->Invoice->InvoicePeriod[0]->EndDate->date, now()->setTimezone($r->client->timezone()->name)->endOfMonth()->startOfDay()->format('Y-m-d H:i:s.u'));

    }


    public function testEInvoicePeriodValidationPasses()
    {

        $data['e_invoice'] = [
            'Invoice' => [
             'InvoicePeriod' => [
                [
                    'StartDate' => '2025-01-01',
                    'EndDate' => '2025-01-01',
                    ]    
             ]
            ]
        ];
        
        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->putJson('/api/v1/invoices/'.$this->invoice->hashed_id, $data);

        $response->assertStatus(200);

        $arr = $response->json();

    }


    public function testEInvoicePeriodValidationFails()
    {

        $data = $this->invoice->toArray();
        $data['e_invoice'] = [
            'Invoice' => [
                'InvoicePeriod' => [
                    'notarealvar' => '2025-01-01',
                    'worseVar' => '2025-01-01',
                    'Description' => 'Mustafa'
                ]
            ]
        ];
        
        $response = $this->withHeaders([
            'X-API-SECRET' => config('ninja.api_secret'),
            'X-API-TOKEN' => $this->token,
        ])->putJson('/api/v1/invoices/'.$this->invoice->hashed_id, $data);

        $arr = $response->json();

        nlog($arr);
        $response->assertStatus(422);


    }
}
