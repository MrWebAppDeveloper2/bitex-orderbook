<?php

namespace App\Livewire\Components;

use App\Enums\Offer\OfferType;
use App\Models\Service;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BuyFrm extends Component
{
    public Service $service;

    #[Validate('required', 'numeric')]
    public int $amount;

    #[Validate('required', 'numeric')]
    public int $price;

    public function buy()
    {
        $this->validate();

        ($offer = auth()->user()->offers()->create([
            'remaining_amount' => $this->amount,
            'price' => $this->price,
            'type' => OfferType::BUY->value,
            'service_id' => $this->service->id
        ])) ?
            session()->now('alert-success', 'The new buy order has been place.') :
            session()->now('alert-danger', 'There are some errors.');

        $this->resetExcept('service');
    }

    public function render()
    {
        return view('livewire.components.buy-frm');
    }
}
