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

namespace App\Services\EDocument\Standards\Validation\Verifactu;

use App\Services\EDocument\Standards\Validation\EntityLevelInterface;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;

class EntityLevel implements EntityLevelInterface
{
    public function checkClient(Client $client): array
    {
        return [];
    }

    public function checkCompany(Company $company): array
    {
        return [];
    }

    public function checkInvoice(Invoice $invoice): array
    {
        return [];
    }

    private function testClientState(Client $client): array
}