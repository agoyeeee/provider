<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return Inertia::render('Admin/UserView', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|unique:users,nik|size:16',
            'kontak' => 'nullable|string|max:15',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => [
                'required',
                'string',
                Rule::in($this->allowedRolesForAuthenticatedUser()),
            ],
        ]);

        User::create([
            'nik' => $request->nik,
            'kontak' => $request->kontak,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nik' => 'required|string|size:16|unique:users,nik,' . $user->id,
            'kontak' => 'nullable|string|max:15',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'role' => [
                'required',
                'string',
                Rule::in($this->allowedRolesForAuthenticatedUser($user)),
            ],
        ]);

        $updateData = $request->only(['nik', 'kontak', 'name', 'email', 'role']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            abort(403, 'Super Admin tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus');
    }

    /**
     * Determine allowed roles for the currently authenticated user.
     */
    protected function allowedRolesForAuthenticatedUser(?User $targetUser = null): array
    {
        $roles = ['pemilik_umkm', 'verifikator'];
        $currentUser = Auth::user();

        if ($currentUser && $currentUser->role === 'super_admin') {
            $roles[] = 'admin';

            if ($targetUser && $targetUser->role === 'super_admin') {
                $roles[] = 'super_admin';
            }
        } elseif ($targetUser && $targetUser->role === 'admin') {
            $roles[] = 'admin';
        } elseif ($targetUser && $targetUser->role === 'super_admin') {
            abort(403, 'Anda tidak memiliki akses untuk mengubah Super Admin.');
        }

        return array_values(array_unique($roles));
    }
}
