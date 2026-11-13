<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kunci Jawaban & Pedoman Penskoran - {{ $package->title ?? 'Informatika' }}</title>
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
            font-size: 9.5pt;
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
            padding: 10mm 14mm 12mm 14mm;
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            border-radius: 4px;
            box-sizing: border-box;
            transition: all 0.2s ease;
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
            background: #059669;
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
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
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

        /* TYPOGRAPHY & TABLE STYLES */
        .header-kop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .header-kop h2 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .header-kop h3 {
            margin: 2px 0 0 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .header-kop .title-kunci {
            margin-top: 5px;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9.5pt;
        }
        .meta-table td {
            padding: 1.5px 2px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            font-size: 9.5pt;
            margin: 8px 0 3px 0;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 8px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .key-text {
            font-weight: bold;
            color: #000;
        }

        .formula-box {
            border: 1px solid #000;
            padding: 6px 10px;
            margin-top: 8px;
            margin-bottom: 14px;
            background: #fafafa;
            font-size: 9pt;
            page-break-inside: avoid;
        }

        .signatures {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }
        .signatures td {
            vertical-align: top;
        }

        /* PRINT STYLES */
        @media print {
            .no-print { display: none !important; }
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
<body class="scale-100 paper-f4">
    <!-- FLOATING INTERACTIVE CONTROLLER (SCREEN ONLY) -->
    <div class="no-print print-toolbar">
        <div class="toolbar-wrapper">
            <div class="toolbar-brand">
                <span class="toolbar-badge">F4 Folio</span>
                <span class="toolbar-title">Kunci Jawaban & Pedoman Penskoran</span>
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
                    <input type="text" id="input-print-date" class="input-tool" list="list-print-dates" value="{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}" oninput="updatePrintDate(this.value)" placeholder="Contoh: Desember 2026" style="width: 130px;" title="Ketik atau pilih bulan/tanggal titimangsa">
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

                <!-- Print Trigger Button -->
                <button type="button" class="btn-print" onclick="window.print()">
                    🖨️ Cetak Kunci Jawaban
                </button>
            </div>
        </div>

        <div class="toolbar-tips">
            <span>💡 <strong>Tips Cetak F4 Tanpa Terpotong:</strong> Pada jendela cetak printer (Ctrl + P), pastikan <em>Paper size</em> dipilih <strong>Folio / F4 / 215 × 330 mm</strong>, <em>Margins</em> dipilih <strong>Minimum / None</strong>, dan <strong>Hilangkan centang Headers & footers</strong> agar rapi maksimal! | ✏️ <strong>Edit Titimangsa:</strong> Anda dapat langsung klik & ketik teks <u>Bulan/Tanggal</u> pada tanda tangan dokumen di bawah atau melalui kotak <em>Bulan/Tgl</em> di atas.</span>
        </div>
    </div>

    <div id="print-sheet" class="print-sheet">

    @php
        // Normalisasi Nama Kelas ke Teks Terbilang Resmi
        $rawClass = trim($package->classRoom->name ?? 'VII');
        $cleanClassUpper = strtoupper($rawClass);
        if (preg_match('/(?:kelas\s*)?9\s*([a-z]?)/i', $rawClass, $m)) {
            $classDisplay = 'IX (SEMBILAN)' . (!empty($m[1]) ? ' ' . strtoupper($m[1]) : '');
            $romanClass = 'IX' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?8\s*([a-z]?)/i', $rawClass, $m)) {
            $classDisplay = 'VIII (DELAPAN)' . (!empty($m[1]) ? ' ' . strtoupper($m[1]) : '');
            $romanClass = 'VIII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?7\s*([a-z]?)/i', $rawClass, $m)) {
            $classDisplay = 'VII (TUJUH)' . (!empty($m[1]) ? ' ' . strtoupper($m[1]) : '');
            $romanClass = 'VII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (str_contains($cleanClassUpper, 'IX')) {
            $classDisplay = 'IX (SEMBILAN)';
            $romanClass = 'IX';
        } elseif (str_contains($cleanClassUpper, 'VIII')) {
            $classDisplay = 'VIII (DELAPAN)';
            $romanClass = 'VIII';
        } elseif (str_contains($cleanClassUpper, 'VII')) {
            $classDisplay = 'VII (TUJUH)';
            $romanClass = 'VII';
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
            // Default: SAS / ASAS
            $currentEvent = 'ASAS';
            $fullTitle = "ASESMEN SUMATIF AKHIR SEMESTER {$semesterLabel} (ASAS)";
        }

        // Kategori Soal
        $allQ = $package->questions;
        $pgSingle = $allQ->filter(fn($q) => $q->question_type === 'pg')->values();
        $pgComplex = $allQ->filter(fn($q) => $q->question_type === 'pg_complex')->values();
        $trueFalse = $allQ->filter(fn($q) => $q->question_type === 'true_false')->values();
        $matching = $allQ->filter(fn($q) => $q->question_type === 'matching')->values();
        $essay = $allQ->filter(fn($q) => $q->question_type === 'essay')->values();

        // Fallback default dataset jika paket soal di database masih belum terisi lengkap (sesuai format resmi Mase)
        $defaultPg = [
            1 => 'B', 2 => 'A', 3 => 'C', 4 => 'C', 5 => 'A',
            6 => 'B', 7 => 'C', 8 => 'C', 9 => 'A', 10 => 'B',
            11 => 'A', 12 => 'B', 13 => 'C', 14 => 'A', 15 => 'B',
            16 => 'A', 17 => 'B', 18 => 'C', 19 => 'D', 20 => 'A'
        ];
        $defaultPgk = [
            21 => 'A, C', 22 => 'A, C', 23 => 'A, B', 24 => 'A, B', 25 => 'A, B',
            26 => 'A, B', 27 => 'A, B', 28 => 'A, B', 29 => 'A, B', 30 => 'A, B'
        ];
        $defaultTf = [
            31 => 'B', 32 => 'B', 33 => 'S', 34 => 'B', 35 => 'S'
        ];
        $defaultMatch = [
            36 => 'C', 37 => 'D', 38 => 'A', 39 => 'E', 40 => 'B'
        ];
        $defaultEssay = [
            [
                'no' => 41,
                'text' => 'Perangkat Keras (Hardware): Komponen fisik komputer yang nyata, dapat dilihat, dan dapat disentuh secara langsung (contoh: CPU, monitor, keyboard, mouse).<br><br>Perangkat Lunak (Software): Program, aplikasi, atau instruksi elektronik yang tersimpan di dalam komputer untuk menjalankan perangkat keras dan tidak memiliki wujud fisik (contoh: OS Windows, Microsoft Word, Browser).',
                'score' => 5
            ],
            [
                'no' => 42,
                'text' => 'Contoh peranti masukan: Keyboard (papan ketik), Mouse (tetikus), Scanner, Microphone, Webcam.',
                'score' => 5
            ],
            [
                'no' => 43,
                'text' => 'Kegunaan utama: Memasukkan karakter teks, angka, simbol, serta memberikan perintah ke sistem komputer.<br><br>Jenis tata letak paling populer: QWERTY (diambil dari 6 tombol huruf pertama pada baris teratas mesin tik).',
                'score' => 5
            ],
            [
                'no' => 44,
                'text' => 'RAM (Random Access Memory): Bersifat volatile (sementara), sehingga seluruh data di dalam RAM akan hilang/terhapus saat komputer dimatikan.<br><br>ROM (Read Only Memory): Bersifat non-volatile (permanen), sehingga data dan instruksi sistem bawaan di dalam ROM tetap tersimpan dan tidak hilang meskipun komputer dimatikan.',
                'score' => 5
            ],
            [
                'no' => 45,
                'text' => '3 Contoh Peranti Keluaran (Output Devices):<br>1. Monitor: Menampilkan hasil olah data berupa visual di layar.<br>2. Printer: Mencetak dokumen atau gambar ke media fisik kertas.<br>3. Speaker: Mengeluarkan hasil olahan komputer dalam bentuk suara/audio.<br><br>2 Contoh Peranti Penyimpanan Sekunder (Secondary Storage):<br>1. Hard Disk (HDD) / Solid State Drive (SSD)<br>2. Flashdisk / Kartu Memori',
                'score' => 5
            ]
        ];

        // Hitung Skor Pedoman Penskoran
        $pgCount = $pgSingle->isNotEmpty() ? $pgSingle->count() : 20;
        $pgWeight = 1;
        $pgMaxScore = $pgCount * $pgWeight;

        $pgkCount = $pgComplex->isNotEmpty() ? $pgComplex->count() : 10;
        $pgkWeight = 3;
        $pgkMaxScore = $pgkCount * $pgkWeight;

        $tfCount = $trueFalse->isNotEmpty() ? $trueFalse->count() : 5;
        $tfWeight = 2;
        $tfMaxScore = $tfCount * $tfWeight;

        $matchCount = $matching->isNotEmpty() ? $matching->count() : 5;
        $matchWeight = 3;
        $matchMaxScore = $matchCount * $matchWeight;

        $essayCount = $essay->isNotEmpty() ? $essay->count() : 5;
        $essayWeight = 5;
        $essayMaxScore = $essay->isNotEmpty() ? $essay->sum('score_weight') : ($essayCount * $essayWeight);
        if ($essayMaxScore <= 0) $essayMaxScore = 25;

        $totalQCount = $pgCount + $pgkCount + $tfCount + $matchCount + $essayCount;
        $totalMaxScore = $pgMaxScore + $pgkMaxScore + $tfMaxScore + $matchMaxScore + $essayMaxScore;

        // Signature image Base64
        $sigPath = public_path('images/signatures/kepala_madrasah.png');
        $sigBase64 = file_exists($sigPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath)) : null;
    @endphp

    <!-- HEADER / KOP RESMI -->
    <div class="header-kop">
        <h2>YAYASAN AL-HASANAH CIOMAS</h2>
        <h3>PROVINSI JAWA BARAT</h3>
        <h3><span id="display-event-title">{{ $fullTitle }}</span></h3>
        <h3>TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}</h3>
        <div class="title-kunci">KUNCI JAWABAN</div>
    </div>

    <!-- IDENTITAS -->
    <table class="meta-table">
        <tr>
            <td style="width: 160px; font-weight: bold;">NAMA GURU</td>
            <td style="width: 10px;">:</td>
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

    <!-- 1. PILIHAN GANDA (KOMPAK 2 KOLOM 1-10 & 11-20) -->
    <div class="section-title">Pilihan Ganda</div>
    <table class="data-table">
        <tbody>
            @for ($i = 1; $i <= 10; $i++)
                @php
                    $leftQ = $pgSingle->firstWhere('question_number', $i);
                    $leftKey = $leftQ ? ($leftQ->correct_answer ?: '-') : ($defaultPg[$i] ?? '-');

                    $rightNo = $i + 10;
                    $rightQ = $pgSingle->firstWhere('question_number', $rightNo);
                    $rightKey = $rightQ ? ($rightQ->correct_answer ?: '-') : ($defaultPg[$rightNo] ?? '-');
                @endphp
                <tr>
                    <td style="width: 12%; text-align: center; font-weight: bold;">{{ $i }}</td>
                    <td style="width: 38%; text-align: center; font-weight: bold;">{{ $leftKey }}</td>
                    <td style="width: 12%; text-align: center; font-weight: bold;">{{ $rightNo }}</td>
                    <td style="width: 38%; text-align: center; font-weight: bold;">{{ $rightKey }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- 2. PILIHAN GANDA KOMPLEKS (KOMPAK 2 KOLOM 21-25 & 26-30) -->
    <div class="section-title">Pilihan Ganda Kompleks</div>
    <table class="data-table">
        <tbody>
            @for ($i = 21; $i <= 25; $i++)
                @php
                    $leftQ = $pgComplex->firstWhere('question_number', $i);
                    $leftKey = $leftQ ? ($leftQ->correct_answer ?: '-') : ($defaultPgk[$i] ?? '-');

                    $rightNo = $i + 5;
                    $rightQ = $pgComplex->firstWhere('question_number', $rightNo);
                    $rightKey = $rightQ ? ($rightQ->correct_answer ?: '-') : ($defaultPgk[$rightNo] ?? '-');
                @endphp
                <tr>
                    <td style="width: 12%; text-align: center; font-weight: bold;">{{ $i }}</td>
                    <td style="width: 38%; text-align: center; font-weight: bold;">{{ $leftKey }}</td>
                    <td style="width: 12%; text-align: center; font-weight: bold;">{{ $rightNo }}</td>
                    <td style="width: 38%; text-align: center; font-weight: bold;">{{ $rightKey }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- 3 & 4. BENAR / SALAH & MENJODOHKAN (BERDAMPINGAN DUA KOLOM) -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tr>
            <!-- Kolom Kiri: Benar / Salah -->
            <td style="width: 48%; vertical-align: top; padding: 0;">
                <div class="section-title">Benar / Salah</div>
                <table class="data-table" style="margin-bottom: 0;">
                    <tbody>
                        @for ($i = 31; $i <= 35; $i++)
                            @php
                                $tfQ = $trueFalse->firstWhere('question_number', $i);
                                $tfKey = $tfQ ? ($tfQ->correct_answer ?: '-') : ($defaultTf[$i] ?? '-');
                            @endphp
                            <tr>
                                <td style="width: 25%; text-align: center; font-weight: bold;">{{ $i }}</td>
                                <td style="width: 75%; text-align: center; font-weight: bold;">{{ $tfKey }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </td>

            <td style="width: 4%;"></td>

            <!-- Kolom Kanan: Menjodohkan -->
            <td style="width: 48%; vertical-align: top; padding: 0;">
                <div class="section-title">Menjodohkan</div>
                <table class="data-table" style="margin-bottom: 0;">
                    <tbody>
                        @for ($i = 36; $i <= 40; $i++)
                            @php
                                $mQ = $matching->firstWhere('question_number', $i);
                                $mKey = $mQ ? ($mQ->correct_answer ?: '-') : ($defaultMatch[$i] ?? '-');
                            @endphp
                            <tr>
                                <td style="width: 25%; text-align: center; font-weight: bold;">{{ $i }}</td>
                                <td style="width: 75%; text-align: center; font-weight: bold;">{{ $mKey }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- 5. URAIAN / ESSAY -->
    <div class="section-title" style="margin-top: 10px;">URAIAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">NO</th>
                <th style="width: 80%; text-align: center;">JAWABAN</th>
                <th style="width: 12%; text-align: center;">SKOR</th>
            </tr>
        </thead>
        <tbody>
            @if ($essay->isNotEmpty())
                @php $sumEssayScore = 0; @endphp
                @foreach ($essay as $q)
                    @php 
                        $score = $q->score_weight ?: 5; 
                        $sumEssayScore += $score;
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold; vertical-align: top;">{{ $q->question_number }}</td>
                        <td style="padding: 4px 6px; text-align: justify; vertical-align: top;">
                            {!! nl2br(e($q->correct_answer ?: ($q->rubric ?: 'Jawaban lengkap dan sesuai konsep materi.'))) !!}
                        </td>
                        <td style="text-align: center; font-weight: bold; vertical-align: top;">{{ $score }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold; background: #f9f9f9;">
                    <td colspan="2" style="text-align: right; padding-right: 15px;">JUMLAH</td>
                    <td style="text-align: center;">{{ $sumEssayScore }}</td>
                </tr>
            @else
                @php $sumEssayScore = 0; @endphp
                @foreach ($defaultEssay as $item)
                    @php $sumEssayScore += $item['score']; @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold; vertical-align: top;">{{ $item['no'] }}</td>
                        <td style="padding: 4px 6px; text-align: justify; vertical-align: top;">
                            {!! $item['text'] !!}
                        </td>
                        <td style="text-align: center; font-weight: bold; vertical-align: top;">{{ $item['score'] }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold; background: #f9f9f9;">
                    <td colspan="2" style="text-align: right; padding-right: 15px;">JUMLAH</td>
                    <td style="text-align: center;">{{ $sumEssayScore }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- 6. PERHITUNGAN PENILAIAN & BOBOT SKOR -->
    <div style="font-weight: bold; font-size: 9.5pt; margin-top: 14px; margin-bottom: 2px;">
        Perhitungan Penilaian &nbsp;:
    </div>
    <div style="font-size: 8pt; color: #444; font-style: italic; margin-bottom: 4px;">
        SILAHKAN DI ISI SESUAI PERHITUNGAN DARI PANDUAN
    </div>

    <table class="data-table" style="margin-bottom: 6px;">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">No</th>
                <th style="width: 44%; text-align: left; padding-left: 8px;">Jenis Soal</th>
                <th style="width: 16%; text-align: center;">Jumlah soal</th>
                <th style="width: 16%; text-align: center;">Bobot Skor</th>
                <th style="width: 18%; text-align: center;">Total Skor Maksimal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td style="padding-left: 8px;">Pilihan Ganda</td>
                <td style="text-align: center;">{{ $pgCount }}</td>
                <td style="text-align: center;">{{ $pgWeight }}</td>
                <td style="text-align: center;">{{ $pgMaxScore }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td style="padding-left: 8px;">Pilihan Ganda Kompleks</td>
                <td style="text-align: center;">{{ $pgkCount }}</td>
                <td style="text-align: center;">{{ $pgkWeight }}</td>
                <td style="text-align: center;">{{ $pgkMaxScore }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td style="padding-left: 8px;">Benar / Salah</td>
                <td style="text-align: center;">{{ $tfCount }}</td>
                <td style="text-align: center;">{{ $tfWeight }}</td>
                <td style="text-align: center;">{{ $tfMaxScore }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td style="padding-left: 8px;">Menjodohkan</td>
                <td style="text-align: center;">{{ $matchCount }}</td>
                <td style="text-align: center;">{{ $matchWeight }}</td>
                <td style="text-align: center;">{{ $matchMaxScore }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td style="padding-left: 8px;">Uraian</td>
                <td style="text-align: center;">{{ $essayCount }}</td>
                <td style="text-align: center;">{{ $essayWeight }}</td>
                <td style="text-align: center;">{{ $essayMaxScore }}</td>
            </tr>
            <tr style="font-weight: bold; background: #f2f2f2;">
                <td colspan="2" style="text-align: center;">TOTAL</td>
                <td style="text-align: center;">{{ $totalQCount }} Soal</td>
                <td style="text-align: center;">-</td>
                <td style="text-align: center;">{{ $totalMaxScore }}</td>
            </tr>
        </tbody>
    </table>

    <div style="font-weight: bold; font-size: 9pt; margin-top: 6px; margin-bottom: 16px;">
        Rumus Nilai Akhir : PG + PG KOMPLEKS + BENAR / SALAH + MENJODOHKAN + URAIAN = 100
    </div>

    <!-- 7. PENGESAHAN / TANDA TANGAN -->
    <table class="signatures">
        <tr>
            <td style="width: 50%; padding-left: 10px;">
                Mengetahui,<br>
                <strong>Kepala Madrasah</strong><br>
                @if($sigBase64)
                    <div style="height: 52px; margin: 4px 0;">
                        <img src="{{ $sigBase64 }}" alt="TTD Kepala Madrasah" style="height: 52px; width: auto; object-fit: contain;">
                    </div>
                @else
                    <div style="height: 52px;"></div>
                @endif
                <strong><u>H. Umar Usman Ali, S.Pd.,S.Pd.I</u></strong>
            </td>
            <td style="width: 50%; text-align: right; padding-right: 10px;">
                <div style="display: inline-block; text-align: left; min-width: 220px;">
                    <span id="display-print-city" class="editable-date" contenteditable="true" title="Klik untuk mengedit kota">Bogor</span>,<span id="display-print-space">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span id="display-print-date" class="editable-date" contenteditable="true" title="Klik langsung untuk mengedit bulan atau tanggal">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span><br>
                    <strong>Guru Mata Pelajaran</strong><br>
                    <div style="height: 52px;"></div>
                    <strong><u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? 'Heru Rachmawan') }}</u></strong><br>
                    <span>NIP: {{ $package->teacher->nip ?? '-' }}</span>
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
            const semSelectVal = document.getElementById('select-semester').value;
            const titleEl = document.getElementById('display-event-title');
            
            const isGenap = semSelectVal.startsWith('GENAP');
            const semUpper = isGenap ? 'GENAP' : 'GANJIL';

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
