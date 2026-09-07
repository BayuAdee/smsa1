<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Posyandu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalitaController extends Controller
{
    public function search(Request $request)
    {
        $keyword = trim($request->input('q', ''));
        $posyanduId = $request->input('posyandu_id');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        if ($request->has('periode') && ! empty($request->input('periode'))) {
            $parts = explode('-', $request->input('periode'));
            if (count($parts) === 2) {
                $bulan = (int) $parts[0];
                $tahun = (int) $parts[1];
            }
        }

        if (strlen($keyword) < 3) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Kata kunci minimal 3 karakter.',
            ]);
        }

        $user = Auth::user();
        $query = Anak::with(['posyandu'])
            ->where('status_aktif', true);

        if ($user->isKader() && $user->posyandu_id) {
            $query->where('posyandu_id', $user->posyandu_id);
        } elseif ($user->isBidan() && ! empty($posyanduId) && $posyanduId !== 'all') {
            $query->where('posyandu_id', $posyanduId);
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
                ->orWhere('nik', 'like', "%{$keyword}%")
                ->orWhere('nama_orang_tua', 'like', "%{$keyword}%")
                ->orWhere('token_akses', 'like', "%{$keyword}%");
        });

        if ($bulan && $tahun) {
            $query->with(['pengukurans' => function ($q) use ($bulan, $tahun) {
                $q->where('bulan_ukur', (int) $bulan)->where('tahun_ukur', (int) $tahun);
            }]);
        }

        $anaks = $query->orderBy('nama', 'asc')->limit(20)->get();

        $results = $anaks->map(function ($anak) use ($bulan, $tahun) {
            $sudahDiukur = false;
            $pengukuranData = null;
            $usiaPeriode = $anak->usia_bulan;

            if ($bulan && $tahun) {
                $tglUkurDummy = sprintf('%04d-%02d-01', (int) $tahun, (int) $bulan);
                $usiaPeriode = max(0, (int) floor(Carbon::parse($anak->tanggal_lahir)->diffInMonths(Carbon::parse($tglUkurDummy))));

                $p = $anak->pengukurans->first();
                if ($p) {
                    $sudahDiukur = true;
                    $pengukuranData = [
                        'id' => $p->id,
                        'tinggi_cm' => (float) $p->tinggi_cm,
                        'berat_kg' => (float) $p->berat_kg,
                        'tinggi_formatted' => number_format((float) $p->tinggi_cm, 1),
                        'berat_formatted' => number_format((float) $p->berat_kg, 1),
                    ];
                }
            }

            return [
                'id' => $anak->id,
                'nama' => $anak->nama,
                'nik' => $anak->nik,
                'tanggal_lahir' => $anak->tanggal_lahir ? $anak->tanggal_lahir->format('Y-m-d') : null,
                'tanggal_lahir_formatted' => $anak->tanggal_lahir ? $anak->tanggal_lahir->translatedFormat('d M Y') : '-',
                'jenis_kelamin' => $anak->jenis_kelamin,
                'status_bblr' => $anak->status_bblr,
                'nama_orang_tua' => $anak->nama_orang_tua,
                'token_akses' => $anak->token_akses,
                'posyandu_id' => $anak->posyandu_id,
                'posyandu_nama' => $anak->posyandu->nama ?? '-',
                'usia_bulan' => $anak->usia_bulan,
                'usia_periode' => $usiaPeriode,
                'sudah_diukur' => $sudahDiukur,
                'pengukuran' => $pengukuranData,
                'show_url' => route('balita.show', $anak->id),
                'edit_url' => route('balita.edit', $anak->id),
                'create_pengukuran_url' => route('pengukuran.create', ['anak_id' => $anak->id]),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results,
            'count' => $results->count(),
        ]);
    }

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

        $anaks = $query->orderBy('nama', 'asc')->paginate(25)->withQueryString();
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

        // Generate clean unique access token for parents (BALITA-UUID format)
        $validated['token_akses'] = Anak::generateTokenAkses();

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
