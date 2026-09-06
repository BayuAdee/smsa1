<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BalitaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Anak::with(['posyandu', 'pengukuranTerakhir', 'hasilSawTerakhir'])
            ->where('status_aktif', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('token_akses', 'like', "%{$search}%")
                    ->orWhere('nama_orang_tua', 'like', "%{$search}%");
            });
        }

        $anaks = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();
        $posyandus = Auth::user()->isBidan() ? Posyandu::all() : collect();

        return view('balita.index', compact('anaks', 'posyandus', 'search'));
    }

    public function create()
    {
        $posyandus = Auth::user()->isBidan()
            ? Posyandu::where('is_active', true)->orderBy('nama', 'asc')->get()
            : Posyandu::where('id', Auth::user()->posyandu_id)->get();

        return view('balita.create', compact('posyandus'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:20',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'berat_lahir_gram' => 'required|numeric|min:500|max:6000',
            'status_bblr' => 'required|in:tidak,bblr,tidak_diketahui',
            'nama_orang_tua' => 'nullable|string|max:255',
        ];

        if ($user->isBidan()) {
            $rules['posyandu_id'] = 'required|exists:posyandus,id';
        }

        $validated = $request->validate($rules);

        if ($user->isKader()) {
            $validated['posyandu_id'] = $user->posyandu_id;
        }

        // Generate clean unique access token for parents
        $slugNama = Str::slug(substr($validated['nama'], 0, 10));
        $validated['token_akses'] = 'BALITA-'.strtoupper($slugNama).'-'.sprintf('%02d', rand(1, 99));

        $anak = Anak::create($validated);

        return redirect()->route('balita.index')->with('success', "Data balita {$anak->nama} berhasil ditambahkan dengan Token Akses: {$anak->token_akses}");
    }

    public function show($id)
    {
        $anak = Anak::with(['posyandu', 'pengukurans.pembuat', 'hasilSaws'])->findOrFail($id);

        return view('balita.show', compact('anak'));
    }

    public function edit($id)
    {
        $anak = Anak::findOrFail($id);
        $posyandus = Auth::user()->isBidan()
            ? Posyandu::where('is_active', true)->orWhere('id', $anak->posyandu_id)->orderBy('nama', 'asc')->get()
            : Posyandu::where('id', Auth::user()->posyandu_id)->get();

        return view('balita.edit', compact('anak', 'posyandus'));
    }

    public function update(Request $request, $id)
    {
        $anak = Anak::findOrFail($id);
        $user = Auth::user();

        $rules = [
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:20',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'berat_lahir_gram' => 'required|numeric|min:500|max:6000',
            'status_bblr' => 'required|in:tidak,bblr,tidak_diketahui',
            'nama_orang_tua' => 'nullable|string|max:255',
        ];

        if ($user->isBidan()) {
            $rules['posyandu_id'] = 'required|exists:posyandus,id';
        }

        $validated = $request->validate($rules);
        $anak->update($validated);

        return redirect()->route('balita.index')->with('success', "Data balita {$anak->nama} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $anak = Anak::findOrFail($id);
        $nama = $anak->nama;
        $anak->delete();

        return redirect()->route('balita.index')->with('success', "Data balita {$nama} telah dihapus.");
    }
}
