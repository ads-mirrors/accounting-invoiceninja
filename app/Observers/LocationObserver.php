<?php

namespace App\Observers;

use App\Models\Location;
use App\Jobs\Client\UpdateLocationTaxData;
class LocationObserver
{
    /**
     * Handle the location "created" event.
     *
     * @param Location $location
     * @return void
     */
    public function created(Location $location)
    {
       
        if ($location->client->country_id == 840 && $location->client->company->calculate_taxes && !$location->client->company->account->isFreeHostedClient()) {
            UpdateLocationTaxData::dispatch($location, $location->client->company);
        }

    }

    /**
     * Handle the location "updated" event.
     *
     * @param Location $location
     * @return void
     */
    public function updated(Location $location)
    {
        
        if ($location->getOriginal('postal_code') != $location->postal_code && $location->country_id == 840 && $location->client->company->calculate_taxes && !$location->client->company->account->isFreeHostedClient()) {
            UpdateLocationTaxData::dispatch($location, $location->client->company);
        }

    }

    /**
     * Handle the location "deleted" event.
     *
     * @param Location $location
     * @return void
     */
    public function deleted(Location $location)
    {
        
    }

    /**
     * Handle the location "restored" event.
     *
     * @param Location $location
     * @return void
     */
    public function restored(Location $location)
    {
        
    }

    /**
     * Handle the location "force deleted" event.
     *
     * @param Location $location
     * @return void
     */
    public function forceDeleted(Location $location)
    {
        //
    }
}
