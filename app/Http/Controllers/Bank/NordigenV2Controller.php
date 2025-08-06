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

namespace App\Http\Controllers\Bank;

use App\Helpers\Bank\Nordigen\Nordigen;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Nordigen\ConfirmNordigenBankIntegrationRequest;
use App\Http\Requests\Nordigen\ConnectNordigenBankIntegrationRequest;
use App\Jobs\Bank\ProcessBankTransactionsNordigen;
use App\Models\BankIntegration;
use App\Models\Company;
use App\Utils\Ninja;
use Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Nordigen\NordigenPHP\Exceptions\NordigenExceptions\NordigenException;

class NordigenController extends BaseController
{
    /**
     * Handles the initial bank connection flow
     */
    public function connect(ConnectNordigenBankIntegrationRequest $request): View|RedirectResponse
    {
    }
    
    /**
     * Handles reconnects confirm /  requisition updates
     *
     */
    public function confirm(ConfirmNordigenBankIntegrationRequest $request): View|RedirectResponse
    {
    }
    
    /**
     * Returns the list of available banking institutions from Nordigen
     *
     */
    public function institutions(Request $request): JsonResponse
    {
        if (!(config('ninja.nordigen.secret_id') && config('ninja.nordigen.secret_key'))) {
            return response()->json(['message' => 'Not yet authenticated with Nordigen Bank Integration service'], 400);
        }

        $nordigen = new Nordigen();

        return response()->json($nordigen->getInstitutions());
    }

}