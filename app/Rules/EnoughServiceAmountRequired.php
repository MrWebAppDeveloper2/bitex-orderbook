<?php

namespace App\Rules;

use App\Models\Service;
use App\Models\UserBalance;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class EnoughServiceAmountRequired implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];
 
    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
 
        return $this;
    }

    public function __construct(
        private Service $service
    )
    {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!$balance = UserBalance::where('service_key', $this->service->key)->first())
            $fail('Service not found !')->translate();

        elseif($balance->value < $value)
            $fail('Determined amount is greather than your balance')->translate();
    }
}
