<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\SellFrm;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class SellFrmTest extends TestCase
{
    public function test_renders_successfully()
    {
        $service = Service::factory()->create();

        Livewire::test(SellFrm::class, ['service' => $service])
            ->assertStatus(200);
    }

    public function test_exclude_place_offer_when_user_has_not_enough_amount_of_target_service_in_user_balance_table()
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

        Livewire::test(SellFrm::class, ['service' => $service])
            ->set('amount', $amount * 2)
            ->set('price', rand(1111111, 9999999))
            ->call('sell')
            ->assertHasErrors(['amount' => 'Determined amount is greather than your balance']);
    }

    public function test_place_offer_when_user_has_not_enough_amount_of_target_service_in_user_balance_table()
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

        Livewire::test(SellFrm::class, ['service' => $service])
            ->set('amount', $amount)
            ->set('price', rand(1111111, 9999999))
            ->call('sell')
            ->assertHasNoErrors();
    }
}
