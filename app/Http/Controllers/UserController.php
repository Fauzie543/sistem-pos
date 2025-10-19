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
        // Ambil role yang BUKAN superadmin untuk dropdown di modal
        $roles = Role::where('name', '!=', 'superadmin')->get();
        return view('users.index', compact('roles'));
    }

    /**
     * Menyediakan data untuk DataTables.
     */
    public function data()
    {
        // 1. Ambil ID perusahaan dari user yang sedang login
        $companyId = auth()->user()->company_id;

        // 2. Query user yang HANYA berasal dari company tersebut dan BUKAN superadmin
        $users = User::with('role')
            ->where('company_id', $companyId)
            ->whereHas('role', function ($query) {
                $query->where('name', '!=', 'superadmin');
            })
            ->select('users.*');

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('action', function ($user) {
                $editBtn = '<a href="javascript:void(0)" data-id="'.$user->id.'" class="edit-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs">Edit</a>';
                
                // User tidak bisa menghapus dirinya sendiri
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
        $companyId = auth()->user()->company_id;
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->where('company_id', $companyId)],
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'company_id' => $companyId, // <- INI KUNCINYA: Otomatis set company_id
        ]);

        return response()->json(['success' => 'Pegawai berhasil ditambahkan.']);
    }

    /**
     * Mengambil data user untuk form edit.
     */
    public function edit(User $user)
    {
        // Keamanan: Pastikan user yang diedit berasal dari company yang sama
        if ($user->company_id !== auth()->user()->company_id) {
            abort(404); // Sembunyikan, seolah-olah tidak ada
        }
        return response()->json($user);
    }

    /**
     * Memperbarui data user.
     */
    public function update(Request $request, User $user)
    {
        // Keamanan: Pastikan user yang diupdate berasal dari company yang sama
        if ($user->company_id !== auth()->user()->company_id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $data = $request->except('password');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => 'Data pegawai berhasil diperbarui.']);
    }

    /**
     * Menghapus user.
     */
    public function destroy(User $user)
    {
        // Keamanan: Pastikan user yang dihapus berasal dari company yang sama
        if ($user->company_id !== auth()->user()->company_id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }
        
        if ($user->id == auth()->id()) {
            return response()->json(['error' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 403);
        }

        $user->delete();

        return response()->json(['success' => 'Pegawai berhasil dihapus.']);
    }
}