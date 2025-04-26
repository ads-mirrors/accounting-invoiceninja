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

namespace App\Http\Requests\Credit;

use App\Http\Requests\Request;
use App\Http\ValidationRules\Credit\UniqueCreditNumberRule;
use App\Http\ValidationRules\Credit\ValidInvoiceCreditRule;
use App\Models\Credit;
use App\Utils\Traits\CleanLineItems;
use App\Utils\Traits\MakesHash;
use Illuminate\Validation\Rule;

class StoreCreditRequest extends Request
{
    use MakesHash;
    use CleanLineItems;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $user->can('create', Credit::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $rules = [];

        $rules['file'] = 'bail|sometimes|array';
        $rules['file.*'] = $this->fileValidation();
        $rules['documents'] = 'bail|sometimes|array';
        $rules['documents.*'] = $this->fileValidation();

        $rules['client_id'] = 'required|exists:clients,id,company_id,'.$user->company()->id;

        $rules['invitations'] = 'sometimes|bail|array';
        $rules['invitations.*.client_contact_id'] = 'bail|required|distinct';
        $rules['number'] = ['nullable', Rule::unique('credits')->where('company_id', $user->company()->id)];
        $rules['discount'] = 'sometimes|numeric|max:99999999999999';
        $rules['is_amount_discount'] = ['boolean'];
        $rules['tax_rate1'] = 'bail|sometimes|numeric';
        $rules['tax_rate2'] = 'bail|sometimes|numeric';
        $rules['tax_rate3'] = 'bail|sometimes|numeric';
        $rules['tax_name1'] = 'bail|sometimes|string|nullable';
        $rules['tax_name2'] = 'bail|sometimes|string|nullable';
        $rules['tax_name3'] = 'bail|sometimes|string|nullable';
        $rules['exchange_rate'] = 'bail|sometimes|numeric';
        $rules['amount'] = ['sometimes', 'bail', 'numeric', 'max:99999999999999'];

        $rules['custom_surcharge1'] = ['sometimes', 'nullable', 'bail', 'numeric', 'max:99999999999999'];
        $rules['custom_surcharge2'] = ['sometimes', 'nullable', 'bail', 'numeric', 'max:99999999999999'];
        $rules['custom_surcharge3'] = ['sometimes', 'nullable', 'bail', 'numeric', 'max:99999999999999'];
        $rules['custom_surcharge4'] = ['sometimes', 'nullable', 'bail', 'numeric', 'max:99999999999999'];

        $rules['date'] = 'bail|sometimes|date:Y-m-d';

        if ($this->invoice_id) {
            $rules['invoice_id'] = new ValidInvoiceCreditRule();
        }

        $rules['line_items'] = 'array';

        $rules['location_id'] = ['nullable', 'sometimes','bail',Rule::exists('locations', 'id')->where('company_id', $user->company()->id)->where('client_id', $this->client_id)];

        return $rules;
    }

    public function prepareForValidation()
    {
        $input = $this->all();

        if ($this->file('documents') instanceof \Illuminate\Http\UploadedFile) {
            $this->files->set('documents', [$this->file('documents')]);
        }

        if ($this->file('file') instanceof \Illuminate\Http\UploadedFile) {
            $this->files->set('file', [$this->file('file')]);
        }

        if (array_key_exists('is_amount_discount', $input) && is_bool($input['is_amount_discount'])) {
            $input['is_amount_discount'] = $this->setBoolean($input['is_amount_discount']);
        } else {
            $input['is_amount_discount'] = false;
        }

        if (isset($input['exchange_rate'])) {
            $input['exchange_rate'] = $this->parseFloat($input['exchange_rate']);
        }

        if (isset($input['amount'])) {
            $input['amount'] = $this->parseFloat($input['amount']);
        }

        if (isset($input['custom_surcharge1'])) {
            $input['custom_surcharge1'] = $this->parseFloat($input['custom_surcharge1']);
        }

        if (isset($input['custom_surcharge2'])) {
            $input['custom_surcharge2'] = $this->parseFloat($input['custom_surcharge2']);
        }

        if (isset($input['custom_surcharge3'])) {
            $input['custom_surcharge3'] = $this->parseFloat($input['custom_surcharge3']);
        }

        if (isset($input['custom_surcharge4'])) {
            $input['custom_surcharge4'] = $this->parseFloat($input['custom_surcharge4']);
        }

        if (array_key_exists('design_id', $input) && is_string($input['design_id'])) {
            $input['design_id'] = $this->decodePrimaryKey($input['design_id']);
        }

        if (isset($input['partial']) && $input['partial'] == 0) {
            $input['partial_due_date'] = null;
        }

        $input = $this->decodePrimaryKeys($input);

        $input['line_items'] = isset($input['line_items']) ? $this->cleanItems($input['line_items']) : [];
        $input['line_items'] = $this->cleanFeeItems($input['line_items']);
        $input['amount'] = $this->entityTotalAmount($input['line_items']);

        if (isset($input['footer']) && $this->hasHeader('X-REACT')) {
            $input['footer'] = str_replace("\n", "", $input['footer']);
        }
        if (isset($input['public_notes']) && $this->hasHeader('X-REACT')) {
            $input['public_notes'] = str_replace("\n", "", $input['public_notes']);
        }
        if (isset($input['private_notes']) && $this->hasHeader('X-REACT')) {
            $input['private_notes'] = str_replace("\n", "", $input['private_notes']);
        }
        if (isset($input['terms']) && $this->hasHeader('X-REACT')) {
            $input['terms'] = str_replace("\n", "", $input['terms']);
        }

        $this->replace($input);
    }
}
