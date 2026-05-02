<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Auth::user()->vehicles()->orderByDesc('created_at')->get();
        return view('customer.vehicles.index', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'color' => 'required|string|max:50',
        ]);

        Auth::user()->vehicles()->create($request->only('brand', 'model', 'year', 'license_plate', 'color'));
        return redirect()->route('customer.vehicles.index')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        abort_if($vehicle->user_id !== Auth::id(), 403);
        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $vehicle->id,
            'color' => 'required|string|max:50',
        ]);
        $vehicle->update($request->only('brand', 'model', 'year', 'license_plate', 'color'));
        return redirect()->route('customer.vehicles.index')->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        abort_if($vehicle->user_id !== Auth::id(), 403);
        $vehicle->delete();
        return redirect()->route('customer.vehicles.index')->with('success', 'Kendaraan berhasil dihapus.');
    }
}
