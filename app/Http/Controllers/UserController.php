<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Menampilkan halaman utama manajemen user.
     */
    public function index()
    {
        // Ambil semua role untuk ditampilkan di dropdown form
        $roles = Role::all();
        return view('users.index', compact('roles'));
    }

    /**
     * Menyediakan data untuk DataTables.
     */
    public function data()
    {
        $users = User::with('role')->select('users.*');

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('action', function ($user) {
                $editBtn = '<a href="javascript:void(0)" data-id="'.$user->id.'" class="edit-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs">Edit</a>';
                
                // Jangan tampilkan tombol delete untuk user yang sedang login
                if ($user->id == auth()->id()) {
                    return $editBtn;
                }

                $deleteBtn = '<a href="javascript:void(0)" data-url="'.route('users.destroy', $user->id).'" class="delete-btn bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs ml-2">Delete</a>';
                
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Menyimpan user baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return response()->json(['success' => 'User created successfully.']);
    }

    /**
     * Mengambil data user untuk form edit.
     */
    public function edit(User $user)
    {
        return response()->json($user);
    }

    /**
     * Memperbarui data user.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ];

        // Jika password diisi, tambahkan validasi password
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $data = $request->except('password');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => 'User updated successfully.']);
    }

    /**
     * Menghapus user.
     */
    public function destroy(User $user)
    {
        // Tambahan: Pastikan user tidak menghapus diri sendiri
        if ($user->id == auth()->id()) {
            return response()->json(['error' => 'You cannot delete your own account.'], 403);
        }

        $user->delete();

        return response()->json(['success' => 'User deleted successfully.']);
    }
}