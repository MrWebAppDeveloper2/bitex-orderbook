<?php

namespace Tests\Feature\Events;

use App\Events\OfferDeleted;
use Tests\TestCase;
use App\Models\Offer;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OfferDeletedEventTest extends TestCase
{
    public function test_offer_deleted_event_dispatches_when_an_offer_deleted()
    {
        Event::fake();

        $amount = rand(111, 999);

        $offer = Offer::factory()->create(['remaining_amount' => $amount]);

        $offer->delete();

        Event::assertDispatched(OfferDeleted::class);
    }
}
