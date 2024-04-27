<?php

namespace Tests\Feature\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferCacheListName;
use App\Events\BuyOffersCacheListUpdated;
use App\Events\OfferCreated;
use App\Listeners\BuyOffersCacheListUpdater;
use App\Models\Offer;
use Illuminate\Cache\ArrayLock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\TestCase;

class BuyOffersCacheListUpdaterTest extends TestCase
{
    public function test_the_listener_listens_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, BuyOffersCacheListUpdater::class);
    }

    private function mockCacheFacadeForTestAtomLoc()
    {
        Cache::shouldReceive('get')->andReturn(new Collection());

        Cache::shouldReceive('set');
    }

    public function test_the_listener_request_for_atomic_lock_with_buy_offer_lock_key_with_specified_second_lock_time_in_the_config_when_new_offer_type_is_buy()
    {
        $this->mockCacheFacadeForTestAtomLoc();

        $lockTime = config()->get('custom.offer.lock_time');

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::BUY_LOCK->value, $lockTime);

        Offer::factory()->buy()->create();
    }

    public function test_the_listener_wait_for_release_buy_offer_type_atomic_lock_when_new_offer_type_is_buy_and_atomic_lock_is_not_free()
    {
        $this->mockCacheFacadeForTestAtomLoc();

        $lockTime = config()->get('custom.offer.lock_time');

        $waitingTime = config()->get('custom.offer.waiting_time');

        $mockLock = $this->partialMock(ArrayLock::class, function (MockInterface $mock) use ($waitingTime) {
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

    public function test_the_listener_break_the_buy_offer_type_atomic_lock_and_force_release_it_when_after_maximum_waiting_timeout_when_new_offer_type_is_buy()
    {
        $this->mockCacheFacadeForTestAtomLoc();

        $lockTime = config()->get('custom.offer.lock_time');

        $waitingTime = config()->get('custom.offer.waiting_time');

        $mockLock = $this->partialMock(ArrayLock::class, function (MockInterface $mock) use ($waitingTime) {
            $mock->shouldReceive('block')
                ->once()
                ->with($waitingTime)
                ->andThrows(LockTimeoutException::class);

            $mock->shouldReceive('forceRelease')
                ->once();
        });

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::BUY_LOCK->value, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->buy()->create();
    }

    public function test_the_listener_push_the_buy_type_new_created_offer_and_broadcast_it_through_socket_channel_when_buy_offer_cache_list_is_empty()
    {
        Event::fake(BuyOffersCacheListUpdated::class);

        $this->assertEmpty(Cache::get(OfferCacheListName::BUY_CACHE_LIST->value));

        $offer = Offer::factory()->buy()->create();

        $expectedCacheResult = [
            [
                'remaining_amount' => $offer->remaining_amount,
                'price' => $offer->price
            ]
        ];

        $this->assertNotEmpty(Cache::get(OfferCacheListName::BUY_CACHE_LIST->value));

        $this->assertEquals($expectedCacheResult, Cache::get(OfferCacheListName::BUY_CACHE_LIST->value));

        Event::assertDispatched(BuyOffersCacheListUpdated::class);
    }
}
