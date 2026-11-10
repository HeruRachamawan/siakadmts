<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Instrumen Telaah Soal - {{ $package->title }}</title>
    <style id="page-size-style">
        @page {
            size: 330mm 215mm; /* Standar F4 / Folio Landscape (Lebar 33cm, Tinggi 21.5cm) */
            margin: 8mm 10mm 8mm 10mm;
        }
    </style>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
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
            padding: 10mm 12mm;
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
            padding: 2.5px 1px;
            vertical-align: middle;
        }
        .matrix-table th {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
        }
        .matrix-table td.col-v {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }
        .matrix-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .center { text-align: center; }

        .signatures {
            width: 100%;
            margin-top: 14px;
            break-inside: avoid;
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
            height: 48px;
            display: flex;
            align-items: center;
            margin: 2px 0;
        }
        .sig-img {
            max-height: 45px;
            max-width: 150px;
            object-fit: contain;
            display: block;
        }
        .sig-space {
            height: 48px;
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
            background: #8b5cf6;
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
                <span class="toolbar-title">Instrumen Telaah Butir Soal</span>
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
                        <option value="GANJIL">Ganjil (1)</option>
                        <option value="GENAP">Genap (2)</option>
                    </select>
                </div>

                <!-- Scaling Zoom -->
                <div class="toolbar-group">
                    <span class="group-label">Skala:</span>
                    <button type="button" class="btn-tool active" id="btn-scale-100" onclick="setScale(100)">100%</button>
                    <button type="button" class="btn-tool" id="btn-scale-95" onclick="setScale(95)">95%</button>
                    <button type="button" class="btn-tool" id="btn-scale-90" onclick="setScale(90)">90%</button>
                </div>

                <!-- Print Trigger Button -->
                <button type="button" class="btn-print" onclick="window.print()">
                    🖨️ Cetak Telaah
                </button>
            </div>
        </div>

        <div class="toolbar-tips">
            <span>💡 <strong>Tips Cetak Landscape F4:</strong> Pada dialog printer (Ctrl + P), pastikan <em>Paper size</em> dipilih <strong>Folio / F4 / 215 × 330 mm</strong>, <em>Orientation</em> dipilih <strong>Landscape</strong>, dan <em>Margins</em> dipilih <strong>Default / None</strong>.</span>
        </div>
    </div>

    <div id="print-sheet" class="print-sheet">

    @php
        // Deteksi Semester Baku
        $rawSem = strtolower(trim($package->semester ?? ''));
        $semStr = '1';
        $semesterLabel = 'GANJIL';
        if ($rawSem === 'genap' || $rawSem === '2') {
            $semStr = '2';
            $semesterLabel = 'GENAP';
        }

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
    @php
        $schoolTitle = strtoupper(trim($school->school_name ?? ''));
        if (empty($schoolTitle) || str_contains($schoolTitle, 'YASPIN') || str_contains($schoolTitle, 'SMK')) {
            $schoolTitle = 'MADRASAH TSANAWIYAH AL-HASANAH';
        }
    @endphp
    <div class="header">
        <div class="title-main">INSTRUMEN TELAAH SOAL <span id="display-event-title">{{ $fullTitle }}</span></div>
        <div class="title-sub">{{ $schoolTitle }}</div>
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
                <strong><u>H. Umar Usman Ali, S.Pd.,S.Pd.I</u></strong>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 20px;">
                <div style="display: inline-block; text-align: left; min-width: 250px;">
                    Bogor, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}<br>
                    <strong>Guru Mapel</strong><br>
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
                styleEl.innerHTML = '@page { size: 330mm 215mm; margin: 8mm 10mm 8mm 10mm; }';
                btnF4.classList.add('active');
                btnA4.classList.remove('active');
                sheet.style.maxWidth = '330mm';
                sheet.style.minHeight = '215mm';
            } else {
                styleEl.innerHTML = '@page { size: 297mm 210mm; margin: 8mm 10mm 8mm 10mm; }';
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
            const semVal = document.getElementById('select-semester').value;
            const titleEl = document.getElementById('display-event-title');
            
            let titleText = '';

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
