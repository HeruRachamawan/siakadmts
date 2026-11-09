<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kisi-Kisi Soal - {{ $package->title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 14px;
        }
        .header h2 { 
            margin: 0; 
            font-size: 13pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .header h3 { 
            margin: 3px 0 0 0; 
            font-size: 12pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10pt;
            border-collapse: collapse;
        }
        .meta-table td { 
            padding: 2px 4px; 
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 9.5pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }
        .data-table th {
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .center { text-align: center; }

        .stats-box {
            margin-top: 14px;
            margin-bottom: 10px;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .stats-table {
            border: none;
            border-collapse: collapse;
        }
        .stats-table td {
            border: none;
            padding: 1px 4px;
            vertical-align: top;
            font-size: 10pt;
        }

        .signatures {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
            font-size: 10pt;
            border: none;
            border-collapse: collapse;
        }
        .signatures td {
            border: none;
            padding: 4px;
            vertical-align: top;
        }

        .sig-container {
            height: 65px;
            display: flex;
            align-items: center;
            margin: 4px 0;
        }
        .sig-img {
            max-height: 60px;
            max-width: 180px;
            object-fit: contain;
            display: block;
        }
        .sig-space {
            height: 65px;
        }

        .no-print {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #059669;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: none;
            z-index: 9999;
        }
        .no-print:hover {
            background: #047857;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨️ Cetak Kisi-Kisi</button>

    @php
        // Judul Asesmen Dinamis (Default: ASAS sesuai format Word)
        $examTitle = 'ASESMEN SUMATIF AKHIR SEMESTER (ASAS)';
        if (!empty($package->exam_type)) {
            $t = strtoupper($package->exam_type);
            if (str_contains($t, 'TENGAH') || str_contains($t, 'ASTS') || str_contains($t, 'PTS')) {
                $examTitle = 'ASESMEN SUMATIF TENGAH SEMESTER (ASTS)';
            } elseif (str_contains($t, 'MADRASAH') || str_contains($t, 'AM') || str_contains($t, 'UM')) {
                $examTitle = 'ASESMEN MADRASAH (AM)';
            } elseif (str_contains($t, 'HARIAN') || str_contains($t, 'UH')) {
                $examTitle = 'ASESMEN FORMATIF / HARIAN';
            } elseif (str_contains($t, 'AKHIR') || str_contains($t, 'ASAS') || str_contains($t, 'PAS') || str_contains($t, 'PAT')) {
                $examTitle = 'ASESMEN SUMATIF AKHIR SEMESTER (ASAS)';
            } else {
                $examTitle = strtoupper($package->exam_type);
            }
        }

        // Hitung Statistik Persentase Soal per Level Kognitif
        $totalQ = $package->questions->count();
        $l1Count = 0;
        $l2Count = 0;
        $l3Count = 0;

        foreach ($package->questions as $q) {
            $lvl = strtoupper(trim($q->cognitive_level ?? ''));
            if ($lvl === 'L3' || str_starts_with($lvl, 'L3') || str_contains($lvl, 'C4') || str_contains($lvl, 'C5') || str_contains($lvl, 'C6')) {
                $l3Count++;
            } elseif ($lvl === 'L2' || str_starts_with($lvl, 'L2') || str_contains($lvl, 'C3')) {
                $l2Count++;
            } else {
                // Default ke L1 jika L1, C1, C2, atau belum diisi
                $l1Count++;
            }
        }

        $l1Pct = $totalQ > 0 ? round(($l1Count / $totalQ) * 100) : 0;
        $l2Pct = $totalQ > 0 ? round(($l2Count / $totalQ) * 100) : 0;
        $l3Pct = $totalQ > 0 ? (100 - $l1Pct - $l2Pct) : 0;
        if ($l3Pct < 0) { $l3Pct = 0; }

        // Signature image Base64 untuk memastikan selalu tampil offline maupun online
        $sigPath = public_path('images/signatures/kepala_madrasah.png');
        $sigBase64 = file_exists($sigPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath)) : null;

        // Label Bentuk Soal
        $qTypeLabels = [
            'pg' => 'Pilihan Ganda',
            'pg_complex' => 'PG Kompleks',
            'true_false' => 'Benar / Salah',
            'matching' => 'Menjodohkan',
            'short_answer' => 'Isian Singkat',
            'essay' => 'Uraian',
        ];

        // Format Semester
        $semStr = '1';
        if (strtolower($package->semester ?? '') === 'genap' || $package->semester == '2') {
            $semStr = '2';
        }
    @endphp

    <!-- HEADER / KOP -->
    <div class="header">
        <h2>KISI-KISI SOAL {{ $examTitle }}</h2>
        <h3>TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}</h3>
        <h3>{{ strtoupper($school->school_name ?? 'MTS. AL-HASANAH') }}</h3>
    </div>

    <!-- IDENTITAS / METADATA -->
    <table class="meta-table">
        <tr>
            <td style="width: 14%;">Nama Guru</td>
            <td style="width: 36%;">: {{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</td>
            <td style="width: 14%;">Kurikulum</td>
            <td style="width: 36%;">: {{ $package->curriculum ?? 'Kurikulum Merdeka' }}</td>
        </tr>
        <tr>
            <td>Mata Pelajaran</td>
            <td>: {{ $package->subject->name ?? 'Informatika' }}</td>
            <td>Jumlah soal</td>
            <td>: {{ $totalQ }} Soal</td>
        </tr>
        <tr>
            <td>Kelas / Semester</td>
            <td>: {{ $package->classRoom->name ?? 'VII' }}/{{ $semStr }}</td>
            <td>Bentuk Soal</td>
            <td>: {{ $package->assessment_type ?? 'HOTS' }}</td>
        </tr>
    </table>

    <!-- TABEL KISI-KISI 7 KOLOM -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 20%;">Tujuan Pembelajaran (TP)</th>
                <th style="width: 16%;">Materi</th>
                <th style="width: 40%;">Indikator Soal</th>
                <th style="width: 6%;">Level Kognitif</th>
                <th style="width: 7%;">Bentuk Soal</th>
                <th style="width: 7%;">Nomor Soal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($package->questions as $q)
                @php
                    $tp = $q->basic_competency ?: ($q->material ? 'Peserta didik mampu memahami konsep ' . $q->material : 'Peserta didik mampu memahami materi pokok.');
                    $materi = $q->material ?: '-';
                    $indikator = $q->indicator ?: 'Disajikan masalah terkait ' . ($q->material ?? 'materi') . ', peserta didik dapat menentukan solusi yang tepat.';
                    $cognitive = $q->cognitive_level ? strtoupper($q->cognitive_level) : 'L1';
                    $typeName = $qTypeLabels[$q->question_type] ?? strtoupper($q->question_type);
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $tp }}</td>
                    <td>{{ $materi }}</td>
                    <td>{{ $indikator }}</td>
                    <td class="center">{{ $cognitive }}</td>
                    <td class="center">{{ $typeName }}</td>
                    <td class="center">{{ $q->question_number }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Belum ada butir soal dalam paket kisi-kisi ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- STATISTIK PERSENTASE SOAL -->
    <div class="stats-box">
        <table class="stats-table">
            <tr>
                <td style="font-weight: bold; width: 140px;">Persentase Soal :</td>
                <td>
                    <div>L1 = &nbsp;{{ $l1Pct }} %</div>
                    <div>L2 = &nbsp;{{ $l2Pct }} %</div>
                    <div>L3 = &nbsp;{{ $l3Pct }} %</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TANDA TANGAN -->
    <table class="signatures">
        <tr>
            <td style="width: 50%; text-align: left; padding-left: 20px;">
                Mengetahui,<br>
                <strong>Kepala Madrasah</strong><br>
                @if($sigBase64)
                    <div class="sig-container">
                        <img src="{{ $sigBase64 }}" alt="Tanda Tangan Kepala Madrasah" class="sig-img">
                    </div>
                @else
                    <div class="sig-space"></div>
                @endif
                <strong><u>H. Umar Usman Ali, S.Pd,S.Pd.I</u></strong>
            </td>
            <td style="width: 50%; text-align: left; padding-left: 80px;">
                Bogor, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}<br>
                <strong>Guru Mata Pelajaran</strong><br>
                <div class="sig-space"></div>
                <strong><u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>
