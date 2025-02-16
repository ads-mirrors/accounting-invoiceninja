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

    public function testEInvoicePeriodValidationPasses()
    {

        $data = $this->invoice->toArray();
        $data['e_invoice'] = [
            'Invoice' => [
                'InvoicePeriod' => [
                    'StartDate' => '2025-01-01',
                    'EndDate' => '2025-01-01',
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
