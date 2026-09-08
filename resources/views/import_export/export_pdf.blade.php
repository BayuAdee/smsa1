<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan {{ strtoupper($jenisData) }} - {{ $namaPosyandu }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .header h2 {
            margin: 4px 0 0 0;
            font-size: 13px;
            font-weight: 600;
            color: #059669;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 11px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 140px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-tinggi { background-color: #fee2e2; color: #991b1b; }
        .badge-sedang { background-color: #fef3c7; color: #92400e; }
        .badge-rendah { background-color: #d1fae5; color: #065f46; }
        
        .footer-sig {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .footer-sig td {
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
        .sig-space {
            height: 60px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Printable Header Controls -->
    <div class="no-print" style="margin-bottom: 20px; background: #0f172a; padding: 12px 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; color: white;">
        <div>
            <strong style="color: #34d399;">Mode Pratinjau Cetak Laporan PDF</strong>
            <span style="font-size: 10px; color: #94a3b8; display: block;">Gunakan tombol di kanan untuk mencetak atau menyimpan ke format PDF.</span>
        </div>
        <div>
            <button onclick="window.print()" style="background: #10b981; color: #090d16; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11px;">🖨️ Cetak / Simpan PDF</button>
            <button onclick="window.close()" style="background: #334155; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11px; margin-left: 8px;">Tutup</button>
        </div>
    </div>

    <!-- Document Header -->
    <div class="header">
        <h1>SISTEM MONITORING STUNTING & SPK SAW POSYANDU</h1>
        <h2>
            @if($jenisData === 'profil_balita')
                LAPORAN PROFIL DATA BALITA (ANAK)
            @elseif($jenisData === 'pengukuran_bulanan')
                LAPORAN CATATAN PENGUKURAN BULANAN BALITA
            @else
                LAPORAN HASIL PERANGKINGAN PRIORITAS STUNTING METODE SAW
            @endif
        </h2>
    </div>

    <!-- Metadata Information -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Wilayah Posyandu</td>
            <td>: <strong>{{ $namaPosyandu }}</strong></td>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Periode Laporan</td>
            <td>: {{ $namaBulan }} {{ $tahun }}</td>
            <td class="meta-label">Total Data</td>
            <td>: <strong>{{ count($data) }} Record</strong></td>
        </tr>
    </table>

    <!-- Data Tables -->
    @if($jenisData === 'profil_balita')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Posyandu</th>
                <th>Nama Balita</th>
                <th>NIK</th>
                <th>Tgl Lahir</th>
                <th>Usia</th>
                <th>JK</th>
                <th>BB Lahir</th>
                <th>BBLR</th>
                <th>Nama Orang Tua</th>
                <th>Token Akses</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $row)
            <tr>
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>{{ $row->posyandu->nama ?? '-' }}</td>
                <td><strong>{{ $row->nama }}</strong></td>
                <td style="font-family: monospace;">{{ $row->nik ?? '-' }}</td>
                <td>{{ $row->tanggal_lahir ? $row->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                <td>{{ $row->usia_bulan }} bln</td>
                <td>{{ $row->jenis_kelamin }}</td>
                <td>{{ number_format($row->berat_lahir_gram) }} g</td>
                <td style="text-transform: uppercase;">{{ $row->status_bblr }}</td>
                <td>{{ $row->nama_orang_tua ?? '-' }}</td>
                <td style="font-family: monospace; font-size: 9px;">{{ $row->token_akses }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center; color: #94a3b8; padding: 20px;">Tidak ada data balita.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @elseif($jenisData === 'pengukuran_bulanan')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Posyandu</th>
                <th>Nama Balita</th>
                <th>NIK</th>
                <th>Usia Saat Ukur</th>
                <th>Tgl Ukur</th>
                <th>TB (cm)</th>
                <th>BB (kg)</th>
                <th>Petugas Input</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $row)
            <tr>
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>{{ $row->anak->posyandu->nama ?? '-' }}</td>
                <td><strong>{{ $row->anak->nama ?? '-' }}</strong></td>
                <td style="font-family: monospace;">{{ $row->anak->nik ?? '-' }}</td>
                <td>{{ $row->usia_bulan }} Bulan</td>
                <td>{{ $row->tanggal_ukur ? $row->tanggal_ukur->format('d/m/Y') : '-' }}</td>
                <td><strong>{{ $row->tinggi_cm }} cm</strong></td>
                <td><strong>{{ $row->berat_kg }} kg</strong></td>
                <td>{{ $row->pembuat->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; color: #94a3b8; padding: 20px;">Tidak ada data pengukuran di periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @else <!-- ranking_saw -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">Rank</th>
                <th>Posyandu</th>
                <th>Nama Balita</th>
                <th>Usia</th>
                <th>TB / BB</th>
                <th>Z-Score TB/U</th>
                <th>Z-Score BB/U</th>
                <th>Nilai V (SAW)</th>
                <th>Kategori Risiko</th>
                <th>Ket. C2</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $row)
            <tr>
                <td style="text-align: center; font-weight: bold;">#{{ $i + 1 }}</td>
                <td>{{ $row->posyandu->nama ?? '-' }}</td>
                <td><strong>{{ $row->anak->nama ?? '-' }}</strong></td>
                <td>{{ $row->pengukuran->usia_bulan ?? '-' }} bln</td>
                <td>{{ $row->pengukuran->tinggi_cm ?? '-' }}cm / {{ $row->pengukuran->berat_kg ?? '-' }}kg</td>
                <td>{{ number_format($row->z_tbu, 2) }} SD</td>
                <td>{{ number_format($row->z_bbu, 2) }} SD</td>
                <td style="font-weight: bold; color: #047857;">{{ number_format($row->nilai_v, 4) }}</td>
                <td>
                    @php
                        $katPdf = strtolower($row->kategori_risiko);
                    @endphp
                    @if(in_array($katPdf, ['tinggi', 'sangat tinggi', 'sangat_tinggi']))
                        <span class="badge badge-tinggi">RISIKO TINGGI</span>
                    @elseif($katPdf === 'sedang')
                        <span class="badge badge-sedang">RISIKO SEDANG</span>
                    @else
                        <span class="badge badge-rendah">RISIKO RENDAH</span>
                    @endif
                </td>
                <td style="font-size: 9px; color: #64748b;">
                    {{ $row->is_c2_estimasi ? 'Estimasi (BBLR ?)' : 'Normal' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada hasil kalkulasi SAW pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- Signature Block -->
    <table class="footer-sig">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Kader Posyandu</strong>
                <div class="sig-space"></div>
                ( ..................................... )
            </td>
            <td>
                {{ $namaPosyandu }}, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Bidan Desa / Penanggung Jawab</strong>
                <div class="sig-space"></div>
                ( ..................................... )
            </td>
        </tr>
    </table>

    <script>
        // Auto trigger print window on print view open
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
