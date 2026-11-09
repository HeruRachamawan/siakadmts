<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Naskah Soal - {{ $package->title }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header h2 { margin: 0; font-size: 14pt; text-transform: uppercase; letter-spacing: 0.5px; }
        .header h3 { margin: 2px 0; font-size: 12pt; text-transform: uppercase; }
        .header p { margin: 1px 0; font-size: 9.5pt; }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .petunjuk {
            border: 1px solid #000;
            padding: 6px 10px;
            margin-bottom: 14px;
            font-size: 9pt;
            background-color: #fafafa;
        }
        .petunjuk strong { text-transform: uppercase; font-size: 9.5pt; }
        .petunjuk ol { margin: 3px 0 0 18px; padding: 0; }

        /* Multi-column layout */
        .content-container {
            width: 100%;
        }
        .col-2 {
            column-count: 2;
            column-gap: 24px;
            column-rule: 1px dashed #bbb;
            text-align: justify;
        }

        .question-item {
            break-inside: avoid;
            page-break-inside: avoid;
            margin-bottom: 14px;
        }
        .question-item::after {
            content: "";
            display: table;
            clear: both;
        }
        .q-number {
            font-weight: bold;
            float: left;
            width: 22px;
        }
        .q-text {
            margin-left: 24px;
            display: block;
            text-indent: 0 !important;
        }
        .q-text p, .q-text div, .q-text span {
            text-indent: 0 !important;
        }
        .q-text p {
            margin: 0 0 4px 0;
        }
        .q-text table {
            border-collapse: collapse;
            width: 100%;
            margin: 6px 0;
            font-size: 9pt;
        }
        .q-text th, .q-text td {
            border: 1px solid #444;
            padding: 3px 6px;
        }
        .q-text th {
            background-color: #f2f2f2;
        }
        .q-text ul {
            list-style-type: disc;
            padding-left: 18px;
            margin: 4px 0;
        }
        .q-text ol {
            list-style-type: decimal;
            padding-left: 18px;
            margin: 4px 0;
        }
        .q-text ol.list-paren-num {
            counter-reset: item;
            list-style: none !important;
            padding-left: 20px;
        }
        .q-text ol.list-paren-num > li {
            counter-increment: item;
            position: relative;
            margin-bottom: 2px;
        }
        .q-text ol.list-paren-num > li::before {
            content: "(" counter(item) ") ";
            font-weight: bold;
            display: inline-block;
            margin-left: -20px;
            width: 20px;
        }
        .q-text ol.list-paren-alpha {
            counter-reset: alpha-item;
            list-style: none !important;
            padding-left: 20px;
        }
        .q-text ol.list-paren-alpha > li {
            counter-increment: alpha-item;
            position: relative;
            margin-bottom: 2px;
        }
        .q-text ol.list-paren-alpha > li::before {
            content: "(" counter(alpha-item, lower-alpha) ") ";
            font-weight: bold;
            display: inline-block;
            margin-left: -20px;
            width: 20px;
        }
        .q-text ul.list-dash { list-style-type: '– '; }
        .q-text ul.list-check { list-style-type: '✓ '; }
        .q-text ul.list-arrow { list-style-type: '➢ '; }
        .q-img {
            display: block;
            max-width: 90%;
            max-height: 160px;
            margin: 6px 0 6px 24px;
            border: 1px solid #ccc;
            padding: 2px;
        }
        .options-list {
            list-style-type: none;
            padding-left: 24px;
            margin: 4px 0 0 0;
        }
        .options-list li {
            margin-bottom: 2px;
            display: flex;
            align-items: baseline;
        }
        .opt-label {
            font-weight: bold;
            width: 22px;
            flex-shrink: 0;
        }
        .key-badge {
            color: #059669;
            font-weight: bold;
            margin-left: 6px;
            font-size: 9pt;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10.5pt;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin: 16px 0 8px 0;
            letter-spacing: 0.5px;
        }
        .section-desc {
            font-size: 9.5pt;
            font-style: italic;
            margin: 0 0 8px 0;
        }

        .akm-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
            font-size: 9pt;
            page-break-inside: auto;
        }
        .akm-table tr {
            page-break-inside: avoid;
        }
        .akm-table th, .akm-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        .akm-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #059669;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: none;
            z-index: 9999;
        }
        .no-print:hover { background: #047857; }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨️ Cetak Naskah Soal</button>

    <div class="header">
        <h2>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h2>
        <h3>{{ $school->school_name ?? 'MTs AL-HASANAH' }}</h3>
        <p>PENILAIAN / ASESMEN SEMESTER {{ strtoupper($package->semester) }} TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Mata Pelajaran</strong></td>
            <td width="35%">: {{ $package->subject->name ?? '-' }}</td>
            <td width="15%"><strong>Kelas / Fase</strong></td>
            <td width="35%">: {{ $package->classRoom->name ?? '-' }} ({{ $package->academic_phase ?? 'Fase D' }})</td>
        </tr>
        <tr>
            <td><strong>Jenis Ujian</strong></td>
            <td>: {{ strtoupper($package->exam_type) }} - {{ $package->title }}</td>
            <td><strong>Hari / Tanggal</strong></td>
            <td>: ...............................................</td>
        </tr>
        <tr>
            <td><strong>Guru Pengampu</strong></td>
            <td>: {{ $package->teacher->user->name ?? ($package->teacher->name ?? '-') }}</td>
            <td><strong>Alokasi Waktu</strong></td>
            <td>: 90 Menit</td>
        </tr>
    </table>

    <div class="petunjuk">
        <strong>Petunjuk Umum:</strong>
        <ol>
            <li>Berdoalah sebelum mengerjakan soal.</li>
            <li>Tuliskan nama, nomor peserta, dan kelas pada lembar jawaban yang telah disediakan.</li>
            <li>Periksa dan bacalah setiap butir soal dengan teliti sebelum menjawab.</li>
            <li>Dahulukan menjawab soal-soal yang Anda anggap mudah.</li>
            <li>Laporkan kepada pengawas jika terdapat tulisan yang kurang jelas, rusak, atau jumlah soal kurang.</li>
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
            <p class="section-desc">Pilihlah salah satu jawaban yang paling tepat dengan memberi tanda silang (X) pada huruf pilihan yang tersedia!</p>

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
            <p class="section-desc">Pilihlah lebih dari satu jawaban yang benar dengan memberi tanda centang (&check;) pada kotak yang tersedia!</p>

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
                <p class="section-desc">Berilah tanda centang (✓) pada kolom <strong>Benar</strong> jika pernyataan sesuai, atau pada kolom <strong>Salah</strong> jika pernyataan tidak sesuai!</p>

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
                <p class="section-desc">Jodohkanlah istilah pada Kolom Kiri dengan pilihan jawaban yang tepat pada Kolom Kanan dengan menuliskan hurufnya (A, B, C, D, atau E) pada kolom <strong>Jawaban</strong> di tengah!</p>

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
            <div class="section-title">{{ $secLetters[$secIdx++] }}. Uraian / Essay</div>
            <p class="section-desc">Jawablah pertanyaan-pertanyaan berikut dengan jelas, tepat, dan lengkap!</p>

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
</body>
</html>
