<?php

namespace App\Livewire\AccessToken;

use Livewire\Component;
use Livewire\Features\SupportPagination\WithoutUrlPagination;
use Livewire\WithPagination;

class AccessTokenIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public function render()
    {
        return view('livewire.access-token.access-token-index')
            ->with('tokens', auth()->user()->tokens()->paginate(10));
    }
}
