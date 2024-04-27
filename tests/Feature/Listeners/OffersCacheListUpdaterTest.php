<?php

namespace Tests\Feature\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Events\OfferCreated;
use App\Listeners\OffersCacheListUpdater;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OffersCacheListUpdaterTest extends TestCase
{
    public function test_the_listener_listens_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, OffersCacheListUpdater::class);
    }

    public function test_the_listener_request_for_atomic_lock_with_buy_offer_lock_key_with_specified_second_lock_time_in_the_config_when_new_offer_type_is_buy()
    {
        $lockTime = config()->get('custom.offer.lock_time');

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::BUY_LOCK->value, $lockTime);

        Offer::factory()->buy()->create();
    }
}
