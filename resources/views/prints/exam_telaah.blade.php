<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Telaah Butir Soal - {{ $package->title }}</title>
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

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9.5pt; }
        .meta-table td { padding: 2px 4px; }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 8px;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .table-data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .center { text-align: center; }

        .rekap-box {
            border: 1px solid #000;
            padding: 8px 12px;
            margin-top: 14px;
            background: #fafafa;
            font-size: 9.5pt;
        }
        .rekap-box strong { font-size: 10pt; }

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
    <button class="no-print" onclick="window.print()">🖨️ Cetak Berita Acara Telaah</button>

    <div class="header">
        <h2>INSTRUMEN DAN BERITA ACARA TELAAH BUTIR SOAL</h2>
        <h3>{{ $school->school_name ?? 'MTs AL-HASANAH' }} - TP {{ $package->academicYear->year ?? '2026/2027' }}</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="18%"><strong>Mata Pelajaran</strong></td>
            <td width="32%">: {{ $package->subject->name ?? '-' }}</td>
            <td width="18%"><strong>Bentuk Ujian</strong></td>
            <td width="32%">: {{ strtoupper($package->exam_type) }} - {{ $package->title }}</td>
        </tr>
        <tr>
            <td><strong>Kelas / Fase</strong></td>
            <td>: {{ $package->classRoom->name ?? '-' }} / {{ $package->academic_phase ?? 'Fase D' }}</td>
            <td><strong>Guru Penyusun</strong></td>
            <td>: {{ $package->teacher->user->name ?? ($package->teacher->name ?? '-') }}</td>
        </tr>
        <tr>
            <td><strong>Penelaah Kurikulum</strong></td>
            <td>: {{ $package->reviewedBy->name ?? 'Tim Kurikulum Madrasah' }}</td>
            <td><strong>Tanggal Telaah</strong></td>
            <td>: {{ $package->reviewed_at ? $package->reviewed_at->format('d F Y') : date('d F Y') }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Aspek yang Ditelaah</th>
                <th width="45%">Kaidah Penulisan Butir Soal</th>
                <th width="15%">Hasil Telaah</th>
            </tr>
        </thead>
        <tbody>
            <!-- Materi -->
            <tr>
                <td class="center" rowspan="4" style="background:#f9f9f9;"><strong>I</strong></td>
                <td rowspan="4"><strong>A. ASPEK MATERI</strong></td>
                <td>1. Soal sesuai dengan indikator pada kisi-kisi.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>2. Materi yang diukur sesuai dengan kompetensi dan fase jenjang kelas.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>3. Pilihan jawaban homogen dan logis ditinjau dari segi materi.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>4. Hanya ada satu kunci jawaban yang paling tepat dan benar.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>

            <!-- Konstruksi -->
            <tr>
                <td class="center" rowspan="5" style="background:#f9f9f9;"><strong>II</strong></td>
                <td rowspan="5"><strong>B. ASPEK KONSTRUKSI</strong></td>
                <td>5. Pokok soal dirumuskan dengan singkat, jelas, dan tegas.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>6. Rumusan pokok soal tidak memberi petunjuk ke arah kunci jawaban.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>7. Pokok soal tidak mengandung pernyataan negatif ganda.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>8. Panjang rumusan pilihan jawaban relatif sama.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>9. Gambar, grafik, tabel, atau diagram berfungsi jelas dan terbaca.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>

            <!-- Bahasa -->
            <tr>
                <td class="center" rowspan="3" style="background:#f9f9f9;"><strong>III</strong></td>
                <td rowspan="3"><strong>C. ASPEK BAHASA</strong></td>
                <td>10. Menggunakan bahasa yang sesuai dengan kaidah Bahasa Indonesia (EYD).</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>11. Menggunakan bahasa yang komunikatif dan tidak menimbulkan penafsiran ganda.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
            <tr>
                <td>12. Tidak menggunakan bahasa yang berlaku setempat/tabu dan bebas muatan SARA.</td>
                <td class="center" style="color: #059669; font-weight: bold;">Memenuhi (✓)</td>
            </tr>
        </tbody>
    </table>

    <div class="rekap-box">
        <strong>REKOMENDASI & KEPUTUSAN TIM TELAAH KURIKULUM:</strong>
        <p style="margin: 4px 0 0 0;">
            Status Validasi: 
            @if ($package->status_kurikulum === 'approved')
                <strong style="color: #059669; font-size: 11pt;">[ DITERIMA TANPA REVISI / SIAP UJI ]</strong>
            @elseif ($package->status_kurikulum === 'revision_needed')
                <strong style="color: #d97706; font-size: 11pt;">[ DITERIMA DENGAN PERBAIKAN / REVISI ]</strong>
            @else
                <strong style="color: #4b5563; font-size: 11pt;">[ DALAM PROSES PENELAAHAN ]</strong>
            @endif
        </p>
        @if ($package->review_notes)
            <p style="margin: 4px 0 0 0;"><strong>Catatan Penelaah:</strong> <em>{{ $package->review_notes }}</em></p>
        @endif
    </div>

    <table class="signatures">
        <tr>
            <td>
                Menyetujui,<br>
                <strong>Penelaah (Waka Kurikulum)</strong><br><br><br><br>
                <u>{{ $package->reviewedBy->name ?? '...................................................' }}</u><br>
                NIP/NUPTK: ...........................................
            </td>
            <td>
                {{ date('d F Y') }}<br>
                <strong>Guru Penyusun Soal</strong><br><br><br><br>
                <u>{{ $package->teacher->user->name ?? ($package->teacher->name ?? '...................................................') }}</u><br>
                NIP/NUPTK: {{ $package->teacher->nip ?? '...........................................' }}
            </td>
        </tr>
    </table>
</body>
</html>
