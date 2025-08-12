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

namespace App\Http\ValidationRules\Invoice;

use Closure;
use App\Models\Invoice;
use App\Utils\Traits\MakesHash;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Class CanGenerateModificationInvoice.
 */
class CanGenerateModificationInvoice implements ValidationRule
{
    use MakesHash;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (empty($value)) {
            return;
        }

        $user = auth()->user();

        $company = $user->company();

        /** For verifactu, we do not allow restores of deleted invoices */
        if (!$company->verifactuEnabled())
            $fail("Verifactu is not enabled for this company");

        $invoice = Invoice::withTrashed()->find($this->decodePrimaryKey($value));
        
        if (is_null($invoice)) {
            $fail("Invoice not found.");
        }elseif($invoice->is_deleted) {
            $fail("Cannot create a modification invoice for a deleted invoice.");
        } elseif($invoice->status_id === Invoice::STATUS_DRAFT){
            $fail("Cannot create a modification invoice for a draft invoice.");
        } elseif(in_array($invoice->status_id, [Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])) {
            $fail("Cannot create a modification invoice where a payment has been made.");
        } elseif($invoice->status_id === Invoice::STATUS_CANCELLED  ) {
            $fail("Cannot create a modification invoice for a cancelled invoice.");
        } elseif($invoice->status_id === Invoice::STATUS_REPLACED) {
            $fail("Cannot create a modification invoice for a replaced invoice.");
        } elseif($invoice->status_id === Invoice::STATUS_REVERSED) {
            $fail("Cannot create a modification invoice for a reversed invoice.");
        // } elseif ($invoice->status_id !== Invoice::STATUS_SENT) {
        //     $fail("Cannot create a modification invoice.");
        // } elseif($invoice->amount <= 0){
        //     $fail("Cannot create a modification invoice for an invoice with an amount less than 0.");
        }

    }
}
