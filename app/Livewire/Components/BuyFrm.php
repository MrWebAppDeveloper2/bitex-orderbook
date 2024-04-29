<?php

namespace App\Livewire\Components;

use App\Enums\Offer\OfferType;
use App\Models\Offer;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BuyFrm extends Component
{
    #[Validate('required', 'numeric')]
    public int $amount;

    #[Validate('required', 'numeric')]
    public int $price;

    public function buy()
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
            'type' => OfferType::BUY->value,
        ])) ?
            session()->now('alert-success', 'The new buy order has been place.') :
            session()->now('alert-danger', 'There are some errors.');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.components.buy-frm');
    }
}
