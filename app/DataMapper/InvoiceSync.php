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

namespace App\DataMapper;

use App\Casts\InvoiceSyncCast;
use Illuminate\Contracts\Database\Eloquent\Castable;

/**
 * InvoiceSync.
 */
class InvoiceSync implements Castable
{
    public string $qb_id;
    public ?TaxReport $tax_report; // Structured nested object

    public function __construct(array $attributes = [])
    {
        $this->qb_id = $attributes['qb_id'] ?? '';
        $this->tax_report = isset($attributes['tax_report']) 
            ? new TaxReport($attributes['tax_report']) 
            : null; // Handle structured nested object
    }

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     */
    public static function castUsing(array $arguments): string
    {
        return InvoiceSyncCast::class;
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}

/**
 * Tax report object for InvoiceSync - tracks incremental tax history
 */
class TaxReport
{
    public string $nexus;
    public string $country_nexus;
    public string $report_period; // e.g., "2024-Q1", "2024-01"
    public string $last_updated;
    public ?array $tax_summary; // Summary totals
    public ?array $tax_details; // Array of TaxDetail objects
    public ?array $tax_adjustments; // Array of TaxAdjustment objects

    public function __construct(array $attributes = [])
    {
        $this->nexus = $attributes['nexus'] ?? '';
        $this->country_nexus = $attributes['country_nexus'] ?? '';
        $this->report_period = $attributes['report_period'] ?? '';
        $this->last_updated = $attributes['last_updated'] ?? '';
        $this->tax_summary = isset($attributes['tax_summary']) 
            ? new TaxSummary($attributes['tax_summary']) 
            : null;
        $this->tax_details = isset($attributes['tax_details']) 
            ? array_map(fn($detail) => new TaxDetail($detail), $attributes['tax_details'])
            : null;
        $this->tax_adjustments = isset($attributes['tax_adjustments']) 
            ? array_map(fn($adjustment) => new TaxAdjustment($adjustment), $attributes['tax_adjustments'])
            : null;
    }

    public function toArray(): array
    {
        return [
            'nexus' => $this->nexus,
            'country_nexus' => $this->country_nexus,
            'report_period' => $this->report_period,
            'last_updated' => $this->last_updated,
            'tax_summary' => $this->tax_summary?->toArray(),
            'tax_details' => $this->tax_details ? array_map(fn($detail) => $detail->toArray(), $this->tax_details) : null,
            'tax_adjustments' => $this->tax_adjustments ? array_map(fn($adjustment) => $adjustment->toArray(), $this->tax_adjustments) : null,
        ];
    }
}

/**
 * Tax summary with totals for different tax states
 */
class TaxSummary
{
    public float $total_collected; // Tax collected and confirmed
    public float $total_pending; // Tax pending collection
    public float $total_refundable; // Tax that needs to be claimed back
    public float $total_partially_paid; // Tax partially paid
    public float $total_adjustments; // Net adjustments
    public float $net_tax_liability; // Final tax liability
    public ?array $period_totals; // Totals by report period

    public function __construct(array $attributes = [])
    {
        $this->total_collected = $attributes['total_collected'] ?? 0.0;
        $this->total_pending = $attributes['total_pending'] ?? 0.0;
        $this->total_refundable = $attributes['total_refundable'] ?? 0.0;
        $this->total_partially_paid = $attributes['total_partially_paid'] ?? 0.0;
        $this->total_adjustments = $attributes['total_adjustments'] ?? 0.0;
        $this->net_tax_liability = $attributes['net_tax_liability'] ?? 0.0;
        $this->period_totals = isset($attributes['period_totals']) 
            ? array_map(fn($period) => new PeriodTotal($period), $attributes['period_totals'])
            : null;
    }

    public function toArray(): array
    {
        return [
            'total_collected' => $this->total_collected,
            'total_pending' => $this->total_pending,
            'total_refundable' => $this->total_refundable,
            'total_partially_paid' => $this->total_partially_paid,
            'total_adjustments' => $this->total_adjustments,
            'net_tax_liability' => $this->net_tax_liability,
            'period_totals' => $this->period_totals ? array_map(fn($period) => $period->toArray(), $this->period_totals) : null,
        ];
    }
}

/**
 * Period-specific tax totals
 */
class PeriodTotal
{
    public string $report_period;
    public float $collected_in_period;
    public float $pending_in_period;
    public float $refundable_in_period;
    public float $adjustments_in_period;

    public function __construct(array $attributes = [])
    {
        $this->report_period = $attributes['report_period'] ?? '';
        $this->collected_in_period = $attributes['collected_in_period'] ?? 0.0;
        $this->pending_in_period = $attributes['pending_in_period'] ?? 0.0;
        $this->refundable_in_period = $attributes['refundable_in_period'] ?? 0.0;
        $this->adjustments_in_period = $attributes['adjustments_in_period'] ?? 0.0;
    }

    public function toArray(): array
    {
        return [
            'report_period' => $this->report_period,
            'collected_in_period' => $this->collected_in_period,
            'pending_in_period' => $this->pending_in_period,
            'refundable_in_period' => $this->refundable_in_period,
            'adjustments_in_period' => $this->adjustments_in_period,
        ];
    }
}

/**
 * Individual tax detail object with status tracking
 */
class TaxDetail
{
    public string $invoice_id;
    public string $tax_type; // e.g., "state_tax", "city_tax", "county_tax"
    public float $tax_rate;
    public float $taxable_amount;
    public float $tax_amount;
    public float $tax_amount_paid; // Amount actually paid
    public float $tax_amount_remaining; // Amount still pending
    public string $tax_status; // "collected", "pending", "refundable", "partially_paid"
    public string $collection_date; // When tax was collected
    public string $due_date; // When tax is due
    public ?array $payment_history; // Array of PaymentHistory objects
    public ?array $metadata; // Additional tax-specific data

    public function __construct(array $attributes = [])
    {
        $this->invoice_id = $attributes['invoice_id'] ?? '';
        $this->tax_type = $attributes['tax_type'] ?? '';
        $this->tax_rate = $attributes['tax_rate'] ?? 0.0;
        $this->taxable_amount = $attributes['taxable_amount'] ?? 0.0;
        $this->tax_amount = $attributes['tax_amount'] ?? 0.0;
        $this->tax_amount_paid = $attributes['tax_amount_paid'] ?? 0.0;
        $this->tax_amount_remaining = $attributes['tax_amount_remaining'] ?? 0.0;
        $this->tax_status = $attributes['tax_status'] ?? 'pending';
        $this->collection_date = $attributes['collection_date'] ?? '';
        $this->due_date = $attributes['due_date'] ?? '';
        $this->payment_history = isset($attributes['payment_history']) 
            ? array_map(fn($payment) => new PaymentHistory($payment), $attributes['payment_history'])
            : null;
        $this->metadata = $attributes['metadata'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'tax_type' => $this->tax_type,
            'tax_rate' => $this->tax_rate,
            'taxable_amount' => $this->taxable_amount,
            'tax_amount' => $this->tax_amount,
            'tax_amount_paid' => $this->tax_amount_paid,
            'tax_amount_remaining' => $this->tax_amount_remaining,
            'tax_status' => $this->tax_status,
            'collection_date' => $this->collection_date,
            'due_date' => $this->due_date,
            'payment_history' => $this->payment_history ? array_map(fn($payment) => $payment->toArray(), $this->payment_history) : null,
            'metadata' => $this->metadata,
        ];
    }
}

/**
 * Payment history for tracking partial payments across periods
 */
class PaymentHistory
{
    public string $payment_id;
    public string $payment_date;
    public string $report_period; // Which period this payment belongs to
    public float $payment_amount;
    public float $tax_amount_paid; // Tax portion of this payment
    public string $payment_method;
    public string $status; // "processed", "pending", "failed"
    public ?array $allocation_details; // How the payment was allocated

    public function __construct(array $attributes = [])
    {
        $this->payment_id = $attributes['payment_id'] ?? '';
        $this->payment_date = $attributes['payment_date'] ?? '';
        $this->report_period = $attributes['report_period'] ?? '';
        $this->payment_amount = $attributes['payment_amount'] ?? 0.0;
        $this->tax_amount_paid = $attributes['tax_amount_paid'] ?? 0.0;
        $this->payment_method = $attributes['payment_method'] ?? '';
        $this->status = $attributes['status'] ?? 'processed';
        $this->allocation_details = $attributes['allocation_details'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'payment_id' => $this->payment_id,
            'payment_date' => $this->payment_date,
            'report_period' => $this->report_period,
            'payment_amount' => $this->payment_amount,
            'tax_amount_paid' => $this->tax_amount_paid,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'allocation_details' => $this->allocation_details,
        ];
    }
}

/**
 * Tax adjustment for status changes and corrections
 */
class TaxAdjustment
{
    public string $adjustment_id;
    public string $original_invoice_id;
    public string $adjustment_type; // "refund", "correction", "status_change"
    public string $adjustment_reason; // "invoice_cancelled", "tax_rate_change", "exemption_applied"
    public float $adjustment_amount;
    public string $adjustment_date;
    public string $status; // "pending", "approved", "processed"
    public ?array $supporting_documents; // References to supporting docs

    public function __construct(array $attributes = [])
    {
        $this->adjustment_id = $attributes['adjustment_id'] ?? '';
        $this->original_invoice_id = $attributes['original_invoice_id'] ?? '';
        $this->adjustment_type = $attributes['adjustment_type'] ?? '';
        $this->adjustment_reason = $attributes['adjustment_reason'] ?? '';
        $this->adjustment_amount = $attributes['adjustment_amount'] ?? 0.0;
        $this->adjustment_date = $attributes['adjustment_date'] ?? '';
        $this->status = $attributes['status'] ?? 'pending';
        $this->supporting_documents = $attributes['supporting_documents'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'adjustment_id' => $this->adjustment_id,
            'original_invoice_id' => $this->original_invoice_id,
            'adjustment_type' => $this->adjustment_type,
            'adjustment_reason' => $this->adjustment_reason,
            'adjustment_amount' => $this->adjustment_amount,
            'adjustment_date' => $this->adjustment_date,
            'status' => $this->status,
            'supporting_documents' => $this->supporting_documents,
        ];
    }
}
