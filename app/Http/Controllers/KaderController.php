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

    public function create()
    {
        $this->checkBidanAccess();

        $posyandus = Posyandu::where('is_active', true)->orderBy('nama', 'asc')->get();

        return view('kader.create', compact('posyandus'));
    }

    public function store(Request $request)
    {
        $this->checkBidanAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'posyandu_id' => 'required|exists:posyandus,id',
        ]);

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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$kader->id,
            'email' => 'nullable|email|max:255|unique:users,email,'.$kader->id,
            'password' => 'nullable|string|min:6',
            'posyandu_id' => 'required|exists:posyandus,id',
        ]);

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
