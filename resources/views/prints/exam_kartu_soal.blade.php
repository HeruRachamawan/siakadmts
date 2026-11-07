<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Soal - {{ $package->title }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
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
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨️ Cetak Kartu Soal</button>

    <div class="header">
        <h2>KARTU SOAL {{ strtoupper($package->exam_type) }}</h2>
        <h3>{{ $school->school_name ?? 'MTs AL-HASANAH' }} - TP {{ $package->academicYear->year ?? '2026/2027' }}</h3>
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
                    <td>{{ $package->classRoom->name ?? '-' }} / {{ ucfirst($package->semester) }}</td>
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
                <div>{!! nl2br(e($q->question_text ?? 'Soal belum diisi.')) !!}</div>

                @if ($q->question_image)
                    <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus">
                @endif

                @php
                    $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                @endphp

                <!-- PG Tunggal -->
                @if (!empty($opts) && $q->question_type === 'pg')
                    <ul class="opts">
                        @foreach (['A', 'B', 'C', 'D'] as $key)
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
                @elseif (!empty($opts['statements']) && $q->question_type === 'true_false')
                    <table style="width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8.5pt;">
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #ccc; padding: 2px 5px; width: 25px;">No</th>
                            <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left;">Pernyataan</th>
                            <th style="border: 1px solid #ccc; padding: 2px 5px; width: 45px; text-align: center;">Kunci</th>
                        </tr>
                        @foreach ($opts['statements'] as $sIdx => $st)
                            <tr>
                                <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center;">{{ $sIdx + 1 }}</td>
                                <td style="border: 1px solid #ccc; padding: 2px 5px;">{{ $st['text'] }}</td>
                                <td style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; font-weight: bold; color: {{ ($st['answer'] ?? '') === 'B' ? '#059669' : '#dc2626' }};">
                                    {{ ($st['answer'] ?? '') === 'B' ? 'Benar' : 'Salah' }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                <!-- Menjodohkan -->
                @elseif (!empty($opts['pairs']) && $q->question_type === 'matching')
                    <table style="width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8.5pt;">
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left; width: 45%;">Premis Kiri</th>
                            <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: left; width: 45%;">Pilihan Kanan</th>
                            <th style="border: 1px solid #ccc; padding: 2px 5px; text-align: center; width: 10%;">Kunci</th>
                        </tr>
                        @foreach ($opts['pairs'] as $pIdx => $pair)
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
            </div>
        </div>
    @endforeach
</body>
</html>
