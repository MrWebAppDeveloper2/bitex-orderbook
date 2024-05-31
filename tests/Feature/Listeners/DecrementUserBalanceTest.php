<?php

namespace Tests\Feature\Listeners;

use App\Enums\Offer\OfferType;
use App\Events\OfferCreated;
use App\Listeners\DecrementUserBalance;
use App\Models\Offer;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DecrementUserBalanceTest extends TestCase
{
    public function test_listenes_to_offer_created_event()
    {
        Event::fake();

        Event::assertListening(OfferCreated::class, DecrementUserBalance::class);
    }

    public function test_decrement_balance_column_value_from_users_table_after_created_new_buy_type_offer()
    {
        Event::fake();

        $data = [
            'remaining_amount' => rand(1, 100),
            'price' => rand(100, 1000),
        ];

        $user = User::factory()->create([
            'balance' => $data['remaining_amount'] * $data['price'],
        ]);

        $this->actingAs($user);

        $offer = Offer::factory()->for($user)->buy()->create($data);

        $event = new OfferCreated($offer);

        $listener = app()->make(DecrementUserBalance::class);

        $listener->handle($event);

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'balance' => ($user->balance - $offer->totalValue)
        ]);
    }

    public function test_decrement_value_column_from_user_balance_table_after_created_new_buy_type_offer()
    {
        Event::fake();

        $data = [
            'remaining_amount' => rand(1, 100),
            'price' => rand(100, 1000),
        ];

        $user = User::factory()->create();

        $this->actingAs($user);

        $service = Service::factory()->create();

        $userBalance = UserBalance::factory()->for($user)->create([
            'service_key' =>  $service->key,
            'value' => $data['remaining_amount'] * $data['price']
        ]);

        $offer = Offer::factory()->for($user)->for($service)->sell()->create($data);

        $event = new OfferCreated($offer);

        $listener = app()->make(DecrementUserBalance::class);

        $listener->handle($event);

        $this->assertDatabaseHas(UserBalance::class, [
            'id' => $userBalance->id,
            'value' => ($userBalance->value - $offer->totalValue)
        ]);
    }
}
