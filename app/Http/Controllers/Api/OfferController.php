<?php

namespace App\Http\Controllers\Api;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Enums\Offer\OfferType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfferRequest $request): JsonResponse
    {
        $data = $request->only(['amount', 'price', 'type', 'service_id']);

        $data['amount'] = $request->input('remaining_amount');

        return auth()->user()->offers()->create($data)?
            response()->json(['msg' => __('placed')]):
            response()->json(['msg' => __('Oops ! There is a problem.')], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show(Offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Offer $offer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer)
    {
        //
    }
}
