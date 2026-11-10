<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kunci Jawaban - {{ $package->title }}</title>
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

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt; }
        .meta-table td { padding: 2px 4px; }

        .section-title {
            font-weight: bold;
            font-size: 10.5pt;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin: 14px 0 8px 0;
        }

        .pg-matrix {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 14px;
        }
        .pg-matrix th, .pg-matrix td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
        }
        .pg-matrix th { background-color: #f2f2f2; }
        .key-cell { font-weight: bold; color: #059669; font-size: 11pt; }

        .essay-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 6px;
        }
        .essay-table th, .essay-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }
        .essay-table th { background-color: #f2f2f2; text-align: center; }

        .formula-box {
            border: 1px dashed #000;
            padding: 8px 12px;
            margin-top: 14px;
            background: #fdfdfd;
            font-size: 9pt;
        }

        .signatures {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
            font-size: 10pt;
        }
        .signatures td {
            text-align: center;
            vertical-align: top;
            width: 50%;
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
    <button class="no-print" onclick="window.print()">🖨️ Cetak Kunci & Pedoman Penskoran</button>

    <div class="header">
        <h2>KUNCI JAWABAN & PEDOMAN PENSKORAN</h2>
        <h3>{{ $school->school_name ?? 'MTs AL-HASANAH' }} - TP {{ $package->academicYear->year ?? '2026/2027' }}</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="18%"><strong>Mata Pelajaran</strong></td>
            <td width="32%">: {{ $package->subject->name ?? '-' }}</td>
            <td width="18%"><strong>Jenis Ujian</strong></td>
            <td width="32%">: {{ strtoupper($package->exam_type) }} - {{ $package->title }}</td>
        </tr>
        <tr>
            <td><strong>Kelas / Semester</strong></td>
            <td>: {{ $package->classRoom->name ?? '-' }} / {{ ucfirst($package->semester) }}</td>
            <td><strong>Bobot Nilai</strong></td>
            <td>: PG ({{ $package->pg_weight }}%) | Essay ({{ $package->essay_weight }}%)</td>
        </tr>
        <tr>
            <td><strong>Guru Pengampu</strong></td>
            <td>: {{ $package->teacher->user->name ?? ($package->teacher->name ?? '-') }}</td>
            <td><strong>KKM / KKTP</strong></td>
            <td>: {{ number_format($package->kkm, 0) }}</td>
        </tr>
    </table>

    @php
        $secLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
        $secIdx = 0;
    @endphp

    @if ($pgQuestions->isNotEmpty())
        <div class="section-title">{{ $secLetters[$secIdx++] }}. KUNCI JAWABAN PILIHAN GANDA (TUNGGAL & KOMPLEKS)</div>
        
        @php
            $chunks = $pgQuestions->chunk(10);
        @endphp

        <table class="pg-matrix">
            <thead>
                <tr>
                    @foreach ($chunks as $chunk)
                        <th width="{{ 100 / count($chunks) }}%">Nomor</th>
                        <th width="{{ 100 / count($chunks) }}%">Kunci</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $maxRows = $chunks->map(fn($c) => $c->count())->max();
                @endphp
                @for ($r = 0; $r < $maxRows; $r++)
                    <tr>
                        @foreach ($chunks as $chunk)
                            @php
                                $item = $chunk->values()->get($r);
                            @endphp
                            @if ($item)
                                <td><strong>{{ $item->question_number }}</strong> ({{ strtoupper($item->question_type === 'pg_complex' ? 'PGK' : 'PG') }})</td>
                                <td class="key-cell">{{ $item->correct_answer ?: '-' }}</td>
                            @else
                                <td></td>
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                @endfor
            </tbody>
        </table>
    @endif

    @if (isset($trueFalseQuestions) && $trueFalseQuestions->isNotEmpty())
        <div class="section-title">{{ $secLetters[$secIdx++] }}. KUNCI JAWABAN PERNYATAAN BENAR / SALAH</div>
        <table class="essay-table">
            <thead>
                <tr>
                    <th width="8%">No. Soal</th>
                    <th width="72%">Daftar Pernyataan & Kunci Kebenaran</th>
                    <th width="20%">Kunci Ringkas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($trueFalseQuestions as $q)
                    @php
                        $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                        $statements = $opts['statements'] ?? [];
                        $isSingle = ($opts['mode'] ?? '') === 'single' || empty($statements) || count($statements) <= 1;
                    @endphp
                    <tr>
                        <td align="center"><strong>{{ $q->question_number }}</strong></td>
                        <td>
                            @if ($isSingle)
                                <div>{!! strip_tags($q->question_text) !!}</div>
                            @else
                                <ol style="margin: 0; padding-left: 18px;">
                                    @foreach ($statements as $sIdx => $st)
                                        <li>{{ $st['text'] }} &rarr; <strong>{{ ($st['answer'] ?? '') === 'B' ? 'BENAR' : 'SALAH' }}</strong></li>
                                    @endforeach
                                </ol>
                            @endif
                        </td>
                        <td align="center" style="font-weight: bold; color: {{ strtoupper($q->correct_answer) === 'B' ? '#059669' : '#dc2626' }}; font-size: 10pt;">
                            @if ($isSingle)
                                {{ strtoupper($q->correct_answer) === 'B' ? 'BENAR (B)' : 'SALAH (S)' }}
                            @else
                                {{ $q->correct_answer }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($matchingQuestions) && $matchingQuestions->isNotEmpty())
        <div class="section-title">{{ $secLetters[$secIdx++] }}. KUNCI JAWABAN MENJODOHKAN</div>
        <table class="essay-table">
            <thead>
                <tr>
                    <th width="8%">No. Soal</th>
                    <th width="72%">Pasangan Jawaban</th>
                    <th width="20%">Kunci Ringkas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($matchingQuestions as $q)
                    @php
                        $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                        $pairs = $opts['pairs'] ?? [];
                        $isSingle = ($opts['mode'] ?? '') === 'single' || empty($pairs) || count($pairs) <= 1;
                    @endphp
                    <tr>
                        <td align="center"><strong>{{ $q->question_number }}</strong></td>
                        <td>
                            @if ($isSingle)
                                <div>Premis: <strong>{!! strip_tags($q->question_text) !!}</strong></div>
                                <div style="color: #4b5563; font-size: 8.5pt; margin-top: 2px;">
                                    Pasangan: <em>{{ $opts['right_text'] ?? ($pairs[0]['right'] ?? '-') }}</em>
                                </div>
                            @else
                                <ul style="margin: 0; padding-left: 16px; list-style-type: square;">
                                    @foreach ($pairs as $pIdx => $pair)
                                        <li>Premis {{ $pIdx + 1 }} (<em>{{ $pair['left'] }}</em>) &rarr; Pilihan {{ $pair['key'] ?? chr(65 + $pIdx) }} (<em>{{ $pair['right'] }}</em>)</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td align="center" style="font-weight: bold; color: #059669; font-size: 10pt;">
                            @if ($isSingle)
                                &rarr; {{ strtoupper($q->correct_answer ?: ($opts['key'] ?? 'A')) }}
                            @else
                                {{ $q->correct_answer }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($shortAnswerQuestions) && $shortAnswerQuestions->isNotEmpty())
        <div class="section-title">{{ $secLetters[$secIdx++] }}. KUNCI JAWABAN ISIAN SINGKAT</div>
        <table class="essay-table">
            <thead>
                <tr>
                    <th width="8%">No. Soal</th>
                    <th width="72%">Kata Kunci Jawaban</th>
                    <th width="20%">Bobot Skor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shortAnswerQuestions as $q)
                    <tr>
                        <td align="center"><strong>{{ $q->question_number }}</strong></td>
                        <td><strong style="color: #059669; font-size: 10pt;">{{ $q->correct_answer }}</strong></td>
                        <td align="center">{{ $q->score_weight }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($essayQuestions->isNotEmpty())
        <div class="section-title">{{ $secLetters[$secIdx++] }}. PEDOMAN PENSKORAN SOAL URAIAN / ESSAY</div>
        <table class="essay-table">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th width="72%">Kunci Jawaban / Kriteria Rubrik Penilaian</th>
                    <th width="20%">Bobot Maksimal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($essayQuestions as $q)
                    <tr>
                        <td align="center"><strong>{{ $q->question_number }}</strong></td>
                        <td>{!! nl2br(e($q->correct_answer ?: ($q->rubric ?: 'Jawaban lengkap dan sesuai konsep materi.'))) !!}</td>
                        <td align="center">{{ $q->score_weight }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="formula-box">
        <strong>Rumus Perhitungan Nilai Akhir:</strong>
        <p style="margin: 3px 0 0 0;">
            Nilai Akhir (NA) = 
            @if ($essayQuestions->isNotEmpty())
                ((Jumlah Benar PG / {{ $pgQuestions->count() ?: 1 }}) &times; {{ $package->pg_weight }}) + 
                ((Skor Perolehan Essay / {{ $essayQuestions->sum('score_weight') ?: 1 }}) &times; {{ $package->essay_weight }})
            @else
                (Jumlah Benar PG / {{ $pgQuestions->count() ?: 1 }}) &times; 100
            @endif
        </p>
    </div>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Waka Kurikulum</strong><br><br><br><br>
                <u>{{ $package->reviewedBy->name ?? '...................................................' }}</u><br>
                NIP/NUPTK: ...........................................
            </td>
            <td>
                {{ date('d F Y') }}<br>
                <strong>Guru Mata Pelajaran</strong><br><br><br><br>
                <u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? '...................................................') }}</u><br>
                NIP/NUPTK: {{ $package->teacher->nip ?? '...........................................' }}
            </td>
        </tr>
    </table>
</body>
</html>
