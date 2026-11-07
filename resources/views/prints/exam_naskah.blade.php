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
        .q-number {
            font-weight: bold;
            display: inline-block;
            width: 20px;
        }
        .q-text {
            display: inline;
        }
        .q-img {
            display: block;
            max-width: 90%;
            max-height: 160px;
            margin: 6px auto;
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
            margin: 16px 0 10px 0;
            letter-spacing: 0.5px;
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

    <div class="content-container {{ $layout === '2col' ? 'col-2' : '' }}">
        @if ($pgQuestions->isNotEmpty())
            <div class="section-title">A. Pilihan Ganda</div>
            <p style="font-size: 9.5pt; font-style: italic; margin: 0 0 8px 0;">Pilihlah salah satu jawaban yang paling tepat dengan memberi tanda silang (X) pada huruf A, B, C, atau D!</p>

            @foreach ($pgQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! nl2br(e($q->question_text ?? 'Soal belum diisi.')) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    @php
                        $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                    @endphp
                    @if (!empty($opts))
                        <ul class="options-list">
                            @foreach (['A', 'B', 'C', 'D'] as $key)
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

        @if ($essayQuestions->isNotEmpty())
            <div class="section-title">B. Uraian / Essay</div>
            <p style="font-size: 9.5pt; font-style: italic; margin: 0 0 8px 0;">Jawablah pertanyaan-pertanyaan berikut dengan jelas, tepat, dan lengkap!</p>

            @foreach ($essayQuestions as $q)
                <div class="question-item">
                    <span class="q-number">{{ $q->question_number }}.</span>
                    <div class="q-text">{!! nl2br(e($q->question_text ?? 'Soal belum diisi.')) !!}</div>

                    @if ($q->question_image)
                        <img src="{{ asset('storage/' . str_replace('exam-questions/', '', $q->question_image)) }}" class="q-img" alt="Stimulus No {{ $q->question_number }}">
                    @endif

                    @if ($showKeys && !empty($q->correct_answer))
                        <div style="margin-top: 4px; padding: 4px 8px; background: #f0fdf4; border: 1px solid #bbf7d0; font-size: 9pt;">
                            <strong>Kunci / Pedoman Penskoran:</strong> {{ $q->correct_answer }}
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</body>
</html>
