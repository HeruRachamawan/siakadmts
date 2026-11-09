<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Instrumen Telaah Soal HOTS - {{ $package->title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.8cm 1cm 0.8cm 1cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .header .title-main { 
            font-size: 11pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header .title-sub { 
            font-size: 9.5pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            margin: 1px 0;
        }

        .meta-table { 
            border-collapse: collapse; 
            margin-bottom: 6px; 
            font-size: 8.5pt; 
        }
        .meta-table td { 
            padding: 1px 4px; 
            vertical-align: top;
        }

        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            table-layout: fixed;
        }
        .matrix-table th, .matrix-table td {
            border: 1px solid #000;
            padding: 2px 1px;
            vertical-align: middle;
        }
        .matrix-table th {
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
        }
        .matrix-table td.col-v {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }
        .center { text-align: center; }

        .signatures {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
            font-size: 9pt;
            border-collapse: collapse;
        }
        .signatures td {
            border: none;
            padding: 2px 4px;
            vertical-align: top;
        }
        .sig-container {
            height: 50px;
            display: flex;
            align-items: center;
            margin: 2px 0;
        }
        .sig-img {
            max-height: 48px;
            max-width: 150px;
            object-fit: contain;
            display: block;
        }
        .sig-space {
            height: 50px;
        }

        .no-print {
            position: fixed;
            top: 12px;
            right: 12px;
            background: #059669;
            color: white;
            padding: 7px 14px;
            border-radius: 6px;
            font-family: sans-serif;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: none;
            z-index: 9999;
        }
        .no-print:hover { background: #047857; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨️ Cetak Instrumen Telaah (HOTS)</button>

    @php
        // Judul Asesmen
        $examTitle = 'ASESMEN SUMATIF AKHIR SEMESTER (ASAS)';
        if (!empty($package->exam_type)) {
            $t = strtoupper($package->exam_type);
            if (str_contains($t, 'TENGAH') || str_contains($t, 'ASTS') || str_contains($t, 'PTS')) {
                $examTitle = 'ASESMEN SUMATIF TENGAH SEMESTER (ASTS)';
            } elseif (str_contains($t, 'MADRASAH') || str_contains($t, 'AM') || str_contains($t, 'UM')) {
                $examTitle = 'ASESMEN MADRASAH (AM)';
            }
        }

        // Teks Kelas Terbilang
        $cleanClassName = strtoupper(trim($package->classRoom->name ?? 'VII'));
        if (str_contains($cleanClassName, '7') || str_contains($cleanClassName, 'VII')) {
            $classDisplay = 'VII (TUJUH)';
        } elseif (str_contains($cleanClassName, '8') || str_contains($cleanClassName, 'VIII')) {
            $classDisplay = 'VIII (DELAPAN)';
        } elseif (str_contains($cleanClassName, '9') || str_contains($cleanClassName, 'IX')) {
            $classDisplay = 'IX (SEMBILAN)';
        } else {
            $classDisplay = $cleanClassName;
        }

        // Total Soal (Default 45 sesuai data Excel)
        $totalQ = max($package->questions->count(), 45);
        $questionsByNum = $package->questions->keyBy('question_number');

        // Signature image Base64
        $sigPath = public_path('images/signatures/kepala_madrasah.png');
        $sigBase64 = file_exists($sigPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath)) : null;

        // Logika checkmark V matriks HOTS berdasar Excel data resmi
        $isV = function($aspectKey, $qNum) use ($questionsByNum) {
            $q = $questionsByNum->get($qNum);
            $qType = $q ? $q->question_type : '';
            $cog = $q ? strtoupper($q->cognitive_level ?? '') : '';

            if ($aspectKey === 'stimulus_menarik' || $aspectKey === 'stimulus_kontekstual' || $aspectKey === 'memproses_info' || $aspectKey === 'tersirat_stimulus') {
                return true;
            }
            if ($aspectKey === 'transfer_konsep') {
                return in_array($qNum, [3, 10, 25, 27, 30, 33, 34, 35, 41, 44]) || $cog === 'L3';
            }
            if ($aspectKey === 'mencari_kaitan') {
                return in_array($qNum, [5, 9]) || ($qNum >= 21 && $qNum <= 45) || in_array($qType, ['pg_complex', 'matching', 'essay']);
            }
            if ($aspectKey === 'menyelesaikan_masalah') {
                return in_array($qNum, [8, 16, 17, 18, 19, 20, 22, 23, 25, 27, 28, 29, 30, 44, 45]) || $cog === 'L2' || $cog === 'L3';
            }
            if ($aspectKey === 'menelaah_kritis') {
                return in_array($qNum, [3, 10, 25, 27, 30, 31, 32, 33, 34, 35, 41, 44]) || in_array($qType, ['true_false', 'essay']) || $cog === 'L3';
            }
            return false;
        };
    @endphp

    <!-- HEADER / KOP TELAAH -->
    <div class="header">
        <div class="title-main">INSTRUMEN TELAAH SOAL {{ $examTitle }} (HOTS)</div>
        <div class="title-sub">BENTUK TES HOTS</div>
        <div class="title-sub">{{ strtoupper($school->school_name ?? 'MADRASAH TSANAWIYAH AL-HASANAH') }}</div>
        <div class="title-sub">TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}</div>
    </div>

    <!-- IDENTITAS TELAAH -->
    <table class="meta-table">
        <tr>
            <td style="width: 170px; font-weight: bold;">NAMA PENGEMBANG SOAL</td>
            <td style="width: 8px;">:</td>
            <td>{{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">MATA PELAJARAN</td>
            <td>:</td>
            <td>{{ $package->subject->name ?? 'Informatika' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">KELAS</td>
            <td>:</td>
            <td>{{ $classDisplay }}</td>
        </tr>
    </table>

    <!-- TABEL MATRIKS 45 BUTIR SOAL -->
    <table class="matrix-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">NO</th>
                <th rowspan="2" style="width: 250px; text-align: left; padding-left: 4px;">Aspek yang ditelaah</th>
                <th colspan="{{ $totalQ }}">Butir Soal</th>
            </tr>
            <tr>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <th style="font-size: 7pt; width: 15px;">{{ $n }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <!-- 1. Stimulus Menarik -->
            <tr>
                <td class="center">1</td>
                <td style="padding-left: 4px;">Soal menggunakan stimulus yang menarik (baru, mendorong peserta didik untuk membaca).</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('stimulus_menarik', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>

            <!-- 2. Stimulus Kontekstual -->
            <tr>
                <td class="center">2</td>
                <td style="padding-left: 4px;">soal menggunakan stimulus yang kontekstual (gambar/grafik, teks, visualisasi, dll, sesuai dengan dunia nyata)</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('stimulus_kontekstual', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>

            <!-- 3. Header Level Kognitif Penalaran -->
            <tr style="background: #fafafa;">
                <td class="center">3</td>
                <td style="padding-left: 4px; font-weight: bold;">soal mengukur level kognitif penaralan menganalisis, mengevaluasi, mencipta) yang dalam penyelesaian nya dicirikan dengan salah satu atau lebih tahapan proses berfikir berikut :</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td></td>
                @endfor
            </tr>

            <!-- 3.1 Transfer satu konsep -->
            <tr>
                <td class="center"></td>
                <td style="padding-left: 12px;">● Transfer satu konsep ke konsep lainnya</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('transfer_konsep', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>

            <!-- 3.2 Memproses dan menerapkan informasi -->
            <tr>
                <td class="center"></td>
                <td style="padding-left: 12px;">● memproses dan menerapkan informasi</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('memproses_info', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>

            <!-- 3.3 Mencari kaitan dari berbagai informasi -->
            <tr>
                <td class="center"></td>
                <td style="padding-left: 12px;">● mencari kaitan dari berbagai informasi yang berbeda-beda</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('mencari_kaitan', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>

            <!-- 3.4 Menggunakan informasi untuk menyelesaikan masalah -->
            <tr>
                <td class="center"></td>
                <td style="padding-left: 12px;">● menggunakan informasi untuk menyelesaikan masalah</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('menyelesaikan_masalah', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>

            <!-- 3.5 Menelaah ide dan informasi secara kritis -->
            <tr>
                <td class="center"></td>
                <td style="padding-left: 12px;">● Menelaah ide dan informasi secara kritis</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('menelaah_kritis', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>

            <!-- 4. Jawaban tersirat pada stimulus -->
            <tr>
                <td class="center">4</td>
                <td style="padding-left: 4px;">Jawaban tersirat pada stimulus</td>
                @for ($n = 1; $n <= $totalQ; $n++)
                    <td class="col-v">{{ $isV('tersirat_stimulus', $n) ? 'V' : '' }}</td>
                @endfor
            </tr>
        </tbody>
    </table>

    <!-- TANDA TANGAN PENGESAHAN -->
    <table class="signatures">
        <tr>
            <td style="width: 50%; text-align: left; padding-left: 40px;">
                Mengetahui,<br>
                <strong>Kepala Madrasah</strong><br>
                @if($sigBase64)
                    <div class="sig-container">
                        <img src="{{ $sigBase64 }}" alt="Tanda Tangan Kepala Madrasah" class="sig-img">
                    </div>
                @else
                    <div class="sig-space"></div>
                @endif
                <strong><u>H. Umar Usman Ali, S.Pd.,S.Pd.I</u></strong>
            </td>
            <td style="width: 50%; text-align: left; padding-left: 160px;">
                Bogor, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}<br>
                <strong>Guru Mapel</strong><br>
                <div class="sig-space"></div>
                <strong><u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>
