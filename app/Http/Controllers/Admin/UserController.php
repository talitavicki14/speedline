<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->has('tab')) {
            return redirect()->route('admin.users.index', array_merge($request->query(), ['tab' => 'internal']));
        }

        $search  = $request->search;
        $perPage = in_array($request->per_page, [10, 25, 50]) ? (int)$request->per_page : 10;
        $tab     = in_array($request->tab, ['internal', 'customer']) ? $request->tab : 'internal';

        $internalQuery = User::withTrashed()->whereIn('role', ['admin','owner','mekanik','kasir']);
        if ($request->role && in_array($request->role, ['admin','owner','mekanik','kasir'])) {
            $internalQuery->where('role', $request->role);
        }
        if ($search) {
            $internalQuery->where(fn($q) => $q->where('name','like',"%{$search}%")->orWhere('email','like',"%{$search}%"));
        }
        $internalUsers = $internalQuery->orderByDesc('created_at')->paginate($perPage, ['*'], 'internal_page')->withQueryString();

        $customerQuery = User::withTrashed()->where('role', 'customer');
        if ($search) {
            $customerQuery->where(fn($q) => $q->where('name','like',"%{$search}%")->orWhere('email','like',"%{$search}%"));
        }
        $customers = $customerQuery->orderByDesc('created_at')->paginate($perPage, ['*'], 'customer_page')->withQueryString();

        return view('admin.users.index', compact('internalUsers', 'customers', 'perPage', 'tab'));
    }

    public function create() { 
        Gate::authorize('manage-data');
        return view('admin.users.form', ['user' => null]); 
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-data');
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|unique:users,phone',
            'role'     => 'required|in:admin,mekanik,kasir,customer,owner',
            'password' => 'required|min:6|confirmed',
            'photo'    => 'nullable|image|max:2048',
        ]);

        if ($request->role === 'owner' && $request->user()->role !== 'owner') {
            abort(403, 'Unauthorized. Only owners can create other owners.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('avatars', 'public');
        }

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'address'           => $request->address,
            'role'              => $request->role,
            'password'          => Hash::make($request->password),
            'photo'             => $photoPath,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function edit($id) 
    { 
        Gate::authorize('manage-data');
        $user = User::withTrashed()->findOrFail($id);
        Gate::authorize('manage', $user);
        return view('admin.users.form', compact('user')); 
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('manage-data');
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'role'  => 'required|in:admin,mekanik,kasir,customer,owner',
            'photo' => 'nullable|image|max:2048',
        ]);

        Gate::authorize('manage', $user);

        $data = $request->only('name', 'email', 'phone', 'address', 'role');
                
        if ($request->role === 'owner' && $request->user()->role !== 'owner') {
            $data['role'] = $user->role;
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('manage-data');
        Gate::authorize('manage', $user);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna dinonaktifkan.');
    }

    public function restore($id)
    {
        Gate::authorize('manage-data');
        $user = User::withTrashed()->findOrFail($id);
        Gate::authorize('manage', $user);
        $user->restore();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diaktifkan kembali.');
    }

    public function deletePhoto(User $user)
    {
        Gate::authorize('manage-data');
        Gate::authorize('manage', $user);
        
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->update(['photo' => null]);
        }
        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
