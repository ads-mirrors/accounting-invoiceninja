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
 * Class RestoreDisabledRule.
 */
class RestoreDisabledRule implements ValidationRule
{
    use MakesHash;
    
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (empty($value) || $value != 'restore') {
            return;
        }

        $user = auth()->user();
        
        $company = $user->company();

        /** For verifactu, we do not allow restores of deleted invoices */
        if($company->verifactuEnabled() && Invoice::withTrashed()->whereIn('id', $this->transformKeys(request()->ids))->where('company_id', $company->id)->where('is_deleted', true)->exists()) {
            $fail(ctrans('texts.restore_disabled_verifactu'));
        }

    }
}
