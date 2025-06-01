<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Nilai Kelas - {{ $kelas->nama_kelas }} - {{ $mapel->nama_mapel }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; font-size: 9pt; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { margin: 0 0 5px 0; font-size: 14pt; }
        .header p { margin: 0; font-size: 9pt; }
        .info-rekap { margin-bottom: 10px; font-size: 9pt; }
        .info-rekap table { width: 100%; }
        .info-rekap td { padding: 2px 0;}
        .nilai-table table { width: 100%; border-collapse: collapse; }
        .nilai-table th, .nilai-table td { border: 1px solid #333; padding: 4px; text-align: center; }
        .nilai-table th { background-color: #f2f2f2; font-weight: bold; }
        .nilai-table td.nama { text-align: left; }
        .footer { margin-top: 30px; font-size: 8pt; }
        .footer .signature { float: right; width: 200px; text-align: center; }
        .footer .signature .label { margin-bottom: 40px; } /* Jarak untuk tanda tangan */
        .footer .date { float: left; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAPITULASI NILAI SISWA</h1>
        <p>{{'SMA KARTIKA XIX 1 BANDUNG' }}</p>
    </div>

    <div class="info-rekap">
        <table>
            <tr>
                <td style="width: 100px;">Kelas</td>
                <td>: {{ $kelas->nama_kelas }}</td>
                <td style="width: 100px;">Tahun Ajaran</td>
                <td>: {{ $tahunAjaran }}</td>
            </tr>
            <tr>
                <td>Mata Pelajaran</td>
                <td>: {{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '('.$mapel->kode_mapel.')' : '' }}</td>
                <td>Semester</td>
                <td>: {{ $semester }}</td>
            </tr>
            <tr>
                <td>Guru Pengampu</td>
                <td>: {{ $guru->nama_guru }}</td>
                <td>KKM</td>
                <td>: {{ $bobotAktif ? $bobotAktif->kkm : '-' }}</td>
            </tr>
        </table>
        @if($bobotAktif)
        <p style="font-size: 8pt; margin-top: 2px;">
            Bobot: 
            Tugas: {{ $bobotAktif->bobot_tugas }}% |
            UTS: {{ $bobotAktif->bobot_uts }}% |
            UAS: {{ $bobotAktif->bobot_uas }}%
        </p>
        @endif
    </div>

    <div class="nilai-table">
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">NIS</th>
                    <th style="width: 30%;">Nama Siswa</th>
                    <th style="width: 10%;">Rata2 Tugas</th>
                    <th style="width: 10%;">UTS</th>
                    <th style="width: 10%;">UAS</th>
                    <th style="width: 10%;">Nilai Akhir</th>
                    <th style="width: 10%;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($siswaList as $index => $siswa)
                    @php
                        $nilaiSiswa = $nilaiData->get($siswa->id);
                        $rataRataTugas = $nilaiSiswa ? \App\Models\Nilai::calculateRataRataTugas($nilaiSiswa->nilai_tugas) : null;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $siswa->nis }}</td>
                        <td class="nama">{{ $siswa->nama_siswa }}</td>
                        <td>
                            {{ !is_null($rataRataTugasPerSiswa[$siswa->id] ?? null) ? number_format($rataRataTugasPerSiswa[$siswa->id], 2) : '-' }}
                        </td>
                        <td>{{ $nilaiSiswa?->nilai_uts ?? '-' }}</td>
                        <td>{{ $nilaiSiswa?->nilai_uas ?? '-' }}</td>
                        <td><b>{{ !is_null($nilaiSiswa?->nilai_akhir) ? number_format($nilaiSiswa->nilai_akhir, 2) : '-' }}</b></td>
                        <td>{{ $nilaiSiswa?->predikat ?? '-' }}</td>
                    </tr>
                @empty
                <tr>
                    <td colspan="8">Tidak ada siswa di kelas ini atau belum ada nilai.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="date">
            Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
        </div>
        <div class="signature">
            Guru Mata Pelajaran,
            <div class="label"></div>
            <b>{{ $guru->nama_guru }}</b><br>
            {{ $guru->nip ? 'NIP. ' . $guru->nip : '' }}
        </div>
        <div style="clear:both;"></div>
    </div>

</body>
</html>