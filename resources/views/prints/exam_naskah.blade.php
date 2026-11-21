<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Naskah Soal - {{ $package->title }}</title>
    <style id="page-size-style">
        @page {
            size: 215mm 330mm; /* Standar F4 / Folio Portrait Indonesia */
            margin-top: 5mm;    /* Atas hemat 5mm (ruang kosong atas rapat) */
            margin-bottom: 8mm; /* Bawah aman 8mm (anti-terpotong roller printer) */
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
            padding: 12mm 14mm;
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
        .content-container,
        .content-container *,
        .question-item,
        .q-number,
        .q-text,
        .q-text *,
        .options-container,
        .options-list,
        .options-list *,
        .options-grid-table,
        .options-grid-table *,
        .opt-label,
        .opt-text,
        .akm-table,
        .akm-table *,
        .meta-table,
        .meta-table *,
        .petunjuk,
        .petunjuk * {
            font-family: 'Times New Roman', Times, serif !important;
        }

        /* DENSITY: KOMPAK (DEFAULT FIT MAKSIMAL 2 LEMBAR / 4 MUKA) */
        body.density-compact .print-sheet {
            font-size: 10pt;
            line-height: 1.22;
        }
        body.density-compact .header-kop {
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        body.density-compact .meta-table {
            font-size: 9pt !important;
            margin-bottom: 5px !important;
        }
        body.density-compact .meta-table td {
            padding: 1.5px 3px !important;
        }
        body.density-compact .petunjuk {
            font-size: 8pt !important;
            padding: 3px 8px !important;
            margin-bottom: 6px !important;
        }
        body.density-compact .petunjuk ol {
            margin: 2px 0 0 14px !important;
        }
        body.density-compact .section-title {
            margin: 8px 0 3px 0 !important;
            padding-bottom: 1px !important;
            font-size: 10pt !important;
        }
        body.density-compact .section-desc {
            margin: 2px 0 4px 0 !important;
            font-size: 9pt !important;
        }
        body.density-compact .question-item {
            margin-bottom: 7px !important;
        }
        body.density-compact .q-text p {
            margin: 0 0 2px 0 !important;
        }
        body.density-compact .options-container {
            margin-top: 2px !important;
        }
        body.density-compact .options-list {
            margin: 1px 0 0 0 !important;
        }
        body.density-compact .options-list li {
            margin-bottom: 1px !important;
        }
        body.density-compact .akm-table {
            font-size: 9pt !important;
            margin: 4px 0 6px 0 !important;
        }
        body.density-compact .akm-table th, 
        body.density-compact .akm-table td {
            padding: 2.5px 4px !important;
        }
        body.density-compact .q-img {
            max-height: 100px !important;
            margin: 4px 0 4px 20px !important;
        }

        /* DENSITY: NORMAL (UNTUK SOAL SEDIKIT / 20 SOAL) */
        body.density-normal .print-sheet {
            font-size: 10.5pt;
            line-height: 1.32;
        }
        body.density-normal .header-kop {
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        body.density-normal .meta-table {
            font-size: 9.5pt;
            margin-bottom: 8px;
        }
        body.density-normal .meta-table td {
            padding: 2px 4px;
        }
        body.density-normal .petunjuk {
            font-size: 8.5pt;
            padding: 5px 10px;
            margin-bottom: 10px;
        }
        body.density-normal .section-title {
            margin: 12px 0 6px 0;
            font-size: 10.5pt;
        }
        body.density-normal .question-item {
            margin-bottom: 12px;
        }
        body.density-normal .q-img {
            max-height: 140px;
            margin: 6px 0 6px 24px;
        }

        /* SCALING ZOOM PRESETS */
        body.scale-100 .print-sheet { zoom: 1; }
        body.scale-95 .print-sheet { zoom: 0.95; }
        body.scale-90 .print-sheet { zoom: 0.90; }
        body.scale-85 .print-sheet { zoom: 0.85; }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .meta-table td {
            vertical-align: top;
        }

        .petunjuk {
            border: 1px solid #000;
            background-color: #fafafa;
        }
        .petunjuk strong { text-transform: uppercase; }

        /* Multi-column layout */
        .content-container {
            width: 100%;
        }
        .col-2 {
            column-count: 2;
            column-gap: 18px;
            column-rule: 1px dashed #cbd5e1;
            text-align: justify;
        }

        /* Anti-clipping protections */
        .question-item {
            break-inside: avoid;
            page-break-inside: avoid;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .question-item::after {
            content: "";
            display: table;
            clear: both;
        }
        .q-number {
            font-weight: bold;
            float: left;
            width: 20px;
        }
        .q-text {
            margin-left: 22px;
            display: block;
            text-indent: 0 !important;
        }
        .q-text p, .q-text div, .q-text span {
            text-indent: 0 !important;
        }
        .q-text p {
            margin: 0 0 3px 0;
        }
        .q-text table {
            border-collapse: collapse;
            width: 100%;
            margin: 4px 0;
            font-size: 8.5pt;
        }
        .q-text th, .q-text td {
            border: 1px solid #444;
            padding: 2.5px 5px;
        }
        .q-text th {
            background-color: #f2f2f2;
        }
        .q-text ul {
            list-style-type: disc;
            padding-left: 16px;
            margin: 3px 0;
        }
        .q-text ol {
            list-style-type: decimal;
            padding-left: 16px;
            margin: 3px 0;
        }
        .q-text ol.list-paren-num {
            counter-reset: item;
            list-style: none !important;
            padding-left: 18px;
        }
        .q-text ol.list-paren-num > li {
            counter-increment: item;
            position: relative;
            margin-bottom: 1.5px;
        }
        .q-text ol.list-paren-num > li::before {
            content: "(" counter(item) ") ";
            font-weight: bold;
            display: inline-block;
            margin-left: -18px;
            width: 18px;
        }
        .q-text ol.list-paren-alpha {
            counter-reset: alpha-item;
            list-style: none !important;
            padding-left: 18px;
        }
        .q-text ol.list-paren-alpha > li {
            counter-increment: alpha-item;
            position: relative;
            margin-bottom: 1.5px;
        }
        .q-text ol.list-paren-alpha > li::before {
            content: "(" counter(alpha-item, lower-alpha) ") ";
            font-weight: bold;
            display: inline-block;
            margin-left: -18px;
            width: 18px;
        }
        .q-text ul.list-dash { list-style-type: '– '; }
        .q-text ul.list-check { list-style-type: '✓ '; }
        .q-text ul.list-arrow { list-style-type: '➢ '; }
        .q-img {
            display: block;
            max-width: 95%;
            border: 1px solid #ccc;
            padding: 2px;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .options-container {
            margin-left: 22px;
            margin-top: 3px;
        }
        .options-list {
            list-style-type: none;
            padding-left: 22px;
            margin: 3px 0 0 0;
        }
        .options-container .options-list {
            padding-left: 0;
            margin: 0;
        }
        .options-list li {
            margin-bottom: 1.5px;
            display: flex;
            align-items: baseline;
            break-inside: avoid;
        }
        .opt-label {
            font-weight: bold;
            width: 18px;
            flex-shrink: 0;
            display: inline-block;
        }
        .opt-text {
            flex-grow: 1;
        }
        .options-grid-table {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
            margin: 1px 0;
        }
        .options-grid-table td {
            border: none !important;
            vertical-align: top;
            padding: 1.5px 8px 1.5px 0;
        }
        .key-badge {
            color: #059669;
            font-weight: bold;
            margin-left: 6px;
            font-size: 8.5pt;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            letter-spacing: 0.5px;
        }
        .section-desc {
            font-style: italic;
        }

        .akm-table {
            width: 100%;
            border-collapse: collapse;
        }
        .akm-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .akm-table th, .akm-table td {
            border: 1px solid #000;
            vertical-align: top;
        }
        .akm-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            background: #f59e0b;
            color: #78350f;
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
        .btn-tool.active-green {
            background: #059669;
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
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
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
            body.scale-95 .print-sheet { zoom: 0.95; }
            body.scale-90 .print-sheet { zoom: 0.90; }
            body.scale-85 .print-sheet { zoom: 0.85; }
        }
    </style>
</head>
<body class="density-compact scale-100 paper-f4">
    <!-- FLOATING INTERACTIVE CONTROLLER (SCREEN ONLY) -->
    <div class="no-print print-toolbar">
        <div class="toolbar-wrapper">
            <div class="toolbar-brand">
                <span class="toolbar-badge">F4 Folio</span>
                <span class="toolbar-title">Naskah Soal Siswa (Target: Maks 2 Lembar)</span>
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
                        <option value="GANJIL">Ganjil (1)</option>
                        <option value="GENAP">Genap (2)</option>
                    </select>
                </div>

                <!-- Density Preset -->
                <div class="toolbar-group">
                    <span class="group-label">Kepadatan:</span>
                    <button type="button" class="btn-tool active active-green" id="btn-den-compact" onclick="setDensity('compact')">⚡ Fit 2 Lembar</button>
                    <button type="button" class="btn-tool" id="btn-den-normal" onclick="setDensity('normal')">Standar</button>
                </div>

                <!-- Scaling Zoom -->
                <div class="toolbar-group">
                    <span class="group-label">Skala:</span>
                    <button type="button" class="btn-tool active" id="btn-scale-100" onclick="setScale(100)">100%</button>
                    <button type="button" class="btn-tool" id="btn-scale-95" onclick="setScale(95)">95%</button>
                    <button type="button" class="btn-tool" id="btn-scale-90" onclick="setScale(90)">90%</button>
                    <button type="button" class="btn-tool" id="btn-scale-85" onclick="setScale(85)">85%</button>
                </div>

                <!-- Layout Toggle -->
                <div class="toolbar-group">
                    <span class="group-label">Kolom:</span>
                    <button type="button" class="btn-tool {{ $layout === '2col' ? 'active' : '' }}" id="btn-layout-2" onclick="setLayout('2col')">2 Kolom</button>
                    <button type="button" class="btn-tool {{ $layout === '1col' ? 'active' : '' }}" id="btn-layout-1" onclick="setLayout('1col')">1 Kolom</button>
                </div>

                <!-- Word Download Button -->
                <button type="button" class="btn-word" id="btn-unduh-word" onclick="downloadNaskahWord()" title="Unduh format Microsoft Word (.doc) anti-terpotong">
                    📄 Unduh Word (.doc)
                </button>

                <!-- Print Trigger Button -->
                <button type="button" class="btn-print" onclick="window.print()">
                    🖨️ Cetak Sekarang
                </button>
            </div>
        </div>

        <div class="toolbar-tips">
            <span>💡 <strong>Tips Cetak Rapat Tanpa Terpotong:</strong> Pada jendela cetak printer (Ctrl + P), pastikan <em>Paper size</em> dipilih <strong>Folio / F4 / 215 × 330 mm</strong>, <em>Margins</em> dipilih <strong>None (Tanpa Margin)</strong>, dan <strong>Hilangkan centang Headers & footers</strong> agar ruang kosong atas hilang dan nomor soal paling bawah 100% aman tidak terpotong!</span>
        </div>
    </div>

    <div id="print-sheet" class="print-sheet">

    @php
        $logoPath = public_path('images/logo_madrasah_yaspih.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        if (!empty($package->target_grade)) {
            $classDisplay = match((string)$package->target_grade) {
                '9' => 'IX(SEMBILAN) A/B*  Fase D',
                '8' => 'VIII(DELAPAN) A/B*  Fase D',
                '7' => 'VII(TUJUH) A/B*  Fase D',
                default => 'Kelas ' . $package->target_grade . '  Fase D'
            };
        } else {
            $cleanClassName = strtoupper(trim($package->classRoom->name ?? 'VII'));
            if (str_contains($cleanClassName, '7') || str_contains($cleanClassName, 'VII')) {
                $classDisplay = 'VII(TUJUH) A/B*  Fase D';
            } elseif (str_contains($cleanClassName, '8') || str_contains($cleanClassName, 'VIII')) {
                $classDisplay = 'VIII(DELAPAN) A/B*  Fase D';
            } elseif (str_contains($cleanClassName, '9') || str_contains($cleanClassName, 'IX')) {
                $classDisplay = 'IX(SEMBILAN) A/B*  Fase D';
            } else {
                $classDisplay = $cleanClassName . '  ' . ($package->academic_phase ?? 'Fase D');
            }
        }

        // Deteksi Semester Baku
        $rawSem = strtolower(trim($package->semester ?? ''));
        $semNum = ($rawSem === 'genap' || $rawSem === '2') ? '2' : '1';
        $semesterLabel = ($semNum === '2') ? 'GENAP' : 'GANJIL';

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

        $yearShort = '26/27';
        if (!empty($package->academicYear->year)) {
            $parts = explode('/', $package->academicYear->year);
            if (count($parts) === 2) {
                $yearShort = substr(trim($parts[0]), -2) . '/' . substr(trim($parts[1]), -2);
            }
        }
        // Pemetaan Kode Singkatan Baku Mapel Madrasah
        $rawSubjName = strtolower(trim($package->subject->name ?? ''));
        $rawSubjCode = strtoupper(trim(preg_replace('/\d+/', '', $package->subject->code ?? '')));

        $subjMap = [
            'akidah' => 'AA',
            'aqidah' => 'AA',
            'qur' => 'QH',
            'hadits' => 'QH',
            'hadis' => 'QH',
            'fikih' => 'FIK',
            'fiqih' => 'FIK',
            'sejarah kebudayaan' => 'SKI',
            'ski' => 'SKI',
            'bahasa arab' => 'BA',
            'arab' => 'BA',
            'indonesia' => 'BIN',
            'inggris' => 'BIG',
            'matematika' => 'MTK',
            'ipa' => 'IPA',
            'alam' => 'IPA',
            'ips' => 'IPS',
            'sosial' => 'IPS',
            'informatika' => 'INF',
            'komputer' => 'INF',
            'pancasila' => 'PPKN',
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
    @endphp

    <!-- KOTAK KODE NASKAH UJIAN (POJOK KANAN ATAS) -->
    <div style="display: flex; justify-content: flex-end; margin-bottom: 4px;">
        <div style="border: 1.5px solid #000; padding: 2px 10px; font-size: 9.5pt; font-family: 'Times New Roman', Times, serif; font-weight: bold; line-height: 1.2; background: #fff; display: inline-block;">
            <span id="display-exam-code">{{ $examCode }}</span>
        </div>
    </div>

    <div class="header-kop" style="display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 3px double #000; padding-bottom: 6px; margin-bottom: 8px;">
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
                <span id="display-event-title">{{ $fullTitle }}</span>
            </div>
            <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; font-family: 'Times New Roman', serif; line-height: 1.2; margin-top: 2px;">
                TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}
            </div>
        </div>
    </div>

    <table class="meta-table" style="font-size: 9.5pt; margin-bottom: 8px;">
        <tr>
            <td width="18%"><strong>MATA PELAJARAN</strong></td>
            <td width="35%">: {{ strtoupper($package->subject->name ?? 'INFORMATIKA') }}</td>
            <td width="18%"><strong>HARI/TANGGAL</strong></td>
            <td width="29%">: ________________</td>
        </tr>
        <tr>
            <td><strong>NAMA</strong></td>
            <td>: ______________________</td>
            <td><strong>WAKTU</strong></td>
            <td>: 90  Menit</td>
        </tr>
        <tr>
            <td><strong>KELAS/FASE</strong></td>
            <td>: {{ $classDisplay }}</td>
            <td><strong>PUKUL</strong></td>
            <td>: ____ – ____</td>
        </tr>
    </table>

    <div class="petunjuk" style="margin-bottom: 12px; font-size: 8.5pt; padding: 5px 10px;">
        <strong>PETUNJUK UMUM :</strong>
        <ol style="margin: 2px 0 0 16px; padding: 0;">
            <li>Bacalah Do’a sebelum dan sesudah mengerjakan Soal</li>
            <li>Jawaban di kerjakan langsung pada Soal</li>
            <li>Isilah nama dengan lengkap dan jelas, lalu lingkari dalam satu pilihan kelas</li>
            <li>Pergunakan ballpoint untuk mengisi</li>
            <li>Dahulukan menjawab soal – soal yang anda anggap mudah</li>
        </ol>
    </div>

    @php
        $secLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
        $secIdx = 0;
        if (!function_exists('render_exam_question_html')) {
            function render_exam_question_html($text) {
                if (!$text) return 'Soal belum diisi.';
                if (strip_tags($text) !== $text) {
                    $allowed = '<b><strong><i><em><u><s><strike><sub><sup><span><p><div><br><table><thead><tbody><tr><th><td><ul><ol><li>';
                    $cleaned = strip_tags($text, $allowed);
                    $cleaned = preg_replace('/text-indent\s*:\s*-[^;"]+;?/i', 'text-indent:0;', $cleaned);
                    $cleaned = preg_replace('/margin-left\s*:\s*-[^;"]+;?/i', 'margin-left:0;', $cleaned);
                    return $cleaned;
                }
                return nl2br(e($text));
            }
        }
    @endphp

    <div class="content-container {{ $layout === '2col' ? 'col-2' : '' }}">
        <!-- 1. BAGIAN: PILIHAN GANDA TUNGGAL -->
        @if ($pgQuestions->isNotEmpty())
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Pilihan Ganda</div>
            <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Pilihlah salah satu jawaban yang paling tepat!</p>

            @foreach ($pgQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    @php
                        $rawOpts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                        $alphaMap = ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'];
                        $validOpts = [];
                        $maxOptLen = 0;
                        $hasComplexHtml = false;

                        if (!empty($rawOpts)) {
                            foreach ($alphaMap as $uKey => $lKey) {
                                if (isset($rawOpts[$uKey]) && trim((string)$rawOpts[$uKey]) !== '') {
                                    $val = $rawOpts[$uKey];
                                    $clean = trim(strip_tags($val));
                                    $len = mb_strlen($clean);
                                    if ($len > $maxOptLen) {
                                        $maxOptLen = $len;
                                    }
                                    if (preg_match('/<table|<ul|<ol|<div|<p|<br\s*\/?>/i', $val)) {
                                        $hasComplexHtml = true;
                                    }
                                    $validOpts[] = [
                                        'upper' => $uKey,
                                        'lower' => $lKey,
                                        'label' => $lKey . '.',
                                        'val' => $val,
                                        'is_correct' => ($showKeys && strtoupper($q->correct_answer ?? '') === $uKey),
                                    ];
                                }
                            }
                        }

                        $isShortOpts = (count($validOpts) >= 3 && count($validOpts) <= 5 && !$hasComplexHtml && $maxOptLen <= 32);
                    @endphp
                    @if (!empty($validOpts))
                        <div class="options-container" style="margin-left: 22px;">
                            @if ($isShortOpts)
                                <table class="options-grid-table" style="width: 100%; border-collapse: collapse; border: none; margin: 2px 0;">
                                    <tbody>
                                        @php
                                            $half = (int) ceil(count($validOpts) / 2);
                                        @endphp
                                        @for ($r = 0; $r < $half; $r++)
                                            @php
                                                $leftOpt = $validOpts[$r] ?? null;
                                                $rightOpt = $validOpts[$r + $half] ?? null;
                                            @endphp
                                            <tr>
                                                <td style="width: 50%; vertical-align: top; border: none; padding: 1.5px 8px 1.5px 0;">
                                                    @if ($leftOpt)
                                                        <span class="opt-label">{{ $leftOpt['label'] }}</span>
                                                        <span class="opt-text">{!! $leftOpt['val'] !!}</span>
                                                        @if ($leftOpt['is_correct'])
                                                            <span class="key-badge">✓ (Kunci)</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td style="width: 50%; vertical-align: top; border: none; padding: 1.5px 0 1.5px 0;">
                                                    @if ($rightOpt)
                                                        <span class="opt-label">{{ $rightOpt['label'] }}</span>
                                                        <span class="opt-text">{!! $rightOpt['val'] !!}</span>
                                                        @if ($rightOpt['is_correct'])
                                                            <span class="key-badge">✓ (Kunci)</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            @else
                                <ul class="options-list">
                                    @foreach ($validOpts as $opt)
                                        <li>
                                            <span class="opt-label">{{ $opt['label'] }}</span>
                                            <span class="opt-text">{!! $opt['val'] !!}</span>
                                            @if ($opt['is_correct'])
                                                <span class="key-badge">✓ (Kunci)</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        <!-- 2. BAGIAN: PILIHAN GANDA KOMPLEKS (AKM) -->
        @if (isset($pgComplexQuestions) && $pgComplexQuestions->isNotEmpty())
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Pilihan Ganda Kompleks</div>
            <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Pilihlah lebih dari satu jawaban yang benar dengan memberi tanda centang (✓) pada kotak pilihan jawaban!</p>

            @foreach ($pgComplexQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    @php
                        $rawOpts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                        $alphaMap = ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'];
                        $keys = array_map('trim', explode(',', strtoupper($q->correct_answer ?? '')));
                        $validOpts = [];
                        $maxOptLen = 0;
                        $hasComplexHtml = false;

                        if (!empty($rawOpts)) {
                            foreach ($alphaMap as $uKey => $lKey) {
                                if (isset($rawOpts[$uKey]) && trim((string)$rawOpts[$uKey]) !== '') {
                                    $val = $rawOpts[$uKey];
                                    $clean = trim(strip_tags($val));
                                    $len = mb_strlen($clean);
                                    if ($len > $maxOptLen) {
                                        $maxOptLen = $len;
                                    }
                                    if (preg_match('/<table|<ul|<ol|<div|<p|<br\s*\/?>/i', $val)) {
                                        $hasComplexHtml = true;
                                    }
                                    $validOpts[] = [
                                        'upper' => $uKey,
                                        'lower' => $lKey,
                                        'label' => $lKey . '.',
                                        'val' => $val,
                                        'is_correct' => ($showKeys && in_array($uKey, $keys)),
                                    ];
                                }
                            }
                        }

                        $isShortOpts = (count($validOpts) >= 3 && count($validOpts) <= 5 && !$hasComplexHtml && $maxOptLen <= 32);
                    @endphp
                    @if (!empty($validOpts))
                        <div class="options-container" style="margin-left: 22px;">
                            @if ($isShortOpts)
                                <table class="options-grid-table" style="width: 100%; border-collapse: collapse; border: none; margin: 2px 0;">
                                    <tbody>
                                        @php
                                            $half = (int) ceil(count($validOpts) / 2);
                                        @endphp
                                        @for ($r = 0; $r < $half; $r++)
                                            @php
                                                $leftOpt = $validOpts[$r] ?? null;
                                                $rightOpt = $validOpts[$r + $half] ?? null;
                                            @endphp
                                            <tr>
                                                <td style="width: 50%; vertical-align: top; border: none; padding: 1.5px 8px 1.5px 0;">
                                                    @if ($leftOpt)
                                                        <span class="opt-label" style="display:inline-flex; align-items:center; gap:2px; width:auto; margin-right:4px;">
                                                            <span style="display:inline-block; width:11px; height:11px; border:1px solid #000; text-align:center; line-height:10px; font-size:7.5pt;">
                                                                {{ $leftOpt['is_correct'] ? '✓' : '' }}
                                                            </span>
                                                            {{ $leftOpt['label'] }}
                                                        </span>
                                                        <span class="opt-text">{!! $leftOpt['val'] !!}</span>
                                                        @if ($leftOpt['is_correct'])
                                                            <span class="key-badge">✓</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td style="width: 50%; vertical-align: top; border: none; padding: 1.5px 0 1.5px 0;">
                                                    @if ($rightOpt)
                                                        <span class="opt-label" style="display:inline-flex; align-items:center; gap:2px; width:auto; margin-right:4px;">
                                                            <span style="display:inline-block; width:11px; height:11px; border:1px solid #000; text-align:center; line-height:10px; font-size:7.5pt;">
                                                                {{ $rightOpt['is_correct'] ? '✓' : '' }}
                                                            </span>
                                                            {{ $rightOpt['label'] }}
                                                        </span>
                                                        <span class="opt-text">{!! $rightOpt['val'] !!}</span>
                                                        @if ($rightOpt['is_correct'])
                                                            <span class="key-badge">✓</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            @else
                                <ul class="options-list">
                                    @foreach ($validOpts as $opt)
                                        <li>
                                            <span class="opt-label" style="display:inline-flex; align-items:center; gap:3px;">
                                                <span style="display:inline-block; width:12px; height:12px; border:1px solid #000; text-align:center; line-height:11px; font-size:8pt;">
                                                    {{ $opt['is_correct'] ? '✓' : '' }}
                                                </span>
                                                {{ $opt['label'] }}
                                            </span>
                                            <span class="opt-text">{!! $opt['val'] !!}</span>
                                            @if ($opt['is_correct'])
                                                <span class="key-badge">✓</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        <!-- 3. BAGIAN: BENAR / SALAH -->
        @if (isset($trueFalseQuestions) && $trueFalseQuestions->isNotEmpty())
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Pernyataan Benar - Salah</div>

            @php
                $singleTfList = [];
                $matrixTfList = [];
                foreach ($trueFalseQuestions as $q) {
                    $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                    $statements = $opts['statements'] ?? [];
                    $isMat = ($opts['mode'] ?? '') === 'matrix' || count($statements) > 1;
                    if ($isMat) {
                        $matrixTfList[] = $q;
                    } else {
                        $singleTfList[] = $q;
                    }
                }
            @endphp

            {{-- 3A. TABEL UTUH BENAR / SALAH MODE PER-NOMOR (No. 31 s/d 35) --}}
            @if (!empty($singleTfList))
                <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Bacalah setiap pernyataan berikut dengan teliti, kemudian berikan tanda centang (✓) pada kolom Benar jika pernyataan sesuai atau pada kolom Salah jika pernyataan tidak sesuai!</p>

                <table class="akm-table" style="margin-bottom: 14px;">
                    <thead>
                        <tr>
                            <th width="6%" style="text-align: center;">No</th>
                            <th width="74%" style="text-align: left;">Pernyataan</th>
                            <th width="10%" style="text-align: center;">Benar</th>
                            <th width="10%" style="text-align: center;">Salah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($singleTfList as $q)
                            <tr>
                                <td align="center" style="font-weight: bold; vertical-align: top;">{{ $q->question_number }}.</td>
                                <td style="vertical-align: top;">
                                    {!! render_exam_question_html($q->question_text) !!}
                                    @if ($q->question_image)
                                        <div style="margin-top: 3px;">
                                            <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" style="max-height: 80px;" alt="Stimulus">
                                        </div>
                                    @endif
                                </td>
                                <td align="center" style="vertical-align: middle; font-weight: bold; font-size: 11pt; color: #059669;">
                                    @if ($showKeys && strtoupper($q->correct_answer) === 'B')
                                        ✓
                                    @endif
                                </td>
                                <td align="center" style="vertical-align: middle; font-weight: bold; font-size: 11pt; color: #dc2626;">
                                    @if ($showKeys && strtoupper($q->correct_answer) === 'S')
                                        ✓
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- 3B. JIKA ADA SOAL MODE MATRIKS AKM (1 Soal Banyak Pernyataan) --}}
            @if (!empty($matrixTfList))
                @foreach ($matrixTfList as $q)
                    <div class="question-item">
                        <span class="q-number">{{ $q->question_number }}.</span>
                        <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                        @if ($q->question_image)
                            <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                        @endif

                        @php
                            $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                            $statements = $opts['statements'] ?? [];
                        @endphp
                        @if (!empty($statements))
                            <table class="akm-table">
                                <thead>
                                    <tr>
                                        <th width="6%">No</th>
                                        <th width="74%">Pernyataan</th>
                                        <th width="10%">B</th>
                                        <th width="10%">S</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($statements as $sIdx => $st)
                                        <tr>
                                            <td align="center">{{ $sIdx + 1 }}</td>
                                            <td>{{ $st['text'] ?? '-' }}</td>
                                            <td align="center" style="font-weight: bold;">
                                                {{ ($showKeys && ($st['answer'] ?? '') === 'B') ? '✓' : '' }}
                                            </td>
                                            <td align="center" style="font-weight: bold;">
                                                {{ ($showKeys && ($st['answer'] ?? '') === 'S') ? '✓' : '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endforeach
            @endif
        @endif

        <!-- 4. BAGIAN: MENJODOHKAN -->
        @if (isset($matchingQuestions) && $matchingQuestions->isNotEmpty())
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Menjodohkan</div>

            @php
                // Pisahkan antara soal mode mandiri (No. 36-40) dan mode matriks
                $singleMatchingList = [];
                $matrixMatchingList = [];

                foreach ($matchingQuestions as $mq) {
                    $mOpts = is_array($mq->options) ? $mq->options : json_decode($mq->options, true);
                    $pairs = $mOpts['pairs'] ?? [];
                    $isMat = ($mOpts['mode'] ?? '') === 'matrix' || count($pairs) > 1;
                    if ($isMat) {
                        $matrixMatchingList[] = $mq;
                    } else {
                        $singleMatchingList[] = $mq;
                    }
                }
            @endphp

            {{-- 4A. TABEL UTUH MENJODOHKAN MODE MANDIRI DENGAN KOLOM JAWABAN DI TENGAH --}}
            @if (!empty($singleMatchingList))
                @php
                    // Kumpulkan semua pilihan kanan yang unik, diurutkan berdasarkan huruf A, B, C...
                    $rightOptions = [];
                    foreach ($singleMatchingList as $sqIdx => $sq) {
                        $sOpts = is_array($sq->options) ? $sq->options : json_decode($sq->options, true);
                        $rText = $sOpts['right_text'] ?? ($sOpts['pairs'][0]['right'] ?? '');
                        $rawKey = strtoupper($sq->correct_answer ?: ($sOpts['key'] ?? chr(65 + $sqIdx)));
                        if (!empty($rText)) {
                            if (!isset($rightOptions[$rawKey])) {
                                $rightOptions[$rawKey] = $rText;
                            } else {
                                $candKey = $rawKey;
                                for ($k = 0; $k < 26; $k++) {
                                    $c = chr(65 + $k);
                                    if (!isset($rightOptions[$c])) {
                                        $candKey = $c;
                                        break;
                                    }
                                }
                                $rightOptions[$candKey] = $rText;
                            }
                        }
                    }
                    ksort($rightOptions);
                    $rightList = [];
                    foreach ($rightOptions as $k => $text) {
                        $rightList[] = ['key' => $k, 'text' => $text];
                    }
                    $maxRows = max(count($singleMatchingList), count($rightList));

                    // Hitung rentang nomor soal dan rentang pilihan huruf secara otomatis
                    $firstMatchNo = $singleMatchingList[0]->question_number ?? 1;
                    $lastMatchItem = end($singleMatchingList);
                    $lastMatchNo = $lastMatchItem->question_number ?? count($singleMatchingList);
                    $matchRangeStr = ($firstMatchNo == $lastMatchNo) ? "Nomor {$firstMatchNo}" : "Nomor {$firstMatchNo}-{$lastMatchNo}";

                    $firstLetter = !empty($rightList) ? $rightList[0]['key'] : 'A';
                    $lastLetterItem = end($rightList);
                    $lastLetter = !empty($lastLetterItem) ? $lastLetterItem['key'] : 'E';
                    $letterRangeStr = ($firstLetter == $lastLetter) ? "Huruf {$firstLetter}" : "Huruf {$firstLetter}-{$lastLetter}";
                @endphp

                <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">
                    Pasangkan pernyataan/istilah pada Lajur Kiri ({{ $matchRangeStr }}) dengan pilihan jawaban yang paling tepat pada Lajur Kanan ({{ $letterRangeStr }})! Tuliskan huruf pilihannya saja pada kolom Jawaban di tengah!
                </p>

                <table class="akm-table" style="margin-bottom: 14px;">
                    <thead>
                        <tr>
                            <th width="6%" style="text-align: center;">No</th>
                            <th width="38%" style="text-align: left;">Pernyataan / Istilah</th>
                            <th width="10%" style="text-align: center;">Jawaban</th>
                            <th width="46%" style="text-align: left;">Pilihan Pasangan Jawaban</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($r = 0; $r < $maxRows; $r++)
                            @php
                                $leftQ = $singleMatchingList[$r] ?? null;
                                $rightItem = $rightList[$r] ?? null;
                            @endphp
                            <tr>
                                <td align="center" style="font-weight: bold; vertical-align: top;">
                                    @if ($leftQ)
                                        {{ $leftQ->question_number }}.
                                    @endif
                                </td>
                                <td style="vertical-align: top;">
                                    @if ($leftQ)
                                        {!! render_exam_question_html($leftQ->question_text) !!}
                                        @if ($leftQ->question_image)
                                            <div style="margin-top: 3px;">
                                                <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $leftQ->question_image)) }}" style="max-height: 70px;" alt="Stimulus">
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td align="center" style="vertical-align: middle; font-weight: bold; font-size: 11pt;">
                                    @if ($leftQ)
                                        @if ($showKeys)
                                            <span style="color: #059669; font-weight: bold;">{{ strtoupper($leftQ->correct_answer ?: '-') }}</span>
                                        @else
                                            &nbsp;
                                        @endif
                                    @endif
                                </td>
                                <td style="vertical-align: top;">
                                    @if ($rightItem)
                                        <strong>{{ $rightItem['key'] }}.</strong> {{ $rightItem['text'] }}
                                    @endif
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            @endif

            {{-- 4B. JIKA ADA SOAL MENJODOHKAN MODE MATRIKS (1 Soal Banyak Pasangan) --}}
            @if (!empty($matrixMatchingList))
                @foreach ($matrixMatchingList as $q)
                    <div class="question-item">
                        <span class="q-number">{{ $q->question_number }}.</span>
                        <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                        @if ($q->question_image)
                            <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                        @endif

                        @php
                            $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                            $pairs = $opts['pairs'] ?? [];
                        @endphp
                        @if (!empty($pairs))
                            <table class="akm-table">
                                <thead>
                                    <tr>
                                        <th width="50%">Kolom Kiri (Pernyataan)</th>
                                        <th width="50%">Kolom Kanan (Pilihan Jawaban)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pairs as $pIdx => $pair)
                                        <tr>
                                            <td><strong>{{ $pIdx + 1 }}.</strong> {{ $pair['left'] ?? '-' }}</td>
                                            <td><strong>{{ chr(65 + $pIdx) }}.</strong> {{ $pair['right'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if ($showKeys && !empty($q->correct_answer))
                                <div style="font-size: 8.5pt; color: #059669; font-weight: bold; margin-top: 3px;">
                                    Kunci Pasangan: {{ $q->correct_answer }}
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            @endif
        @endif

        <!-- 5. BAGIAN: ISIAN SINGKAT -->
        @if (isset($shortAnswerQuestions) && $shortAnswerQuestions->isNotEmpty())
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Isian Singkat</div>
            <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Jawablah pertanyaan-pertanyaan berikut dengan singkat dan tepat!</p>

            @foreach ($shortAnswerQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    <div style="margin-top: 4px; font-size: 9.5pt;">
                        <strong>Jawaban:</strong> ............................................................................................
                    </div>
                    @if ($showKeys && !empty($q->correct_answer))
                        <div style="font-size: 8.5pt; color: #059669; font-weight: bold; margin-top: 2px;">
                            Kunci: {{ $q->correct_answer }}
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        <!-- 6. BAGIAN: URAIAN / ESSAY -->
        @if ($essayQuestions->isNotEmpty())
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Uraian</div>
            <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Jawablah pertanyaan-pertanyaan berikut dengan jelas dan tepat!</p>

            @foreach ($essayQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    @if ($showKeys && !empty($q->correct_answer))
                        <div style="margin-top: 4px; padding: 4px 8px; background: #f0fdf4; border: 1px solid #bbf7d0; font-size: 9pt;">
                            <strong>Kunci / Pedoman Penskoran:</strong> {!! nl2br(e($q->correct_answer)) !!}
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <!-- RUNNING FOOTER -->
    <div style="text-align: center; font-style: italic; font-size: 8.5pt; margin-top: 14px; color: #333; border-top: 1px dotted #888; padding-top: 4px;">
        Kerjakan lah soal dengan teliti dan benar, jangan terburu-buru
    </div>
    </div> <!-- END #print-sheet -->

    <script>
        function setPaper(size) {
            const styleEl = document.getElementById('page-size-style');
            const btnF4 = document.getElementById('btn-paper-f4');
            const btnA4 = document.getElementById('btn-paper-a4');
            const sheet = document.getElementById('print-sheet');

            if (size === 'f4') {
                styleEl.innerHTML = '@page { size: 215mm 330mm; margin: 5mm 10mm 8mm 10mm; }';
                btnF4.classList.add('active');
                btnA4.classList.remove('active');
                sheet.style.maxWidth = '215mm';
                sheet.style.minHeight = '330mm';
            } else {
                styleEl.innerHTML = '@page { size: 210mm 297mm; margin: 5mm 10mm 8mm 10mm; }';
                btnA4.classList.add('active');
                btnF4.classList.remove('active');
                sheet.style.maxWidth = '210mm';
                sheet.style.minHeight = '297mm';
            }
        }

        function setDensity(mode) {
            const btnCompact = document.getElementById('btn-den-compact');
            const btnNormal = document.getElementById('btn-den-normal');

            if (mode === 'compact') {
                document.body.classList.add('density-compact');
                document.body.classList.remove('density-normal');
                btnCompact.classList.add('active', 'active-green');
                btnNormal.classList.remove('active');
            } else {
                document.body.classList.remove('density-compact');
                document.body.classList.add('density-normal');
                btnNormal.classList.add('active');
                btnCompact.classList.remove('active', 'active-green');
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

        function setLayout(cols) {
            const container = document.querySelector('.content-container');
            const btn2 = document.getElementById('btn-layout-2');
            const btn1 = document.getElementById('btn-layout-1');

            if (cols === '2col') {
                container.classList.add('col-2');
                btn2.classList.add('active');
                btn1.classList.remove('active');
            } else {
                container.classList.remove('col-2');
                btn1.classList.add('active');
                btn2.classList.remove('active');
            }
        }

        function updateExamEvent() {
            const eventVal = document.getElementById('select-event').value;
            const semVal = document.getElementById('select-semester').value;
            const titleEl = document.getElementById('display-event-title');
            const codeEl = document.getElementById('display-exam-code');
            
            let titleText = '';
            let semNum = semVal === 'GENAP' ? '2' : '1';

            if (eventVal === 'ASAS') {
                titleText = 'ASESMEN SUMATIF AKHIR SEMESTER ' + semVal + ' (ASAS)';
            } else if (eventVal === 'ASTS') {
                titleText = 'ASESMEN SUMATIF TENGAH SEMESTER ' + semVal + ' (ASTS)';
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

            if (codeEl) {
                const yearShort = '{{ $yearShort }}';
                const subjCode = '{{ $subjCode }}';
                codeEl.textContent = eventVal + '-' + semNum + '.' + yearShort + '- ' + subjCode;
            }
        }

        // Sinkronisasi nilai default dropdown sesuai paket soal
        document.addEventListener('DOMContentLoaded', function() {
            const evEl = document.getElementById('select-event');
            const smEl = document.getElementById('select-semester');
            if (evEl) evEl.value = '{{ $currentEvent }}';
            if (smEl) smEl.value = '{{ $semesterLabel }}';
        });

        // Fungsi Ekspor Resmi Naskah Soal ke Microsoft Word (.doc)
        function downloadNaskahWord() {
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
                const paperMarginCss = '12mm 15mm 12mm 15mm';

                const is2Col = document.getElementById('btn-layout-2')?.classList.contains('active');
                const layoutName = is2Col ? '2Kolom' : '1Kolom';

                const isCompact = document.body.classList.contains('density-compact');
                const fontSizeCss = '10pt';
                const lineHeightCss = isCompact ? '1.22' : '1.25';

                const subjectName = @json($package->subject->name ?? 'Informatika');
                const classDisplay = @json($classDisplay ?? 'Kelas');
                const academicYear = @json($package->academicYear->year ?? '2026/2027');
                const examCode = document.getElementById('display-exam-code')?.textContent.trim() || @json($examCode);
                const eventTitle = document.getElementById('display-event-title')?.textContent.trim() || @json($fullTitle);

                // Kloning container butir soal
                const originalContainer = document.querySelector('.content-container');
                if (!originalContainer) throw new Error('Kontainer soal tidak ditemukan');
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

                // 2. Gambar stimulus soal
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
                    img.style.maxWidth = '95%';
                    img.style.height = 'auto';
                    img.style.display = 'block';
                    img.style.margin = '4pt 0 4pt 15pt';
                    img.style.border = '1pt solid #ccc';
                    img.style.padding = '2pt';
                });

                // Perapian tabel AKM & Soal di dalam Word (kecuali tabel opsi jawaban 2 kolom)
                cloneContainer.querySelectorAll('table:not(.options-grid-table)').forEach(tbl => {
                    tbl.setAttribute('border', '1');
                    tbl.setAttribute('cellspacing', '0');
                    tbl.setAttribute('cellpadding', '2');
                    tbl.style.borderCollapse = 'collapse';
                    tbl.style.border = '1pt solid #000000';
                    tbl.style.pageBreakInside = 'avoid';
                    tbl.style.breakInside = 'avoid';
                    tbl.style.fontFamily = "'Times New Roman', Times, serif";
                    tbl.style.fontSize = '9pt';
                    tbl.querySelectorAll('th, td').forEach(c => {
                        c.style.border = '1pt solid #000000';
                        c.style.padding = '2.5pt 4pt';
                        c.style.fontFamily = "'Times New Roman', Times, serif";
                    });
                });

                cloneContainer.querySelectorAll('table.options-grid-table').forEach(tbl => {
                    tbl.setAttribute('border', '0');
                    tbl.setAttribute('cellspacing', '0');
                    tbl.setAttribute('cellpadding', '0');
                    tbl.style.borderCollapse = 'collapse';
                    tbl.style.border = 'none';
                    tbl.style.width = '100%';
                    tbl.style.pageBreakInside = 'avoid';
                    tbl.style.breakInside = 'avoid';
                    tbl.style.fontFamily = "'Times New Roman', Times, serif";
                    tbl.querySelectorAll('td').forEach(c => {
                        c.style.border = 'none';
                        c.style.padding = '1.5pt 6pt 1.5pt 0';
                        c.style.verticalAlign = 'top';
                        c.style.fontFamily = "'Times New Roman', Times, serif";
                    });
                });

                // Perapian proteksi putus nomor butir soal
                cloneContainer.querySelectorAll('.question-item').forEach(qi => {
                    qi.style.pageBreakInside = 'avoid';
                    qi.style.breakInside = 'avoid';
                    qi.style.marginBottom = '6pt';
                    qi.style.textAlign = 'justify';
                    qi.style.fontFamily = "'Times New Roman', Times, serif";
                });

                let wordHtml = `
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Naskah Soal - ${subjectName}</title>
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
      font-size: ${fontSizeCss};
      line-height: ${lineHeightCss};
      color: #000000;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .content-container.col-2 {
      column-count: 2;
      column-gap: 18pt;
      mso-column-count: 2;
      mso-column-gap: 18pt;
      text-align: justify;
    }
    .content-container {
      width: 100%;
      text-align: justify;
      font-family: 'Times New Roman', Times, serif;
    }
    .question-item {
      page-break-inside: avoid;
      break-inside: avoid;
      mso-pagination: lines-together;
      margin-bottom: 6pt;
      text-align: justify;
      font-family: 'Times New Roman', Times, serif;
      font-size: ${fontSizeCss};
    }
    .q-number {
      font-weight: bold;
      float: left;
      width: 18pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .q-text {
      margin-left: 20pt;
      display: block;
      text-indent: 0pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .q-text p, .q-text div, .q-text span {
      text-indent: 0pt !important;
      font-family: 'Times New Roman', Times, serif !important;
    }
    .options-container {
      margin-left: 20pt;
    }
    .options-grid-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
      margin: 2pt 0;
    }
    .options-grid-table td {
      border: none;
      vertical-align: top;
      padding: 1.5pt 6pt 1.5pt 0;
      font-family: 'Times New Roman', Times, serif;
      font-size: ${fontSizeCss};
    }
    .options-list {
      list-style: none;
      margin: 2pt 0 0 0;
      padding-left: 0;
    }
    .options-list li {
      margin-bottom: 1.5pt;
      line-height: 1.15;
      font-family: 'Times New Roman', Times, serif;
      font-size: ${fontSizeCss};
    }
    .opt-label {
      font-weight: bold;
      display: inline-block;
      width: 15pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .opt-text {
      font-family: 'Times New Roman', Times, serif;
    }
    .section-title {
      font-weight: bold;
      font-size: 10pt;
      text-transform: uppercase;
      margin: 8pt 0 2pt 0;
      border-bottom: 1pt solid #000;
      padding-bottom: 1pt;
      font-family: 'Times New Roman', Times, serif;
    }
    .section-desc {
      font-size: 9pt;
      font-weight: bold;
      font-style: italic;
      margin: 2pt 0 4pt 0;
      font-family: 'Times New Roman', Times, serif;
    }
    table.akm-table {
      width: 100%;
      border-collapse: collapse;
      border: 1pt solid #000000;
      margin: 4pt 0 6pt 0;
      font-size: 9pt;
      font-family: 'Times New Roman', Times, serif;
    }
    table.akm-table th, table.akm-table td {
      border: 1pt solid #000000;
      padding: 2.5pt 4pt;
      font-family: 'Times New Roman', Times, serif;
    }
  </style>
</head>
<body style="font-family: 'Times New Roman', Times, serif; color: #000000; margin: 0; padding: 0;">
  <div class="Section1">
    <!-- KODE UJIAN (KOTAK PERSEGI PANJANG) -->
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
    <table style="width: 100%; border-collapse: collapse; border-bottom: 3pt double #000000; padding-bottom: 5pt; margin-bottom: 7pt; border-top: none; border-left: none; border-right: none;">
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

    <!-- IDENTITAS SISWA -->
    <table style="width: 100%; border-collapse: collapse; font-size: 9pt; font-family: 'Times New Roman', serif; margin-bottom: 6pt; border: none;">
      <tr>
        <td style="width: 18%; border: none; padding: 1.5pt 0;"><strong>MATA PELAJARAN</strong></td>
        <td style="width: 35%; border: none; padding: 1.5pt 0;">: ${subjectName.toUpperCase()}</td>
        <td style="width: 18%; border: none; padding: 1.5pt 0;"><strong>HARI/TANGGAL</strong></td>
        <td style="width: 29%; border: none; padding: 1.5pt 0;">: ________________</td>
      </tr>
      <tr>
        <td style="border: none; padding: 1.5pt 0;"><strong>NAMA</strong></td>
        <td style="border: none; padding: 1.5pt 0;">: ______________________</td>
        <td style="border: none; padding: 1.5pt 0;"><strong>WAKTU</strong></td>
        <td style="border: none; padding: 1.5pt 0;">: 90  Menit</td>
      </tr>
      <tr>
        <td style="border: none; padding: 1.5pt 0;"><strong>KELAS</strong></td>
        <td colspan="3" style="border: none; padding: 1.5pt 0;">: ${classDisplay}</td>
      </tr>
    </table>

    <!-- PETUNJUK UMUM -->
    <div style="border: 1pt solid #000000; background-color: #fafafa; padding: 4pt 8pt; font-size: 7.5pt; font-family: 'Times New Roman', serif; margin-bottom: 7pt;">
      <strong>PETUNJUK UMUM :</strong>
      <ol style="margin: 2pt 0 0 16pt; padding: 0;">
        <li>Bacalah Do’a sebelum dan sesudah mengerjakan Soal</li>
        <li>Jawaban di kerjakan langsung pada Soal</li>
        <li>Isilah nama dengan lengkap dan jelas, lalu lingkari dalam satu pilihan kelas</li>
        <li>Pergunakan ballpoint untuk mengisi</li>
        <li>Dahulukan menjawab soal – soal yang anda anggap mudah</li>
      </ol>
    </div>

    <!-- BUTIR SOAL -->
    <div class="content-container ${is2Col ? 'col-2' : ''}">
      ${cloneContainer.innerHTML}
    </div>
  </div>
</body>
</html>`;

                // Kemas Dokumen MHTML Word (.doc)
                const boundary = "----=_NextPart_SiakadMts_Naskah_" + Date.now();
                let mhtml = "";

                mhtml += "MIME-Version: 1.0\r\n";
                mhtml += `Content-Type: multipart/related; boundary="${boundary}"\r\n\r\n`;

                // Part 1: Main HTML Body
                mhtml += `--${boundary}\r\n`;
                mhtml += "Content-Type: text/html; charset=utf-8\r\n";
                mhtml += "Content-Transfer-Encoding: 8bit\r\n\r\n";
                mhtml += wordHtml + "\r\n\r\n";

                // Part 2+: Gambar (Logo & Stimulus)
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
                a.download = `Naskah_Soal_${safeMapel}_${safeClass}_${safeEvent}_${paperName}_${layoutName}.doc`;
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
