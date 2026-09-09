<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Pengaturanumum;
use App\Models\Jamkerja;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Detailsetjamkerjabydept;
use App\Models\GlobalJamkerja;
use App\Models\GrupDetail;
use App\Models\GrupJamkerjaBydate;
use App\Models\MesinFingerprint;
use App\Models\LogMesinPresensi;
use App\Jobs\SendWaMessage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdmsController extends Controller
{
    /**
     * Menangkap dan mencatat semua request dari mesin Fingerprint ADMS (Fingerspot JSON Format)
     */
    /**
     * Backup method capture lama (sebagai antisipasi)
     */
    public function captureV1(Request $request, $any = null)
    {
        $requestCode = $request->header('request-code');
        if (empty($requestCode)) {
            $requestCode = $_SERVER['HTTP_REQUEST_CODE'] ?? $_SERVER['request-code'] ?? $_SERVER['REQUEST_CODE_RAW'] ?? $request->header('Request-Code', '');
        }

        $transId = $request->header('trans-id');
        if (empty($transId)) {
            $transId = $_SERVER['HTTP_TRANS_ID'] ?? $_SERVER['trans-id'] ?? $_SERVER['TRANS_ID_RAW'] ?? $request->header('Trans-Id', '');
        }

        // 2. Jika ini MURNI HANYA detak jantung (heartbeat / poll request) dari mesin
        $isHeartbeat = (strtolower($requestCode) === 'receive_cmd') ||
            (empty($request->getContent()) && $request->isMethod('GET'));

        // 3. Baca body request
        $rawBody = $request->getContent();
        Log::info('DATA RECEIVED V1', [
            'request-code' => $requestCode,
            'trans-id' => $transId,
            'content' => $request->getContent(),
        ]);

        // 4. Proses data jika bukan heartbeat
        if (!$isHeartbeat) {
            $devId = $request->header('dev-id');
            if (empty($devId)) {
                $devId = $_SERVER['HTTP_DEV_ID'] ?? $_SERVER['dev-id'] ?? $_SERVER['DEV_ID_RAW'] ?? $request->header('Dev-Id', '');
            }

            $mesin = MesinFingerprint::where('sn', $devId)->where('status', 'Aktif')->first();

            if (!$mesin) {
                return response("OK", 200)->header('response_code', 'OK');
            }

            $jsonStart = strpos($rawBody, '{');
            $jsonEnd = strrpos($rawBody, '}');
            $jsonData = [];
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($rawBody, $jsonStart, $jsonEnd - $jsonStart + 1);
                $jsonData = json_decode($jsonString, true) ?? [];
            }

            if (!empty($jsonData) && isset($jsonData['user_id']) && isset($jsonData['io_time'])) {
                $this->processAttendance($jsonData['user_id'], date('Y-m-d H:i:s'), $jsonData['io_mode'] ?? 0, $mesin);
            }
        }

        return response("OK", 200)->header('response_code', 'OK');
    }

    /**
     * Method baru: Langsung masukkan data tanpa validasi SN/IP yang ribet
     * (Asumsi: Menggunakan mesin aktif pertama jika SN tidak terbaca)
     */
    /**
     * Backup capture method dengan validasi SN wajib terdaftar
     */
    public function captureV2(Request $request, $any = null)
    {
        // 1. Identifikasi Serial Number Mesin (Multi-Format Support)
        $devId = $request->header('dev-id') ??
            $request->header('dev_id') ??
            $request->header('X-Dev-Id') ??
            $_SERVER['HTTP_DEV_ID'] ??
            $_SERVER['DEV_ID'] ??
            $request->query('sn') ??
            '';

        $rawBody = $request->getContent();
        Log::debug('FULL REQUEST', [
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'query' => request()->query(),
            'headers' => request()->headers->all(),
            'body' => request()->getContent(),
        ]);
        // 2. Parse JSON dari body
        $jsonStart = strpos($rawBody, '{');
        $jsonEnd = strrpos($rawBody, '}');
        $jsonData = [];
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($rawBody, $jsonStart, $jsonEnd - $jsonStart + 1);
            $jsonData = json_decode($jsonString, true) ?? [];
        }

        // 3. Cari Data Mesin di Database (Wajib Terdaftar)
        $mesin = MesinFingerprint::where('sn', $devId)->where('status', 'Aktif')->first();
        if (!$mesin) {
            Log::warning('Unregistered or inactive machine attempted to send data', [
                'sn' => $devId,
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);

            return response("OK", 200)
                ->header('Content-Type', 'application/octet-stream; charset=utf-8')
                ->header('response_code', 'OK')
                ->header('Connection', 'close');
        }

        // 4. Jika tidak ada isi JSON (Heartbeat Mentah)
        if (empty($jsonData)) {
            return response("OK", 200)
                ->header('Content-Type', 'application/octet-stream; charset=utf-8')
                ->header('response_code', 'OK')
                ->header('Connection', 'close');
        }

        try {
            // 5. Proses Data Absensi (Ada user_id dan io_time)
            if (isset($jsonData['user_id']) && isset($jsonData['io_time'])) {
                if ($mesin) {
                    // Format waktu: 2026-03-26 01:10:15
                    $io_time_str = $jsonData['io_time'];
                    $scan = (strlen($io_time_str) == 14)
                        ? substr($io_time_str, 0, 4) . '-' . substr($io_time_str, 4, 2) . '-' . substr($io_time_str, 6, 2) . ' ' . substr($io_time_str, 8, 2) . ':' . substr($io_time_str, 10, 2) . ':' . substr($io_time_str, 12, 2)
                        : date('Y-m-d H:i:s');

                    $io_mode = $jsonData['io_mode'] ?? 0;
                    $status = ($io_mode >= 16777216) ? ($io_mode / 16777216) - 1 : ($jsonData['status_scan'] ?? 0);

                    // Eksekusi Simpan Absensi
                    $this->processAttendance($jsonData['user_id'], $scan, $status, $mesin);
                }
            }
        } catch (\Exception $e) {
            Log::error('ADMS CAPTURE ERROR: ' . $e->getMessage());
        }

        return response("OK", 200)
            ->header('Content-Type', 'application/octet-stream; charset=utf-8')
            ->header('response_code', 'OK')
            ->header('Connection', 'close');
    }

    /**
     * Method baru: Langsung masukkan data tanpa validasi SN/IP yang ribet
     * (Asumsi: Menggunakan mesin aktif pertama jika SN tidak terbaca)
     */
    public function capture(Request $request, $any = null)
    {
        // 1. Identifikasi Serial Number Mesin (Multi-Format Support)
        $devId = $request->header('dev-id') ??
            $request->header('dev_id') ??
            $request->header('X-Dev-Id') ??
            $_SERVER['HTTP_DEV_ID'] ??
            $_SERVER['DEV_ID'] ??
            $request->query('sn') ??
            '';

        $rawBody = $request->getContent();
        Log::debug('FULL REQUEST', [
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'query' => request()->query(),
            'headers' => request()->headers->all(),
            'body' => request()->getContent(),
        ]);
        Log::debug('devid: ' . $devId);
        // 2. Parse JSON dari body
        $jsonStart = strpos($rawBody, '{');
        $jsonEnd = strrpos($rawBody, '}');
        $jsonData = [];
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($rawBody, $jsonStart, $jsonEnd - $jsonStart + 1);
            $jsonData = json_decode($jsonString, true) ?? [];
        }

        // 3. Cari Data Mesin di Database
        $mesin = MesinFingerprint::where('sn', $devId)->where('status', 'Aktif')->first();

        // Jika tidak ditemukan berdasarkan SN, gunakan mesin aktif pertama sebagai fallback
        if (!$mesin) {
            $mesin = MesinFingerprint::where('status', 'Aktif')->first();
        }

        // Jika tidak ada mesin aktif sama sekali di database
        if (!$mesin) {
            Log::warning('No active machine found in database to process data', [
                'sn' => $devId,
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);

            return response("OK", 200)
                ->header('Content-Type', 'application/octet-stream; charset=utf-8')
                ->header('response_code', 'OK')
                ->header('Connection', 'close');
        }

        // 4. Jika tidak ada isi JSON (Heartbeat Mentah)
        if (empty($jsonData)) {
            return response("OK", 200)
                ->header('Content-Type', 'application/octet-stream; charset=utf-8')
                ->header('response_code', 'OK')
                ->header('Connection', 'close');
        }

        try {
            // 5. Proses Data Absensi (Ada user_id dan io_time)
            if (isset($jsonData['user_id']) && isset($jsonData['io_time'])) {
                if ($mesin) {
                    // Format waktu: 2026-03-26 01:10:15
                    $io_time_str = $jsonData['io_time'];
                    $scan = (strlen($io_time_str) == 14)
                        ? substr($io_time_str, 0, 4) . '-' . substr($io_time_str, 4, 2) . '-' . substr($io_time_str, 6, 2) . ' ' . substr($io_time_str, 8, 2) . ':' . substr($io_time_str, 10, 2) . ':' . substr($io_time_str, 12, 2)
                        : date('Y-m-d H:i:s');

                    $io_mode = $jsonData['io_mode'] ?? 0;
                    $status = ($io_mode >= 16777216) ? ($io_mode / 16777216) - 1 : ($jsonData['status_scan'] ?? 0);

                    // Eksekusi Simpan Absensi
                    $this->processAttendance($jsonData['user_id'], $scan, $status, $mesin);
                }
            }
        } catch (\Exception $e) {
            Log::error('ADMS CAPTURE ERROR: ' . $e->getMessage());
        }

        $transId = $request->header('trans-id') ?? $request->header('trans_id') ?? 'undefined';
        $cmdCode = $request->header('cmd-code') ?? $request->header('cmd_code') ?? 'undefined';
        $blkNo = $request->header('blk-no') ?? $request->header('blk_no');
        $blkLen = $request->header('blk-len') ?? $request->header('blk_len');

        // Bypass Laravel/Symfony Response Header Normalization to preserve underscores
        header('Content-Type: application/octet-stream; charset=utf-8');
        header('response_code: OK');
        header('trans_id: ' . $transId);
        header('cmd_code: ' . $cmdCode);
        header('Connection: close');

        if ($blkNo !== null) {
            header('blk_no: ' . $blkNo);
        }
        if ($blkLen !== null) {
            header('blk_len: ' . $blkLen);
        }

        echo "OK";
        exit;
    }

    /**
     * Endpoint untuk format asli ADMS ZKTeco / Solution (X100C Plain Text ATTLOG Format)
     */
    public function receiveX100c(Request $request)
    {
        // X100C hardcoded ke timezone +8 (China) tanpa opsi ubah.
        // Kompensasi: kirim UTC-1jam agar mesin +8 = WIB (+7)
        // $dateForX100c = now()->format('D, d M Y H:i:s');

        $dateForX100c = now()->timezone('UTC')->subHour()->format('D, d M Y H:i:s') . ' GMT';

        // 1. Ambil SN dari query parameter jika ada, supaya bisa cari data mesin
        $devId = $request->query('SN', '');
        // 2. Jika method GET (Initialization handshake / heartbeat), balas OK
        if ($request->isMethod('GET')) {
            return response("OK\n", 200)
                ->header('Content-Type', 'text/plain')
                ->header('Date', $dateForX100c);

        }

        // 3. Jika POST (Data Push)
        $rawBody = $request->getContent();

        $mesin = MesinFingerprint::where('sn', $devId)->where('status', 'Aktif')->first();
        if (!$mesin) {
            Log::warning('Unregistered X100C machine attempted to send data', [
                'sn' => $devId,
                'ip' => $request->ip()
            ]);
            return response("OK\n", 200)
                ->header('Content-Type', 'text/plain')
                ->header('Date', $dateForX100c);
        }

        try {
            // Parse Plain Text ATTLOG format: PIN \t Time \t Status \t VerifyType \n
            $lines = explode("\n", $rawBody);
            foreach ($lines as $line) {
                $line = trim($line);

                // Abaikan jika baris kosong atau merupakan Operation Log (OPLOG)
                if (empty($line) || str_starts_with($line, 'OPLOG'))
                    continue;

                $parts = explode("\t", $line);
                if (count($parts) >= 3) {
                    $pin = $parts[0];
                    $scan = $parts[1];
                    $status = (int) $parts[2];

                    // Validasi format tanggal agar tidak menyebabkan error SQL
                    if (!strtotime($scan)) {
                        continue;
                    }

                    // Panggil core logic
                    $this->processAttendance($pin, $scan, $status, $mesin);
                }
            }
        } catch (\Exception $e) {
            Log::error('X100C DATA PROCESS ERROR: ' . $e->getMessage());
        }

        // Standard ADMS expect "OK" as response for success
        // Tetap balas OK agar mesin tidak mengirim data yang sama berulang kali
        return response("OK\n", 200)
            ->header('Content-Type', 'text/plain')
            ->header('Date', $dateForX100c);
    }

    /**
     * Endpoint ADMS ZKTeco STANDAR (untuk mesin baru: X904, dll)
     * Versi bersih TANPA kompensasi timezone.
     * Gunakan endpoint ini untuk mesin yang timezone-nya sudah benar / bisa diatur.
     */
    public function receiveZktecoStandard(Request $request)
    {
        // 1. Ambil SN dari query parameter
        $devId = $request->query('SN', '');

        // 2. Jika method GET (Initialization handshake / heartbeat), balas OK
        if ($request->isMethod('GET')) {
            return response("OK\n", 200)->header('Content-Type', 'text/plain');
        }

        // 3. Jika POST (Data Push)
        $rawBody = $request->getContent();

        $mesin = MesinFingerprint::where('sn', $devId)->where('status', 'Aktif')->first();
        if (!$mesin) {
            Log::warning('Unregistered ZKTeco machine attempted to send data', [
                'sn' => $devId,
                'ip' => $request->ip()
            ]);
            return response("OK\n", 200)->header('Content-Type', 'text/plain');
        }

        try {
            // Parse Plain Text ATTLOG format: PIN \t Time \t Status \t VerifyType \n
            $lines = explode("\n", $rawBody);
            foreach ($lines as $line) {
                $line = trim($line);

                // Abaikan jika baris kosong atau merupakan Operation Log (OPLOG)
                if (empty($line) || str_starts_with($line, 'OPLOG'))
                    continue;

                $parts = explode("\t", $line);
                if (count($parts) >= 3) {
                    $pin = $parts[0];
                    $scan = $parts[1];
                    $status = (int) $parts[2];

                    // Validasi format tanggal agar tidak menyebabkan error SQL
                    if (!strtotime($scan)) {
                        continue;
                    }

                    // Panggil core logic
                    $this->processAttendance($pin, $scan, $status, $mesin);
                }
            }
        } catch (\Exception $e) {
            Log::error('ZKTECO STANDARD DATA PROCESS ERROR: ' . $e->getMessage());
        }

        return response("OK\n", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Core logic untuk memproses dan menyimpan data absensi dari segala mesin
     */
    private function processAttendance($pin, $scan, $normalized_status, $mesin)
    {
        Log::info('MASUK KE PROCESS ATTENDANCE', ['pin' => $pin, 'scan' => $scan, 'normalized_status' => $normalized_status]);
        $karyawan = Karyawan::where('pin', $pin)->first();

        if ($karyawan != null) {
            Log::info('KARYAWAN DITEMUKAN', ['nik' => $karyawan->nik]);
            $generalsetting = Pengaturanumum::where('id', 1)->first();
            $tanggal_sekarang = date("Y-m-d", strtotime($scan));
            $jam_sekarang = date("H:i", strtotime($scan));
            $tanggal_kemarin = date("Y-m-d", strtotime("-1 days", strtotime($tanggal_sekarang)));
            $tanggal_besok = date("Y-m-d", strtotime("+1 days", strtotime($tanggal_sekarang)));

            //Cek Presensi Kemarin
            $presensi_kemarin = Presensi::where('nik', $karyawan->nik)
                ->join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('presensi.tanggal', $tanggal_kemarin)->first();

            $lintas_hari = $presensi_kemarin ? $presensi_kemarin->lintashari : 0;

            // Tentukan batas lintas hari: prioritas dari jam kerja kemarin, fallback ke general setting
            $batas_presensi_lintashari = ($presensi_kemarin && $presensi_kemarin->batas_presensi_pulang)
                ? $presensi_kemarin->batas_presensi_pulang
                : $generalsetting->batas_presensi_lintashari;

            // --- PENENTUAN TANGGAL PRESENSI ---
            // Secara default adalah hari ini
            $tanggal_presensi = $tanggal_sekarang;

            // HANYA jika kemarin lintas hari (atau non-lintas hari tetapi melakukan absen pulang) DAN belum absen pulang DAN belum melewati batas jam, maka dianggap absen kemarin
            if ($presensi_kemarin && $presensi_kemarin->jam_out == null) {
                if ($presensi_kemarin->lintashari == 1 || ($normalized_status % 2 != 0)) {
                    if ($jam_sekarang < $batas_presensi_lintashari) {
                        $tanggal_presensi = $tanggal_kemarin;
                    }
                }
            }


            $namahari = getnamaHari(date('D', strtotime($tanggal_presensi)));

            // 1) Cek Jam Kerja By Date (presensi_jamkerja_bydate)
            $jamkerja = Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('nik', $karyawan->nik)
                ->where('tanggal', $tanggal_presensi)
                ->first();

            // 1.5) Cek Approved Ajuan Jadwal
            if ($jamkerja == null) {
                $ajuan = DB::table('ajuan_jadwal')
                    ->join('presensi_jamkerja', 'ajuan_jadwal.kode_jam_kerja_tujuan', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->where('nik', $karyawan->nik)
                    ->where('tanggal', $tanggal_presensi)
                    ->where('status', 'a')
                    ->first();
                if ($ajuan) {
                    $jamkerja = $ajuan;
                }
            }

            if ($jamkerja == null) {
                $cek_group = GrupDetail::where('nik', $karyawan->nik)->first();
                if ($cek_group) {
                    $jamkerja = GrupJamkerjaBydate::where('kode_grup', $cek_group->kode_grup)
                        ->where('tanggal', $tanggal_presensi)
                        ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                        ->first();
                } else {
                    $jamkerja = null;
                }

                if ($jamkerja == null) {
                    $jamkerja = Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                        ->where('nik', $karyawan->nik)->where('hari', $namahari)->first();
                }

                if ($jamkerja == null) {
                    $jamkerja = Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                        ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                        ->where('kode_dept', $karyawan->kode_dept)
                        ->where('kode_cabang', $karyawan->kode_cabang)
                        ->where('hari', $namahari)->first();
                }

                // Fallback: Cek Jadwal Kerja Global
                if ($jamkerja == null) {
                    if ($generalsetting && $generalsetting->global_jamkerja_aktif) {
                        $globalJk = GlobalJamkerja::where('hari', $namahari)->first();
                        if ($globalJk && $globalJk->kode_jam_kerja) {
                            $jamkerja = Jamkerja::where('kode_jam_kerja', $globalJk->kode_jam_kerja)->first();
                        }
                    }
                }
            }

            Log::info('Status jamkerja', ['is_null' => $jamkerja == null]);

            if ($jamkerja != null) {
                $kode_jam_kerja = $jamkerja->kode_jam_kerja;
                Log::info('Jam kerja ditemukan', ['kode' => $kode_jam_kerja]);
                $jam_kerja = Jamkerja::where('kode_jam_kerja', $kode_jam_kerja)->first();

                $jam_presensi = $tanggal_sekarang . " " . $jam_sekarang;

                $presensi_hariini = Presensi::where('nik', $karyawan->nik)
                    ->where('tanggal', $tanggal_presensi)
                    ->first();

                Log::info('Presensi hari ini', ['is_null' => $presensi_hariini == null]);

                $is_even = ($normalized_status % 2 == 0);

                // LOGIC AUTO-PULANG (Jika tidak ada tombol keluar yang ditekan di mesin)
                // Jika status dari mesin adalah MASUK (is_even = true),
                // tapi karyawan sudah absen masuk hari ini...
                if ($is_even && $presensi_hariini != null && $presensi_hariini->jam_in != null) {
                    $jam_in_time = strtotime($presensi_hariini->jam_in);
                    $scan_time = strtotime($scan);

                    // Kalau scan berikutnya lebih dari 30 menit (1800 detik) sejak jam masuk pertama, 
                    // otomatis kita anggap ini sebagai absen PULANG.
                    if (($scan_time - $jam_in_time) > 1800) {
                        $is_even = false; // Override jadi absen pulang
                    } else {
                        // Kalau kurang dari 30 menit, ini cuma spam scan masuk. Abaikan saja supaya tidak error.
                        Log::info('Abaikan scan berulang (SPAM IN)', ['pin' => $pin, 'time' => $scan]);
                        $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Spam Scan IN (Abaikan scan berulang)');
                        return; // Selesai
                    }
                }

                // --- LOGIC BATAS JAM PRESENSI ---
                $batas_jam_absen = $generalsetting->batas_jam_absen * 60;
                $batas_jam_absen_pulang = $generalsetting->batas_jam_absen_pulang * 60;

                $jam_masuk_string = $tanggal_presensi . " " . $jam_kerja->jam_masuk;
                $jam_masuk_carbon = Carbon::parse($jam_masuk_string);
                $jam_mulai_masuk_carbon = $jam_masuk_carbon->copy()->subMinutes($batas_jam_absen);
                $jam_akhir_masuk_carbon = $jam_masuk_carbon->copy()->addMinutes($batas_jam_absen);

                if ($jam_akhir_masuk_carbon->format('H:i') >= '00:00' && $jam_akhir_masuk_carbon->day != $jam_masuk_carbon->day) {
                    $jam_akhir_masuk_carbon = Carbon::parse($jam_akhir_masuk_carbon->format('Y-m-d H:i'));
                }

                // --- PENENTUAN TANGGAL PULANG ---
                // Default berdasarkan shift hari ini
                $tanggal_pulang = $jam_kerja->lintashari == 1 ? $tanggal_besok : $tanggal_sekarang;
                $jam_kerja_pulang = $jam_kerja->jam_pulang;

                // Jika kemarin lintas hari DAN belum absen pulang DAN masih dalam batas, pakai jam pulang kemarin
                if ($presensi_kemarin && $presensi_kemarin->lintashari == 1 && $presensi_kemarin->jam_out == null) {
                    if ($jam_sekarang < $batas_presensi_lintashari) {
                        $tanggal_pulang = $tanggal_sekarang;
                        $jam_kerja_pulang = $presensi_kemarin->jam_pulang;
                    }
                }


                $jam_pulang_string = $tanggal_pulang . " " . $jam_kerja_pulang;
                $jam_pulang_carbon = Carbon::parse($jam_pulang_string);
                $jam_mulai_pulang_carbon = $jam_pulang_carbon->copy()->subMinutes($batas_jam_absen_pulang);

                $jam_presensi_carbon = Carbon::parse($jam_presensi);

                if ($generalsetting->batasi_absen == 1) {
                    if ($is_even) {
                        // Cek Masuk
                        if ($jam_presensi_carbon->lt($jam_mulai_masuk_carbon)) {
                            Log::info('Tolak Masuk: Terlalu pagi', ['pin' => $pin]);
                            $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Masuk ditolak: Belum waktunya absen masuk');
                            return;
                        }
                        if ($jam_presensi_carbon->gt($jam_akhir_masuk_carbon)) {
                            Log::info('Tolak Masuk: Lewat batas', ['pin' => $pin]);
                            $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Masuk ditolak: Waktu absen masuk sudah habis');
                            return;
                        }
                    } else {
                        // Cek Pulang
                        if ($jam_presensi_carbon->lt($jam_mulai_pulang_carbon)) {
                            Log::info('Tolak Pulang: Belum waktunya', ['pin' => $pin]);
                            $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Pulang ditolak: Belum waktunya absen pulang');
                            return;
                        }
                    }
                }
                // --- END LOGIC BATAS JAM PRESENSI ---

                Log::info('Mulai insert/update', ['is_even' => $is_even]);

                if ($is_even) {
                    // ABSEN MASUK
                    if ($presensi_hariini == null || $presensi_hariini->jam_in == null) {
                        Log::info('Mencoba simpan masuk');
                        try {
                            if ($presensi_hariini != null) {
                                Presensi::where('id', $presensi_hariini->id)->update([
                                    'jam_in' => $jam_presensi,
                                    'lokasi_in' => $mesin->titik_koordinat ?? 'Fingerprint ADMS',
                                    'id_mesin' => $mesin->id,
                                ]);
                                Log::info('Berhasil update masuk');
                                $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 1, 'Berhasil update absen masuk');
                            } else {
                                Presensi::create([
                                    'nik' => $karyawan->nik,
                                    'tanggal' => $tanggal_presensi,
                                    'jam_in' => $jam_presensi,
                                    'jam_out' => null,
                                    'lokasi_in' => $mesin->titik_koordinat ?? 'Fingerprint ADMS',
                                    'lokasi_out' => null,
                                    'foto_in' => null,
                                    'foto_out' => null,
                                    'id_mesin' => $mesin->id,
                                    'kode_jam_kerja' => $kode_jam_kerja,
                                    'status' => 'h'
                                ]);
                                Log::info('Berhasil create masuk');
                                $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 1, 'Berhasil buat absen masuk baru');
                            }

                            // Send WA
                            if ($karyawan->no_hp != null || $karyawan->no_hp != "" && $generalsetting->notifikasi_wa == 1) {
                                try {
                                    $message = "Terimakasih, Hari ini " . $karyawan->nama_karyawan . " absen masuk (Fingerprint) pada " . $jam_presensi . " Semangat Bekerja";
                                    dispatch(new SendWaMessage($karyawan->no_hp, $message));
                                    Log::info('WA masuk queued');
                                } catch (\Exception $waException) {
                                    Log::error('WA Error', ['nik' => $karyawan->nik, 'error' => $waException->getMessage()]);
                                }
                            }
                        } catch (\Throwable $e) { // Ubah jadi Throwable agar bisa tangkap Error juga
                            Log::error('Gagal simpan absen masuk ADMS', ['nik' => $karyawan->nik, 'error' => $e->getMessage()]);
                            $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Gagal simpan absen masuk: ' . $e->getMessage());
                        }
                    }
                } else {
                    // ABSEN PULANG
                    try {
                        if ($presensi_hariini != null) {
                            Presensi::where('id', $presensi_hariini->id)->update([
                                'jam_out' => $jam_presensi,
                                'lokasi_out' => $mesin->titik_koordinat ?? 'Fingerprint ADMS',
                                'id_mesin' => $mesin->id,
                            ]);
                            $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 1, 'Berhasil update absen pulang');
                        } else {
                            Presensi::create([
                                'nik' => $karyawan->nik,
                                'tanggal' => $tanggal_presensi,
                                'jam_in' => null,
                                'jam_out' => $jam_presensi,
                                'lokasi_in' => null,
                                'lokasi_out' => $mesin->titik_koordinat ?? 'Fingerprint ADMS',
                                'foto_in' => null,
                                'foto_out' => null,
                                'id_mesin' => $mesin->id,
                                'kode_jam_kerja' => $kode_jam_kerja,
                                'status' => 'h'
                            ]);
                            $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 1, 'Berhasil buat absen pulang baru');
                        }

                        // Send WA
                        if ($karyawan->no_hp != null || $karyawan->no_hp != "" && $generalsetting->notifikasi_wa == 1) {
                            // Kita hanya kirim notif pulang jika ini adalah update pertama jam out hari ini
                            if ($presensi_hariini == null || $presensi_hariini->jam_out == null) {
                                try {
                                    $message = "Terimakasih, Hari ini " . $karyawan->nama_karyawan . " absen Pulang (Fingerprint) pada " . $jam_presensi . " Hati-hati di Jalan";
                                    dispatch(new SendWaMessage($karyawan->no_hp, $message));
                                } catch (\Exception $waException) {
                                    Log::error('WA Error', ['nik' => $karyawan->nik, 'error' => $waException->getMessage()]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal simpan absen pulang ADMS', ['nik' => $karyawan->nik, 'error' => $e->getMessage()]);
                        $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Gagal simpan absen pulang: ' . $e->getMessage());
                    }
                }
            } else {
                $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Jam kerja karyawan tidak ditemukan');
            }
        } else {
            Log::info('Karyawan ADMS Fingerprint Tidak Ditemukan', ['pin' => $pin]);
            $this->recordLogMesin($pin, $scan, $normalized_status, $mesin ? $mesin->id : null, 0, 'Karyawan tidak ditemukan');
        }
    }

    private function recordLogMesin($pin, $scan, $status_scan, $id_mesin, $status, $keterangan)
    {
        try {
            LogMesinPresensi::create([
                'pin' => $pin,
                'status_scan' => $status_scan,
                'jam_absen' => $scan,
                'id_mesin' => $id_mesin,
                'status' => $status,
                'keterangan' => $keterangan,
            ]);
        } catch (\Exception $ex) {
            Log::error('Gagal mencatat log mesin presensi', ['error' => $ex->getMessage()]);
        }
    }

    /**
     * Method khusus untuk cek data mentah yang dikirim dari mesin fingerprint.
     * Tidak ada logic apapun, hanya log semua data mentah ke file terpisah.
     */
    public function rawDump(Request $request)
    {
        $data = [
            'time' => now()->toDateTimeString(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'headers_laravel' => $request->headers->all(),
            'apache_headers' => function_exists('apache_request_headers') ? apache_request_headers() : 'N/A',
            'server_vars' => $_SERVER,
            'query_string' => $request->query(),
            'body_raw' => $request->getContent(),
            'body_base64' => base64_encode($request->getContent()),
        ];

        // Parse body JSON jika ada
        $rawBody = $request->getContent();
        $jsonStart = strpos($rawBody, '{');
        $jsonEnd = strrpos($rawBody, '}');
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($rawBody, $jsonStart, $jsonEnd - $jsonStart + 1);
            $data['body_parsed_json'] = json_decode($jsonString, true);
        }

        // Log ke file terpisah supaya mudah dibaca
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/raw-dump.log'),
        ])->info('RAW DUMP FROM MACHINE', $data);

        // Juga log ke laravel.log biasa
        Log::info('RAW DUMP', $data);

        return response("OK", 200)
            ->header('Content-Type', 'application/octet-stream; charset=utf-8')
            ->header('response_code', 'OK')
            ->header('Connection', 'close');
    }
}
