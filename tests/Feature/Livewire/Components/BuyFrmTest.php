<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\BuyFrm;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class BuyFrmTest extends TestCase
{
    public function test_renders_successfully()
    {
        $service = Service::factory()->create();

        Livewire::test(BuyFrm::class, ['service' => $service])
            ->assertStatus(200);
    }

    public function test_exclude_submit_offer_when_user_has_not_enough_balance_according_offer_value()
    {
        $service = Service::factory()->create();

        $balance = rand(111111111, 999999999);

        $user = User::factory()->create([
            'balance' => $balance
        ]);

        $this->actingAs($user);

        Livewire::test(BuyFrm::class, ['service' => $service])
            ->set('amount', 2)
            ->set('price', $balance)
            ->call('buy')
            ->assertHasErrors();
    }
}
