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

namespace App\Services\Invoice;

use App\Events\Invoice\InvoiceWasCancelled;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Services\AbstractService;
use App\Utils\Ninja;
use App\Utils\Traits\GeneratesCounter;
use stdClass;

class HandleCancellation extends AbstractService
{
    use GeneratesCounter;

    public function __construct(private Invoice $invoice, private ?string $reason = null)
    {
    }

    public function run()
    {
        /* Check again!! */
        if (! $this->invoice->invoiceCancellable($this->invoice)) {
            return $this->invoice;
        }

        if($this->invoice->company->verifactuEnabled()) {
            return $this->verifactuCancellation();
        }

        $adjustment = ($this->invoice->balance < 0) ? abs($this->invoice->balance) : $this->invoice->balance * -1;

        $this->backupCancellation($adjustment);

        //set invoice balance to 0
        $this->invoice->ledger()->updateInvoiceBalance($adjustment, "Invoice {$this->invoice->number} cancellation");

        $this->invoice->balance = 0;
        $this->invoice = $this->invoice->service()->setStatus(Invoice::STATUS_CANCELLED)->save();

        // $this->invoice->client->service()->updateBalance($adjustment)->save();
        $this->invoice->client->service()->calculateBalance();

        $this->invoice->service()->workFlow()->save();

        event(new InvoiceWasCancelled($this->invoice, $this->invoice->company, Ninja::eventVars(auth()->user() ? auth()->user()->id : null)));

        event('eloquent.updated: App\Models\Invoice', $this->invoice);

        return $this->invoice;
    }


    private function verifactuCancellation(): Invoice
    {

        $replicated_invoice = $this->invoice->replicate();

        $this->invoice = $this->invoice->service()->setStatus(Invoice::STATUS_CANCELLED)->save();
        $this->invoice->service()->workFlow()->save();

        $replicated_invoice->status_id = Invoice::STATUS_DRAFT;
        $replicated_invoice->date = now()->format('Y-m-d');
        $replicated_invoice->due_date = null;
        $replicated_invoice->partial = 0;
        $replicated_invoice->partial_due_date = null;
        $replicated_invoice->number = null;
        $replicated_invoice->amount = 0;
        $replicated_invoice->balance = 0;
        $replicated_invoice->paid_to_date = 0;

        $items = $replicated_invoice->line_items;

            foreach($items as &$item) {
                $item->quantity = $item->quantity * -1;
            }

        $replicated_invoice->line_items = $items;

        $backup = new \App\DataMapper\InvoiceBackup(
            cancelled_invoice_id: $this->invoice->hashed_id,
            cancelled_invoice_number: $this->invoice->number,
            cancellation_reason: $this->reason ?? 'R3'
        );

        $replicated_invoice->backup = $backup;

        $invoice_repository = new InvoiceRepository();
        $replicated_invoice = $invoice_repository->save([], $replicated_invoice);
        $replicated_invoice->service()->markSent()->sendVerifactu()->save();

        $old_backup = new \App\DataMapper\InvoiceBackup(
            credit_invoice_id: $replicated_invoice->hashed_id,
            credit_invoice_number: $replicated_invoice->number,
            cancellation_reason: $this->reason ?? 'R3'
        );

        $this->invoice->backup = $old_backup;
        $this->invoice->saveQuietly();
        $this->invoice->fresh();
        
        event(new InvoiceWasCancelled($this->invoice, $this->invoice->company, Ninja::eventVars(auth()->user() ? auth()->user()->id : null)));
        event('eloquent.updated: App\Models\Invoice', $this->invoice);

        return $this->invoice;
    }

    public function reverse()
    {
        /* Will turn the negative cancellation amount to a positive adjustment*/
        
        $cancellation = $this->invoice->backup->cancellation;
        $adjustment = $cancellation->adjustment * -1;

        $this->invoice->ledger()->updateInvoiceBalance($adjustment, "Invoice {$this->invoice->number} reversal");

        $this->invoice = $this->invoice->fresh();

        /* Reverse the invoice status and balance */
        $this->invoice->balance += $adjustment;
        $this->invoice->status_id = $cancellation->status_id;

        $this->invoice->client->service()->updateBalance($adjustment)->save();

        $this->invoice->client->service()->calculateBalance();

        /* Clear the cancellation data */
        $this->invoice->backup->cancellation->adjustment = 0;
        $this->invoice->backup->cancellation->status_id = 0;
        $this->invoice->saveQuietly();
        $this->invoice->fresh();

        return $this->invoice;
    }

    /**
     * Backup the cancellation in case we ever need to reverse it.
     *
     * @param  float $adjustment  The amount the balance has been reduced by to cancel the invoice
     * @return void
     */
    private function backupCancellation($adjustment)
    {

        // Direct assignment to properties
        $this->invoice->backup->cancellation->adjustment = $adjustment;
        $this->invoice->backup->cancellation->status_id = $this->invoice->status_id;
        
        $this->invoice->saveQuietly();
    }
}
