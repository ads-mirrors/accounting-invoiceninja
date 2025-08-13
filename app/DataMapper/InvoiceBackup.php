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
use Illuminate\Support\Collection;

/**
 * InvoiceBackup.
 */
class InvoiceBackup implements Castable
{
    public function __construct(
        public string $guid = '', // The E-INVOICE SENT GUID reference - or enum to advise the document has been successfully sent.
        public Cancellation $cancellation = new Cancellation(0,0), 
        public ?string $parent_invoice_id = null, // The id of the invoice that was cancelled
        public ?string $parent_invoice_number = null, // The number of the invoice that was cancelled
        public ?string $document_type = null, // F1, R2
        public Collection $child_invoice_ids = new Collection(), // Collection of child invoice IDs
        public ?string $redirect = null, // The redirect url for the invoice
        public float $adjustable_amount = 0,
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
            parent_invoice_id: $data['parent_invoice_id'] ?? null,
            parent_invoice_number: $data['parent_invoice_number'] ?? null,
            document_type: $data['document_type'] ?? null,
            child_invoice_ids: isset($data['child_invoice_ids']) ? collect($data['child_invoice_ids']) : new Collection(),
            redirect: $data['redirect'] ?? null,
            adjustable_amount: $data['adjustable_amount'] ?? 0,
        );
    }

    /**
     * Add a child invoice ID to the collection
     */
    public function addChildInvoiceId(string $invoiceId): void
    {
        $this->child_invoice_ids->push($invoiceId);
    }

    /**
     * Remove a child invoice ID from the collection
     */
    public function removeChildInvoiceId(string $invoiceId): void
    {
        $this->child_invoice_ids = $this->child_invoice_ids->reject($invoiceId);
    }

    /**
     * Check if a child invoice ID exists
     */
    public function hasChildInvoiceId(string $invoiceId): bool
    {
        return $this->child_invoice_ids->contains($invoiceId);
    }

    /**
     * Get all child invoice IDs as an array
     */
    public function getChildInvoiceIds(): array
    {
        return $this->child_invoice_ids->toArray();
    }
}

