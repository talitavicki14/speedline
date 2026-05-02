<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->search) {
            $query->where(fn($q) => $q->where('service_name', 'like', "%{$request->search}%")
                                      ->orWhere('description', 'like', "%{$request->search}%"));
        }

        $perPage  = in_array($request->per_page, [10, 25, 50]) ? (int) $request->per_page : 10;
        $services = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('admin.services.index', compact('services', 'perPage'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-data');
        $request->merge([
            'price' => str_replace('.', '', $request->price),
        ]);

        $request->validate([
            'service_name'   => 'required|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'required|min:0',
            'estimated_time' => 'required|integer|min:1',
        ]);
        Service::create($request->only('service_name', 'description', 'price', 'estimated_time'));
        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        Gate::authorize('manage-data');
        $request->merge([
            'price' => str_replace('.', '', $request->price),
        ]);

        $request->validate([
            'service_name'   => 'required|string|max:255',
            'price'          => 'required|min:0',
            'estimated_time' => 'required|integer|min:1',
        ]);
        $service->update($request->only('service_name', 'description', 'price', 'estimated_time'));
        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        Gate::authorize('manage-data');
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil dihapus.');
    }
}
