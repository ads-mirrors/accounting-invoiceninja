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

/**
 * Tax summary with totals for different tax states
 */
class TaxSummary
{
    public float $total_taxes; // Tax collected and confirmed (ie. Invoice Paid)
    public float $total_paid; // Tax pending collection (Outstanding tax of balance owing)
    public string $status;

    public function __construct(array $attributes = [])
    {
        $this->total_taxes = $attributes['total_taxes'] ?? 0.0;
        $this->total_paid = $attributes['total_paid'] ?? 0.0;
        $this->status = $attributes['status'] ?? 'updated';
    }

    public function toArray(): array
    {
        return [
            'total_taxes' => $this->total_taxes,
            'total_paid' => $this->total_paid,
            'status' => $this->status,
        ];
    }
}
