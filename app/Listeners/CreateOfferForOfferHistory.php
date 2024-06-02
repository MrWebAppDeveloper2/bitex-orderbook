<?php

namespace App\Listeners;

use App\Events\OfferHistoryCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOfferForOfferHistory
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
    public function handle(OfferHistoryCreated $event): void
    {
        $history = $event->offerHistory;

        $history->offer()->create([
            'remaining_amount' => $history->amount,
            'price' => $history->price,
            'type' => $history->type,
            'service_id' => $history->service_id,
            'user_id' => $history->user_id
        ]);
    }
}
