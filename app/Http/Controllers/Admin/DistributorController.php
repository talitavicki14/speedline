<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DistributorController extends Controller
{
    public function index()
    {
        $distributors = Distributor::orderBy('name')->get();
        return view('admin.distributors.index', compact('distributors'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-data');
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        Distributor::create($request->all());

        return redirect()->back()->with('success', 'Distributor berhasil ditambahkan');
    }

    public function update(Request $request, Distributor $distributor)
    {
        Gate::authorize('manage-data');
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $distributor->update($request->all());

        return redirect()->back()->with('success', 'Distributor berhasil diperbarui');
    }

    public function destroy(Distributor $distributor)
    {
        Gate::authorize('manage-data');
        if ($distributor->spareparts()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus distributor yang memiliki data sparepart');
        }

        $distributor->delete();

        return redirect()->back()->with('success', 'Distributor berhasil dihapus');
    }
}
