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
            font-size: 9.5pt;
            line-height: 1.18;
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
        }

        /* DENSITY: KOMPAK (DEFAULT FIT MAKSIMAL 2 LEMBAR / 4 MUKA) */
        body.density-compact .print-sheet {
            font-size: 9.5pt;
            line-height: 1.18;
        }
        body.density-compact .header-kop {
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        body.density-compact .meta-table {
            font-size: 8.5pt !important;
            margin-bottom: 5px !important;
        }
        body.density-compact .meta-table td {
            padding: 1px 3px !important;
        }
        body.density-compact .petunjuk {
            font-size: 7.5pt !important;
            padding: 3px 8px !important;
            margin-bottom: 6px !important;
        }
        body.density-compact .petunjuk ol {
            margin: 2px 0 0 14px !important;
        }
        body.density-compact .section-title {
            margin: 8px 0 3px 0 !important;
            padding-bottom: 1px !important;
            font-size: 9.5pt !important;
        }
        body.density-compact .section-desc {
            margin: 2px 0 4px 0 !important;
            font-size: 8.5pt !important;
        }
        body.density-compact .question-item {
            margin-bottom: 7px !important;
        }
        body.density-compact .q-text p {
            margin: 0 0 2px 0 !important;
        }
        body.density-compact .options-list {
            margin: 2px 0 0 0 !important;
            padding-left: 20px !important;
        }
        body.density-compact .options-list li {
            margin-bottom: 1px !important;
        }
        body.density-compact .akm-table {
            font-size: 8.5pt !important;
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
        .options-list {
            list-style-type: none;
            padding-left: 22px;
            margin: 3px 0 0 0;
        }
        .options-list li {
            margin-bottom: 1.5px;
            display: flex;
            align-items: baseline;
            break-inside: avoid;
        }
        .opt-label {
            font-weight: bold;
            width: 20px;
            flex-shrink: 0;
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
        $subjCode = strtoupper(substr($package->subject->code ?? ($package->subject->name ?? 'INF'), 0, 3));
        $examCode = strtoupper($package->exam_type ?? 'ASAS') . '-' . $semNum . '.' . $yearShort . '- ' . $subjCode;
    @endphp

    <div style="display: flex; justify-content: flex-end; font-size: 8.5pt; font-family: Arial, sans-serif; font-weight: bold; margin-bottom: 2px;">
        <span id="display-exam-code">{{ $examCode }}</span>
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
            <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Pilihlah jawaban dibawah ini dengan tepat!!</p>

            @foreach ($pgQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    @php
                        $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                    @endphp
                    @if (!empty($opts))
                        <ul class="options-list">
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $key)
                                @if (isset($opts[$key]) && $opts[$key] !== '')
                                    <li>
                                        <span class="opt-label">{{ $key }}.</span>
                                        <span>{{ $opts[$key] }}</span>
                                        @if ($showKeys && strtoupper($q->correct_answer) === $key)
                                            <span class="key-badge">✓ (Kunci)</span>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        @endif

        <!-- 2. BAGIAN: PILIHAN GANDA KOMPLEKS (AKM) -->
        @if (isset($pgComplexQuestions) && $pgComplexQuestions->isNotEmpty())
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Pilihan Ganda Kompleks</div>
            <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Jawablah pernyatan dibawah ini dengan mengisi lebih dari pada 1 pilihan!!!</p>

            @foreach ($pgComplexQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! render_exam_question_html($q->question_text) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    @php
                        $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                        $keys = array_map('trim', explode(',', strtoupper($q->correct_answer ?? '')));
                    @endphp
                    @if (!empty($opts))
                        <ul class="options-list">
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $key)
                                @if (isset($opts[$key]) && $opts[$key] !== '')
                                    <li>
                                        <span class="opt-label" style="display:inline-flex; align-items:center; gap:3px;">
                                            <span style="display:inline-block; width:12px; height:12px; border:1px solid #000; text-align:center; line-height:11px; font-size:8pt;">
                                                {{ ($showKeys && in_array($key, $keys)) ? '✓' : '' }}
                                            </span>
                                            {{ $key }}.
                                        </span>
                                        <span>{{ $opts[$key] }}</span>
                                        @if ($showKeys && in_array($key, $keys))
                                            <span class="key-badge">✓</span>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
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
                <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Bacalah setiap pernyataan dibawah ini dengan teliti, kemudian berilah tadna centang (√) pada kolom Benar jika pernyataan sesuai dengan materi buku, atau pada kolom salah jika pernyataan tidak sesuai!!!!</p>

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

            {{-- 4A. TABEL UTUH MENJODOHKAN MODE MANDIRI (No. 36 s/d 40) DENGAN KOLOM JAWABAN DI TENGAH --}}
            @if (!empty($singleMatchingList))
                <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Pasangkan nama perangkat keras komputer pada Lajur Kiri (Nomor 1-5) dengan fungsi atau pengertiannya yang paling tepat pada Lajur Kanan (Huru A-F)! Tuliskan huruf pilihannya saja (A, B, C, D, E, atau F) pada kolom Jawaban di tengah!</p>

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
                @endphp

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
            <p class="section-desc">Jawablah pertanyaan berikut dengan singkat dan tepat!</p>

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
            <p class="section-desc" style="font-weight: bold; margin: 4px 0 8px 0;">Jawabalah pertanyaan di bawah ini dengan benar!!!</p>

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
    </script>
</body>
</html>
