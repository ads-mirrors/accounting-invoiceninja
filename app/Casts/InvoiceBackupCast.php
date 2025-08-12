<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www/elastic.co/licensing/elastic-license
 */

namespace App\Casts;

use App\DataMapper\InvoiceBackup;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InvoiceBackupCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return new InvoiceBackup();
        }

        $data = json_decode($value, true) ?? [];

        return InvoiceBackup::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return [$key => null];
        }

        // Ensure we're dealing with our object type
        if (! $value instanceof InvoiceBackup) {
            throw new \InvalidArgumentException('Value must be an InvoiceBackup instance.');
        }

        return [
            $key => json_encode([
                'guid' => $value->guid,
                'cancellation' => $value->cancellation ? [
                    'adjustment' => $value->cancellation->adjustment,
                    'status_id' => $value->cancellation->status_id,
                ] : [],
                'cancelled_invoice_id' => $value->cancelled_invoice_id,
                'cancelled_invoice_number' => $value->cancelled_invoice_number,
                'cancellation_reason' => $value->cancellation_reason,
                'credit_invoice_id' => $value->credit_invoice_id,
                'credit_invoice_number' => $value->credit_invoice_number,
                'redirect' => $value->redirect,
                'modified_invoice_id' => $value->modified_invoice_id,
                'replaced_invoice_id' => $value->replaced_invoice_id,
            ])
        ];
    }
}
