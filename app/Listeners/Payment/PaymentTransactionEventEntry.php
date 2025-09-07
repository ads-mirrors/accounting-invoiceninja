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

use App\Models\Invoice;
use App\Models\Activity;
use App\Models\TransactionEvent;
use Illuminate\Support\Collection;
use App\DataMapper\TaxReport\TaxDetail;
use App\DataMapper\TaxReport\TaxReport;
use App\DataMapper\TaxReport\TaxSummary;
use App\Repositories\ActivityRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\DataMapper\TransactionEventMetadata;
use App\Libraries\MultiDB;
use App\Models\Payment;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;

class PaymentTransactionEventEntry implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;

    private float $paid_ratio;

    private Collection $payments;

    /**
     */
    public function __construct(private Payment $payment, private array $invoice_ids, private string $db, private mixed $invoice_adjustment = 0, private bool $is_deleted = false)
    {}

    public function handle()
    {
        
        //payment vs refunded
        MultiDB::setDb($this->db);

        $this->payments = $this->payment
                            ->invoices()
                            ->get()
                            ->filter(function($invoice){
                                //only insert adjustment entries if we are after the end of the month!!
                                return Carbon::parse($invoice->date)->endOfMonth()->isBefore(now()->addSeconds($this->payment->company->timezone_offset()));
                            })
                            ->map(function ($invoice) {
                                return [
                                    'number' => $this->payment->number,
                                    'amount' => $invoice->pivot->amount,
                                    'refunded' => $invoice->pivot->refunded,
                                    'date' => $invoice->pivot->created_at->format('Y-m-d'),
                                ];
                        });

        Invoice::withTrashed()
                ->whereIn('id', $this->invoice_ids)
                ->get()
                ->filter(function($invoice){
                    //only insert adjustment entries if we are after the end of the month!!
                    return Carbon::parse($invoice->date)->endOfMonth()->isBefore(now()->addSeconds($this->payment->company->timezone_offset()));
                })
                ->each(function($invoice){

                $this->setPaidRatio($invoice);

                //delete any other payment mutations here if this is a delete event, the refunds are redundant in this time period
                $invoice->transaction_events()
                        ->where('event_id', TransactionEvent::PAYMENT_REFUNDED)
                        ->where('period', now()->endOfMonth()->format('Y-m-d'))
                        ->delete();
                
                TransactionEvent::create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'client_balance' => $invoice->client->balance,
                    'client_paid_to_date' => $invoice->client->paid_to_date,
                    'client_credit_balance' => $invoice->client->credit_balance,
                    'invoice_balance' => $invoice->balance ?? 0,
                    'invoice_amount' => $invoice->amount ?? 0  ,
                    'invoice_partial' => $invoice->partial ?? 0,
                    'invoice_paid_to_date' => $invoice->paid_to_date ?? 0,
                    'invoice_status' => $invoice->is_deleted ? 7 : $invoice->status_id,
                    'event_id' => $this->is_deleted ? TransactionEvent::PAYMENT_DELETED : TransactionEvent::PAYMENT_REFUNDED,
                    'timestamp' => now()->timestamp,
                    'metadata' => $this->getMetadata($invoice),
                    'period' => now()->endOfMonth()->format('Y-m-d'),
                    'payment_id' => $this->payment->id,
                    'payment_amount' => $this->payment->amount,
                    'payment_refunded' => $this->payment->refunded,
                    'payment_applied' => $this->payment->applied,
                    'payment_status' => $this->payment->status_id,
                ]);
        });
    }

    private function setPaidRatio(Invoice $invoice): self
    {
        if ($invoice->amount == 0) {
            $this->paid_ratio = 0;
            return $this;
        }

        $this->paid_ratio = $invoice->paid_to_date / $invoice->amount;

        return $this;
    }

    private function calculateRatio(float $amount): float
    {
        return round($amount * $this->paid_ratio, 2);
    }

    /**
     * Existing tax details are not deleted, but pending taxes are set to 0
     *
     * @param  mixed $invoice
     */
    private function getRefundedMetaData($invoice)
    {

        $calc = $invoice->calc();

        $details = [];

        $taxes = array_merge($calc->getTaxMap()->merge($calc->getTotalTaxMap())->toArray());

        foreach ($taxes as $tax) {

            $base_amount = $tax['base_amount'] ?? $calc->getNetSubtotal();
            

            $tax_detail = [
                'tax_name' => $tax['name'],
                'tax_rate' => $tax['tax_rate'],
                'taxable_amount' => $base_amount,
                'tax_amount' => $tax['total'],
                'tax_amount_paid' => $this->calculateRatio($tax['total']),
                'tax_amount_remaining' => round($tax['total'] - $this->calculateRatio($tax['total']), 2),
            ];
            $details[] = $tax_detail;
        }

        return new TransactionEventMetadata([
            'tax_report' => [
                'tax_details' => $details,
                'payment_history' => $this->payments->toArray(),
                'tax_summary' => [
                    'total_taxes' => $invoice->total_taxes,
                    'total_paid' => $this->getTotalTaxPaid($invoice),
                    'adjustment' => round($invoice->total_taxes - $this->getTotalTaxPaid($invoice), 2) * -1,
                    'status' => 'adjustment',
                ],
            ],
        ]);

    }

    /**
     * Set all tax details to 0
     *
     * @param  mixed $invoice
     */
    private function getDeletedMetaData($invoice)
    {

        $calc = $invoice->calc();

        $details = [];

        $taxes = array_merge($calc->getTaxMap()->merge($calc->getTotalTaxMap())->toArray());

        foreach ($taxes as $tax) {

            
            $base_amount = $tax['base_amount'] ?? $calc->getNetSubtotal();
            
            if($this->invoice_adjustment > 0)
                $tax_amount_paid = round(($this->invoice_adjustment / ($base_amount+$tax['total'])) * $tax['total'], 2);
            else {
                $tax_amount_paid = $this->calculateRatio($tax['total']);
            }

            $tax_detail = [
                'tax_name' => $tax['name'],
                'tax_rate' => $tax['tax_rate'],
                'taxable_amount' => $base_amount,
                'tax_amount' => $tax['total'],
                'tax_amount_paid' => $tax_amount_paid,
                'tax_amount_remaining' => 0,
                'tax_status' => 'payment_deleted',
            ];

            $details[] = $tax_detail;
        }

        return new TransactionEventMetadata([
            'tax_report' => [
                'tax_details' => $details,
                'payment_history' => $this->payments->toArray(),
                'tax_summary' => [
                    'total_taxes' => $invoice->total_taxes,
                    'total_paid' => $this->getTotalTaxPaid($invoice),
                    'adjustment' => round($invoice->total_taxes - $this->getTotalTaxPaid($invoice), 2) * -1,
                    'status' => 'adjustment',
                ],
            ],
        ]);

    }

    private function getMetadata($invoice)
    {

        if ($this->payment->is_deleted) {
            return $this->getDeletedMetaData($invoice);
        } else {
            return $this->getRefundedMetaData($invoice);
        }

    }

    private function getTotalTaxPaid($invoice)
    {
        if ($invoice->amount == 0) {
            return 0;
        }

        $total_paid = $this->payments->sum('amount') - $this->payments->sum('refunded');

        return round($invoice->total_taxes * ($total_paid / $invoice->amount), 2);

    }

    public function middleware()
    {
        return [(new WithoutOverlapping("payment_transaction_event_entry_".$this->payment->id.'_'.$this->db))->releaseAfter(10)->expireAfter(30)];
    }

    public function failed(\Throwable $exception = null)
    {
        nlog("PaymentTransactionEventEntry::failed");
        nlog($exception->getMessage());
    }
}
