<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kisi-Kisi Soal - {{ $package->title }}</title>
    <style id="page-size-style">
        @page {
            size: 330mm 215mm; /* Standar F4 / Folio Landscape (Lebar 33cm, Tinggi 21.5cm) */
            margin: 10mm 12mm 10mm 12mm;
        }
    </style>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
            background: #e2e8f0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-sheet {
            background: #fff;
            max-width: 330mm;
            min-height: 215mm;
            margin: 105px auto 30px auto;
            padding: 12mm 14mm;
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            border-radius: 4px;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        /* SCALING ZOOM PRESETS */
        body.scale-100 .print-sheet { zoom: 1; }
        body.scale-95 .print-sheet { zoom: 0.95; }
        body.scale-90 .print-sheet { zoom: 0.90; }
        body.scale-85 .print-sheet { zoom: 0.85; }

        .header {
            text-align: center;
            margin-bottom: 12px;
            font-family: 'Times New Roman', Times, serif;
        }
        .header h2 { 
            margin: 0; 
            font-size: 12pt; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            line-height: 1.3;
            color: #000;
        }
        .header h3 { 
            margin: 2px 0 0 0; 
            font-size: 11pt; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            line-height: 1.3;
            color: #000;
        }

        /* Harmonisasi menyeluruh font & angka Times New Roman */
        .print-sheet,
        .print-sheet *,
        .header,
        .header *,
        .meta-table,
        .meta-table *,
        .data-table,
        .data-table *,
        .stats-box,
        .stats-box *,
        .signatures,
        .signatures * {
            font-family: 'Times New Roman', Times, serif !important;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }
        .meta-table td { 
            padding: 2px 4px; 
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 9pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 3.5px 5px;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
        }
        .data-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .center { text-align: center; }

        .stats-box {
            margin-top: 10px;
            margin-bottom: 8px;
            font-size: 9.5pt;
            break-inside: avoid;
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
            font-size: 9.5pt;
        }

        .signatures {
            width: 100%;
            margin-top: 14px;
            break-inside: avoid;
            page-break-inside: avoid;
            font-size: 9.5pt;
            border: none;
            border-collapse: collapse;
        }
        .signatures td {
            border: none;
            padding: 4px;
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
            max-width: 160px;
            object-fit: contain;
            display: block;
        }
        .sig-space {
            height: 50px;
        }

        /* FLOATING PRINT TOOLBAR (SCREEN ONLY) */
        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #f8fafc;
            padding: 10px 16px;
            z-index: 99999;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
        }
        .toolbar-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .toolbar-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toolbar-badge {
            background: #0284c7;
            color: #ffffff;
            font-weight: 800;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .toolbar-title {
            font-weight: 700;
            font-size: 13px;
            color: #ffffff;
        }
        .toolbar-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .toolbar-group {
            display: flex;
            align-items: center;
            background: #1e293b;
            padding: 2px 4px;
            border-radius: 8px;
            border: 1px solid #334155;
            gap: 2px;
        }
        .group-label {
            font-size: 10px;
            font-weight: 600;
            color: #94a3b8;
            padding: 0 6px;
            text-transform: uppercase;
        }
        .btn-tool {
            background: transparent;
            color: #cbd5e1;
            border: none;
            padding: 4px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-tool:hover {
            color: #ffffff;
            background: #334155;
        }
        .btn-tool.active {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .select-tool {
            background: #0f172a;
            color: #f8fafc;
            border: 1px solid #475569;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            transition: all 0.15s ease;
        }
        .select-tool:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
        }
        .input-tool {
            background: #0f172a;
            color: #f8fafc;
            border: 1px solid #475569;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            outline: none;
            transition: all 0.15s ease;
        }
        .input-tool:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
        }
        .editable-date {
            cursor: pointer;
            padding: 1px 3px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }
        .editable-date:hover {
            background-color: #fef08a;
            outline: 1px dashed #ca8a04;
        }
        .editable-date:focus {
            background-color: #fef9c3;
            outline: 2px solid #2563eb;
            cursor: text;
        }
        @media print {
            .editable-date {
                background-color: transparent !important;
                outline: none !important;
                border: none !important;
                padding: 0 !important;
            }
        }
        .btn-word {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);
            transition: all 0.15s ease;
        }
        .btn-word:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.6);
        }
        .btn-word:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .btn-excel {
            background: linear-gradient(135deg, #107c41 0%, #0b5c30 100%);
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(16, 124, 65, 0.4);
            transition: all 0.15s ease;
        }
        .btn-excel:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 124, 65, 0.6);
        }
        .btn-excel:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .btn-print {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
            transition: all 0.15s ease;
        }
        .btn-print:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.6);
        }
        .toolbar-tips {
            max-width: 1280px;
            margin: 6px auto 0 auto;
            font-size: 10.5px;
            color: #cbd5e1;
            padding-top: 4px;
            border-top: 1px solid #1e293b;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; margin: 0 !important; }
            .print-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                min-height: auto !important;
            }
            body.scale-95 .print-sheet { zoom: 0.95; }
            body.scale-90 .print-sheet { zoom: 0.90; }
            body.scale-85 .print-sheet { zoom: 0.85; }
        }
    </style>
</head>
<body class="scale-100 paper-f4">
    <!-- FLOATING INTERACTIVE CONTROLLER (SCREEN ONLY) -->
    <div class="no-print print-toolbar">
        <div class="toolbar-wrapper">
            <div class="toolbar-brand">
                <span class="toolbar-badge">F4 Landscape</span>
                <span class="toolbar-title">Kisi-Kisi Soal Resmi Standar Kemenag</span>
            </div>

            <div class="toolbar-controls">
                <!-- Paper Size Selector -->
                <div class="toolbar-group">
                    <span class="group-label">Kertas:</span>
                    <button type="button" class="btn-tool active" id="btn-paper-f4" onclick="setPaper('f4')">F4 Landscape</button>
                    <button type="button" class="btn-tool" id="btn-paper-a4" onclick="setPaper('a4')">A4 Landscape</button>
                </div>

                <!-- Event / Acara Selector -->
                <div class="toolbar-group">
                    <span class="group-label">Acara:</span>
                    <select id="select-event" class="select-tool" onchange="updateExamEvent()">
                        <option value="ASAS">ASAS (Sumatif Akhir Semester)</option>
                        <option value="ASTS">ASTS (Sumatif Tengah Semester)</option>
                        <option value="ASAT">ASAT (Sumatif Akhir Tahun)</option>
                        <option value="AM">AM (Asesmen Madrasah)</option>
                        <option value="FORMATIF">UH / Formatif</option>
                    </select>
                </div>

                <!-- Semester Selector -->
                <div class="toolbar-group">
                    <span class="group-label">Semester:</span>
                    <select id="select-semester" class="select-tool" onchange="updateExamEvent()">
                        <option value="GANJIL_HURUF">Ganjil (Huruf)</option>
                        <option value="GANJIL_ROMAWI">I (Romawi)</option>
                        <option value="GANJIL_ANGKA">1 (Angka)</option>
                        <option value="GENAP_HURUF">Genap (Huruf)</option>
                        <option value="GENAP_ROMAWI">II (Romawi)</option>
                        <option value="GENAP_ANGKA">2 (Angka)</option>
                    </select>
                </div>

                <!-- Titimangsa / Bulan Selector -->
                <div class="toolbar-group">
                    <span class="group-label">Bulan/Tgl:</span>
                    <input type="text" id="input-print-date" class="input-tool" list="list-print-dates" value="{{ $package->execution_date ?: \Carbon\Carbon::now()->translatedFormat('F Y') }}" oninput="updatePrintDate(this.value)" placeholder="Contoh: Desember 2026" style="width: 130px;" title="Ketik atau pilih bulan/tanggal titimangsa">
                    <datalist id="list-print-dates">
                        <option value="November 2026">
                        <option value="Desember 2026">
                        <option value="Januari 2027">
                        <option value="Februari 2027">
                        <option value="Maret 2027">
                        <option value="April 2027">
                        <option value="Mei 2027">
                        <option value="Juni 2027">
                    </datalist>
                </div>

                <!-- Scaling Zoom -->
                <div class="toolbar-group">
                    <span class="group-label">Skala:</span>
                    <button type="button" class="btn-tool active" id="btn-scale-100" onclick="setScale(100)">100%</button>
                    <button type="button" class="btn-tool" id="btn-scale-95" onclick="setScale(95)">95%</button>
                    <button type="button" class="btn-tool" id="btn-scale-90" onclick="setScale(90)">90%</button>
                </div>

                <!-- Word (.doc) Download Button -->
                <button type="button" class="btn-word" id="btn-download-word" onclick="downloadWordDoc()" title="Unduh Lembar Kisi-Kisi dalam Format Microsoft Word (.doc)">
                    📄 Unduh Word (.doc)
                </button>

                <!-- Excel (.xls) Download Button -->
                <button type="button" class="btn-excel" id="btn-download-excel" onclick="downloadKisiExcel()" title="Unduh Lembar Kisi-Kisi dalam Format Microsoft Excel (.xls)">
                    📊 Unduh Excel (.xls)
                </button>

                <!-- Print Trigger Button -->
                <button type="button" class="btn-print" onclick="window.print()">
                    🖨️ Cetak Kisi-Kisi
                </button>
            </div>
        </div>

        <div class="toolbar-tips">
            <span>💡 <strong>Tips Cetak Landscape F4:</strong> Pada dialog printer (Ctrl + P), pastikan <em>Paper size</em> dipilih <strong>Folio / F4 / 215 × 330 mm</strong>, <em>Orientation</em> dipilih <strong>Landscape</strong>, dan <em>Margins</em> dipilih <strong>Default / None</strong>. | ✏️ <strong>Edit Titimangsa:</strong> Anda dapat langsung klik & ketik teks <u>Bulan/Tanggal</u> pada tanda tangan dokumen di bawah atau melalui kotak <em>Bulan/Tgl</em> di atas.</span>
        </div>
    </div>

    <div id="print-sheet" class="print-sheet">

    @php
        // Normalisasi Nama Kelas ke Angka Romawi Resmi
        $rawClass = trim($package->classRoom->name ?? 'VII');
        $cleanClassUpper = strtoupper($rawClass);
        if (!empty($package->target_grade)) {
            $romanClass = match((string)$package->target_grade) {
                '9' => 'IX',
                '8' => 'VIII',
                '7' => 'VII',
                default => strtoupper($package->target_grade)
            };
        } elseif (preg_match('/(?:kelas\s*)?9\s*([a-z]?)/i', $rawClass, $m)) {
            $romanClass = 'IX' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?8\s*([a-z]?)/i', $rawClass, $m)) {
            $romanClass = 'VIII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?7\s*([a-z]?)/i', $rawClass, $m)) {
            $romanClass = 'VII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (str_contains($cleanClassUpper, 'IX')) {
            $romanClass = $cleanClassUpper;
        } elseif (str_contains($cleanClassUpper, 'VIII')) {
            $romanClass = $cleanClassUpper;
        } elseif (str_contains($cleanClassUpper, 'VII')) {
            $romanClass = $cleanClassUpper;
        } else {
            $romanClass = $cleanClassUpper;
        }

        // Deteksi Semester Baku
        $rawSem = strtolower(trim($package->semester ?? ''));
        $semNum = ($rawSem === 'genap' || $rawSem === '2') ? '2' : '1';
        $semesterLabel = ($semNum === '2') ? 'GENAP' : 'GANJIL';
        $semesterWord = ($semNum === '2') ? 'Genap' : 'Ganjil';
        $semesterRoman = ($semNum === '2') ? 'II' : 'I';

        // Deteksi Acara Asesmen Baku & Full Title
        $examTypeKey = strtolower(trim($package->exam_type ?? 'sas'));
        $currentEvent = 'ASAS';

        if (str_contains($examTypeKey, 'tengah') || str_contains($examTypeKey, 'sts') || str_contains($examTypeKey, 'pts') || str_contains($examTypeKey, 'asts')) {
            $currentEvent = 'ASTS';
            $fullTitle = "ASESMEN SUMATIF TENGAH SEMESTER {$semesterLabel} (ASTS)";
        } elseif (str_contains($examTypeKey, 'madrasah') || str_contains($examTypeKey, 'am') || str_contains($examTypeKey, 'um')) {
            $currentEvent = 'AM';
            $fullTitle = "ASESMEN MADRASAH (AM)";
        } elseif (str_contains($examTypeKey, 'harian') || str_contains($examTypeKey, 'uh') || str_contains($examTypeKey, 'formatif')) {
            $currentEvent = 'FORMATIF';
            $fullTitle = "ASESMEN FORMATIF / ULANGAN HARIAN";
        } elseif (str_contains($examTypeKey, 'pat') || str_contains($examTypeKey, 'asat') || (str_contains($examTypeKey, 'akhir') && $semesterLabel === 'GENAP')) {
            $currentEvent = 'ASAT';
            $fullTitle = "ASESMEN SUMATIF AKHIR TAHUN (ASAT)";
        } else {
            // Default: SAS / ASAS
            $currentEvent = 'ASAS';
            $fullTitle = "ASESMEN SUMATIF AKHIR SEMESTER {$semesterLabel} (ASAS)";
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

        // Format Bentuk Soal Realistis (bukan teks HOTS)
        $distinctTypes = $package->questions->pluck('question_type')->unique()->filter()->values();
        $typeNames = [];
        foreach ($distinctTypes as $dt) {
            if (isset($qTypeLabels[$dt])) {
                $typeNames[] = $qTypeLabels[$dt];
            }
        }
        $bentukSoalDisplay = !empty($typeNames) ? implode(', ', $typeNames) : 'Pilihan Ganda';
    @endphp

    <!-- HEADER / KOP -->
    @php
        $schoolTitle = strtoupper(trim($school->school_name ?? ''));
        if (empty($schoolTitle) || str_contains($schoolTitle, 'YASPIN') || str_contains($schoolTitle, 'SMK')) {
            $schoolTitle = 'MTS AL - HASANAH';
        }
    @endphp
    <div class="header">
        <h2>KISI-KISI SOAL <span id="display-event-title">{{ $fullTitle }}</span></h2>
        <h3>TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}</h3>
        <h3>{{ $schoolTitle }}</h3>
    </div>

    <!-- IDENTITAS / METADATA -->
    <table class="meta-table" style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt;">
        <tr>
            <!-- Kolom Kiri -->
            <td style="width: 50%; vertical-align: top; text-align: left; padding: 0;">
                <table style="border-collapse: collapse;">
                    <tr>
                        <td style="width: 135px; padding: 2px 0;">Nama Guru</td>
                        <td style="padding: 2px 0;">: {{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;">Mata Pelajaran</td>
                        <td style="padding: 2px 0;">: {{ $package->subject->name ?? 'Informatika' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;">Kelas / Semester</td>
                        <td style="padding: 2px 0;">: <span id="display-semester-table" data-base-class="{{ $romanClass }}">{{ $romanClass }} / {{ $semesterWord }}</span></td>
                    </tr>
                </table>
            </td>
            <!-- Kolom Kanan (Posisi Pas di Sisi Kanan, Nyaman dan Seimbang) -->
            <td style="width: 50%; vertical-align: top; text-align: right; padding: 0 70px 0 0;">
                <div style="display: inline-block; text-align: left;">
                    <table style="border-collapse: collapse;">
                        <tr>
                            <td style="width: 110px; padding: 2px 0;">Kurikulum</td>
                            <td style="padding: 2px 0; white-space: nowrap;">: {{ $package->curriculum ?? 'Kurikulum Merdeka' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;">Jumlah soal</td>
                            <td style="padding: 2px 0; white-space: nowrap;">: {{ $totalQ }} Soal</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0;">Bentuk Soal</td>
                            <td style="padding: 2px 0; white-space: nowrap;">: {{ $bentukSoalDisplay }}</td>
                        </tr>
                    </table>
                </div>
            </td>
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
    <table class="signatures" style="width: 100%; border-collapse: collapse; margin-top: 14px;">
        <tr>
            <td style="width: 50%; text-align: left; vertical-align: top; padding-left: 20px;">
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
            <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 20px;">
                <div style="display: inline-block; text-align: left; min-width: 250px;">
                    <span id="display-print-city" class="editable-date" contenteditable="true" title="Klik untuk mengedit kota">Bogor</span>,<span id="display-print-space">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span id="display-print-date" class="editable-date" contenteditable="true" title="Klik langsung untuk mengedit bulan atau tanggal">{{ $package->execution_date ?: \Carbon\Carbon::now()->translatedFormat('F Y') }}</span><br>
                    <strong>Guru Mata Pelajaran</strong><br>
                    <div class="sig-space"></div>
                    <strong><u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</u></strong>
                </div>
            </td>
        </tr>
    </table>
    </div> <!-- END #print-sheet -->

    <script>
        function setPaper(size) {
            const styleEl = document.getElementById('page-size-style');
            const btnF4 = document.getElementById('btn-paper-f4');
            const btnA4 = document.getElementById('btn-paper-a4');
            const sheet = document.getElementById('print-sheet');

            if (size === 'f4') {
                styleEl.innerHTML = '@page { size: 330mm 215mm; margin: 10mm 12mm 10mm 12mm; }';
                btnF4.classList.add('active');
                btnA4.classList.remove('active');
                sheet.style.maxWidth = '330mm';
                sheet.style.minHeight = '215mm';
            } else {
                styleEl.innerHTML = '@page { size: 297mm 210mm; margin: 10mm 12mm 10mm 12mm; }';
                btnA4.classList.add('active');
                btnF4.classList.remove('active');
                sheet.style.maxWidth = '297mm';
                sheet.style.minHeight = '210mm';
            }
        }

        function setScale(val) {
            ['100', '95', '90'].forEach(s => {
                document.body.classList.remove('scale-' + s);
                const b = document.getElementById('btn-scale-' + s);
                if (b) b.classList.remove('active');
            });

            document.body.classList.add('scale-' + val);
            const activeBtn = document.getElementById('btn-scale-' + val);
            if (activeBtn) activeBtn.classList.add('active');
        }

        function updateExamEvent() {
            const eventVal = document.getElementById('select-event').value;
            const semSelectVal = document.getElementById('select-semester').value;
            const titleEl = document.getElementById('display-event-title');
            const semTableEl = document.getElementById('display-semester-table');
            
            const isGenap = semSelectVal.startsWith('GENAP');
            const semUpper = isGenap ? 'GENAP' : 'GANJIL';
            let semDisplay = '';

            if (semSelectVal.endsWith('_ROMAWI')) {
                semDisplay = isGenap ? 'II' : 'I';
            } else if (semSelectVal.endsWith('_ANGKA')) {
                semDisplay = isGenap ? '2' : '1';
            } else {
                semDisplay = isGenap ? 'Genap' : 'Ganjil';
            }

            let titleText = '';
            if (eventVal === 'ASAS') {
                titleText = 'ASESMEN SUMATIF AKHIR SEMESTER ' + semUpper + ' (ASAS)';
            } else if (eventVal === 'ASTS') {
                titleText = 'ASESMEN SUMATIF TENGAH SEMESTER ' + semUpper + ' (ASTS)';
            } else if (eventVal === 'ASAT') {
                titleText = 'ASESMEN SUMATIF AKHIR TAHUN (ASAT)';
            } else if (eventVal === 'AM') {
                titleText = 'ASESMEN MADRASAH (AM)';
            } else if (eventVal === 'FORMATIF') {
                titleText = 'ASESMEN FORMATIF / ULANGAN HARIAN';
            }

            if (titleEl) {
                titleEl.textContent = titleText;
            }

            if (semTableEl) {
                const baseClass = semTableEl.getAttribute('data-base-class') || 'VII';
                semTableEl.textContent = baseClass + ' / ' + semDisplay;
            }
        }

        function updatePrintDate(val) {
            const displayEl = document.getElementById('display-print-date');
            const spaceEl = document.getElementById('display-print-space');
            if (displayEl) {
                displayEl.textContent = val;
            }
            if (spaceEl) {
                if (/^\d/.test(val.trim())) {
                    spaceEl.innerHTML = ' ';
                } else {
                    spaceEl.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            }
        }

        function downloadWordDoc() {
            const btnWord = document.getElementById('btn-download-word');
            const originalText = btnWord ? btnWord.innerHTML : '📄 Unduh Word (.doc)';
            if (btnWord) {
                btnWord.innerHTML = '⏳ Menyiapkan Word...';
                btnWord.disabled = true;
            }

            try {
                const isA4 = document.getElementById('btn-paper-a4')?.classList.contains('active');
                const paperSizeCss = isA4 ? '297mm 210mm' : '330mm 215mm';
                const paperMarginCss = isA4 ? '10mm 15mm 10mm 15mm' : '12mm 15mm 12mm 15mm';
                const paperName = isA4 ? 'A4' : 'F4';

                const eventTitle = document.getElementById('display-event-title')?.textContent.trim() || @json($fullTitle);
                const academicYear = @json($package->academicYear->year ?? '2026/2027');
                const schoolTitle = @json($schoolTitle);
                const teacherName = @json($package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan'));
                const subjectName = @json($package->subject->name ?? 'Informatika');
                const semDisplay = document.getElementById('display-semester-table')?.textContent.trim() || @json($romanClass . ' / ' . $semesterWord);
                const curriculum = @json($package->curriculum ?? 'Kurikulum Merdeka');
                const totalQuestions = @json($totalQ . ' Soal');
                const questionTypes = @json($bentukSoalDisplay);

                const city = document.getElementById('display-print-city')?.textContent.trim() || 'Bogor';
                const date = document.getElementById('display-print-date')?.textContent.trim() || @json($package->execution_date ?: \Carbon\Carbon::now()->translatedFormat('F Y'));
                const space = document.getElementById('display-print-space')?.innerHTML || '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

                const l1Pct = @json((string)$l1Pct);
                const l2Pct = @json((string)$l2Pct);
                const l3Pct = @json((string)$l3Pct);

                // Ekstrak baris tabel kisi-kisi langsung dari tampilan DOM
                const tbodyRows = document.querySelectorAll('.data-table tbody tr');
                let rowsHtml = '';
                tbodyRows.forEach(tr => {
                    const tds = tr.querySelectorAll('td');
                    if (tds.length === 7) {
                        rowsHtml += `<tr>
                            <td style="border: 1pt solid #000000; padding: 4.5pt 3pt; text-align: center; vertical-align: top; font-size: 9pt;">${tds[0].innerHTML.trim()}</td>
                            <td style="border: 1pt solid #000000; padding: 4.5pt 5pt; text-align: left; vertical-align: top; font-size: 9pt;">${tds[1].innerHTML.trim()}</td>
                            <td style="border: 1pt solid #000000; padding: 4.5pt 5pt; text-align: left; vertical-align: top; font-size: 9pt;">${tds[2].innerHTML.trim()}</td>
                            <td style="border: 1pt solid #000000; padding: 4.5pt 5pt; text-align: left; vertical-align: top; font-size: 9pt;">${tds[3].innerHTML.trim()}</td>
                            <td style="border: 1pt solid #000000; padding: 4.5pt 2pt; text-align: center; vertical-align: top; font-size: 9pt;">${tds[4].innerHTML.trim()}</td>
                            <td style="border: 1pt solid #000000; padding: 4.5pt 2pt; text-align: center; vertical-align: top; font-size: 9pt;">${tds[5].innerHTML.trim()}</td>
                            <td style="border: 1pt solid #000000; padding: 4.5pt 2pt; text-align: center; vertical-align: top; font-size: 9pt;">${tds[6].innerHTML.trim()}</td>
                        </tr>`;
                    } else {
                        rowsHtml += tr.outerHTML;
                    }
                });

                // Tanda tangan Kepala Madrasah (Base64 MHTML)
                const sigBase64Data = @json($sigBase64);
                let sigImgHtml = '';
                if (sigBase64Data) {
                    sigImgHtml = '<div style="height: 50px; margin: 2px 0;"><img src="signature_kepala.png" width="140" height="48" style="display:block; object-fit:contain; border:none;" alt="Tanda Tangan Kepala Madrasah"></div>';
                } else {
                    sigImgHtml = '<div style="height: 50px;"></div>';
                }

                // Susun HTML Dokumen Word Standar Microsoft Word XML
                const wordHtml = `<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8">
  <title>Kisi-Kisi Soal ${subjectName} - ${schoolTitle}</title>
  <!--[if gte mso 9]>
  <xml>
    <w:WordDocument>
      <w:View>Print</w:View>
      <w:Zoom>100</w:Zoom>
      <w:DoNotOptimizeForBrowser/>
    </w:WordDocument>
  </xml>
  <![endif]-->
  <style>
    @page Section1 {
      size: ${paperSizeCss};
      mso-page-orientation: landscape;
      margin: ${paperMarginCss};
    }
    div.Section1 {
      page: Section1;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 9.5pt;
      color: #000000;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }
  </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #000000; margin: 0; padding: 0;">
  <div class="Section1">
    <!-- HEADER -->
    <div style="text-align: center; margin-bottom: 12pt;">
      <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; color: #000000;">
        KISI-KISI SOAL ${eventTitle}
      </div>
      <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; color: #000000; margin-top: 3pt;">
        TAHUN PELAJARAN ${academicYear}
      </div>
      <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; color: #000000; margin-top: 3pt;">
        ${schoolTitle}
      </div>
    </div>

    <!-- IDENTITAS -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-bottom: 10pt; font-size: 9.5pt; font-family: Arial, sans-serif;">
      <tr>
        <td width="50%" valign="top" style="text-align: left; padding: 0;">
          <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 9.5pt;">
            <tr>
              <td style="width: 135px; padding: 2px 0;">Nama Guru</td>
              <td style="padding: 2px 0;">: ${teacherName}</td>
            </tr>
            <tr>
              <td style="padding: 2px 0;">Mata Pelajaran</td>
              <td style="padding: 2px 0;">: ${subjectName}</td>
            </tr>
            <tr>
              <td style="padding: 2px 0;">Kelas / Semester</td>
              <td style="padding: 2px 0;">: ${semDisplay}</td>
            </tr>
          </table>
        </td>
        <td width="50%" valign="top" align="right" style="text-align: right; padding: 0 40px 0 0;">
          <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 9.5pt; display: inline-table; text-align: left;">
            <tr>
              <td style="width: 110px; padding: 2px 0;">Kurikulum</td>
              <td style="padding: 2px 0;">: ${curriculum}</td>
            </tr>
            <tr>
              <td style="padding: 2px 0;">Jumlah soal</td>
              <td style="padding: 2px 0;">: ${totalQuestions}</td>
            </tr>
            <tr>
              <td style="padding: 2px 0;">Bentuk Soal</td>
              <td style="padding: 2px 0;">: ${questionTypes}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- TABEL KISI-KISI 7 KOLOM -->
    <table width="100%" border="1" cellspacing="0" cellpadding="4" style="width: 100%; border-collapse: collapse; border: 1pt solid #000000; margin-top: 4pt; margin-bottom: 8pt; font-family: Arial, sans-serif; font-size: 9pt;">
      <thead>
        <tr style="background-color: #f1f5f9;">
          <th style="width: 4%; border: 1pt solid #000000; padding: 5pt 3pt; text-align: center; font-weight: bold;">NO</th>
          <th style="width: 20%; border: 1pt solid #000000; padding: 5pt 4pt; text-align: center; font-weight: bold;">Tujuan Pembelajaran (TP)</th>
          <th style="width: 16%; border: 1pt solid #000000; padding: 5pt 4pt; text-align: center; font-weight: bold;">Materi</th>
          <th style="width: 40%; border: 1pt solid #000000; padding: 5pt 4pt; text-align: center; font-weight: bold;">Indikator Soal</th>
          <th style="width: 6%; border: 1pt solid #000000; padding: 5pt 2pt; text-align: center; font-weight: bold;">Level Kognitif</th>
          <th style="width: 7%; border: 1pt solid #000000; padding: 5pt 2pt; text-align: center; font-weight: bold;">Bentuk Soal</th>
          <th style="width: 7%; border: 1pt solid #000000; padding: 5pt 2pt; text-align: center; font-weight: bold;">Nomor Soal</th>
        </tr>
      </thead>
      <tbody>
        ${rowsHtml}
      </tbody>
    </table>

    <!-- STATISTIK PERSENTASE -->
    <div style="margin-top: 8pt; margin-bottom: 12pt; font-size: 9.5pt; font-family: Arial, sans-serif;">
      <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 9.5pt;">
        <tr>
          <td style="font-weight: bold; width: 140px; vertical-align: top;">Persentase Soal :</td>
          <td style="vertical-align: top;">
            <div>L1 = &nbsp;${l1Pct} %</div>
            <div>L2 = &nbsp;${l2Pct} %</div>
            <div>L3 = &nbsp;${l3Pct} %</div>
          </td>
        </tr>
      </table>
    </div>

    <!-- TANDA TANGAN -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 14pt; font-size: 9.5pt; font-family: Arial, sans-serif;">
      <tr>
        <td width="50%" valign="top" style="text-align: left; padding-left: 20px;">
          Mengetahui,<br>
          <strong>Kepala Madrasah</strong><br>
          ${sigImgHtml}
          <strong><u>H. Umar Usman Ali, S.Pd,S.Pd.I</u></strong>
        </td>
        <td width="50%" valign="top" align="right" style="text-align: right; padding-right: 20px;">
          <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 9.5pt; display: inline-table; text-align: left; min-width: 230px;">
            <tr>
              <td>
                <span>${city},${space}${date}</span><br>
                <strong>Guru Mata Pelajaran</strong><br>
                <div style="height: 50px;"></div>
                <strong><u>${teacherName}</u></strong>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>`;

                // Kemas ke Format Dokumen MHTML Word (.doc)
                const boundary = "----=_NextPart_SiakadMts_KisiKisi_" + Date.now();
                let mhtml = "";

                mhtml += "MIME-Version: 1.0\r\n";
                mhtml += `Content-Type: multipart/related; boundary="${boundary}"\r\n\r\n`;

                // Part 1: Main HTML Body
                mhtml += `--${boundary}\r\n`;
                mhtml += "Content-Type: text/html; charset=utf-8\r\n";
                mhtml += "Content-Transfer-Encoding: 8bit\r\n\r\n";
                mhtml += wordHtml + "\r\n\r\n";

                // Part 2: Disematkan Gambar Tanda Tangan Kepala Madrasah jika ada
                if (sigBase64Data) {
                    const cleanB64 = sigBase64Data.replace(/^data:image\/[a-z]+;base64,/, '');
                    const wrappedB64 = cleanB64.replace(/(.{76})/g, "$1\r\n");
                    mhtml += `--${boundary}\r\n`;
                    mhtml += "Content-Type: image/png\r\n";
                    mhtml += "Content-Transfer-Encoding: base64\r\n";
                    mhtml += "Content-Location: signature_kepala.png\r\n\r\n";
                    mhtml += wrappedB64 + "\r\n\r\n";
                }

                mhtml += `--${boundary}--\r\n`;

                const blob = new Blob([mhtml], { type: 'application/msword;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                const safeMapel = (subjectName || 'Mapel').replace(/[^a-zA-Z0-9_-]/g, '_');
                const safeClass = (document.getElementById('display-semester-table')?.getAttribute('data-base-class') || 'Kelas').replace(/[^a-zA-Z0-9_-]/g, '_');
                const safeEvent = (document.getElementById('select-event')?.value || 'ASAS').replace(/[^a-zA-Z0-9_-]/g, '_');
                a.href = url;
                a.download = `Kisi_Kisi_${safeMapel}_${safeClass}_${safeEvent}_${paperName}.doc`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

                if (btnWord) {
                    btnWord.innerHTML = '✅ Terunduh!';
                    setTimeout(() => {
                        btnWord.innerHTML = originalText;
                        btnWord.disabled = false;
                    }, 2000);
                }
            } catch (err) {
                console.error('Word export error:', err);
                alert('Gagal mengunduh dokumen Word. Silakan coba kembali.');
                if (btnWord) {
                    btnWord.innerHTML = originalText;
                    btnWord.disabled = false;
                }
            }
        }

        function downloadKisiExcel() {
            const btnExcel = document.getElementById('btn-download-excel');
            const originalText = btnExcel ? btnExcel.innerHTML : '📊 Unduh Excel (.xls)';
            if (btnExcel) {
                btnExcel.innerHTML = '⏳ Menyiapkan Excel...';
                btnExcel.disabled = true;
            }

            try {
                const isA4 = document.getElementById('btn-paper-a4')?.classList.contains('active');
                const paperName = isA4 ? 'A4' : 'F4';
                const paperIndex = isA4 ? 9 : 14;

                const subjectName = @json($package->subject->name ?? 'Informatika');
                const teacherName = @json($package->teacher->user->name ?? ($package->teacher->name ?? 'Guru'));
                const academicYear = @json($package->academicYear->year ?? '2026/2027');
                const classDisplay = document.getElementById('display-semester-table')?.getAttribute('data-base-class') || @json($classDisplay ?? 'Kelas');
                const eventTitle = document.getElementById('display-event-title')?.textContent.trim() || 'ASESMEN SUMATIF';
                const printCity = document.getElementById('display-print-city')?.textContent.trim() || 'Bogor';
                const printDate = document.getElementById('display-print-date')?.textContent.trim() || 'Desember 2026';
                const headmasterName = @json($school->headmaster_name ?? 'Drs. H. Ahmad Fauzi, M.Pd');
                const headmasterNip = @json($school->headmaster_nip ?? '-');
                const teacherNip = @json($package->teacher->nip ?? '-');

                // Kloning tabel data kisi-kisi
                const originalTable = document.querySelector('table.data-table');
                if (!originalTable) throw new Error('Tabel kisi-kisi tidak ditemukan');
                const tableClone = originalTable.cloneNode(true);
                tableClone.setAttribute('border', '1');
                tableClone.style.borderCollapse = 'collapse';
                tableClone.querySelectorAll('th').forEach(th => {
                    th.style.backgroundColor = '#f1f5f9';
                    th.style.fontWeight = 'bold';
                    th.style.textAlign = 'center';
                    th.style.border = '1pt solid #000000';
                    th.style.padding = '4pt';
                });
                tableClone.querySelectorAll('td').forEach(td => {
                    td.style.border = '1pt solid #000000';
                    td.style.padding = '3pt 4pt';
                    td.style.verticalAlign = 'top';
                });

                const xmlPageSetup = `
<!--[if gte mso 9]>
<xml>
  <` + `x:ExcelWorkbook>
    <` + `x:ExcelWorksheets>
      <` + `x:ExcelWorksheet>
        <` + `x:Name>Kisi-Kisi Soal<` + `/x:Name>
        <` + `x:WorksheetOptions>
          <` + `x:Print>
            <` + `x:ValidPrinterInfo/>
            <` + `x:PaperSizeIndex>` + paperIndex + `<` + `/x:PaperSizeIndex>
            <` + `x:HorizontalResolution>600<` + `/x:HorizontalResolution>
            <` + `x:VerticalResolution>600<` + `/x:VerticalResolution>
            <` + `x:Scale>85<` + `/x:Scale>
          <` + `/x:Print>
          <` + `x:Selected/>
          <` + `x:DoNotDisplayGridlines/>
        <` + `/x:WorksheetOptions>
      <` + `/x:ExcelWorksheet>
    <` + `/x:ExcelWorksheets>
  <` + `/x:ExcelWorkbook>
</xml>
<![endif]-->
`;

                const excelHtml = `
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Kisi-Kisi Soal - ${subjectName}</title>
${xmlPageSetup}
<style>
  @page {
    mso-page-orientation: landscape;
    margin: 8mm 10mm 8mm 10mm;
  }
  body, table {
    font-family: 'Times New Roman', Times, serif;
    font-size: 9pt;
    color: #000000;
  }
  table {
    border-collapse: collapse;
    mso-table-lspace: 0pt;
    mso-table-rspace: 0pt;
  }
</style>
</head>
<body style="font-family: 'Times New Roman', serif;">
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 8pt;">
    <tr>
      <td colspan="7" style="text-align: center; font-size: 12pt; font-weight: bold; text-transform: uppercase;">
        KISI-KISI PENULISAN SOAL ${eventTitle}
      </td>
    </tr>
    <tr>
      <td colspan="7" style="text-align: center; font-size: 11pt; font-weight: bold; text-transform: uppercase;">
        MADRASAH TSANAWIYAH AL HASANAH
      </td>
    </tr>
    <tr>
      <td colspan="7" style="text-align: center; font-size: 10.5pt; font-weight: bold; text-transform: uppercase;">
        TAHUN PELAJARAN ${academicYear}
      </td>
    </tr>
    <tr style="height: 6pt;"><td colspan="7"></td></tr>
    <tr>
      <td colspan="2" style="font-weight: bold; width: 140pt;">Mata Pelajaran</td>
      <td style="width: 12pt; text-align: center;">:</td>
      <td style="width: 200pt;">${subjectName}</td>
      <td style="font-weight: bold; width: 120pt;">Kelas / Semester</td>
      <td style="width: 12pt; text-align: center;">:</td>
      <td>${classDisplay}</td>
    </tr>
    <tr>
      <td colspan="2" style="font-weight: bold;">Guru Penyusun</td>
      <td style="text-align: center;">:</td>
      <td>${teacherName}</td>
      <td style="font-weight: bold;">Alokasi Waktu</td>
      <td style="text-align: center;">:</td>
      <td>90 Menit</td>
    </tr>
    <tr style="height: 8pt;"><td colspan="7"></td></tr>
  </table>

  ${tableClone.outerHTML}

  <table style="width: 100%; border-collapse: collapse; margin-top: 14pt;">
    <tr style="height: 10pt;"><td colspan="7"></td></tr>
    <tr>
      <td colspan="3" style="text-align: left; vertical-align: top; font-size: 9.5pt;">
        Mengetahui,<br>
        <strong>Kepala Madrasah</strong><br>
        <br><br><br>
        <strong><u>${headmasterName}</u></strong><br>
        <span>NIP. ${headmasterNip}</span>
      </td>
      <td colspan="4" style="text-align: right; vertical-align: top; font-size: 9.5pt;">
        <table style="display: inline-table; text-align: left; border-collapse: collapse; border: none;">
          <tr>
            <td style="border: none; font-size: 9.5pt;">
              ${printCity}, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ${printDate}<br>
              <strong>Guru Mata Pelajaran</strong><br>
              <br><br><br>
              <strong><u>${teacherName}</u></strong><br>
              <span>NIP. ${teacherNip}</span>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>`;

                const blob = new Blob(['\uFEFF' + excelHtml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                const safeMapel = (subjectName || 'Mapel').replace(/[^a-zA-Z0-9_-]/g, '_');
                const safeClass = (classDisplay || 'Kelas').replace(/[^a-zA-Z0-9_-]/g, '_');
                const safeEvent = (document.getElementById('select-event')?.value || 'ASAS').replace(/[^a-zA-Z0-9_-]/g, '_');
                a.href = url;
                a.download = `Kisi_Kisi_${safeMapel}_${safeClass}_${safeEvent}_${paperName}.xls`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

                if (btnExcel) {
                    btnExcel.innerHTML = '✅ Terunduh!';
                    setTimeout(() => {
                        btnExcel.innerHTML = originalText;
                        btnExcel.disabled = false;
                    }, 2000);
                }
            } catch (err) {
                console.error('Excel export error:', err);
                alert('Gagal mengunduh file Excel. Silakan coba kembali.');
                if (btnExcel) {
                    btnExcel.innerHTML = originalText;
                    btnExcel.disabled = false;
                }
            }
        }

        // Sinkronisasi nilai default dropdown sesuai paket soal
        document.addEventListener('DOMContentLoaded', function() {
            const evEl = document.getElementById('select-event');
            const smEl = document.getElementById('select-semester');
            if (evEl) evEl.value = '{{ $currentEvent }}';
            if (smEl) smEl.value = '{{ $semesterLabel }}_HURUF';

            // Two-way sync: jika user klik & mengedit langsung pada lembar dokumen
            const displayDateEl = document.getElementById('display-print-date');
            const inputDateEl = document.getElementById('input-print-date');
            const spaceEl = document.getElementById('display-print-space');
            if (displayDateEl && inputDateEl) {
                displayDateEl.addEventListener('input', function() {
                    const text = this.textContent.trim();
                    inputDateEl.value = text;
                    if (spaceEl) {
                        if (/^\d/.test(text)) {
                            spaceEl.innerHTML = ' ';
                        } else {
                            spaceEl.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
