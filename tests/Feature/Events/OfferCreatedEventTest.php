<?php

namespace Tests\Feature\Events;

use App\Events\OfferCreated;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OfferCreatedEventTest extends TestCase
{
    public function test_offer_created_event_dispatches_when_a_new_offer_is_created()
    {
        Event::fake();

        Offer::factory()->create();

        Event::assertDispatched(OfferCreated::class);
    }
}
