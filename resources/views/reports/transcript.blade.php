<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 5px 8px; text-align: left; }
        th { background: #eee; }
        .right { text-align: right; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h2>Transkrip Nilai Siswa</h2>
    <h3>{{ $student->full_name ?? ($student->only(['full_name'])['full_name'] ?? '-') }}</h3>
    <p class="muted">
        NISN: {{ $student->nisn }} &nbsp;|&nbsp; NIS: {{ $student->nis }} &nbsp;|&nbsp;
        Kelas: {{ $student->classRoom->name ?? '-' }} &nbsp;|&nbsp;
        Tahun Ajaran: {{ $academic_year->year ?? '-' }} ({{ $academic_year->semester ?? '' }})
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Mata Pelajaran</th>
                <th>Kode</th>
                <th class="right">Tugas</th>
                <th class="right">UTS</th>
                <th class="right">UAS</th>
                <th class="right">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php($no = 1)
            @foreach ($grades as $grade)
                <tr>
                    <td class="right">{{ $no++ }}</td>
                    <td>{{ $grade->subject->name }}</td>
                    <td>{{ $grade->subject->code }}</td>
                    <td class="right">{{ $grade->score_assignment }}</td>
                    <td class="right">{{ $grade->score_uts }}</td>
                    <td class="right">{{ $grade->score_uas }}</td>
                    <td class="right">{{ $grade->final_score }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="muted">Rata-rata keseluruhan: <strong>{{ $overall_average }}</strong> &nbsp;|&nbsp;
       Mata pelajaran: {{ $subjects_count }}</p>

    <h3>Rekapitulasi Kehadiran</h3>
    <table>
        <thead>
            <tr>
                <th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpha</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $attendance_summary['present'] ?? 0 }}</td>
                <td>{{ $attendance_summary['sick'] ?? 0 }}</td>
                <td>{{ $attendance_summary['permission'] ?? 0 }}</td>
                <td>{{ $attendance_summary['alpha'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    <p class="muted">Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} oleh sistem Manajemen Data Siswa.</p>
</body>
</html>
