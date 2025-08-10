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
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Class RestoreDisabledRule.
 */
class RestoreDisabledRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {


        if (empty($value) || $value != 'restore') {
            return;
        }

        $user = auth()->user();
        $company = $user->company();

        /** For verifactu, we do not allow restores */
        if($company->settings->e_invoice_type == 'verifactu') {
            $fail(ctrans('texts.restore_disabled_verifactu'));
        }

    }
}
