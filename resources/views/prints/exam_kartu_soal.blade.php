<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Soal - {{ $package->title }}</title>
    <style id="page-size-style">
        @page {
            size: 215mm 330mm; /* Standar F4 / Folio Portrait Indonesia */
            margin-top: 8mm;    /* Atas hemat 8mm */
            margin-bottom: 10mm; /* Bawah aman 10mm (anti-terpotong roller printer) */
            margin-left: 10mm;  /* Samping kiri 1cm */
            margin-right: 10mm; /* Samping kanan 1cm */
        }
    </style>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
            background: #e2e8f0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Sheet Container with realistic Paper preview */
        .print-sheet {
            background: #fff;
            max-width: 215mm;
            min-height: 330mm;
            margin: 110px auto 30px auto;
            padding: 10mm 14mm 14mm 14mm;
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            border-radius: 4px;
            box-sizing: border-box;
            transition: all 0.2s ease;
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.25;
        }

        /* Harmonisasi menyeluruh font & angka Times New Roman */
        .print-sheet,
        .print-sheet *,
        .kartu-container,
        .kartu-container *,
        .kartu-meta,
        .kartu-meta *,
        .rumusan-box,
        .rumusan-box *,
        .signatures,
        .signatures * {
            font-family: 'Times New Roman', Times, serif !important;
        }

        /* SCALING ZOOM PRESETS */
        body.scale-100 .print-sheet { zoom: 1; }
        body.scale-95 .print-sheet { zoom: 0.95; }
        body.scale-90 .print-sheet { zoom: 0.90; }
        body.scale-85 .print-sheet { zoom: 0.85; }

        /* KARTU CONTAINER */
        .kartu-container {
            border: 1.5px solid #000;
            margin-bottom: 16px;
            page-break-inside: avoid;
            break-inside: avoid;
            background: #fff;
        }
        .kartu-header {
            background-color: #f1f5f9;
            padding: 5px 10px;
            border-bottom: 1.5px solid #000;
            font-weight: bold;
            font-size: 9.5pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .kartu-meta {
            width: 100%;
            border-collapse: collapse;
        }
        .kartu-meta td {
            border: 1px solid #000;
            padding: 3px 6px;
            font-size: 9pt;
            vertical-align: top;
        }
        .rumusan-box {
            padding: 8px 10px;
            border-top: 1px solid #000;
            font-size: 10pt;
        }
        .rumusan-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 9.5pt;
            text-transform: uppercase;
        }
        .q-img {
            max-width: 75%;
            max-height: 140px;
            display: block;
            margin: 6px 0;
            border: 1px solid #ccc;
            padding: 2px;
        }
        .opts {
            margin: 4px 0 0 16px;
            padding: 0;
            list-style: none;
        }
        .opts li {
            margin-bottom: 2px;
            line-height: 1.2;
        }
        .opt-label {
            font-weight: bold;
            display: inline-block;
            width: 18px;
        }
        .opt-label-complex {
            font-weight: bold;
            width: 34px;
            min-width: 34px;
            display: inline-flex;
            align-items: center;
            white-space: nowrap !important;
        }
        .opt-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.2px solid #000;
            border-radius: 1.5px;
            text-align: center;
            line-height: 11px;
            font-size: 8pt;
            margin-right: 4px;
            box-sizing: border-box;
            vertical-align: middle;
            background: #fff;
        }
        .rumusan-box table {
            border-collapse: collapse;
            width: 100%;
            margin: 6px 0;
            font-size: 9pt;
        }
        .rumusan-box th, .rumusan-box td {
            border: 1px solid #444;
            padding: 2.5px 5px;
        }
        .rumusan-box th {
            background-color: #f2f2f2;
        }

        /* TANDA TANGAN */
        .signatures {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
            break-inside: avoid;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .signatures td {
            border: none;
            padding: 3px 6px;
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
            padding: 8px 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            z-index: 99999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
        }
        .toolbar-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .toolbar-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toolbar-badge {
            background: #8b5cf6;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 4px;
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
            align-items: center;
            flex-wrap: wrap;
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
            background: #8b5cf6;
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
            border-color: #a78bfa;
            box-shadow: 0 0 0 2px rgba(167, 139, 250, 0.25);
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

        /* PRINT STYLES */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                min-height: auto !important;
                border-radius: 0 !important;
            }
            .editable-date {
                background-color: transparent !important;
                outline: none !important;
                border: none !important;
                padding: 0 !important;
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
                <span class="toolbar-badge">F4 Folio</span>
                <span class="toolbar-title">Kartu Soal Standar Kemenag</span>
            </div>

            <div class="toolbar-controls">
                <!-- Paper Size Selector -->
                <div class="toolbar-group">
                    <span class="group-label">Kertas:</span>
                    <button type="button" class="btn-tool active" id="btn-paper-f4" onclick="setPaper('f4')">F4 (21.5 × 33 cm)</button>
                    <button type="button" class="btn-tool" id="btn-paper-a4" onclick="setPaper('a4')">A4 (21 × 29.7 cm)</button>
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
                    <button type="button" class="btn-tool" id="btn-scale-85" onclick="setScale(85)">85%</button>
                </div>

                <!-- Word (.doc) Download Button -->
                <button type="button" class="btn-word" id="btn-unduh-word" onclick="downloadKartuWord()" title="Unduh naskah kartu soal resmi berformat Microsoft Word (.doc)">
                    📄 Unduh Word (.doc)
                </button>

                <!-- Print Button -->
                <button type="button" class="btn-print" onclick="window.print()" title="Cetak langsung ke printer atau simpan PDF">
                    🖨️ Cetak Kartu Soal
                </button>
            </div>
        </div>

        <div class="toolbar-wrapper toolbar-tips">
            <span>📌 <strong>Tips Cetak Kartu Soal:</strong> Pada dialog cetak printer (Ctrl + P), pastikan <em>Paper size</em> sesuai (Folio/F4 atau A4), <em>Margins</em> = <strong>None / Minimum</strong>, dan hilangkan centang <em>Headers & footers</em>.</span>
        </div>
    </div>

    @php
        // Deteksi Kelas Baku
        $rawClass = trim($package->classRoom->name ?? 'VII');
        $cleanClassUpper = strtoupper($rawClass);

        if (!empty($package->target_grade)) {
            $classDisplay = match((string)$package->target_grade) {
                '9' => 'IX (SEMBILAN)',
                '8' => 'VIII (DELAPAN)',
                '7' => 'VII (TUJUH)',
                default => 'KELAS ' . strtoupper($package->target_grade)
            };
            $romanClass = match((string)$package->target_grade) {
                '9' => 'IX',
                '8' => 'VIII',
                '7' => 'VII',
                default => strtoupper($package->target_grade)
            };
        } elseif (preg_match('/(?:kelas\s*)?9\s*([a-z]?)/i', $rawClass, $m)) {
            $classDisplay = 'IX (SEMBILAN)' . (!empty($m[1]) ? ' ' . strtoupper($m[1]) : '');
            $romanClass = 'IX' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?8\s*([a-z]?)/i', $rawClass, $m)) {
            $classDisplay = 'VIII (DELAPAN)' . (!empty($m[1]) ? ' ' . strtoupper($m[1]) : '');
            $romanClass = 'VIII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?7\s*([a-z]?)/i', $rawClass, $m)) {
            $classDisplay = 'VII (TUJUH)' . (!empty($m[1]) ? ' ' . strtoupper($m[1]) : '');
            $romanClass = 'VII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } else {
            $classDisplay = $cleanClassUpper;
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
            $currentEvent = 'ASAS';
            $fullTitle = "ASESMEN SUMATIF AKHIR SEMESTER {$semesterLabel} (ASAS)";
        }

        // Deteksi Kode Mata Pelajaran
        $yearStr = $package->academicYear->year ?? '2026/2027';
        $yearParts = explode('/', $yearStr);
        $yearShort = count($yearParts) === 2 ? substr($yearParts[0], 2, 2) . '/' . substr($yearParts[1], 2, 2) : '26/27';

        $rawSubjName = strtolower($package->subject->name ?? '');
        $rawSubjCode = strtoupper(trim($package->subject->code ?? ''));

        $subjMap = [
            'akidah' => 'AA',
            'aqidah' => 'AA',
            'qur\'an' => 'QH',
            'quran' => 'QH',
            'hadis' => 'QH',
            'hadits' => 'QH',
            'fikih' => 'FIK',
            'fiqih' => 'FIK',
            'sejarah kebudayaan' => 'SKI',
            'ski' => 'SKI',
            'bahasa arab' => 'ARB',
            'bahasa indonesia' => 'BIN',
            'bahasa inggris' => 'BIG',
            'matematika' => 'MTK',
            'ilmu pengetahuan alam' => 'IPA',
            'ipa' => 'IPA',
            'ilmu pengetahuan sosial' => 'IPS',
            'ips' => 'IPS',
            'informatika' => 'INF',
            'ppkn' => 'PPKN',
            'pkn' => 'PPKN',
            'pjok' => 'PJOK',
            'jasmani' => 'PJOK',
            'olahraga' => 'PJOK',
            'seni' => 'SBK',
            'prakarya' => 'PRK',
            'sunda' => 'BSD',
        ];

        $matchedSubj = null;
        foreach ($subjMap as $key => $code) {
            if (str_contains($rawSubjName, $key)) {
                $matchedSubj = $code;
                break;
            }
        }

        if ($matchedSubj) {
            $subjCode = $matchedSubj;
        } elseif (!empty($rawSubjCode) && strlen($rawSubjCode) >= 2) {
            $subjCode = $rawSubjCode;
        } else {
            $subjCode = strtoupper(substr($package->subject->code ?? ($package->subject->name ?? 'INF'), 0, 3));
        }

        $examCode = strtoupper($package->exam_type ?? 'ASAS') . '-' . $semNum . '.' . $yearShort . '- ' . $subjCode;

        // Logo Base64
        $logoBase64 = null;
        $logoPath = null;
        if (!empty($school->logo)) {
            $checkPath = public_path('storage/' . $school->logo);
            if (file_exists($checkPath)) {
                $logoPath = $checkPath;
            }
        }
        if (!$logoPath) {
            $defaultLogo = public_path('images/logo_madrasah.png');
            if (file_exists($defaultLogo)) {
                $logoPath = $defaultLogo;
            }
        }
        if ($logoPath) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        // Tanda Tangan Base64
        $sigPath = public_path('images/signatures/kepala_madrasah.png');
        $sigBase64 = file_exists($sigPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath)) : null;

        $typeLabels = [
            'pg' => 'PILIHAN GANDA',
            'pg_complex' => 'PILIHAN GANDA KOMPLEKS',
            'true_false' => 'BENAR / SALAH',
            'matching' => 'MENJODOHKAN',
            'short_answer' => 'ISIAN SINGKAT',
            'essay' => 'URAIAN / ESSAY'
        ];
    @endphp

    <!-- REALISTIC PAPER PREVIEW CONTAINER -->
    <div class="print-sheet" id="print-sheet">
        <!-- KOTAK KODE NASKAH UJIAN (POJOK KANAN ATAS) -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 4px;">
            <div style="border: 1.5px solid #000; padding: 2px 10px; font-size: 9.5pt; font-family: 'Times New Roman', Times, serif; font-weight: bold; line-height: 1.2; background: #fff; display: inline-block;">
                <span id="display-exam-code">{{ $examCode }}</span>
            </div>
        </div>

        <!-- KOP RESMI MADRASAH -->
        <div class="header-kop" style="display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 3px double #000; padding-bottom: 6px; margin-bottom: 10px;">
            @if($logoBase64)
                <div style="position: absolute; left: 10px; top: 0;">
                    <img src="{{ $logoBase64 }}" alt="Logo Madrasah" style="height: 64px; width: auto; object-fit: contain;">
                </div>
            @endif
            <div style="text-align: center; width: 100%; padding: 0 80px;">
                <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2;">
                    MADRASAH TSANAWIYAH AL HASANAH
                </div>
                <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2; margin-top: 2px;">
                    KECAMATAN CIOMAS KABUPATEN BOGOR
                </div>
                <div style="font-size: 11.5pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2; margin-top: 2px;">
                    <span id="display-event-title">KARTU SOAL {{ $fullTitle }}</span>
                </div>
                <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2; margin-top: 2px;">
                    TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}
                </div>
            </div>
        </div>

        <!-- DAFTAR KARTU SOAL -->
        <div class="cards-wrapper">
            @foreach ($package->questions as $q)
                <div class="kartu-container">
                    <div class="kartu-header">
                        <span>KARTU SOAL NOMOR : {{ $q->question_number }}</span>
                        <span>BENTUK SOAL : {{ $typeLabels[$q->question_type] ?? strtoupper($q->question_type) }}</span>
                    </div>

                    <table class="kartu-meta">
                        <tr>
                            <td width="20%"><strong>Mata Pelajaran</strong></td>
                            <td width="30%">{{ $package->subject->name ?? '-' }}</td>
                            <td width="20%"><strong>Penyusun Soal</strong></td>
                            <td width="30%">{{ $package->teacher->user->name ?? ($package->teacher->name ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kelas / Semester</strong></td>
                            <td>{{ $romanClass }} / <span class="display-sem-word">{{ $semesterWord }}</span></td>
                            <td><strong>Level Kognitif</strong></td>
                            <td>{{ $q->cognitive_level ?? 'L1' }} (Kesukaran: {{ ucfirst($q->difficulty_level ?? 'sedang') }})</td>
                        </tr>
                        <tr>
                            <td><strong>Capaian Pembelajaran (CP) / KD</strong></td>
                            <td colspan="3">{{ $q->basic_competency ?: ($q->material ?: 'Materi Pokok ' . ($package->subject->name ?? '')) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Materi Pokok</strong></td>
                            <td>{{ $q->material ?: '-' }}</td>
                            <td><strong>Kunci Jawaban</strong></td>
                            <td><strong style="color: #059669; font-size: 10.5pt;">{{ $q->correct_answer ?: '-' }}</strong> (Bobot: {{ $q->score_weight ?: 1 }})</td>
                        </tr>
                        <tr>
                            <td><strong>Indikator Soal</strong></td>
                            <td colspan="3">{{ $q->indicator ?: '-' }}</td>
                        </tr>
                    </table>

                    <div class="rumusan-box">
                        <div class="rumusan-title">RUMUSAN BUTIR SOAL:</div>
                        <div>{!! (strip_tags($q->question_text) !== $q->question_text) ? strip_tags($q->question_text, '<b><strong><i><em><u><s><strike><sub><sup><span><p><div><br><table><thead><tbody><tr><th><td><ul><ol><li>') : nl2br(e($q->question_text ?? 'Soal belum diisi.')) !!}</div>

                        @if ($q->question_image)
                            <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                        @endif

                        @php
                            $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                        @endphp

                        <!-- 1. PG TUNGGAL -->
                        @if (!empty($opts) && $q->question_type === 'pg')
                            @php
                                $alphaMap = ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'];
                                $validOpts = [];
                                foreach ($alphaMap as $uKey => $lKey) {
                                    if (isset($opts[$uKey]) && trim((string)$opts[$uKey]) !== '') {
                                        $validOpts[] = [
                                            'upper' => $uKey,
                                            'lower' => $lKey,
                                            'label' => $lKey . '.',
                                            'val' => $opts[$uKey],
                                            'is_correct' => strtoupper($q->correct_answer ?? '') === $uKey
                                        ];
                                    }
                                }
                            @endphp
                            <ul class="opts">
                                @foreach ($validOpts as $opt)
                                    <li style="display: flex; align-items: baseline;">
                                        <span class="opt-label">{{ $opt['label'] }}</span>
                                        <span>{!! $opt['val'] !!}</span>
                                        @if ($opt['is_correct'])
                                            <span style="font-weight: bold; color: #059669; margin-left: 6px;">✓ (Kunci)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        <!-- 2. PG KOMPLEKS -->
                        @elseif (!empty($opts) && $q->question_type === 'pg_complex')
                            @php
                                $cKeys = array_map('trim', explode(',', strtoupper($q->correct_answer ?? '')));
                                $alphaMap = ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'];
                                $validOpts = [];
                                foreach ($alphaMap as $uKey => $lKey) {
                                    if (isset($opts[$uKey]) && trim((string)$opts[$uKey]) !== '') {
                                        $validOpts[] = [
                                            'upper' => $uKey,
                                            'lower' => $lKey,
                                            'label' => $lKey . '.',
                                            'val' => $opts[$uKey],
                                            'is_correct' => in_array($uKey, $cKeys)
                                        ];
                                    }
                                }
                            @endphp
                            <ul class="opts">
                                @foreach ($validOpts as $opt)
                                    <li style="display: flex; align-items: flex-start; margin-bottom: 2px;">
                                        <span class="opt-label-complex">
                                            <span class="opt-box">{{ $opt['is_correct'] ? '✓' : '' }}</span>{{ $opt['label'] }}
                                        </span>
                                        <span>{!! $opt['val'] !!}</span>
                                        @if ($opt['is_correct'])
                                            <span style="font-weight: bold; color: #059669; margin-left: 6px;">✓ (Kunci)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        <!-- 3. BENAR / SALAH -->
                        @elseif ($q->question_type === 'true_false')
                            @php
                                $statements = $opts['statements'] ?? [];
                                $isSingle = ($opts['mode'] ?? '') === 'single' || empty($statements) || count($statements) <= 1;
                            @endphp
                            @if ($isSingle)
                                <div style="margin-top: 6px; font-size: 9pt;">
                                    <span style="font-weight: bold;">Pilihan Kebenaran:</span> &nbsp;
                                    <span style="display: inline-block; padding: 2px 8px; border: 1px solid #444; border-radius: 4px; font-weight: bold; {{ strtoupper($q->correct_answer) === 'B' ? 'background: #d1fae5; color: #065f46;' : '' }}">
                                        {{ strtoupper($q->correct_answer) === 'B' ? '✓ ' : '' }}BENAR (B)
                                    </span>
                                    &nbsp;
                                    <span style="display: inline-block; padding: 2px 8px; border: 1px solid #444; border-radius: 4px; font-weight: bold; {{ strtoupper($q->correct_answer) === 'S' ? 'background: #fee2e2; color: #991b1b;' : '' }}">
                                        {{ strtoupper($q->correct_answer) === 'S' ? '✓ ' : '' }}SALAH (S)
                                    </span>
                                </div>
                            @elseif (!empty($statements))
                                <table style="width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8.5pt;">
                                    <tr style="background: #f1f5f9;">
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; width: 25px;">No</th>
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left;">Pernyataan</th>
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; width: 55px; text-align: center;">Kunci</th>
                                    </tr>
                                    @foreach ($statements as $sIdx => $st)
                                        <tr>
                                            <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center;">{{ $sIdx + 1 }}</td>
                                            <td style="border: 1px solid #ccc; padding: 2px 5px;">{{ $st['text'] }}</td>
                                            <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; font-weight: bold; color: {{ ($st['answer'] ?? '') === 'B' ? '#059669' : '#dc2626' }};">
                                                {{ ($st['answer'] ?? '') === 'B' ? 'Benar (B)' : 'Salah (S)' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        <!-- 4. MENJODOHKAN -->
                        @elseif ($q->question_type === 'matching')
                            @php
                                $pairs = $opts['pairs'] ?? [];
                                $isSingle = ($opts['mode'] ?? '') === 'single' || empty($pairs) || count($pairs) <= 1;
                            @endphp
                            @if ($isSingle)
                                <table style="width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8.5pt;">
                                    <tr style="background: #f1f5f9;">
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left; width: 45%;">Premis (Teks Soal)</th>
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left; width: 45%;">Pasangan Jawaban</th>
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; width: 10%;">Kunci</th>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ccc; padding: 2px 5px;">{!! strip_tags($q->question_text) !!}</td>
                                        <td style="border: 1px solid #ccc; padding: 2px 5px;">
                                            <strong>{{ strtoupper($q->correct_answer ?: ($opts['key'] ?? 'A')) }}.</strong> {{ $opts['right_text'] ?? ($pairs[0]['right'] ?? '-') }}
                                        </td>
                                        <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; font-weight: bold; color: #059669;">
                                            → {{ strtoupper($q->correct_answer ?: ($opts['key'] ?? 'A')) }}
                                        </td>
                                    </tr>
                                </table>
                            @elseif (!empty($pairs))
                                <table style="width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8.5pt;">
                                    <tr style="background: #f1f5f9;">
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left; width: 45%;">Premis Kiri</th>
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left; width: 45%;">Pilihan Kanan</th>
                                        <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; width: 10%;">Kunci</th>
                                    </tr>
                                    @foreach ($pairs as $pIdx => $pair)
                                        <tr>
                                            <td style="border: 1px solid #ccc; padding: 2px 5px;">{{ $pIdx + 1 }}. {{ $pair['left'] }}</td>
                                            <td style="border: 1px solid #ccc; padding: 2px 5px;">{{ chr(65 + $pIdx) }}. {{ $pair['right'] }}</td>
                                            <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; font-weight: bold; color: #059669;">
                                                → {{ $pair['key'] ?? chr(65 + $pIdx) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- TANDA TANGAN & PENGESAHAN RESMI -->
        <table class="signatures">
            <tr>
                <td style="width: 50%; text-align: left;">
                    Mengetahui,<br>
                    <strong>Kepala Madrasah</strong>
                    <div class="sig-container">
                        @if($sigBase64)
                            <img src="{{ $sigBase64 }}" class="sig-img" alt="TTD Kepala Madrasah">
                        @else
                            <div class="sig-space"></div>
                        @endif
                    </div>
                    <strong><u>{{ $school->headmaster_name ?? 'Drs. H. Ahmad Fauzi, M.Pd' }}</u></strong><br>
                    <span>NIP. {{ $school->headmaster_nip ?? '197508152005011003' }}</span>
                </td>
                <td style="width: 50%; text-align: left; padding-left: 40px;">
                    Ciomas, <span id="display-print-date" class="editable-date" title="Klik untuk edit tanggal langsung">{{ $package->execution_date ?: \Carbon\Carbon::now()->translatedFormat('F Y') }}</span><br>
                    <strong>Guru Mata Pelajaran</strong>
                    <div class="sig-space"></div>
                    <strong><u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</u></strong><br>
                    <span>NIP. {{ $package->teacher->nip ?? '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <script>
        function setPaper(size) {
            const styleEl = document.getElementById('page-size-style');
            const btnF4 = document.getElementById('btn-paper-f4');
            const btnA4 = document.getElementById('btn-paper-a4');
            const sheet = document.getElementById('print-sheet');

            if (size === 'f4') {
                styleEl.innerHTML = '@page { size: 215mm 330mm; margin: 8mm 10mm 10mm 10mm; }';
                btnF4.classList.add('active');
                btnA4.classList.remove('active');
                sheet.style.maxWidth = '215mm';
                sheet.style.minHeight = '330mm';
            } else {
                styleEl.innerHTML = '@page { size: 210mm 297mm; margin: 8mm 10mm 10mm 10mm; }';
                btnA4.classList.add('active');
                btnF4.classList.remove('active');
                sheet.style.maxWidth = '210mm';
                sheet.style.minHeight = '297mm';
            }
        }

        function setScale(val) {
            ['100', '95', '90', '85'].forEach(s => {
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
            const semVal = document.getElementById('select-semester').value;
            const titleEl = document.getElementById('display-event-title');
            const codeEl = document.getElementById('display-exam-code');
            
            let semNum = '1';
            let semWord = 'Ganjil';
            if (semVal.includes('GENAP')) {
                semNum = '2';
                semWord = 'Genap';
            }

            document.querySelectorAll('.display-sem-word').forEach(el => {
                el.textContent = semWord;
            });

            let eventName = 'ASESMEN SUMATIF AKHIR SEMESTER';
            if (eventVal === 'ASTS') eventName = 'ASESMEN SUMATIF TENGAH SEMESTER';
            else if (eventVal === 'ASAT') eventName = 'ASESMEN SUMATIF AKHIR TAHUN';
            else if (eventVal === 'AM') eventName = 'ASESMEN MADRASAH';
            else if (eventVal === 'FORMATIF') eventName = 'ASESMEN FORMATIF / ULANGAN HARIAN';

            if (titleEl) {
                if (eventVal === 'ASAT' || eventVal === 'AM' || eventVal === 'FORMATIF') {
                    titleEl.textContent = 'KARTU SOAL ' + eventName + ' (' + eventVal + ')';
                } else {
                    const semLabel = semNum === '2' ? 'GENAP' : 'GANJIL';
                    titleEl.textContent = 'KARTU SOAL ' + eventName + ' ' + semLabel + ' (' + eventVal + ')';
                }
            }

            if (codeEl) {
                const yearShort = '{{ $yearShort }}';
                const subjCode = '{{ $subjCode }}';
                codeEl.textContent = eventVal + '-' + semNum + '.' + yearShort + '- ' + subjCode;
            }
        }

        function updatePrintDate(val) {
            const dateEl = document.getElementById('display-print-date');
            if (dateEl) {
                dateEl.textContent = val.trim() || 'Desember 2026';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dateEl = document.getElementById('display-print-date');
            if (dateEl) {
                dateEl.setAttribute('contenteditable', 'true');
                dateEl.addEventListener('input', function() {
                    const inputEl = document.getElementById('input-print-date');
                    if (inputEl) inputEl.value = this.textContent.trim();
                });
            }

            const evEl = document.getElementById('select-event');
            const smEl = document.getElementById('select-semester');
            if (evEl) evEl.value = '{{ $currentEvent }}';
            if (smEl) smEl.value = '{{ $semesterLabel === "GENAP" ? "GENAP_HURUF" : "GANJIL_HURUF" }}';
        });

        // Ekspor Dokumen Resmi Kartu Soal ke Microsoft Word (.doc)
        function downloadKartuWord() {
            const btnWord = document.getElementById('btn-unduh-word');
            const originalText = btnWord ? btnWord.innerHTML : '';
            if (btnWord) {
                btnWord.innerHTML = '⏳ Menyiapkan Word...';
                btnWord.disabled = true;
            }

            try {
                const isA4 = document.getElementById('btn-paper-a4')?.classList.contains('active');
                const paperName = isA4 ? 'A4' : 'F4';
                const paperSizeCss = isA4 ? '210mm 297mm' : '215.9mm 330.2mm';
                const paperMarginCss = '10mm 12mm 10mm 12mm';

                const subjectName = @json($package->subject->name ?? 'Informatika');
                const classDisplay = @json($classDisplay ?? 'Kelas');
                const academicYear = @json($package->academicYear->year ?? '2026/2027');
                const examCode = document.getElementById('display-exam-code')?.textContent.trim() || @json($examCode);
                const eventTitle = document.getElementById('display-event-title')?.textContent.trim() || @json('KARTU SOAL ' . $fullTitle);
                const printDate = document.getElementById('display-print-date')?.textContent.trim() || 'Desember 2026';
                const teacherName = @json($package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan'));
                const headmasterName = @json($school->headmaster_name ?? 'Drs. H. Ahmad Fauzi, M.Pd');

                // Kloning container butir kartu
                const originalContainer = document.querySelector('.cards-wrapper');
                if (!originalContainer) throw new Error('Kontainer kartu tidak ditemukan');
                const cloneContainer = originalContainer.cloneNode(true);

                // Koleksi dan bundle gambar
                const imgParts = [];
                let imgSeq = 1;

                // 1. Logo Madrasah
                const logoBase64Data = @json($logoBase64);
                let logoFileName = '';
                if (logoBase64Data) {
                    logoFileName = 'logo_madrasah.png';
                    const cleanLogoB64 = logoBase64Data.replace(/^data:image\/[a-z]+;base64,/, '');
                    imgParts.push({
                        name: logoFileName,
                        type: 'image/png',
                        base64: cleanLogoB64
                    });
                }

                // 2. Gambar stimulus
                const realImgs = originalContainer.querySelectorAll('img.q-img');
                const cloneImgs = cloneContainer.querySelectorAll('img.q-img');
                cloneImgs.forEach((img, idx) => {
                    let b64 = '';
                    const src = img.getAttribute('src') || '';
                    if (src.startsWith('data:image')) {
                        b64 = src;
                    } else if (realImgs[idx]) {
                        try {
                            const c = document.createElement('canvas');
                            c.width = realImgs[idx].naturalWidth || realImgs[idx].width || 120;
                            c.height = realImgs[idx].naturalHeight || realImgs[idx].height || 120;
                            const ctx = c.getContext('2d');
                            ctx.drawImage(realImgs[idx], 0, 0);
                            b64 = c.toDataURL('image/png');
                        } catch(e) {}
                    }

                    if (b64) {
                        const fileName = 'stimulus_' + (imgSeq++) + '.png';
                        const cleanB64 = b64.replace(/^data:image\/[a-z0-9-+.]+;base64,/i, '');
                        imgParts.push({
                            name: fileName,
                            type: 'image/png',
                            base64: cleanB64
                        });
                        img.setAttribute('src', fileName);
                    }
                    img.style.maxWidth = '75%';
                    img.style.height = 'auto';
                    img.style.display = 'block';
                    img.style.margin = '4pt 0';
                });

                // Perapian kartu di dalam Word
                cloneContainer.querySelectorAll('.kartu-container').forEach(card => {
                    card.style.border = '1.5pt solid #000000';
                    card.style.marginBottom = '14pt';
                    card.style.pageBreakInside = 'avoid';
                    card.style.breakInside = 'avoid';
                    card.style.fontFamily = "'Times New Roman', Times, serif";
                });

                cloneContainer.querySelectorAll('.kartu-meta').forEach(tbl => {
                    tbl.setAttribute('border', '1');
                    tbl.setAttribute('cellspacing', '0');
                    tbl.setAttribute('cellpadding', '2');
                    tbl.style.borderCollapse = 'collapse';
                    tbl.style.border = '1pt solid #000000';
                    tbl.querySelectorAll('td').forEach(c => {
                        c.style.border = '1pt solid #000000';
                        c.style.padding = '2.5pt 4pt';
                        c.style.fontFamily = "'Times New Roman', Times, serif";
                        c.style.fontSize = '9pt';
                    });
                });

                let wordHtml = `
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Kartu Soal - ${subjectName}</title>
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
      mso-page-orientation: portrait;
      margin: ${paperMarginCss};
      mso-header-margin: 0.2in;
      mso-footer-margin: 0.2in;
    }
    div.Section1 {
      page: Section1;
      font-family: 'Times New Roman', Times, serif;
      font-size: 10pt;
      line-height: 1.25;
      color: #000000;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .kartu-container {
      border: 1.5pt solid #000000;
      margin-bottom: 14pt;
      page-break-inside: avoid;
      break-inside: avoid;
      mso-pagination: lines-together;
      font-family: 'Times New Roman', Times, serif;
    }
    .kartu-header {
      background-color: #f1f5f9;
      padding: 4pt 8pt;
      border-bottom: 1.5pt solid #000000;
      font-weight: bold;
      font-size: 9.5pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .rumusan-box {
      padding: 6pt 8pt;
      font-family: 'Times New Roman', Times, serif;
      font-size: 10pt;
    }
    .opt-label {
      font-weight: bold;
      display: inline-block;
      width: 15pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .opt-label-complex {
      font-weight: bold;
      display: inline-block;
      width: 26pt;
      white-space: nowrap;
      font-family: 'Times New Roman', Times, serif;
    }
    .opt-box {
      display: inline-block;
      width: 9.5pt;
      height: 9.5pt;
      border: 1pt solid #000;
      text-align: center;
      line-height: 9pt;
      font-size: 7.5pt;
      margin-right: 3pt;
      vertical-align: middle;
      font-family: 'Times New Roman', Times, serif;
      background: #ffffff;
    }
    .signatures {
      width: 100%;
      margin-top: 16pt;
      page-break-inside: avoid;
      break-inside: avoid;
      font-size: 9.5pt;
      font-family: 'Times New Roman', Times, serif;
    }
  </style>
</head>
<body style="font-family: 'Times New Roman', Times, serif; color: #000000; margin: 0; padding: 0;">
  <div class="Section1">
    <!-- KODE UJIAN -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4pt; border: none;">
      <tr>
        <td style="text-align: right; border: none; padding: 0;">
          <table style="display: inline-table; border: 1.5pt solid #000000; border-collapse: collapse;">
            <tr>
              <td style="border: 1.5pt solid #000000; padding: 2pt 8pt; font-size: 9.5pt; font-family: 'Times New Roman', Times, serif; font-weight: bold; text-align: center;">
                ${examCode}
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- KOP RESMI MADRASAH -->
    <table style="width: 100%; border-collapse: collapse; border-bottom: 3pt double #000000; padding-bottom: 5pt; margin-bottom: 8pt; border-top: none; border-left: none; border-right: none;">
      <tr>
        <td style="width: 65px; vertical-align: middle; text-align: center; border: none; padding-right: 8px;">
          ${logoFileName ? `<img src="${logoFileName}" width="60" height="60" style="width: 60px; height: 60px; display: block;" alt="Logo">` : ''}
        </td>
        <td style="vertical-align: middle; text-align: center; border: none; padding: 0 5px;">
          <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2;">
            MADRASAH TSANAWIYAH AL HASANAH
          </div>
          <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2; margin-top: 2px;">
            KECAMATAN CIOMAS KABUPATEN BOGOR
          </div>
          <div style="font-size: 11.5pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2; margin-top: 2px;">
            ${eventTitle}
          </div>
          <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2; margin-top: 2px;">
            TAHUN PELAJARAN ${academicYear}
          </div>
        </td>
        <td style="width: 65px; border: none;"></td>
      </tr>
    </table>

    <!-- DAFTAR KARTU -->
    <div>
      ${cloneContainer.innerHTML}
    </div>

    <!-- TANDA TANGAN -->
    <table class="signatures" style="width: 100%; margin-top: 16pt; border: none;">
      <tr>
        <td style="width: 50%; vertical-align: top; border: none; font-size: 9.5pt;">
          Mengetahui,<br>
          <strong>Kepala Madrasah</strong>
          <br><br><br><br>
          <strong><u>${headmasterName}</u></strong><br>
          <span>NIP. {{ $school->headmaster_nip ?? '197508152005011003' }}</span>
        </td>
        <td style="width: 50%; vertical-align: top; border: none; font-size: 9.5pt; padding-left: 30pt;">
          Ciomas, ${printDate}<br>
          <strong>Guru Mata Pelajaran</strong>
          <br><br><br><br>
          <strong><u>${teacherName}</u></strong><br>
          <span>NIP. {{ $package->teacher->nip ?? '-' }}</span>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>`;

                const boundary = "----=_NextPart_SiakadMts_Kartu_" + Date.now();
                let mhtml = "";

                mhtml += "MIME-Version: 1.0\r\n";
                mhtml += `Content-Type: multipart/related; boundary="${boundary}"\r\n\r\n`;

                mhtml += `--${boundary}\r\n`;
                mhtml += "Content-Type: text/html; charset=utf-8\r\n";
                mhtml += "Content-Transfer-Encoding: 8bit\r\n\r\n";
                mhtml += wordHtml + "\r\n\r\n";

                imgParts.forEach(part => {
                    const wrappedB64 = part.base64.replace(/(.{76})/g, "$1\r\n");
                    mhtml += `--${boundary}\r\n`;
                    mhtml += `Content-Type: ${part.type}\r\n`;
                    mhtml += "Content-Transfer-Encoding: base64\r\n";
                    mhtml += `Content-Location: ${part.name}\r\n\r\n`;
                    mhtml += wrappedB64 + "\r\n\r\n";
                });

                mhtml += `--${boundary}--\r\n`;

                const blob = new Blob([mhtml], { type: 'application/msword;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                const safeMapel = (subjectName || 'Mapel').replace(/[^a-zA-Z0-9_-]/g, '_');
                const safeClass = (classDisplay || 'Kelas').replace(/[^a-zA-Z0-9_-]/g, '_');
                const safeEvent = (document.getElementById('select-event')?.value || 'ASAS').replace(/[^a-zA-Z0-9_-]/g, '_');
                a.href = url;
                a.download = `Kartu_Soal_${safeMapel}_${safeClass}_${safeEvent}_${paperName}.doc`;
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
    </script>
</body>
</html>
