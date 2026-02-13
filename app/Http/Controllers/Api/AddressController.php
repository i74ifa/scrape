<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return \App\Models\Address::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_one' => 'nullable|string',
            'phone' => 'nullable|string',
            'longitude' => 'nullable',
            'latitude' => 'nullable',
        ]);


        // format: longitude, latitude, we will convert it to geometry
        $validated['geometry'] = $validated['longitude'] && $validated['latitude'] ? "POINT(" . $validated['longitude'] . " " . $validated['latitude'] . ")" : null;

        return \App\Models\Address::create([
            'user_id' => auth()->id(),
            ...$validated
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return \App\Models\Address::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $address = \App\Models\Address::findOrFail($id);

        $validated = $request->validate([
            'address_one' => 'nullable|string',
            'phone' => 'nullable|string',
            'geometry' => 'nullable',
        ]);

        $address->update($validated);

        return $address;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        \App\Models\Address::destroy($id);

        return response()->noContent();
    }
}
