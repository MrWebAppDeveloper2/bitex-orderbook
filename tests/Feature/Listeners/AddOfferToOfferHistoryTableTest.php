<?php

namespace Tests\Feature\Listeners;

use App\Events\OfferCreated;
use App\Listeners\AddOfferToOfferHistoryTable;
use App\Models\Offer;
use App\Models\OfferHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AddOfferToOfferHistoryTableTest extends TestCase
{
    public function test_listening_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, AddOfferToOfferHistoryTable::class);
    }

    public function test_add_offer_in_offer_histories_table()
    {
        $offer = Offer::factory()->create();

        $event = new OfferCreated($offer);

        $listener = app()->make(AddOfferToOfferHistoryTable::class);

        $listener->handle($event);

        $this->assertDatabaseHas(OfferHistory::class, [
            'id' => $offer->id,
            'amount' => $offer->remaining_amount,
            'price' => $offer->price,
            'type' => $offer->type,
            'service_id' => $offer->service_id,
            'user_id' => $offer->user_id,
        ]);
    }
}
