<?php

namespace App\Livewire\Components;

use App\Models\Order;
use App\Models\Service;
use Livewire\Component;
use App\Enums\Offer\OfferType;
use Livewire\Attributes\Validate;

class SellFrm extends Component
{
    public Service $service;

    #[Validate('required', 'numeric')]
    public int $amount;

    #[Validate('required', 'numeric')]
    public int $price;

    public function sell()
    {
        $this->validate();

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
