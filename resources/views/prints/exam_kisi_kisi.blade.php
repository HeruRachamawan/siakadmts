<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kisi-Kisi Soal - {{ $package->title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
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
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header h2 { margin: 0; font-size: 13pt; text-transform: uppercase; }
        .header h3 { margin: 2px 0; font-size: 11pt; text-transform: uppercase; }

        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9.5pt;
        }
        .meta-table td { padding: 2px 4px; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 9pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
        }
        .center { text-align: center; }

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
    <button class="no-print" onclick="window.print()">🖨️ Cetak Kisi-Kisi</button>

    <div class="header">
        <h2>KISI-KISI PENULISAN SOAL {{ strtoupper($package->exam_type) }}</h2>
        <h3>{{ $school->school_name ?? 'MTs AL-HASANAH' }} - TAHUN PELAJARAN {{ $package->academicYear->year ?? '2026/2027' }}</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="18%"><strong>Satuan Pendidikan</strong></td>
            <td width="32%">: {{ $school->school_name ?? 'MTs AL-HASANAH' }}</td>
            <td width="18%"><strong>Bentuk / Jumlah Soal</strong></td>
            <td width="32%">: PG: {{ $package->questions->where('question_type', 'pg')->count() }} / Essay: {{ $package->questions->where('question_type', 'essay')->count() }} (Total: {{ $package->questions->count() }})</td>
        </tr>
        <tr>
            <td><strong>Mata Pelajaran</strong></td>
            <td>: {{ $package->subject->name ?? '-' }}</td>
            <td><strong>Alokasi Waktu</strong></td>
            <td>: 90 Menit</td>
        </tr>
        <tr>
            <td><strong>Kelas / Semester</strong></td>
            <td>: {{ $package->classRoom->name ?? '-' }} / {{ ucfirst($package->semester) }} ({{ $package->academic_phase ?? 'Fase D' }})</td>
            <td><strong>Penyusun Soal</strong></td>
            <td>: {{ $package->teacher->user->name ?? ($package->teacher->name ?? '-') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Capaian Pembelajaran (CP) / KD</th>
                <th width="18%">Materi Pokok</th>
                <th width="32%">Indikator Soal</th>
                <th width="8%">Level Kognitif</th>
                <th width="7%">Bentuk Soal</th>
                <th width="5%">No. Soal</th>
                <th width="6%">Bobot</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($package->questions as $q)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $q->basic_competency ?: ($q->material ?: 'Materi ' . $package->subject->name) }}</td>
                    <td>{{ $q->material ?: '-' }}</td>
                    <td>{{ $q->indicator ?: 'Disajikan masalah terkait ' . ($q->material ?? 'materi') . ', siswa dapat menyelesaikan dengan benar.' }}</td>
                    <td class="center">{{ $q->cognitive_level ?? 'L1' }}</td>
                    <td class="center">{{ strtoupper($q->question_type) }}</td>
                    <td class="center">{{ $q->question_number }}</td>
                    <td class="center">{{ $q->score_weight }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Belum ada data butir soal untuk kisi-kisi ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Waka Kurikulum</strong><br><br><br><br>
                <u>{{ $package->reviewedBy->name ?? '...................................................' }}</u><br>
                NIP/NUPTK: ...........................................
            </td>
            <td>
                ......................., {{ date('d F Y') }}<br>
                <strong>Guru Mata Pelajaran</strong><br><br><br><br>
                <u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? '...................................................') }}</u><br>
                NIP/NUPTK: {{ $package->teacher->nip ?? '...........................................' }}
            </td>
        </tr>
    </table>
</body>
</html>
