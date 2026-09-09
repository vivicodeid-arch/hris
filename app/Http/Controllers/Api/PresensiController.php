<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendWaMessage;
use App\Models\Detailsetjamkerjabydept;
use App\Models\GlobalJamkerja;
use App\Models\GrupDetail;
use App\Models\GrupJamkerjaBydate;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\LogAbsen;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PresensiController extends Controller
{
    public function store()
    {
        $original_data = file_get_contents('php://input');
        $decoded_data = json_decode($original_data, true);
        $encoded_data = json_encode($decoded_data);

        $data = $decoded_data['data'];
        $pin = $data['pin'];
        $status_scan = $data['status_scan'];
        $scan = $data['scan'];


        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $karyawan = Karyawan::where('pin', $pin)->first();

        if ($karyawan == null) {
            return response()->json([
                'status' => false,
                'message' => 'Karyawan Tidak Ditemukan',
            ]);
            $nik = "";
        }
        else {
            $nik = $karyawan->nik;
        }

        $tanggal_sekarang = date("Y-m-d", strtotime($scan));
        $jam_sekarang = date("H:i", strtotime($scan));
        $tanggal_kemarin = date("Y-m-d", strtotime($tanggal_sekarang . " -1 days"));
        $tanggal_besok = date("Y-m-d", strtotime($tanggal_sekarang . " +1 days"));

        //Cek Presensi Kemarin
        $presensi_kemarin = Presensi::where('nik', $karyawan->nik)
            ->join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_kemarin)->first();

        $lintas_hari = $presensi_kemarin ? $presensi_kemarin->lintashari : 0;

        // Tentukan batas lintas hari: prioritas dari jam kerja kemarin, fallback ke general setting
        $batas_presensi_lintashari = ($presensi_kemarin && $presensi_kemarin->batas_presensi_pulang)
            ? $presensi_kemarin->batas_presensi_pulang
            : $generalsetting->batas_presensi_lintashari;

        // Ambil Jam Kerja saat ini (fallback logic moved up to determine shift properties)
        $namahari = getnamaHari(date('D', strtotime($tanggal_sekarang)));
        $jamkerja = $this->getJamKerjaByDate($karyawan, $tanggal_sekarang, $namahari, $generalsetting);
        
        if ($jamkerja == null) {
            return response()->json([
                'status' => false,
                'message' => 'Jam Kerja Tidak Ditemukan',
            ]);
        }

        // --- PENENTUAN TANGGAL PRESENSI ---
        // Secara default adalah hari ini
        $tanggal_presensi = $tanggal_sekarang;
        $jam_kerja_pulang = $jamkerja->jam_pulang;
        $tanggal_pulang = $jamkerja->lintashari == 1 ? $tanggal_besok : $tanggal_sekarang;

        // HANYA jika kemarin lintas hari (atau non-lintas hari tetapi melakukan absen pulang) DAN belum absen pulang DAN belum melewati batas jam, maka dianggap absen kemarin
        if ($presensi_kemarin && $presensi_kemarin->jam_out == null) {
            if ($presensi_kemarin->lintashari == 1 || !in_array($status_scan, [0, 2, 4, 6, 8])) {
                if ($jam_sekarang < $batas_presensi_lintashari) {
                    $tanggal_presensi = $tanggal_kemarin;
                    $tanggal_pulang = $tanggal_sekarang;
                    $jam_kerja_pulang = $presensi_kemarin->jam_pulang;
                }
            }
        }


        // Re-assign namahari if tanggal_presensi changed (though usually not needed if only for logging)
        $namahari = getnamaHari(date('D', strtotime($tanggal_presensi)));

        //Cek Presensi
        $presensi = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggal_presensi)->first();

        if ($presensi != null && $presensi->status != 'h') {
            return response()->json([
                'status' => false,
                'message' => 'Presensi Sudah Ada',
            ]);
        }
        else if ($jamkerja == null) {
            return response()->json([
                'status' => false,
                'message' => 'Jam Kerja Tidak Ditemukan',
            ]);
        }

        $kode_jam_kerja = $jamkerja->kode_jam_kerja;
        $jam_kerja = Jamkerja::where('kode_jam_kerja', $kode_jam_kerja)->first();

        $jam_presensi = $tanggal_sekarang . " " . $jam_sekarang;

        $jam_masuk = $tanggal_presensi . " " . date('H:i', strtotime($jamkerja->jam_masuk));
        $jam_pulang = $tanggal_pulang . " " . date('H:i', strtotime($jam_kerja_pulang));

        $presensi_hariini = Presensi::where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_presensi)
            ->first();

        if (in_array($status_scan, [0, 2, 4, 6, 8])) {
            if ($presensi_hariini && $presensi_hariini->jam_in != null) {
                return response()->json(['status' => false, 'message' => 'Anda Sudah Absen Masuk Hari Ini', 'notifikasi' => 'notifikasi_sudahabsen'], 400);
            }
            else {
                try {
                    if ($presensi_hariini != null) {
                        Presensi::where('id', $presensi_hariini->id)->update([
                            'jam_in' => $jam_presensi,
                        ]);
                    }
                    else {
                        Presensi::create([
                            'nik' => $karyawan->nik,
                            'tanggal' => $tanggal_presensi,
                            'jam_in' => $jam_presensi,
                            'jam_out' => null,
                            'lokasi_out' => null,
                            'foto_out' => null,
                            'kode_jam_kerja' => $kode_jam_kerja,
                            'status' => 'h'
                        ]);
                    }
                    // Kirim Notifikasi Ke WA (dibungkus try-catch agar error WA tidak mempengaruhi response sukses)
                    if ($karyawan->no_hp != null || $karyawan->no_hp != "" && $generalsetting->notifikasi_wa == 1) {
                        try {
                            $message = "Terimakasih, Hari ini " . $karyawan->nama_karyawan . " absen masuk pada " . $jam_presensi . " Semagat Bekerja";
                            $this->sendwa($karyawan->no_hp, $message);
                        }
                        catch (\Exception $waException) {
                            // Log error pengiriman WA tapi tidak mempengaruhi response sukses
                            Log::error('Gagal mengirim notifikasi WA untuk absen masuk (API)', [
                                'nik' => $karyawan->nik,
                                'nama' => $karyawan->nama_karyawan,
                                'error' => $waException->getMessage(),
                                'trace' => $waException->getTraceAsString()
                            ]);
                        }
                    }

                    return response()->json(['status' => true, 'message' => 'Berhasil Absen Masuk', 'notifikasi' => 'notifikasi_absenmasuk'], 200);
                }
                catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
                }
            }
        }
        else {
            try {
                if ($presensi_hariini != null) {
                    Presensi::where('id', $presensi_hariini->id)->update([
                        'jam_out' => $jam_presensi,
                    ]);
                }
                else {
                    Presensi::create([
                        'nik' => $karyawan->nik,
                        'tanggal' => $tanggal_presensi,
                        'jam_in' => null,
                        'jam_out' => $jam_presensi,
                        'lokasi_in' => null,
                        'foto_in' => null,
                        'kode_jam_kerja' => $kode_jam_kerja,
                        'status' => 'h'
                    ]);
                }
                // Kirim Notifikasi Ke WA (dibungkus try-catch agar error WA tidak mempengaruhi response sukses)
                if ($karyawan->no_hp != null || $karyawan->no_hp != "" && $generalsetting->notifikasi_wa == 1) {
                    try {
                        $message = "Terimakasih, Hari ini " . $karyawan->nama_karyawan . " absen Pulang pada " . $jam_presensi . "Hati Hati di Jalan";
                        $this->sendwa($karyawan->no_hp, $message);
                    }
                    catch (\Exception $waException) {
                        // Log error pengiriman WA tapi tidak mempengaruhi response sukses
                        Log::error('Gagal mengirim notifikasi WA untuk absen pulang (API)', [
                            'nik' => $karyawan->nik,
                            'nama' => $karyawan->nama_karyawan,
                            'error' => $waException->getMessage(),
                            'trace' => $waException->getTraceAsString()
                        ]);
                    }
                }
                return response()->json(['status' => true, 'message' => 'Berhasil Absen Pulang', 'notifikasi' => 'notifikasi_absenpulang'], 200);
            }
            catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
            }
        }
    }


    private function getJamKerjaByDate($karyawan, $tanggal, $namahari, $generalsetting)
    {
        // 1) Cek Jam Kerja By Date
        $jamkerja = Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal)
            ->first();

        // 1.5) Cek Approved Ajuan Jadwal
        if ($jamkerja == null) {
            $ajuan = DB::table('ajuan_jadwal')
                ->join('presensi_jamkerja', 'ajuan_jadwal.kode_jam_kerja_tujuan', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('nik', $karyawan->nik)
                ->where('tanggal', $tanggal)
                ->where('status', 'a')
                ->first();
            if ($ajuan) {
                $jamkerja = $ajuan;
            }
        }

        // 2) Jam Kerja Grup
        if ($jamkerja == null) {
            $cek_group = GrupDetail::where('nik', $karyawan->nik)->first();
            if ($cek_group) {
                $jamkerja = GrupJamkerjaBydate::where('kode_grup', $cek_group->kode_grup)
                    ->where('tanggal', $tanggal)
                    ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->first();
            }

            // 3) Jam Kerja Harian / Perorangan
            if ($jamkerja == null) {
                $jamkerja = Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->where('nik', $karyawan->nik)->where('hari', $namahari)->first();
            }

            // 4) Jam Kerja Departemen
            if ($jamkerja == null) {
                $jamkerja = Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                    ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->where('kode_dept', $karyawan->kode_dept)
                    ->where('kode_cabang', $karyawan->kode_cabang)
                    ->where('hari', $namahari)->first();
            }

            // 5) Global Fallback
            if ($jamkerja == null) {
                if ($generalsetting && $generalsetting->global_jamkerja_aktif) {
                    $globalJk = GlobalJamkerja::where('hari', $namahari)->first();
                    if ($globalJk && $globalJk->kode_jam_kerja) {
                        $jamkerja = Jamkerja::where('kode_jam_kerja', $globalJk->kode_jam_kerja)->first();
                    }
                }
                if ($jamkerja == null) {
                    $jamkerja = Jamkerja::where('kode_jam_kerja', 'JK01')->first();
                }
            }
        }

        return $jamkerja;
    }

    function sendwa($no_hp, $message)
    {
        dispatch(new SendWaMessage($no_hp, $message));
    }

/**
 * Menerima data dari mesin Fingerspot REVO melalui ADMS
 * Data akan disimpan ke file txt untuk keperluan debugging dan logging
 * Response disesuaikan agar mesin tidak terus mengirim request
 */
// public function receiveRevoData(Request $request)
// {
//     try {
//         // Ambil raw data dari request
//         $rawData = file_get_contents('php://input');

//         // Ambil semua data dari request (termasuk form data dan JSON)
//         $requestData = $request->all();

//         // Buat hash dari raw data untuk mencegah duplikasi
//         $dataHash = md5($rawData . $request->ip() . microtime(true));
//         $cacheKey = 'revo_data_' . $dataHash;

//         // Cek apakah data ini sudah pernah diterima (dalam 5 detik terakhir)
//         if (Cache::has($cacheKey)) {
//             // Data duplikat, langsung return OK tanpa proses ulang
//             Log::info('Data REVO duplikat terdeteksi, skip processing', [
//                 'hash' => $dataHash,
//                 'ip' => $request->ip()
//             ]);

//             $responseText = 'OK';
//             return response($responseText, 200)
//                 ->header('Content-Type', 'text/plain')
//                 ->header('Content-Length', strlen($responseText))
//                 ->header('Connection', 'close');
//         }

//         // Set cache untuk 5 detik
//         Cache::put($cacheKey, true, 5);

//         // Buat timestamp untuk nama file
//         $timestamp = date('Y-m-d_H-i-s');
//         $dateFolder = date('Y-m-d');

//         // Buat folder berdasarkan tanggal jika belum ada
//         $folderPath = storage_path('app/public/revo_logs/' . $dateFolder);
//         if (!file_exists($folderPath)) {
//             mkdir($folderPath, 0755, true);
//         }

//         // Nama file dengan timestamp dan random string untuk menghindari duplikasi
//         $fileName = 'revo_' . $timestamp . '_' . uniqid() . '.txt';
//         $filePath = $folderPath . '/' . $fileName;

//         // Siapkan konten untuk disimpan
//         $content = "=== DATA REVO DARI ADMS ===\n";
//         $content .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
//         $content .= "IP Address: " . $request->ip() . "\n";
//         $content .= "User Agent: " . ($request->userAgent() ?? 'N/A') . "\n";
//         $content .= "Method: " . $request->method() . "\n";
//         $content .= "URL: " . $request->fullUrl() . "\n";
//         $content .= "Data Hash: " . $dataHash . "\n";
//         $content .= "\n--- RAW DATA (HEX) ---\n";
//         $content .= bin2hex($rawData) . "\n";
//         $content .= "\n--- RAW DATA (STRING) ---\n";
//         $content .= $rawData . "\n";
//         $content .= "\n--- PARSED DATA ---\n";
//         $content .= json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
//         $content .= "\n--- HEADERS ---\n";
//         $content .= json_encode($request->headers->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
//         $content .= "\n=== END OF DATA ===\n";

//         // Simpan ke file
//         file_put_contents($filePath, $content);

//         // Parse JSON dari raw data jika ada
//         $jsonData = null;
//         $parsedJson = null;
//         if (!empty($rawData)) {
//             // Coba extract JSON dari raw data (skip binary header jika ada)
//             $jsonStart = strpos($rawData, '{');
//             if ($jsonStart !== false) {
//                 $jsonString = substr($rawData, $jsonStart);
//                 $parsedJson = json_decode($jsonString, true);
//             }
//         }

//         // Log juga ke Laravel log untuk tracking
//         Log::info('Data REVO diterima dari ADMS', [
//             'file' => $fileName,
//             'ip' => $request->ip(),
//             'data_count' => count($requestData),
//             'raw_length' => strlen($rawData),
//             'hash' => $dataHash,
//             'request_code' => $request->header('request-code'),
//             'dev_id' => $request->header('dev-id'),
//             'trans_id' => $request->header('trans-id'),
//             'parsed_json' => $parsedJson
//         ]);

//         // Ambil header dari request
//         $requestCode = $request->header('request-code', '');
//         $devId = $request->header('dev-id', '');
//         $transId = $request->header('trans-id', '');
//         $contentType = $request->header('Content-Type', '');

//         // Response untuk realtime_glog - format binary/hex yang diharapkan ADMS
//         if ($requestCode === 'realtime_glog') {
//             // Response string "OK" dalam format binary/hex
//             // "OK" dalam hex = 0x4F 0x4B
//             $responseBinary = 'OK';

//             // Log response untuk debugging
//             Log::info('Response REVO realtime_glog', [
//                 'request_code' => $requestCode,
//                 'response_hex' => bin2hex($responseBinary),
//                 'response_string' => $responseBinary,
//                 'response_length' => strlen($responseBinary),
//                 'response_format' => 'ok_string_hex'
//             ]);

//             return response($responseBinary, 200)
//                 ->header('Content-Type', 'application/octet-stream')
//                 ->header('Content-Length', strlen($responseBinary))
//                 ->header('Connection', 'close');
//         }

//         // Response untuk receive_cmd - format binary/hex yang diharapkan ADMS
//         if ($requestCode === 'receive_cmd') {
//             // Response string "OK" dalam format binary/hex
//             // "OK" dalam hex = 0x4F 0x4B
//             $responseBinary = 'OK';

//             // Log response untuk debugging
//             Log::info('Response REVO receive_cmd', [
//                 'request_code' => $requestCode,
//                 'response_hex' => bin2hex($responseBinary),
//                 'response_string' => $responseBinary,
//                 'response_length' => strlen($responseBinary),
//                 'response_format' => 'ok_string_hex'
//             ]);

//             return response($responseBinary, 200)
//                 ->header('Content-Type', 'application/octet-stream')
//                 ->header('Content-Length', strlen($responseBinary))
//                 ->header('Connection', 'close');
//         }

//         // Jika content-type adalah application/octet-stream, return "OK" dalam hex
//         if ($contentType === 'application/octet-stream') {
//             // Response string "OK" dalam format binary/hex
//             $responseBinary = 'OK';

//             return response($responseBinary, 200)
//                 ->header('Content-Type', 'application/octet-stream')
//                 ->header('Content-Length', strlen($responseBinary))
//                 ->header('Connection', 'close');
//         }

//         // Default: Response "OK" dalam format binary/hex
//         $responseBinary = 'OK';

//         return response($responseBinary, 200)
//             ->header('Content-Type', 'application/octet-stream')
//             ->header('Content-Length', strlen($responseBinary))
//             ->header('Connection', 'close');
//     } catch (\Exception $e) {
//         // Log error
//         Log::error('Error menerima data REVO dari ADMS', [
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//             'ip' => $request->ip()
//         ]);

//         // Tetap return response sukses agar mesin tidak terus mengirim
//         // Format "OK" dalam hex sesuai protokol ADMS
//         $responseBinary = 'OK';

//         return response($responseBinary, 200)
//             ->header('Content-Type', 'application/octet-stream')
//             ->header('Content-Length', strlen($responseBinary))
//             ->header('Connection', 'close');
//     }
// }
}
