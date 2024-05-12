<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    private const KEY_PREFIX = 'alert-';

    public const LEVELS = ['success', 'warning', 'info', 'danger'];

    /**
     * Stores all passed session alerts
     *
     * @var array
     */
    public array $alerts;

    /**
     * Create a new component instance.
     *
     * @param array $alerts example: [ 'alert-error' => 'Sorry! There are some errors in the server' ]
     */
    public function __construct()
    {
        $this->alerts = $this->prune(session()->all());
    }

    /**
     * Removes all flashes except alerts that has alert key format
     * True key format examples : 'alert-error', 'alert-success'
     *
     * @param array $alerts
     * @return array
     */
    private function prune(array $alerts):array
    {
        $validAlerts = [];

        foreach ($alerts as $key => $message){
            if(!str_starts_with($key, self::KEY_PREFIX))
                continue;

            $explode = explode('-', $key);

            if(count($explode) != 2)
                continue;

            if(!in_array(strtolower($explode[1]), self::LEVELS))
                continue;

            $validAlerts[$explode[1]] = $message;
        }

        return $validAlerts;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.alert');
    }
}
