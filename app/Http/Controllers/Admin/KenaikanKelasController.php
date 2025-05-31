<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KenaikanKelasController extends Controller
{
    /**
     * Menampilkan form untuk proses kenaikan kelas.
     */
    public function showForm(Request $request)
    {
        $tahunAjaranSaatIni = Setting::getValue('tahun_ajaran_aktif');
        $tahunAjaranSebelumnya = null;
        if ($tahunAjaranSaatIni) {
            $parts = explode('/', $tahunAjaranSaatIni);
            if (count($parts) === 2) {
                $tahunAwal = (int)$parts[0] - 1;
                $tahunAkhir = (int)$parts[1] - 1;
                $tahunAjaranSebelumnya = $tahunAwal . '/' . $tahunAkhir;
            }
        }

        // Ambil kelas dari tahun ajaran sebelumnya (jika ada)
        $kelasAsalOptions = $tahunAjaranSebelumnya ? Kelas::where('tahun_ajaran', $tahunAjaranSebelumnya)->orderBy('nama_kelas')->get() : collect([]);

        // Ambil kelas dari tahun ajaran saat ini (untuk tujuan kenaikan)
        $kelasTujuanOptions = $tahunAjaranSaatIni ? Kelas::where('tahun_ajaran', $tahunAjaranSaatIni)->orderBy('nama_kelas')->get() : collect([]);

        $selectedKelasAsalId = $request->input('kelas_asal_id');
        $siswaDiKelasAsal = collect([]);

        if ($selectedKelasAsalId) {
            $kelasAsal = Kelas::find($selectedKelasAsalId);
            if ($kelasAsal) {
                // Hanya ambil siswa yang masih aktif dari kelas asal
                $siswaDiKelasAsal = $kelasAsal->siswas()->where('status', Siswa::STATUS_AKTIF)->orderBy('nama_siswa')->get();
            }
        }

        return view('admin.kenaikan_kelas.form', compact(
            'tahunAjaranSaatIni',
            'tahunAjaranSebelumnya',
            'kelasAsalOptions',
            'kelasTujuanOptions',
            'selectedKelasAsalId',
            'siswaDiKelasAsal'
        ));
    }

    /**
     * Memproses kenaikan kelas siswa.
     */
    public function processPromotion(Request $request)
    {
        $request->validate([
            'tahun_ajaran_tujuan' => 'required|string',
            'promotions' => 'required|array',
            'promotions.*.siswa_id' => 'required|integer|exists:siswas,id',
            'promotions.*.aksi' => 'required|string|in:naik,tinggal,lulus',
            'promotions.*.kelas_tujuan_id' => 'nullable|required_if:promotions.*.aksi,naik|required_if:promotions.*.aksi,tinggal|integer|exists:kelas,id',
        ]);

        $tahunAjaranTujuan = $request->input('tahun_ajaran_tujuan');
        $promotions = $request->input('promotions');
        $berhasil = 0;
        $gagal = 0;

        DB::beginTransaction();
        try {
            foreach ($promotions as $data) {
                $siswa = Siswa::find($data['siswa_id']);
                if (!$siswa) {
                    $gagal++;
                    continue;
                }

                switch ($data['aksi']) {
                    case 'naik':
                    case 'tinggal':
                        $kelasTujuan = Kelas::where('id', $data['kelas_tujuan_id'])
                                           ->where('tahun_ajaran', $tahunAjaranTujuan)
                                           ->first();
                        if ($kelasTujuan) {
                            $siswa->kelas_id = $kelasTujuan->id;
                            $siswa->status = Siswa::STATUS_AKTIF; // Pastikan statusnya aktif
                            $siswa->save();
                            $berhasil++;
                        } else {
                            $gagal++; // Kelas tujuan tidak valid untuk tahun ajaran tujuan
                        }
                        break;
                    case 'lulus':
                        $siswa->status = Siswa::STATUS_LULUS;
                        // $siswa->kelas_id = null; // Opsional, atau biarkan kelas terakhir
                        $siswa->save();
                        // Nonaktifkan user login siswa jika perlu
                        // if ($siswa->user) {
                        //     $siswa->user->update(['is_active' => false]); // Atau hapus role
                        // }
                        $berhasil++;
                        break;
                    default:
                        $gagal++;
                        break;
                }
            }
            DB::commit();
            return redirect()->route('admin.kenaikan.form')
                             ->with('success', "Proses kenaikan kelas selesai. Berhasil: {$berhasil} siswa, Gagal/Lewati: {$gagal} siswa.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat proses kenaikan kelas: " . $e->getMessage());
            return redirect()->route('admin.kenaikan.form')
                             ->with('error', 'Terjadi kesalahan saat memproses kenaikan kelas: ' . $e->getMessage());
        }
    }
}