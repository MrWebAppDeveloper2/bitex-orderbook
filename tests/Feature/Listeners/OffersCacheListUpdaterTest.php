<?php

namespace Tests\Feature\Listeners;

use App\Events\OfferCreated;
use App\Listeners\OffersCacheListUpdater;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OffersCacheListUpdaterTest extends TestCase
{
    public function test_the_listener_listens_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, OffersCacheListUpdater::class);
    }
}
