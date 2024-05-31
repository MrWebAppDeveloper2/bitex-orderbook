<?php

namespace Tests\Feature\Controller\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\UserBalance;
use App\Events\OfferCreated;
use App\Enums\Offer\OfferType;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OfferControllerTest extends TestCase
{
    public function test_api_exclude_place_buy_offer_when_user_has_not_enough_balance()
    {
        $service = Service::factory()->create();

        $balance = rand(111111111, 999999999);

        $user = User::factory()->create([
            'balance' => $balance
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('offer.store'), [
            'service_id' => $service->id,
            'amount' => 2,
            'price' => $balance,
            'type' => OfferType::BUY->value,
        ]);

        $res->assertJsonValidationErrors(['price' => 'Your balance is not enough !']);
    }

    public function test_api_place_buy_offer_when_user_has_enough_balance()
    {
        Event::fake();

        $service = Service::factory()->create();

        $balance = rand(111111111, 999999999);

        $user = User::factory()->create([
            'balance' => $balance
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('offer.store'), [
            'service_id' => $service->id,
            'amount' => 1,
            'price' => $balance,
            'type' => OfferType::BUY->value,
        ]);

        $res->assertOk();

        $res->assertJsonMissingValidationErrors();

        Event::assertDispatched(OfferCreated::class);
    }

    public function test_api_exclude_place_offer_when_user_has_not_enough_amount_of_target_service_in_user_balance_table()
    {
        Event::fake();

        $service = Service::factory()->create();

        $user = User::factory()->create();

        $amount = rand(11111111, 99999999);

        UserBalance::factory()->for($user)->create([
            'service_key' => $service->key,
            'value' => $amount
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('offer.store'), [
            'service_id' => $service->id,
            'amount' => $amount * 2,
            'price' => rand(1111111, 9999999),
            'type' => OfferType::SELL->value,
        ]);

        $res->assertJsonValidationErrors(['amount' => 'Determined amount is greather than your balance']);
    }


    public function test_api_place_offer_when_user_has_enough_amount_of_target_service_in_user_balance_table()
    {
        Event::fake();

        $service = Service::factory()->create();

        $user = User::factory()->create();

        $amount = rand(11111111, 99999999);

        UserBalance::factory()->for($user)->create([
            'service_key' => $service->key,
            'value' => $amount
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('offer.store'), [
            'service_id' => $service->id,
            'amount' => $amount,
            'price' => rand(1111111, 9999999),
            'type' => OfferType::SELL->value,
        ]);

        $res->assertOk();

        $res->assertJsonMissingValidationErrors();

        Event::assertDispatched(OfferCreated::class);
    }
}
