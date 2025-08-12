<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Services\EDocument\Standards\Verifactu;

use App\Utils\Ninja;
use App\Models\Invoice;
use App\Libraries\MultiDB;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Mail;
use Illuminate\Mail\Mailables\Address;

class SendToAeat implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 5;

    public $deleteWhenMissingModels = true;
    
/**
 * Modification Invoices - (modify) Generates a F3 document which replaces the original invoice. And becomes the new invoice.
 * Create Invoices - (create) Generates a F1 document.
 * Cancellation Invoices - (cancel) Generates a R3 document with full negative  values of the original invoice.
 */


    /**
     * __construct
     *
     * @param  int $invoice_id
     * @param  Company $company
     * @param  string $action create, modify, cancel
     * @return void
     */
    public function __construct(private int $invoice_id, private Company $company, private string $action)
    {
    }

    public function backoff()
    {
        return [5, 30, 240, 3600, 7200];
    }

    public function handle()
    {
        MultiDB::setDB($this->company->db);

        $invoice = Invoice::withTrashed()->find($this->invoice_id);

    }

    public function middleware()
    {
        return [new WithoutOverlapping("send_to_aeat_{$this->company->company_key}")];
    }

    public function failed($exception = null)
    {
        nlog($exception);
    }
}