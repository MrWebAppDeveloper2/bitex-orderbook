<?php

namespace App\Providers;

use App\Models\User;
use App\View\Components\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(!$user = User::first())
            $user = User::factory()->create();

        Auth::loginUsingId($user->id);
    }
}
