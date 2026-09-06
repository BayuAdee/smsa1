<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PosyanduController extends Controller
{
    private function checkBidanAccess()
    {
        if (! Auth::check() || ! Auth::user()->isBidan()) {
            abort(403, 'Akses khusus Bidan Desa');
        }
    }

    public function index()
    {
        // Non-aktif ditaruh di urutan paling bawah
        $posyandus = Posyandu::withCount(['anaks', 'users'])
            ->orderBy('is_active', 'desc')
            ->orderBy('nama', 'asc')
            ->get();

        return view('posyandu.index', compact('posyandus'));
    }

    public function store(Request $request)
    {
        $this->checkBidanAccess();

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:posyandus,nama',
            'wilayah' => 'nullable|string|max:255',
        ], [
            'nama.unique' => 'Nama Posyandu sudah terdaftar.',
        ]);

        $validated['is_active'] = true;

        Posyandu::create($validated);

        return redirect()->route('posyandu.index')->with('success', 'Data Posyandu berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $this->checkBidanAccess();

        $posyandu = Posyandu::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('posyandus', 'nama')->ignore($posyandu->id)],
            'wilayah' => 'nullable|string|max:255',
        ], [
            'nama.unique' => 'Nama Posyandu sudah terdaftar.',
        ]);

        $posyandu->update($validated);

        return redirect()->route('posyandu.index')->with('success', "Data Posyandu {$posyandu->nama} berhasil diperbarui.");
    }

    public function toggleStatus($id)
    {
        $this->checkBidanAccess();

        $posyandu = Posyandu::findOrFail($id);
        $posyandu->is_active = ! $posyandu->is_active;
        $posyandu->save();

        $statusText = $posyandu->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('posyandu.index')->with('success', "Status Posyandu {$posyandu->nama} berhasil {$statusText}.");
    }
}
