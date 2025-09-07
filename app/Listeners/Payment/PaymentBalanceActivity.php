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

namespace App\Listeners\Payment;

use App\Libraries\MultiDB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class PaymentBalanceActivity implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;
    
    public $delay = 5;
    
    public $deleteWhenMissingModels = true;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     */
    public function handle($event)
    {
        MultiDB::setDb($event->company->db);

        $event->payment->client->service()->updatePaymentBalance();
    }

    public function middleware($event): array
    {
        return [(new WithoutOverlapping($event->payment->client->client_hash))->releaseAfter(60)->expireAfter(60)];
    }

    public function failed($exception)
    {
        if ($exception) {
            nlog('PaymentBalanceActivity failed ' . $exception->getMessage());
        } 

        // config(['queue.failed.driver' => null]);
    }
}
