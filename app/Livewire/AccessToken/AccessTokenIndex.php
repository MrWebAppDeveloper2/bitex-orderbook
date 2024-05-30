<?php

namespace App\Livewire\AccessToken;

use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Component;
use Livewire\Features\SupportPagination\WithoutUrlPagination;
use Livewire\WithPagination;

class AccessTokenIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public function delete(PersonalAccessToken $token)
    {
        $token->delete();

        session()->now('alert-success', __('Token deleted !'));
    }

    public function render()
    {
        return view('livewire.access-token.access-token-index')
            ->with('tokens', auth()->user()->tokens()->paginate(10));
    }
}
