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

namespace App\DataMapper;

use App\Casts\InvoiceBackupCast;
use App\DataMapper\Cancellation;
use Illuminate\Contracts\Database\Eloquent\Castable;

/**
 * InvoiceBackup.
 */
class InvoiceBackup implements Castable
{
    public function __construct(
        public string $guid = '', // The E-INVOICE SENT GUID reference 
        public Cancellation $cancellation = new Cancellation(0,0), 
        public ?string $cancelled_invoice_id = null, // The id of the invoice that was cancelled
        public ?string $cancelled_invoice_number = null, // The number of the invoice that was cancelled
        public ?string $cancellation_reason = null, // The reason for the cancellation
        public ?string $credit_invoice_id = null, // The id of the credit invoice that was created
        public ?string $credit_invoice_number = null, // The number of the credit invoice that was created
        public ?string $redirect = null, // The redirect url for the invoice
        public ?string $modified_invoice_id = null, // The id of the modified invoice (replaces the invoice with replaced_invoice_id)
        public ?string $replaced_invoice_id = null // The id of the replaced invoice (The previous invoice that was replaced by the modified invoice)
    ) {}

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     */
    public static function castUsing(array $arguments): string
    {
        return InvoiceBackupCast::class;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            guid: $data['guid'] ?? '',
            cancellation: Cancellation::fromArray($data['cancellation'] ?? []),
            cancelled_invoice_id: $data['cancelled_invoice_id'] ?? null,
            cancelled_invoice_number: $data['cancelled_invoice_number'] ?? null,
            cancellation_reason: $data['cancellation_reason'] ?? null,
            credit_invoice_id: $data['credit_invoice_id'] ?? null,
            credit_invoice_number: $data['credit_invoice_number'] ?? null,
            redirect: $data['redirect'] ?? null,
            modified_invoice_id: $data['modified_invoice_id'] ?? null,
            replaced_invoice_id: $data['replaced_invoice_id'] ?? null
        );
    }
}

