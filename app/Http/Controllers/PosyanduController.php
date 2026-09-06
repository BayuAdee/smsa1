<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posyandu;

class PosyanduController extends Controller
{
    public function index()
    {
        $posyandus = Posyandu::withCount(['anaks', 'users'])->get();
        return view('posyandu.index', compact('posyandus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'wilayah' => 'nullable|string|max:255',
        ]);

        Posyandu::create($validated);

        return redirect()->route('posyandu.index')->with('success', 'Data Posyandu berhasil ditambahkan.');
    }
}
