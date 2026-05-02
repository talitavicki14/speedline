<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Models\Distributor;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SparepartController extends Controller
{
    public function index(Request $request)
    {
        $query = Sparepart::with('distributor');

        if ($request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")
                                      ->orWhere('brand', 'like', "%{$request->search}%"));
        }

        if ($request->stock_status) {
            if ($request->stock_status === 'low') {
                $query->whereBetween('stock', [1, 15]);
            } elseif ($request->stock_status === 'out') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock_status === 'in') {
                $query->where('stock', '>', 15);
            }
        }

        if ($request->distributor_id) {
            $query->where('distributor_id', $request->distributor_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $perPage    = in_array($request->per_page, [10, 25, 50]) ? (int) $request->per_page : 10;
        $spareparts = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
        $distributors = Distributor::where('is_active', true)->orderBy('name')->get();
        $types = Sparepart::distinct()->pluck('type')->sort()->values();

        return view('admin.spareparts.index', compact('spareparts', 'perPage', 'distributors', 'types'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-data');
        $request->merge([
            'price' => str_replace('.', '', $request->price),
            'purchase_price' => str_replace('.', '', $request->purchase_price),
        ]);

        $request->validate([
            'name'           => 'required|string',
            'type'           => 'required|in:Oli,Aki,Ban,Mesin,Rem,Transmisi,Kemudi,Suspensi,Filter,Busi,Cairan,Aksesoris,Lainnya',
            'brand'          => 'required|string',
            'price'          => 'required|min:1',
            'purchase_price' => 'required|min:1',
            'stock'          => 'required|integer|min:0',
            'distributor_id' => 'required|exists:distributors,id',
            'purchase_date'  => 'required|date',
        ]);

        DB::transaction(function() use ($request) {
            $sparepart = Sparepart::create($request->only('name', 'type', 'brand', 'price', 'purchase_price', 'stock', 'distributor_id'));

            if ($sparepart->stock > 0) {
                Purchase::create([
                    'sparepart_id'   => $sparepart->id,
                    'distributor_id' => $sparepart->distributor_id,
                    'qty'            => $sparepart->stock,
                    'purchase_price' => $sparepart->purchase_price,
                    'total_price'    => $sparepart->stock * $sparepart->purchase_price,
                    'purchase_date'  => $request->purchase_date,
                ]);
            }
        });

        return redirect()->route('admin.spareparts.index')->with('success', 'Sparepart dan riwayat pembelian berhasil ditambahkan.');
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        Gate::authorize('manage-data');
        $request->merge([
            'price' => str_replace('.', '', $request->price),
            'purchase_price' => str_replace('.', '', $request->purchase_price),
        ]);

        $request->validate([
            'name'  => 'required|string',
            'type'  => 'required|in:Oli,Aki,Ban,Mesin,Rem,Transmisi,Kemudi,Suspensi,Filter,Busi,Cairan,Aksesoris,Lainnya',
            'brand' => 'required|string',
            'price' => 'required|min:1',
            'purchase_price' => 'required|min:1',
            'stock' => 'required|integer|min:0',
            'distributor_id' => 'required|exists:distributors,id',
        ]);
        $sparepart->update($request->only('name', 'type', 'brand', 'price', 'purchase_price', 'stock', 'distributor_id'));
        return redirect()->route('admin.spareparts.index')->with('success', 'Sparepart berhasil diperbarui.');
    }

    public function destroy(Sparepart $sparepart)
    {
        Gate::authorize('manage-data');
        $sparepart->delete();
        return redirect()->route('admin.spareparts.index')->with('success', 'Sparepart berhasil dihapus.');
    }

    public function storePurchase(Request $request, Sparepart $sparepart)
    {
        Gate::authorize('manage-data');
        $purchasePrice = str_replace('.', '', $request->purchase_price);
        $request->merge(['purchase_price' => $purchasePrice]);

        $request->validate([
            'qty' => 'required|integer|min:1',
            'purchase_price' => 'required|min:1',
            'distributor_id' => 'required|exists:distributors,id',
            'purchase_date' => 'required|date',
        ]);

        Purchase::create([
            'sparepart_id' => $sparepart->id,
            'distributor_id' => $request->distributor_id,
            'qty' => $request->qty,
            'purchase_price' => $purchasePrice,
            'total_price' => $request->qty * $purchasePrice,
            'purchase_date' => $request->purchase_date,
        ]);

        $sparepart->update([
            'stock' => $sparepart->stock + $request->qty,
            'purchase_price' => $request->purchase_price, 
            'distributor_id' => $request->distributor_id
        ]);

        return redirect()->route('admin.spareparts.index')->with('success', 'Stok berhasil ditambahkan dan riwayat pembelian telah dicatat.');
    }
}
