<?php

namespace App\Listeners;

use App\Events\OfferCreated;
use App\Models\OfferHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddOfferToOfferHistoryTable
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
        $data = $event->offer->only(['price', 'type', 'service_id', 'user_id']);

        $data['amount'] = $event->offer->remaining_amount;

        OfferHistory::create($data);
    }
}
