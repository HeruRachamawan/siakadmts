<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kunci Jawaban - {{ $package->title }}</title>
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

    @if ($pgQuestions->isNotEmpty())
        <div class="section-title">A. KUNCI JAWABAN PILIHAN GANDA</div>
        
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
                                <td><strong>{{ $item->question_number }}</strong></td>
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

    @if ($essayQuestions->isNotEmpty())
        <div class="section-title">B. PEDOMAN PENSKORAN SOAL URAIAN / ESSAY</div>
        <table class="essay-table">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th width="62%">Kunci Jawaban / Kriteria Jawaban</th>
                    <th width="15%">Bobot Maksimal</th>
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
