<?php

namespace Tests\Feature\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferCacheListName;
use App\Events\BuyOffersCacheListUpdated;
use App\Events\OfferCreated;
use App\Listeners\AddNewBuyOfferToCacheList;
use App\Models\Offer;
use App\Models\Service;
use Illuminate\Cache\ArrayLock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\TestCase;

class AddNewBuyOfferToCacheListTest extends TestCase
{
    private function mockCacheFacadeForTestAtomicLock()
    {
        Cache::shouldReceive('get')->andReturn([]);

        Cache::shouldReceive('set');
    }

    public function test_the_listener_listens_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, AddNewBuyOfferToCacheList::class);
    }

    public function test_the_listener_request_for_atomic_lock_with_buy_offer_lock_key_and_service_id_with_specified_second_lock_time_in_the_config_when_new_offer_type_is_buy()
    {
        $this->mockCacheFacadeForTestAtomicLock();

        $service = Service::factory()->create();

        $lockTime = config()->get('custom.offer.lock_time');

        $mockLock = $this->partialMock(ArrayLock::class, function (MockInterface $mock) {});

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::BUY_LOCK->value . '.' . $service->id, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->for($service)->buy()->create();
    }

    public function test_the_listener_wait_for_release_buy_offer_type_atomic_lock_when_new_offer_type_is_buy_and_atomic_lock_is_not_free()
    {
        $this->mockCacheFacadeForTestAtomicLock();
        
        $service = Service::factory()->create();

        $lockTime = config()->get('custom.offer.lock_time');

        $waitingTime = config()->get('custom.offer.waiting_time');

        $mockLock = $this->partialMock(ArrayLock::class, function (MockInterface $mock) use ($waitingTime) {
            $mock->shouldReceive('block')
                ->once()
                ->with($waitingTime);
        });

        Cache::shouldReceive('lock')
            ->once()
            ->with(OfferAtomLockName::BUY_LOCK->value . '.' . $service->id, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->for($service)->buy()->create();
    }

    public function test_the_listener_break_the_buy_offer_type_atomic_lock_and_force_release_it_when_after_maximum_waiting_timeout_when_new_offer_type_is_buy()
    {
        $this->mockCacheFacadeForTestAtomicLock();

        $service = Service::factory()->create();

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
            ->with(OfferAtomLockName::BUY_LOCK->value . '.' . $service->id, $lockTime)
            ->andReturn($mockLock);

        Offer::factory()->for($service)->buy()->create();
    }

    public function test_the_listener_push_the_buy_type_new_created_offer_and_broadcast_it_through_socket_channel_when_buy_offer_cache_list_is_empty()
    {
        Event::fake(BuyOffersCacheListUpdated::class);

        $service = Service::factory()->create();

        $this->assertEmpty(Cache::get(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id));

        $offer = Offer::factory()->for($service)->buy()->create();

        $expectedCacheResult = [
            [
                'remaining_amount' => $offer->remaining_amount,
                'price' => $offer->price
            ]
        ];

        $this->assertNotEmpty(Cache::get(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id));

        $this->assertEquals($expectedCacheResult, Cache::get(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id));

        Event::assertDispatched(BuyOffersCacheListUpdated::class);
    }

    public function test_the_listener_avoiding_update_buy_offers_cache_list_and_broadcast_it_when_the_list_is_not_empty_and_new_created_offer_type_is_buy_and_the_offer_has_not_higher_price_than_at_least_one_of_exists_offers()
    {
        Event::fake();

        $service = Service::factory()->create();

        $offers = Offer::factory()->for($service)->buy()->count(config()->get('custom.offer.cache_list_length'))->create();

        $cacheList = $offers->map(function ($offer) {
            return [
                'price' => $offer->price,
                'remaining_amount' => $offer->remaining_amount,
            ];
        })->toArray();

        Cache::set(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id, $cacheList);

        $lowestPriceOffer = $offers->sortBy('price')->first();

        $newOffer = Offer::factory()->for($service)->buy()->create([
            'price' => ($lowestPriceOffer->price - 1),
        ]);

        $listener = app()->make(AddNewBuyOfferToCacheList::class);

        $listener->handle(new OfferCreated($newOffer));

        $this->assertEqualsCanonicalizing(Cache::get(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id), $cacheList);

        Event::assertNotDispatched(BuyOffersCacheListUpdated::class);
    }

    public function test_the_listener_merge_new_offer_remaining_amount_with_which_exists_cache_list_record_that_has_same_price_with_new_offer_and_then_broadcast_resort_list()
    {
        Event::fake();

        $service = Service::factory()->create();

        $offers = Offer::factory()->for($service)->buy()->count(config()->get('custom.offer.cache_list_length'))->create();

        $cacheList = $offers->map(function ($offer) {
            return [
                'price' => $offer->price,
                'remaining_amount' => $offer->remaining_amount,
            ];
        })->toArray();

        Cache::put(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id, $cacheList);

        $randKey = rand(0, (count($cacheList) - 1));

        $existsOffer = $offers[$randKey];

        $newOffer = Offer::factory()->for($service)->buy()->create([
            'price' => $existsOffer->price
        ]);

        $cacheList[$randKey]['remaining_amount'] += $newOffer->remaining_amount;

        $listener = app()->make(AddNewBuyOfferToCacheList::class);

        $listener->handle(new OfferCreated($newOffer));

        $updatedList = Cache::get(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id);

        foreach ($updatedList as $key => $value){
            $this->assertSame($cacheList[$key], $value);
        }

        Event::assertDispatched(BuyOffersCacheListUpdated::class);
    }

    public function test_the_listener_add_new_created_offer_to_the_cache_list_resort_and_broadcast_to_users_with_socket_it_when_new_offer_has_higher_price_than_all_old_offers()
    {
        Event::fake();

        $service = Service::factory()->create();

        $offers = Offer::factory()->for($service)->buy()->count(config()->get('custom.offer.cache_list_length'))->create();

        $cacheList = $offers->map(function ($offer) {
            return [
                'price' => $offer->price,
                'remaining_amount' => $offer->remaining_amount,
            ];
        })->toArray();

        Cache::put(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id, $cacheList);

        $randKey = rand(0, (count($cacheList) - 1));

        $existsOffer = $offers[$randKey];

        $newOffer = Offer::factory()->for($service)->buy()->create([
            'price' => $existsOffer->price + rand(111, 999)
        ]);

        $cacheList[] = [
            'price' => $newOffer->price,
            'remaining_amount' => $newOffer->remaining_amount,
        ];

        $cacheList = array_values(
            collect($cacheList)
            ->sortByDesc('price')
            ->take(config()->get('custom.offer.cache_list_length'))
            ->toArray()
        );

        $listener = app()->make(AddNewBuyOfferToCacheList::class);

        $listener->handle(new OfferCreated($newOffer));

        $updatedList = Cache::get(OfferCacheListName::BUY_CACHE_LIST->value . '.' . $service->id);

        foreach ($updatedList as $key => $value){
            $this->assertEqualsCanonicalizing($cacheList[$key], $value);
        }

        Event::assertDispatched(BuyOffersCacheListUpdated::class);
    }
}
