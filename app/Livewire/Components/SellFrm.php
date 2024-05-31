<?php

namespace App\Livewire\Components;

use App\Models\Order;
use App\Models\Service;
use Livewire\Component;
use App\Enums\Offer\OfferType;
use App\Rules\EnoughServiceAmountRequired;
use Livewire\Attributes\Validate;

class SellFrm extends Component
{
    public Service $service;

    public int $amount;

    public int $price;

    public function sell()
    {
        $this->validate(
            [
                'amount' => ['required', 'numeric', new EnoughServiceAmountRequired($this->service)],
                'price' => ['required', 'numeric'],
            ]
        );

        ($offer = auth()->user()->offers()->create([
            'remaining_amount' => $this->amount,
            'price' => $this->price,
            'type' => OfferType::SELL->value,
            'service_id' => $this->service->id
        ])) ?
            session()->now('alert-success', 'The new sell order has been place.') :
            session()->now('alert-danger', 'There are some errors.');

        $this->resetExcept('service');
    }

    public function render()
    {
        return view('livewire.components.sell-frm');
    }
}
