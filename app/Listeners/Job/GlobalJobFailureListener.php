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

namespace App\Listeners\Job;

use App\DataMapper\Analytics\JobFailureAnalytics;
use Illuminate\Queue\Events\JobFailed;
use Turbo124\Beacon\Facades\LightLogs;

class GlobalJobFailureListener
{
    /**
     * Handle the job failure event.
     */
    public function handle(JobFailed $event): void
    {
        $name = $event->job->resolveName();
        $exception = $event->exception->getMessage();

        LightLogs::create(new JobFailureAnalytics($name, $exception))->send();
    }
} 