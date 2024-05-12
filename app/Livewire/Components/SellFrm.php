<?php

namespace App\Livewire\Components;

use App\Enums\Offer\OfferType;
use App\Models\Order;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SellFrm extends Component
{
    #[Validate('required', 'numeric')]
    public int $amount;

    #[Validate('required', 'numeric')]
    public int $price;

    public function sell()
    {
        $this->validate();

        ($order = Order::create(array_merge(
            $this->only(['amount', 'price']),
            ['user_id' => auth()->id()]
        )))
        &&
        ($offer = $order->offer()->create([
            'remaining_amount' => $this->amount,
            'price' => $this->price,
            'type' => OfferType::SELL->value,
        ])) ?
            session()->now('alert-success', 'The new sell order has been place.') :
            session()->now('alert-danger', 'There are some errors.');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.components.sell-frm');
    }
}
