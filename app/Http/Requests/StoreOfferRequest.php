<?php

namespace App\Http\Requests;

use App\Enums\Offer\OfferType;
use App\Models\Service;
use App\Rules\EnoughBalanceRequired;
use App\Rules\EnoughServiceAmountRequired;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $type = request()->input('type');

        $service = ($service_id = request()->input('service_id')) ? Service::find($service_id) : null;

        return [
            'service_id' => ['required', 'exists:' . Service::class . ',id'],
            'amount' => ['required', 'numeric', 'min:1', ($type == OfferType::SELL->value and $service) ? (new EnoughServiceAmountRequired($service)) : ''],
            'price' => ['required', 'numeric', 'min:1', $type == OfferType::BUY->value ? new EnoughBalanceRequired : ''],
            'type' => ['required', Rule::in(array_map(fn ($type) => $type->value, OfferType::cases()))]
        ];
    }
}
