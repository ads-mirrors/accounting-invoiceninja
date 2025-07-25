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

namespace App\Services\Scheduler;

use App\Models\Invoice;
use App\Models\Scheduler;
use App\Utils\Traits\MakesHash;
use Carbon\Carbon;

class PaymentSchedule
{
    use MakesHash;

    public function __construct(public Scheduler $scheduler)
    {
    }

    public function run()
    {
        $invoice = Invoice::find($this->decodePrimaryKey($this->scheduler->parameters['invoice_id']));

        // Needs to be draft, partial or paid AND not deleted
        if(!$invoice ||!in_array($invoice->status_id, [Invoice::STATUS_DRAFT, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID]) || $invoice->is_deleted){
            $this->scheduler->forceDelete();
            return;
        }

        $invoice = $invoice->service()->markSent()->save();
        
        $offset = $invoice->company->timezone_offset();
        $schedule = $this->scheduler->parameters['schedule'];
        $schedule_index = 0;
        $next_schedule = false;

        foreach($schedule as $key =>$item){
           
            if(now()->startOfDay()->eq(Carbon::parse($item['date'])->subSeconds($offset)->startOfDay())){
                $next_schedule = $item;
                $schedule_index = $key;
            }

        }

        if(!$next_schedule){
            $this->scheduler->forceDelete();
            return;
        }

        $amount = max($next_schedule['amount'], ($next_schedule['percentage'] * $invoice->amount));
        $amount += $invoice->partial;


        if($amount > $invoice->balance){
            $amount = $invoice->balance;
        }

        $invoice->partial = $amount;
        $invoice->partial_due_date = $item['date'];
        $invoice->due_date = Carbon::parse($item['date'])->addDay()->format('Y-m-d');
        
        $invoice->save();

        if($this->scheduler->parameters['auto_bill']){
            $invoice->service()->autoBill();
        }
        else{
            $invoice->service()->sendEmail();
        }

        $total_schedules = count($schedule);

        if($total_schedules >= $schedule_index + 1){
            $next_run = $schedule[$schedule_index + 1]['date'];
            $this->scheduler->next_run_client = $next_run;
            $this->scheduler->next_run = Carbon::parse($next_run)->addSeconds($offset);
            $this->scheduler->save();
        }
        else {
            $this->scheduler->forceDelete();
        }
    }
}
