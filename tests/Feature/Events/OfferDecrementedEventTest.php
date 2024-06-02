<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\Offer;
use App\Events\OfferCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OfferDecrementedEventTest extends TestCase
{
    public function test_offer_decremented_event_dispatches_when_an_offer_updated()
    {
        Event::fake();

        $amount = rand(111, 999);

        $offer = Offer::factory()->create(['remaining_amount' => $amount]);

        $offer->update([
            'remaining_amount' => $amount - 1,
        ]);

        Event::assertDispatched(OfferCreated::class);
    }
}
