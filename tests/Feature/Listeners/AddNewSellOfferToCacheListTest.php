<?php

namespace Tests\Feature\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferCacheListName;
use App\Events\OfferCreated;
use App\Events\SellOffersCacheListUpdated;
use App\Listeners\AddNewSellOfferToCacheList;
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

class AddNewSellOfferToCacheListTest extends TestCase
{
    public function test_the_listener_listens_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, AddNewSellOfferToCacheList::class);
    }

    private function mockCacheFacadeForTestAtomLoc()
    {
        Cache::shouldReceive('get')->andReturn([]);

        Cache::shouldReceive('set');

    }

    public function test_the_listener_request_for_atomic_lock_with_sell_offer_lock_key_with_specified_second_lock_time_in_the_config_when_new_offer_type_is_sell()
    {
        $this->mockCacheFacadeForTestAtomLoc();

        $lockTime = config()->get('custom.offer.lock_time');

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::SELL_LOCK->value, $lockTime);

        Offer::factory()->sell()->create();
    }

    public function test_the_listener_wait_for_release_sell_offer_type_atomic_lock_when_new_offer_type_is_sell_and_atomic_lock_is_not_free()
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
            ->with(OfferAtomLockName::SELL_LOCK->value, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->sell()->create();
    }

    public function test_the_listener_break_the_sell_offer_type_atomic_lock_and_force_release_it_when_after_maximum_waiting_timeout_when_new_offer_type_is_sell()
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
            ->with(OfferAtomLockName::SELL_LOCK->value, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->sell()->create();
    }

    public function test_the_listener_push_the_sell_type_new_created_offer_and_broadcast_it_through_socket_channel_when_sell_offer_cache_list_is_empty()
    {
        Event::fake(SellOffersCacheListUpdated::class);

        $this->assertEmpty(Cache::get(OfferCacheListName::SELL_CACHE_LIST->value));

        $offer = Offer::factory()->sell()->create();

        $expectedCacheResult = [
            [
                'remaining_amount' => $offer->remaining_amount,
                'price' => $offer->price
            ]
        ];

        $this->assertNotEmpty(Cache::get(OfferCacheListName::SELL_CACHE_LIST->value));

        $this->assertEquals($expectedCacheResult, Cache::get(OfferCacheListName::SELL_CACHE_LIST->value));

        Event::assertDispatched(SellOffersCacheListUpdated::class);
    }

    public function test_the_listener_add_new_sell_offer_to_cache_list_and_broadcast_it_when_list_length_is_smaller_than_maximum_length_limitation()
    {
        Event::fake();

        $maximumLength = rand(5, 10);

        config()->set('custom.offer.cache_list_length', $maximumLength);

        $offers = Offer::factory()->sell()->count(($maximumLength - (int)ceil($maximumLength / 2)))->create();

        $cacheList = $offers->map(function ($offer) {
            return [
                'price' => $offer->price,
                'remaining_amount' => $offer->remaining_amount,
            ];
        })->toArray();

        Cache::set(OfferCacheListName::SELL_CACHE_LIST->value, $cacheList);

        $newOffer = Offer::factory()->sell()->create();

        $listener = app()->make(AddNewSellOfferToCacheList::class);

        $listener->handle(new OfferCreated($newOffer));

        $cacheList[] = [
            'price' => $newOffer->price,
            'remaining_amount' => $newOffer->remaining_amount,
        ];

        $this->assertEqualsCanonicalizing(Cache::get(OfferCacheListName::SELL_CACHE_LIST->value), $cacheList);

        Event::assertDispatched(SellOffersCacheListUpdated::class);
    }

    public function test_the_listener_avoiding_update_sell_offers_cache_list_and_broadcast_it_when_the_list_is_not_empty_and_new_created_offer_type_is_sell_and_the_offer_has_not_lower_price_than_at_least_one_of_exists_offers()
    {
        Event::fake();

        $offers = Offer::factory()->sell()->count(config()->get('custom.offer.cache_list_length'))->create();

        $cacheList = $offers->map(function ($offer) {
            return [
                'price' => $offer->price,
                'remaining_amount' => $offer->remaining_amount,
            ];
        })->toArray();

        Cache::set(OfferCacheListName::SELL_CACHE_LIST->value, $cacheList);

        $highestPriceOffer = $offers->sortByDesc('price')->first();

        $newOffer = Offer::factory()->sell()->create([
            'price' => ($highestPriceOffer->price + 1),
        ]);

        $listener = app()->make(AddNewSellOfferToCacheList::class);

        $listener->handle(new OfferCreated($newOffer));

        $this->assertEqualsCanonicalizing(Cache::get(OfferCacheListName::SELL_CACHE_LIST->value), $cacheList);

        Event::assertNotDispatched(SellOffersCacheListUpdated::class);
    }

    public function test_the_listener_merge_new_offer_remaining_amount_with_which_exists_cache_list_record_that_has_same_price_with_new_offer_and_then_broadcast_resort_list()
    {
        Event::fake();

        $offers = Offer::factory()->sell()->count(config()->get('custom.offer.cache_list_length'))->create();

        $cacheList = $offers->map(function ($offer) {
            return [
                'price' => $offer->price,
                'remaining_amount' => $offer->remaining_amount,
            ];
        })->toArray();

        Cache::put(OfferCacheListName::SELL_CACHE_LIST->value, $cacheList);

        $randKey = rand(0, (count($cacheList) - 1));

        $existsOffer = $offers[$randKey];

        $newOffer = Offer::factory()->sell()->create([
            'price' => $existsOffer->price
        ]);

        $cacheList[$randKey]['remaining_amount'] += $newOffer->remaining_amount;

        $listener = app()->make(AddNewSellOfferToCacheList::class);

        $listener->handle(new OfferCreated($newOffer));

        $updatedList = Cache::get(OfferCacheListName::SELL_CACHE_LIST->value);

        foreach ($updatedList as $key => $value){
            $this->assertSame($cacheList[$key], $value);
        }

        Event::assertDispatched(SellOffersCacheListUpdated::class);
    }

    public function test_the_listener_add_new_created_offer_to_the_cache_list_resort_and_broadcast_to_users_with_socket_it_when_new_offer_has_lower_price_than_all_old_offers()
    {
        Event::fake();

        $offers = Offer::factory()->sell()->count(config()->get('custom.offer.cache_list_length'))->create();

        $cacheList = $offers->map(function ($offer) {
            return [
                'price' => $offer->price,
                'remaining_amount' => $offer->remaining_amount,
            ];
        })->toArray();

        Cache::put(OfferCacheListName::SELL_CACHE_LIST->value, $cacheList);

        $randKey = rand(0, (count($cacheList) - 1));

        $existsOffer = $offers[$randKey];

        $newOffer = Offer::factory()->sell()->create([
            'price' => $existsOffer->price - rand(111, 999)
        ]);

        $cacheList[] = [
            'price' => $newOffer->price,
            'remaining_amount' => $newOffer->remaining_amount,
        ];

        $cacheList = collect($cacheList)
            ->sortBy('price')
            ->take(config()->get('custom.offer.cache_list_length'))
            ->toArray();

        $listener = app()->make(AddNewSellOfferToCacheList::class);

        $listener->handle(new OfferCreated($newOffer));

        $updatedList = Cache::get(OfferCacheListName::SELL_CACHE_LIST->value);

        foreach ($updatedList as $key => $value){
            $this->assertEqualsCanonicalizing($cacheList[$key], $value);
        }

        Event::assertDispatched(SellOffersCacheListUpdated::class);
    }
}
