<?php

namespace Tests\Feature\Listeners;

use Tests\TestCase;
use App\Models\Offer;
use App\Models\Trade;
use App\Models\Service;
use App\Events\OfferCreated;
use App\Events\OfferDeleted;
use App\Listeners\TryToTradeNewOffer;
use Illuminate\Support\Facades\Event;
use App\Events\OfferAmountDecremented;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Events\OfferDecrementedEventTest;

class TryToTradeNewOfferTest extends TestCase
{
    public function test_listening_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, TryToTradeNewOffer::class);
    }

    public function test_listener_has_no_action_when_created_offer_type_is_buy_and_there_is_no_any_offer_with_sell_type_and_equal_or_cheaper_price_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $sellPrice = rand(11111, 99999);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $sellPrice]);

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $sellPrice - 1]);

        $event = new OfferCreated($buy);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertNotDispatched(OfferDecrementedEventTest::class);

        Event::assertNotDispatched(OfferDeleted::class);
    }

    public function test_listener_has_no_action_when_created_offer_type_is_sell_and_there_is_no_any_offer_with_buy_type_and_equal_or_higher_price_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $buyPrice = rand(11111, 99999);

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $buyPrice]);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $buyPrice + 1]);

        $event = new OfferCreated($sell);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertNotDispatched(OfferDecrementedEventTest::class);

        Event::assertNotDispatched(OfferDeleted::class);
    }

    public function test_listener_do_trade_by_delete_sell_offer_and_decrement_buy_offer_amount_when_new_offer_type_is_buy_and_there_is_another_offer_with_sell_type_and_has_same_price_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $samePrice = rand(11111111,99999999);

        $sellAmount = rand(11111, 99999);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $samePrice, 'remaining_amount' => $sellAmount]);

        $buyAmount = $sellAmount * 2;

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $samePrice, 'remaining_amount' => $buyAmount]);

        $event = new OfferCreated($buy);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        Event::assertDispatched(OfferAmountDecremented::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $sell->id]);

        $this->assertDatabaseHas(Offer::class, ['id' => $buy->id, 'remaining_amount' => ($buyAmount - $sellAmount)]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }

    public function test_listener_do_trade_by_delete_sell_offer_and_decrement_buy_offer_amount_when_new_offer_type_is_buy_and_there_is_another_offer_with_sell_type_and_has_smaller_price_than_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $sellPrice = rand(1111, 9999);

        $sellAmount = rand(11111, 99999);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $sellPrice, 'remaining_amount' => $sellAmount]);

        $buyAmount = $sellAmount * 2;

        $buyPrice = $sellPrice + 1;

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $buyPrice, 'remaining_amount' => $buyAmount]);

        $event = new OfferCreated($buy);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        Event::assertDispatched(OfferAmountDecremented::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $sell->id]);

        $this->assertDatabaseHas(Offer::class, ['id' => $buy->id, 'remaining_amount' => ($buyAmount - $sellAmount)]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }

    public function test_listener_do_trade_by_delete_sell_offer_and_delete_buy_offer_when_new_offer_type_is_buy_and_there_is_another_offer_with_sell_type_and_has_smaller_price_than_new_offer_and_same_amount()
    {
        Event::fake();

        $service = Service::factory()->create();

        $sameAmount = rand(11111, 99999);

        $sellPrice = rand(1111, 9999);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $sellPrice, 'remaining_amount' => $sameAmount]);

        $buyPrice = $sellPrice + 1;

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $buyPrice, 'remaining_amount' => $sameAmount]);

        $event = new OfferCreated($buy);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $sell->id]);

        $this->assertDatabaseMissing(Offer::class, ['id' => $buy->id]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }

    public function test_listener_do_trade_by_delete_sell_offer_and_delete_buy_offer_when_new_offer_type_is_buy_and_there_is_another_offer_with_sell_type_and_has_same_price_and_same_amount_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $sameAmount = rand(11111, 99999);

        $samePrice = rand(1111, 9999);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $samePrice, 'remaining_amount' => $sameAmount]);

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $samePrice, 'remaining_amount' => $sameAmount]);

        $event = new OfferCreated($buy);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $sell->id]);

        $this->assertDatabaseMissing(Offer::class, ['id' => $buy->id]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }


    public function test_listener_do_trade_by_delete_buy_offer_and_decrement_sell_offer_amount_when_new_offer_type_is_sell_and_there_is_another_offer_with_buy_type_and_has_same_price_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $samePrice = rand(11111111,99999999);

        $buyAmount = rand(11111, 99999);

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $samePrice, 'remaining_amount' => $buyAmount]);

        $sellAmount = $buyAmount * 2;

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $samePrice, 'remaining_amount' => $sellAmount]);

        $event = new OfferCreated($sell);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        Event::assertDispatched(OfferAmountDecremented::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $buy->id]);

        $this->assertDatabaseHas(Offer::class, ['id' => $sell->id, 'remaining_amount' => ($sellAmount - $buyAmount)]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }

    public function test_listener_do_trade_by_delete_buy_offer_and_decrement_sell_offer_amount_when_new_offer_type_is_sell_and_there_is_another_offer_with_buy_type_and_has_higher_price_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $sellPrice = rand(11111111,99999999);

        $buyAmount = rand(11111, 99999);

        $sellAmount = $buyAmount * 2;

        $buyPrice = $sellPrice + 1;

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $sellPrice, 'remaining_amount' => $sellAmount]);

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $buyPrice, 'remaining_amount' => $buyAmount]);

        $event = new OfferCreated($sell);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        Event::assertDispatched(OfferAmountDecremented::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $buy->id]);

        $this->assertDatabaseHas(Offer::class, ['id' => $sell->id, 'remaining_amount' => ($sellAmount - $buyAmount)]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }


    public function test_listener_do_trade_by_delete_buy_offer_and_delete_sell_offer_when_new_offer_type_is_sell_and_there_is_another_offer_with_buy_type_and_has_same_price_and_same_amount_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $samePrice = rand(11111111,99999999);

        $sameAmount = rand(11111, 99999);

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $samePrice, 'remaining_amount' => $sameAmount]);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $samePrice, 'remaining_amount' => $sameAmount]);

        $event = new OfferCreated($sell);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $buy->id]);

        $this->assertDatabaseMissing(Offer::class, ['id' => $sell->id]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }

    public function test_listener_do_trade_by_delete_buy_offer_and_delete_sell_offer_when_new_offer_type_is_sell_and_there_is_another_offer_with_buy_type_and_has_higher_price_and_same_amount_with_new_offer()
    {
        Event::fake();

        $service = Service::factory()->create();

        $sameAmount = rand(11111, 99999);
        
        $sellPrice = rand(11111111,99999999);

        $sell = Offer::factory()->for($service)->sell()->create(['price' => $sellPrice, 'remaining_amount' => $sameAmount]);

        $buyPrice = $sellPrice + 1;

        $buy = Offer::factory()->for($service)->buy()->create(['price' => $buyPrice, 'remaining_amount' => $sameAmount]);

        $event = new OfferCreated($sell);

        $listener = app()->make(TryToTradeNewOffer::class);

        $listener->handle($event);

        Event::assertDispatched(OfferDeleted::class);

        $this->assertDatabaseMissing(Offer::class, ['id' => $buy->id]);

        $this->assertDatabaseMissing(Offer::class, ['id' => $sell->id]);

        $this->assertDatabaseHas(Trade::class, ['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);
    }
}
