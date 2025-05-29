<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rapor Siswa - {{ $siswa->nama_siswa }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; }
        .header h2 { margin: 0; font-size: 12pt; font-weight: normal;}
        .header p { margin: 0; font-size: 9pt; }
        .info-siswa table, .nilai-table table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-siswa th, .info-siswa td { text-align: left; padding: 4px 8px; font-size: 9pt; vertical-align: top;}
        .info-siswa th { width: 150px; }
        .nilai-table th, .nilai-table td { border: 1px solid #333; padding: 5px; text-align: center; font-size: 9pt; }
        .nilai-table th { background-color: #f2f2f2; font-weight: bold; }
        .nilai-table td.mapel { text-align: left; }
        .signatures { margin-top: 50px; width: 100%; }
        .signatures td { width: 33%; text-align: center; font-size: 9pt; padding-top: 40px; }
        .signatures .label { margin-bottom: 60px; } /* Jarak untuk tanda tangan */
        .page-break { page-break-after: always; }
        .small-text { font-size: 8pt; }
        .kkm-column { width: 40px; }
        .nilai-column { width: 50px; }
        .predikat-column { width: 50px; }
        .guru-column { text-align: left; }
        .no-column { width: 25px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN HASIL BELAJAR SISWA</h1>
        <h2>{{'SMA KARTIKA XIX 1 BANDUNG' }}</h2>
        <p>{{'Jl. Taman Pramuka No.163, Cihapit, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40114' }}</p>
        <hr style="border-top: 1px solid black; margin-top: 5px;">
    </div>

    <div class="info-siswa">
        <table>
            <tr>
                <th>Nama Siswa</th>
                <td>: {{ $siswa->nama_siswa }}</td>
                <th>Kelas</th>
                <td>: {{ $kelasPeriode ? $kelasPeriode->nama_kelas : ($siswa->kelas ? $siswa->kelas->nama_kelas : '-') }}</td>
            </tr>
            <tr>
                <th>NIS / NISN</th>
                <td>: {{ $siswa->nis }}{{ $siswa->nisn ? ' / ' . $siswa->nisn : '' }}</td>
                <th>Semester</th>
                <td>: {{ $semester }}</td>
            </tr>
            <tr>
                <th>Tahun Ajaran</th>
                <td>: {{ $tahunAjaran }}</td>
                <th>Wali Kelas</th>
                <td>: {{ $waliKelasPeriode ? $waliKelasPeriode->nama_guru : '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="nilai-table">
        <p style="font-weight: bold; margin-bottom: 5px;">A. SIKAP</p>
        {{-- Bagian sikap bisa Anda kembangkan jika ada datanya --}}
        <table style="margin-bottom: 15px;">
            <thead>
                <tr>
                    <th>Dimensi Sikap</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: left; padding-left:10px; font-weight:bold;">Sikap Spiritual</td>
                    <td style="text-align: left; padding: 8px;">Menunjukkan perkembangan sikap spiritual yang baik, perlu ditingkatkan pada aspek ... (Contoh)</td>
                </tr>
                 <tr>
                    <td style="text-align: left; padding-left:10px; font-weight:bold;">Sikap Sosial</td>
                    <td style="text-align: left; padding: 8px;">Menunjukkan perkembangan sikap sosial yang baik, perlu ditingkatkan pada aspek ... (Contoh)</td>
                </tr>
            </tbody>
        </table>


        <p style="font-weight: bold; margin-bottom: 5px;">B. PENGETAHUAN DAN KETERAMPILAN</p>
        <table>
            <thead>
                <tr>
                    <th class="no-column" rowspan="2">No</th>
                    <th rowspan="2">Mata Pelajaran</th>
                    <th class="kkm-column" rowspan="2">KKM</th>
                    <th colspan="2">Pengetahuan</th>
                    <th colspan="2">Keterampilan *)</th>
                </tr>
                <tr>
                    <th class="nilai-column">Nilai</th>
                    <th class="predikat-column">Predikat</th>
                    <th class="nilai-column">Nilai</th>
                    <th class="predikat-column">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($nilais as $index => $nilai)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="mapel">{{ $nilai->mataPelajaran->nama_mapel }}</td>
                    <td>{{ $kkmMapel[$nilai->mata_pelajaran_id] ?? '-' }}</td>
                    <td>{{ !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 0) : '-' }}</td>
                    <td>{{ $nilai->predikat ?? '-' }}</td>
                    <td>-</td> {{-- Keterampilan, isi jika ada --}}
                    <td>-</td> {{-- Keterampilan, isi jika ada --}}
                </tr>
                @empty
                <tr>
                    <td colspan="7">Belum ada data nilai untuk periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <p class="small-text" style="margin-top: 5px;">*) Jika ada</p>
    </div>

    {{-- Tambahkan bagian Ekstrakurikuler, Absensi, Catatan Wali Kelas jika ada --}}

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                Orang Tua/Wali Murid
                <div class="label"></div>
                (................................)
            </td>
            <td></td>
            <td>
                {{-- Tempat, Tanggal Cetak --}}
                {{-- Misalnya: Bandung, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }} --}}
                Bandung, ............................ <br>
                Wali Kelas
                <div class="label"></div>
                <b>{{ $waliKelasPeriode ? $waliKelasPeriode->nama_guru : '(................................)' }}</b><br>
                {{-- NIP Wali Kelas jika ada --}}
                {{ $waliKelasPeriode && $waliKelasPeriode->nip ? 'NIP. ' . $waliKelasPeriode->nip : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="3" style="padding-top: 20px;">
                Mengetahui,<br>
                Kepala Sekolah
                <div class="label"></div>
                ( Nama Kepala Sekolah, S.Pd., M.Pd. )<br>
                NIP. .................................
            </td>
        </tr>
    </table>

</body>
</html>