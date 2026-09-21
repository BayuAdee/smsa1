<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KaderController extends Controller
{
    private function checkBidanAccess()
    {
        if (! Auth::check() || ! Auth::user()->isBidan()) {
            abort(403, 'Akses khusus Bidan Desa');
        }
    }

    public function index(Request $request)
    {
        $this->checkBidanAccess();

        $search = $request->input('search');
        $posyanduFilter = $request->input('posyandu_id');

        $query = User::with('posyandu')->where('role', 'kader');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($posyanduFilter && $posyanduFilter !== 'all') {
            $query->where('posyandu_id', $posyanduFilter);
        }

        $kaders = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();
        $posyandus = Posyandu::all();

        return view('kader.index', compact('kaders', 'posyandus', 'search', 'posyanduFilter'));
    }

    public function search(Request $request)
    {
        $this->checkBidanAccess();

        $keyword = trim($request->input('q', ''));
        $posyanduFilter = $request->input('posyandu_id');

        if (strlen($keyword) < 3) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Kata kunci minimal 3 karakter.',
            ]);
        }

        $query = User::with('posyandu')->where('role', 'kader');

        if ($posyanduFilter && $posyanduFilter !== 'all') {
            $query->where('posyandu_id', $posyanduFilter);
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('username', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });

        $kaders = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $kaders->map(function ($kader) {
                return [
                    'id' => $kader->id,
                    'name' => $kader->name,
                    'username' => $kader->username,
                    'email' => $kader->email ?? '-',
                    'posyandu_nama' => $kader->posyandu->nama ?? 'Belum Ditugaskan',
                    'edit_url' => route('kader.edit', $kader->id),
                    'destroy_url' => route('kader.destroy', $kader->id),
                ];
            }),
        ]);
    }

    public function create()
    {
        $this->checkBidanAccess();

        $posyandus = Posyandu::where('is_active', true)->orderBy('nama', 'asc')->get();

        return view('kader.create', compact('posyandus'));
    }

    public function store(Request $request)
    {
        $this->checkBidanAccess();

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'posyandu_id' => 'required|exists:posyandus,id',
        ];

        $messages = [
            'name.required' => 'Nama lengkap kader wajib diisi.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username ini sudah terdaftar untuk pengguna lain.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar untuk pengguna lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'posyandu_id.required' => 'Silakan pilih Unit Posyandu penugasan.',
            'posyandu_id.exists' => 'Unit Posyandu yang dipilih tidak valid.',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['role'] = 'kader';
        $validated['password'] = Hash::make($validated['password']);

        $kader = User::create($validated);

        return redirect()->route('kader.index')->with('success', "Akun Kader {$kader->name} berhasil dibuat.");
    }

    public function edit($id)
    {
        $this->checkBidanAccess();

        $kader = User::where('role', 'kader')->findOrFail($id);
        $posyandus = Posyandu::where('is_active', true)->orWhere('id', $kader->posyandu_id)->orderBy('nama', 'asc')->get();

        return view('kader.edit', compact('kader', 'posyandus'));
    }

    public function update(Request $request, $id)
    {
        $this->checkBidanAccess();

        $kader = User::where('role', 'kader')->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$kader->id,
            'email' => 'nullable|email|max:255|unique:users,email,'.$kader->id,
            'password' => 'nullable|string|min:6',
            'posyandu_id' => 'required|exists:posyandus,id',
        ];

        $messages = [
            'name.required' => 'Nama lengkap kader wajib diisi.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username ini sudah terdaftar untuk pengguna lain.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar untuk pengguna lain.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'posyandu_id.required' => 'Silakan pilih Unit Posyandu penugasan.',
            'posyandu_id.exists' => 'Unit Posyandu yang dipilih tidak valid.',
        ];

        $validated = $request->validate($rules, $messages);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $kader->update($validated);

        return redirect()->route('kader.index')->with('success', "Data Kader {$kader->name} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $this->checkBidanAccess();

        $kader = User::where('role', 'kader')->findOrFail($id);
        $nama = $kader->name;
        $kader->delete();

        return redirect()->route('kader.index')->with('success', "Akun Kader {$nama} berhasil dihapus.");
    }
}
