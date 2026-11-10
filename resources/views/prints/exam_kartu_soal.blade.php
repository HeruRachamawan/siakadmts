<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Soal - {{ $package->title }}</title>
    <style>
        @page {
            size: 215mm 330mm; /* Standar F4 / Folio Portrait */
            margin: 10mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 12px;
        }
        .header h2 { margin: 0; font-size: 13pt; text-transform: uppercase; }
        .header h3 { margin: 2px 0; font-size: 11pt; text-transform: uppercase; }

        .kartu-container {
            border: 2px solid #000;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .kartu-header {
            background-color: #f2f2f2;
            padding: 6px 10px;
            border-bottom: 1px solid #000;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }
        .kartu-meta {
            width: 100%;
            border-collapse: collapse;
        }
        .kartu-meta td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9pt;
            vertical-align: top;
        }
        .rumusan-box {
            padding: 8px 10px;
            border-top: 1px solid #000;
            min-height: 90px;
        }
        .rumusan-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 9.5pt;
        }
        .q-img {
            max-width: 60%;
            max-height: 120px;
            display: block;
            margin: 4px 0;
        }
        .opts {
            margin: 4px 0 0 16px;
            padding: 0;
            list-style: none;
        }
        .opts li {
            margin-bottom: 2px;
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
        .rumusan-box table {
            border-collapse: collapse;
            width: 100%;
            margin: 6px 0;
            font-size: 9.5pt;
        }
        .rumusan-box th, .rumusan-box td {
            border: 1px solid #444;
            padding: 3px 6px;
        }
        .rumusan-box th {
            background-color: #f2f2f2;
        }
        .rumusan-box ul {
            list-style-type: disc;
            padding-left: 18px;
            margin: 4px 0;
        }
        .rumusan-box ol {
            list-style-type: decimal;
            padding-left: 18px;
            margin: 4px 0;
        }
        .rumusan-box ol.list-paren-num {
            counter-reset: item;
            list-style: none !important;
            padding-left: 20px;
        }
        .rumusan-box ol.list-paren-num > li {
            counter-increment: item;
            position: relative;
            margin-bottom: 2px;
        }
        .rumusan-box ol.list-paren-num > li::before {
            content: "(" counter(item) ") ";
            font-weight: bold;
            display: inline-block;
            margin-left: -20px;
            width: 20px;
        }
        .rumusan-box ol.list-paren-alpha {
            counter-reset: alpha-item;
            list-style: none !important;
            padding-left: 20px;
        }
        .rumusan-box ol.list-paren-alpha > li {
            counter-increment: alpha-item;
            position: relative;
            margin-bottom: 2px;
        }
        .rumusan-box ol.list-paren-alpha > li::before {
            content: "(" counter(alpha-item, lower-alpha) ") ";
            font-weight: bold;
            display: inline-block;
            margin-left: -20px;
            width: 20px;
        }
        .rumusan-box ul.list-dash { list-style-type: '– '; }
        .rumusan-box ul.list-check { list-style-type: '✓ '; }
        .rumusan-box ul.list-arrow { list-style-type: '➢ '; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨️ Cetak Kartu Soal</button>

    @php
        $schoolTitle = strtoupper(trim($school->school_name ?? ''));
        if (empty($schoolTitle) || str_contains($schoolTitle, 'YASPIN') || str_contains($schoolTitle, 'SMK')) {
            $schoolTitle = 'MTS AL - HASANAH';
        }

        $rawSem = strtolower(trim($package->semester ?? ''));
        $semesterLabel = ($rawSem === 'genap' || $rawSem === '2') ? 'GENAP' : 'GANJIL';
        $semesterWord = ($semesterLabel === 'GENAP') ? 'Genap' : 'Ganjil';

        $rawClass = trim($package->classRoom->name ?? 'VII');
        $cleanClassUpper = strtoupper($rawClass);
        if (preg_match('/(?:kelas\s*)?9\s*([a-z]?)/i', $rawClass, $m)) {
            $romanClass = 'IX' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?8\s*([a-z]?)/i', $rawClass, $m)) {
            $romanClass = 'VIII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } elseif (preg_match('/(?:kelas\s*)?7\s*([a-z]?)/i', $rawClass, $m)) {
            $romanClass = 'VII' . (!empty($m[1]) ? '-' . strtoupper($m[1]) : '');
        } else {
            $romanClass = $cleanClassUpper;
        }

        $examTypeKey = strtolower(trim($package->exam_type ?? 'sas'));
        $examTitle = 'ASESMEN SUMATIF AKHIR SEMESTER ' . $semesterLabel . ' (ASAS)';
        if (str_contains($examTypeKey, 'tengah') || str_contains($examTypeKey, 'sts') || str_contains($examTypeKey, 'pts') || str_contains($examTypeKey, 'asts')) {
            $examTitle = 'ASESMEN SUMATIF TENGAH SEMESTER ' . $semesterLabel . ' (ASTS)';
        } elseif (str_contains($examTypeKey, 'madrasah') || str_contains($examTypeKey, 'am') || str_contains($examTypeKey, 'um')) {
            $examTitle = 'ASESMEN MADRASAH (AM)';
        } elseif (str_contains($examTypeKey, 'harian') || str_contains($examTypeKey, 'uh') || str_contains($examTypeKey, 'formatif')) {
            $examTitle = 'ASESMEN FORMATIF / ULANGAN HARIAN';
        } elseif (str_contains($examTypeKey, 'pat') || str_contains($examTypeKey, 'asat') || (str_contains($examTypeKey, 'akhir') && $semesterLabel === 'GENAP')) {
            $examTitle = 'ASESMEN SUMATIF AKHIR TAHUN (ASAT)';
        }
    @endphp
    <div class="header">
        <h2>KARTU SOAL {{ $examTitle }}</h2>
        <h3>{{ $schoolTitle }} - TP {{ $package->academicYear->year ?? '2026/2027' }}</h3>
    </div>

    @php
        $typeLabels = [
            'pg' => 'PILIHAN GANDA',
            'pg_complex' => 'PILIHAN GANDA KOMPLEKS',
            'true_false' => 'BENAR / SALAH',
            'matching' => 'MENJODOHKAN',
            'short_answer' => 'ISIAN SINGKAT',
            'essay' => 'URAIAN / ESSAY'
        ];
    @endphp

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
                    <td>{{ $romanClass }} / {{ $semesterWord }}</td>
                    <td><strong>Level Kognitif</strong></td>
                    <td>{{ $q->cognitive_level ?? 'L1' }} (Kesukaran: {{ ucfirst($q->difficulty_level ?? 'sedang') }})</td>
                </tr>
                <tr>
                    <td><strong>Capaian Pembelajaran (CP) / KD</strong></td>
                    <td colspan="3">{{ $q->basic_competency ?: ($q->material ?: 'Materi Pokok ' . $package->subject->name) }}</td>
                </tr>
                <tr>
                    <td><strong>Materi Pokok</strong></td>
                    <td>{{ $q->material ?: '-' }}</td>
                    <td><strong>Kunci Jawaban</strong></td>
                    <td><strong style="color: #059669; font-size: 11pt;">{{ $q->correct_answer ?: '-' }}</strong> (Bobot: {{ $q->score_weight }})</td>
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
                    <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus">
                @endif

                @php
                    $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                @endphp

                <!-- PG Tunggal -->
                @if (!empty($opts) && $q->question_type === 'pg')
                    <ul class="opts">
                        @foreach (['A', 'B', 'C', 'D', 'E'] as $key)
                            @if (isset($opts[$key]) && $opts[$key] !== '')
                                <li>
                                    <strong>{{ $key }}.</strong> {{ $opts[$key] }}
                                    @if (strtoupper($q->correct_answer) === $key)
                                        <span style="font-weight: bold; color: #059669;">✓ (Kunci)</span>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                <!-- PG Kompleks -->
                @elseif (!empty($opts) && $q->question_type === 'pg_complex')
                    @php $cKeys = array_map('trim', explode(',', strtoupper($q->correct_answer ?? ''))); @endphp
                    <ul class="opts">
                        @foreach (['A', 'B', 'C', 'D', 'E'] as $key)
                            @if (isset($opts[$key]) && $opts[$key] !== '')
                                <li>
                                    <span style="display:inline-block; width:12px; height:12px; border:1px solid #000; text-align:center; line-height:11px; font-size:8pt;">
                                        {{ in_array($key, $cKeys) ? '✓' : '' }}
                                    </span>
                                    <strong>{{ $key }}.</strong> {{ $opts[$key] }}
                                    @if (in_array($key, $cKeys))
                                        <span style="font-weight: bold; color: #059669;">✓ (Kunci)</span>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                <!-- Benar / Salah -->
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
                                <th style="border: 1px solid #ccc; padding: 2px 5px; width: 45px; text-align: center;">Kunci</th>
                            </tr>
                            @foreach ($statements as $sIdx => $st)
                                <tr>
                                    <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center;">{{ $sIdx + 1 }}</td>
                                    <td style="border: 1px solid #ccc; padding: 2px 5px;">{{ $st['text'] }}</td>
                                    <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; font-weight: bold; color: {{ ($st['answer'] ?? '') === 'B' ? '#059669' : '#dc2626' }};">
                                        {{ ($st['answer'] ?? '') === 'B' ? 'Benar' : 'Salah' }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                <!-- Menjodohkan -->
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
</body>
</html>
