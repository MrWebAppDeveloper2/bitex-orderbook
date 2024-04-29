<?php

namespace App\Livewire\Components;

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
        dd('here');
    }

    public function render()
    {
        return view('livewire.components.sell-frm');
    }
}
