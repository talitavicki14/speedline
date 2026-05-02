<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class HeroController extends Controller
{
    public function index()
    {
        $heroes = Hero::orderBy('order')->get();
        
        $existingOrders = $heroes->pluck('order')->toArray();
        $suggestedOrder = 1;
        while (in_array($suggestedOrder, $existingOrders)) {
            $suggestedOrder++;
        }

        return view('admin.heroes.index', compact('heroes', 'suggestedOrder'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-data');
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'order' => 'required|integer|min:1|unique:heroes,order',
        ]);

        $path = $request->file('image')->store('heroes', 'public');

        Hero::create([
            'image_url' => $path,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', 'Hero baru berhasil ditambahkan.');
    }

    public function update(Request $request, Hero $hero)
    {
        Gate::authorize('manage-data');
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'order' => 'required|integer|min:1|unique:heroes,order,'.$hero->id,
        ]);

        $data = [
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($hero->image_url) {
                Storage::disk('public')->delete($hero->image_url);
            }
            $data['image_url'] = $request->file('image')->store('heroes', 'public');
        }

        $hero->update($data);

        return redirect()->back()->with('success', 'Hero berhasil diperbarui.');
    }

    public function destroy(Hero $hero)
    {
        Gate::authorize('manage-data');
        if ($hero->image_url) {
            Storage::disk('public')->delete($hero->image_url);
        }
        $hero->delete();

        return redirect()->back()->with('success', 'Hero berhasil dihapus.');
    }
}
