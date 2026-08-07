<?php

namespace App\Http\Controllers;

use App\Models\ServicePoint;
use Illuminate\Http\Request;

class ServicePointController extends Controller
{
    /**
     * Display a listing of service points.
     */
    public function index()
    {
        $points = ServicePoint::all();
        $categories = ServicePoint::select('category')->distinct()->pluck('category')->toArray();

        return view('service-points', compact('points', 'categories'));
    }

    /**
     * Store a newly created service point.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'seats' => 'required|integer|min:1',
            'category' => 'required|string|max:255',
        ]);

        // Auto-generate unique code in the backend
        $count = ServicePoint::count();
        $code = 'SP-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        while (ServicePoint::where('code', $code)->exists()) {
            $count++;
            $code = 'SP-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        }

        ServicePoint::create([
            'code' => $code,
            'name' => $validated['name'],
            'seats' => intval($validated['seats']),
            'category' => $validated['category'],
            'status' => 'available',
            'amount' => 0.00,
            'items' => [],
        ]);

        return redirect()->back()->with('success', 'Service Point created successfully!');
    }

    /**
     * Update the state of a service point (quick adds, status toggle, checkout).
     */
    public function update(Request $request, $id)
    {
        $point = ServicePoint::findOrFail($id);

        if ($request->has('status')) {
            $point->status = $request->input('status');
            if ($point->status === 'available') {
                $point->items = [];
                $point->amount = 0.00;
                $point->order_number = null;
            } elseif ($point->status === 'occupied' && !$point->order_number) {
                $point->order_number = '#KFC' . rand(1000, 9999);
                $point->amount = 0.00;
                $point->items = [];
            }
        }

        if ($request->has('items')) {
            $point->items = $request->input('items');
        }

        if ($request->has('amount')) {
            $point->amount = floatval($request->input('amount'));
        }

        $point->save();

        return response()->json([
            'success' => true,
            'point' => $point
        ]);
    }

    /**
     * Remove the specified service point.
     */
    public function destroy($id)
    {
        $point = ServicePoint::findOrFail($id);
        $point->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
