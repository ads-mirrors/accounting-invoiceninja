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

namespace App\DataMapper\TaxReport;

use App\DataMapper\TaxReport\TaxDetail;
use App\DataMapper\TaxReport\TaxSummary;

/**
 * Tax report object for InvoiceSync - tracks incremental tax history
 */
class TaxReport
{
    public ?TaxSummary $tax_summary; // Summary totals
    public ?array $tax_details; // Array of TaxDetail objects (includes adjustments)
    public float $amount; // The total amount of the invoice
    public ?array $payment_history; // Array of PaymentHistory objects

    public function __construct(array $attributes = [])
    {
        $this->tax_summary = isset($attributes['tax_summary'])
            ? new TaxSummary($attributes['tax_summary'])
            : null;
        $this->tax_details = isset($attributes['tax_details'])
            ? array_map(fn ($detail) => new TaxDetail($detail), $attributes['tax_details'])
            : null;
        $this->payment_history = isset($attributes['payment_history'])
            ? array_map(fn ($payment) => new PaymentHistory($payment), $attributes['payment_history'])
            : null;
    }

    public function toArray(): array
    {
        return [
            'tax_summary' => $this->tax_summary?->toArray(),
            'tax_details' => $this->tax_details ? array_map(fn ($detail) => $detail->toArray(), $this->tax_details) : null,
            'payment_history' => $this->payment_history ? array_map(fn ($payment) => $payment->toArray(), $this->payment_history) : null,
        ];
    }
}
