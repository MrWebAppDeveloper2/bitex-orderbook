<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

class Orderbook extends Component
{
    #[Title('Orderbook')]
    public function render()
    {
        return view('livewire.orderbook');
    }
}
