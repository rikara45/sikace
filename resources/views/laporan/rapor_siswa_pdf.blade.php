<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Nilai Siswa - {{ $siswa->nama_siswa }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; }
        .header h2 { margin: 0; font-size: 12pt; font-weight: normal;}
        .header p { margin: 0; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: center; font-size: 10pt; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .mapel { text-align: left; }
        .no-column { width: 30px; }
        .kkm-column { width: 45px; }
        .nilai-column { width: 60px; }
        .predikat-column { width: 60px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP NILAI SISWA</h1>
        <h2>{{ 'SMA KARTIKA XIX 1 BANDUNG' }}</h2>
        <p>{{ 'Jl. Taman Pramuka No.163, Cihapit, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40114' }}</p>
        <hr style="border-top: 1px solid black; margin-top: 5px;">
    </div>

    <table style="margin-bottom: 20px; border: none;">
        <tr>
            <th style="border: none; text-align:left;">Nama Siswa</th>
            <td style="border: none; text-align:left;">: {{ $siswa->nama_siswa }}</td>
            <th style="border: none; text-align:left;">Kelas</th>
            <td style="border: none; text-align:left;">: {{ $kelasPeriode ? $kelasPeriode->nama_kelas : ($siswa->kelas ? $siswa->kelas->nama_kelas : '-') }}</td>
        </tr>
        <tr>
            <th style="border: none; text-align:left;">NIS / NISN</th>
            <td style="border: none; text-align:left;">: {{ $siswa->nis }}{{ $siswa->nisn ? ' / ' . $siswa->nisn : '' }}</td>
            <th style="border: none; text-align:left;">Semester</th>
            <td style="border: none; text-align:left;">: {{ $semester }}</td>
        </tr>
        <tr>
            <th style="border: none; text-align:left;">Tahun Ajaran</th>
            <td style="border: none; text-align:left;">: {{ $tahunAjaran }}</td>
            <th style="border: none; text-align:left;">Wali Kelas</th>
            <td style="border: none; text-align:left;">: {{ $waliKelasPeriode ? $waliKelasPeriode->nama_guru : '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th class="no-column" rowspan="2">No</th>
                <th rowspan="2">Mata Pelajaran</th>
                <th class="kkm-column" rowspan="2">KKM</th>
                <th colspan="3">Komponen Nilai</th>
                <th class="nilai-column" rowspan="2">Nilai Akhir</th>
                <th class="predikat-column" rowspan="2">Predikat</th>
            </tr>
            <tr>
                <th class="nilai-column">Rata2 Tugas</th>
                <th class="nilai-column">UTS</th>
                <th class="nilai-column">UAS</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($nilais as $index => $nilai)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="mapel">{{ $nilai->mataPelajaran->nama_mapel }}</td>
                <td>{{ $kkmMapel[$nilai->mata_pelajaran_id] ?? '-' }}</td>
                {{-- Detail nilai tugas, UTS, UAS --}}
                <td>
                    @php
                        $totalSlots = $totalAssignmentSlotsMapel[$nilai->mata_pelajaran_id] ?? 0;
                        $nilaiTugasArr = is_array($nilai->nilai_tugas) ? $nilai->nilai_tugas : [];
                        $rataTugas = ($totalSlots > 0)
                            ? \App\Models\Nilai::calculateRataRataTugas($nilaiTugasArr, $totalSlots)
                            : null;
                    @endphp
                    {{ !is_null($rataTugas) ? number_format($rataTugas, 2) : '-' }}
                </td>
                <td>{{ !is_null($nilai->nilai_uts) ? number_format($nilai->nilai_uts, 2) : '-' }}</td>
                <td>{{ !is_null($nilai->nilai_uas) ? number_format($nilai->nilai_uas, 2) : '-' }}</td>
                <td>{{ !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                <td>{{ $nilai->predikat ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Belum ada data nilai untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>