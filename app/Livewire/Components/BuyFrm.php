<?php

namespace App\Livewire\Components;

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
    }

    public function render()
    {
        return view('livewire.components.buy-frm');
    }
}
