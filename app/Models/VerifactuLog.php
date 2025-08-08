<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice;

/**
 * @property int $id
 * @property int $company_id
 * @property int $invoice_id
 * @property string $nif
 * @property \Carbon\Carbon $date
 * @property string $invoice_number
 * @property string $hash
 * @property string $previous_hash
 * @property string $status
 * @property object|null $response
 * @property string $state
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\Invoice $invoice
 */
class VerifactuLog extends Model
{
    public $timestamps = true;
    
    protected $casts = [
        'date' => 'date',
        'response' => 'object',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deserialize()
    {
        return Invoice::unserialize($this->state);
    }
}
