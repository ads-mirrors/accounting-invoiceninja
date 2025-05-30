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

namespace App\Jobs\RecurringInvoice;

use App\Models\User;
use App\Models\Company;
use App\Libraries\MultiDB;
use App\Models\RecurringInvoice;
use App\Events\Socket\RefetchEntity;
use App\Jobs\BaseJob;

class UpdateRecurring extends BaseJob
{
    public $tries = 1;

    public function __construct(public array $ids, public Company $company, public User $user, protected string $action, protected float $percentage = 0)
    {
        nlog("UpdateRecurring job constructed with IDs: " . implode(',', $ids) . " Action: {$action}");
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        nlog("UpdateRecurring job STARTING - this proves it's being executed");
        
        MultiDB::setDb($this->company->db);

        nlog("UpdateRecurring");
        $this->user->setCompany($this->company);

        RecurringInvoice::query()->where('company_id', $this->company->id)
            ->whereIn('id', $this->ids)
            ->chunk(100, function ($recurring_invoices) {
                foreach ($recurring_invoices as $recurring_invoice) {
                    
                        if ($this->action == 'update_prices') {
                            $recurring_invoice->service()->updatePrice();
                        } elseif ($this->action == 'increase_prices') {
                            $recurring_invoice->service()->increasePrice($this->percentage);
                        }
                    
                }
            });

        event(new RefetchEntity('recurring_invoices', null, $this->user));
        
        nlog("UpdateRecurring job COMPLETED successfully");
    }

    protected function getJobProperties(): array
    {
        return [
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'action' => $this->action,
            'ids_count' => count($this->ids),
            'ids' => $this->ids,
            'percentage' => $this->percentage,
        ];
    }

    protected function handleSpecificFailure(\Throwable $exception = null): void
    {
        nlog("UpdateRecurring specific failure handler called");
        if ($exception) {
            nlog("UpdateRecurring failed with: " . $exception->getMessage());
        }
    }

    protected function shouldDisableFailedJobStorage(): bool
    {
        return true; // Matches existing behavior
    }
}
