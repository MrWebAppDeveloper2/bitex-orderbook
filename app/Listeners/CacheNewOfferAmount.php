<?php

namespace App\Listeners;

use App\Events\OfferCreated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CacheNewOfferAmount
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OfferCreated $event): void
    {
        $offer = $event->offer;

        Cache::put('offer.' . $offer->id, ['remaining_amount' => $offer->remaining_amount]);
    }
}
