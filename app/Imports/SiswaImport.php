<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithMappedCells; // Optional: Alternative to transformHeader
use Maatwebsite\Excel\Concerns\WithEvents; // Added for row index
use Maatwebsite\Excel\Concerns\RegistersEventListeners; // Added for row index
use Maatwebsite\Excel\Events\BeforeImport; // Added for row index

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithCustomCsvSettings, WithEvents
{
    use SkipsErrors, RegistersEventListeners;

    private $kelasId;
    private $importedCount = 0;
    private $skippedCount = 0;
    private $errorsEncountered = [];
    private $currentRow = 0; // Track current row number

    // Simpler keys expected from CSV after potential automatic slugification by WithHeadingRow
    // Maatwebsite/Excel often converts "No. Induk" to "no_induk" automatically.
    // Let's rely on this and then use prepareForValidation for type.
    private $expectedCsvHeaders = [
        'no_urut', // from "No. Urut"
        'no_induk', // from "No. Induk"
        'nama_peserta_didik', // from "Nama Peserta Didik"
        'l_p', // from "L/P" (Maatwebsite might convert '/' to '_')
    ];


    public function __construct(int $kelasId)
    {
        $this->kelasId = $kelasId;
    }

    public static function beforeImport(BeforeImport $event)
    {
        // Reset row counter before import starts
        $event->getConcernable()->currentRow = 0;
    }

    /**
     * Prepare the row for validation.
     *
     * @param array $row
     * @param int   $index
     * @return array
     */
    public function prepareForValidation(array $row, $index): array
    {
        $this->currentRow = $index + 1; // Update current row number (index is 0-based)

        // Maatwebsite/Excel by default converts headers to snake_case.
        // So, "No. Induk" becomes "no_induk", "Nama Peserta Didik" becomes "nama_peserta_didik",
        // and "L/P" might become "l_p" or similar.

        $preparedRow = [];
        // Manually map based on expected slugified keys or original keys if slugification is off
        // It's safer to check multiple possibilities for the header key due to auto-formatting
        $preparedRow['no_urut'] = $row['no_urut'] ?? $row['no urut'] ?? null;
        $preparedRow['no_induk'] = isset($row['no_induk']) ? (string) $row['no_induk'] : (isset($row['no induk']) ? (string) $row['no induk'] : null);
        $preparedRow['nama_peserta_didik'] = $row['nama_peserta_didik'] ?? $row['nama peserta didik'] ?? null;
        $preparedRow['lp'] = $row['l_p'] ?? $row['lp'] ?? $row['l/p'] ?? null; // Check for l_p, lp, or l/p

        // Ensure 'no_induk' is a string for validation
        if (isset($preparedRow['no_induk'])) {
            $preparedRow['no_induk'] = (string) $preparedRow['no_induk'];
        }

        return $preparedRow;
    }


    public function model(array $row)
    {
        // Data in $row here should be what's returned by prepareForValidation
        // Row index for error messages should be $this->currentRow + 1 (because header is row 1)
        $dataRowNumber = $this->currentRow + 1;

        if (!Kelas::find($this->kelasId)) {
            $this->errorsEncountered[] = "Baris data " . $dataRowNumber . ": Kelas dengan ID {$this->kelasId} tidak ditemukan.";
            $this->skippedCount++;
            return null;
        }

        $nis = $row['no_induk'] ?? null; // Already cast to string in prepareForValidation
        $namaSiswa = $row['nama_peserta_didik'] ?? null;
        $jenisKelaminCsv = isset($row['lp']) ? strtoupper(trim($row['lp'])) : null;

        if (empty($nis) || empty($namaSiswa)) {
            $this->errorsEncountered[] = "Baris data " . $dataRowNumber . ": No. Induk atau Nama Peserta Didik kosong. Lewati.";
            $this->skippedCount++;
            return null;
        }

        // Validasi apakah NIS sudah ada
        $existingSiswa = Siswa::where('nis', $nis)->first();
        if ($existingSiswa) {
            $this->errorsEncountered[] = "Baris data " . $dataRowNumber . ": NIS '{$nis}' sudah terdaftar untuk siswa '{$existingSiswa->nama_siswa}'. Lewati.";
            $this->skippedCount++;
            return null;
        }

        $jenisKelaminDb = null;
        if ($jenisKelaminCsv === 'L') {
            $jenisKelaminDb = 'L';
        } elseif ($jenisKelaminCsv === 'P') {
            $jenisKelaminDb = 'P';
        }

        DB::beginTransaction();
        try {
            $pseudoEmail = $nis . '@internal.siswa';
            $user = User::create([
                'name' => $namaSiswa,
                'email' => $pseudoEmail,
                'password' => Hash::make($nis),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('siswa');

            Siswa::create([
                'user_id' => $user->id,
                'nama_siswa' => $namaSiswa,
                'nis' => $nis,
                'kelas_id' => $this->kelasId,
                'jenis_kelamin' => $jenisKelaminDb,
            ]);

            DB::commit();
            $this->importedCount++;
            return null;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal mengimpor siswa NIS: {$nis}. Error: " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
            $this->errorsEncountered[] = "Baris data " . $dataRowNumber . " (NIS: {$nis}): Gagal diproses - " . $e->getMessage();
            $this->skippedCount++;
            return null;
        }
    }

    public function rules(): array
    {
        // These keys should match the keys used in prepareForValidation
        return [
            'no_induk' => 'required|string|max:20|unique:siswas,nis',
            'nama_peserta_didik' => 'required|string|max:100',
            'lp' => 'nullable|string|in:L,P,l,p',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'no_induk.required' => 'Kolom No. Induk wajib diisi.',
            'no_induk.unique' => 'No. Induk sudah terdaftar.',
            'no_induk.string' => 'No. Induk harus berupa teks/string.',
            'nama_peserta_didik.required' => 'Kolom Nama Peserta Didik wajib diisi.',
            'lp.in' => 'Kolom L/P hanya boleh diisi L atau P.',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'input_encoding' => 'UTF-8' // Tambahkan ini untuk memastikan encoding benar
        ];
    }

    // Tidak perlu headingRowFormatter atau transformHeader jika prepareForValidation digunakan untuk mapping
    // dan jika kita mengandalkan slugifikasi default dari WithHeadingRow.

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrorsEncountered(): array
    {
        return $this->errorsEncountered;
    }

    public function onError(\Throwable $e)
    {
        Log::error("Error selama proses impor CSV (onError): " . $e->getMessage());
        // Use the current row number for the error message
        $this->errorsEncountered[] = "Kesalahan umum pada baris data " . ($this->currentRow + 1) . ": " . $e->getMessage();
        $this->skippedCount++;
    }
}