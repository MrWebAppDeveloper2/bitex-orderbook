<?php

namespace Tests\Feature\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Events\OfferCreated;
use App\Listeners\OffersCacheListUpdater;
use App\Models\Offer;
use Illuminate\Cache\ArrayLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\TestCase;

class OffersCacheListUpdaterTest extends TestCase
{
    public function test_the_listener_listens_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, OffersCacheListUpdater::class);
    }

    // BUY
    public function test_the_listener_request_for_atomic_lock_with_buy_offer_lock_key_with_specified_second_lock_time_in_the_config_when_new_offer_type_is_buy()
    {
        $lockTime = config()->get('custom.offer.lock_time');

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::BUY_LOCK->value, $lockTime);

        Offer::factory()->buy()->create();
    }

    public function test_the_listener_wait_for_release_buy_offer_type_atomic_lock_when_new_offer_type_is_buy_and_atomic_lock_is_not_free()
    {
        $lockTime = config()->get('custom.offer.lock_time');

        $waitingTime = config()->get('custom.offer.waiting_time');

        $mockLock = $this->partialMock(ArrayLock::class, function(MockInterface $mock) use ($waitingTime){
            $mock->shouldReceive('block')
                ->once()
                ->with($waitingTime);
        });

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::BUY_LOCK->value, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->buy()->create();
    }

    // SELL
    public function test_the_listener_request_for_atomic_lock_with_sell_offer_lock_key_with_specified_second_lock_time_in_the_config_when_new_offer_type_is_sell()
    {
        $lockTime = config()->get('custom.offer.lock_time');

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::SELL_LOCK->value, $lockTime);

        Offer::factory()->sell()->create();
    }

    public function test_the_listener_wait_for_release_sell_offer_type_atomic_lock_when_new_offer_type_is_sell_and_atomic_lock_is_not_free()
    {
        $lockTime = config()->get('custom.offer.lock_time');

        $waitingTime = config()->get('custom.offer.waiting_time');

        $mockLock = $this->partialMock(ArrayLock::class, function(MockInterface $mock) use ($waitingTime){
            $mock->shouldReceive('block')
                ->once()
                ->with($waitingTime);
        });

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::SELL_LOCK->value, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->sell()->create();
    }
}
