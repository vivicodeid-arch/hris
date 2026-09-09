<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Detailtunjangan;
use App\Models\Kontrak;
use App\Models\Pengaturanumum;
use App\Models\Userkaryawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KontrakController extends Controller
{
    /**
     * Get list of contracts for the authenticated employee.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $kontraks = Kontrak::select(
            'kontrak.*',
            'jabatan.nama_jabatan',
            'cabang.nama_cabang',
            'departemen.nama_dept'
        )
            ->leftJoin('jabatan', 'kontrak.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('cabang', 'kontrak.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'kontrak.kode_dept', '=', 'departemen.kode_dept')
            ->where('kontrak.nik', $userKaryawan->nik)
            ->orderByDesc('kontrak.tanggal')
            ->get();

        $data = $kontraks->map(function ($k) {
            return [
                'id' => $k->id,
                'no_kontrak' => $k->no_kontrak,
                'no_dokumen' => $k->no_dokumen,
                'dari' => $k->dari,
                'sampai' => $k->sampai,
                'status_kontrak' => (string) $k->status_kontrak,
                'tanggal' => $k->tanggal,
                'nama_jabatan' => $k->nama_jabatan,
                'nama_cabang' => $k->nama_cabang,
                'nama_dept' => $k->nama_dept,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get detail of a specific contract.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $kontrak = Kontrak::select(
            'kontrak.*',
            'karyawan.nama_karyawan',
            'karyawan.nik_show',
            'karyawan.tempat_lahir',
            'karyawan.tanggal_lahir',
            'karyawan.jenis_kelamin',
            'karyawan.alamat',
            'karyawan.no_ktp',
            'karyawan.pendidikan_terakhir',
            'karyawan.no_hp',
            'jabatan.nama_jabatan',
            'cabang.nama_cabang',
            'departemen.nama_dept',
            'gaji.jumlah as jumlah_gaji'
        )
            ->leftJoin('karyawan', 'kontrak.nik', '=', 'karyawan.nik')
            ->leftJoin('jabatan', 'kontrak.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('cabang', 'kontrak.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'kontrak.kode_dept', '=', 'departemen.kode_dept')
            ->leftJoin('karyawan_gaji_pokok as gaji', 'kontrak.kode_gaji', '=', 'gaji.kode_gaji')
            ->where('kontrak.id', $id)
            ->first();

        if (!$kontrak) {
            return response()->json([
                'success' => false,
                'message' => 'Kontrak tidak ditemukan'
            ], 404);
        }

        if ($kontrak->nik !== $userKaryawan->nik) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kontrak ini'
            ], 403);
        }

        $tunjanganItems = Detailtunjangan::select(
            'jenis_tunjangan.jenis_tunjangan as jenis',
            'karyawan_tunjangan_detail.jumlah'
        )
            ->join('jenis_tunjangan', 'karyawan_tunjangan_detail.kode_jenis_tunjangan', '=', 'jenis_tunjangan.kode_jenis_tunjangan')
            ->where('karyawan_tunjangan_detail.kode_tunjangan', $kontrak->kode_tunjangan)
            ->get();

        $pengaturan = Pengaturanumum::first();
        $konten = $this->prepareContractContent($kontrak, $tunjanganItems, $pengaturan);

        $tunjanganBreakdown = $tunjanganItems->map(function ($item) {
            return [
                'jenis' => $item->jenis,
                'jumlah' => (double) $item->jumlah,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $kontrak->id,
                'no_kontrak' => (string)($kontrak->no_kontrak ?? ''),
                'no_dokumen' => $kontrak->no_dokumen,
                'jenis_kontrak' => (string)($kontrak->jenis_kontrak ?? ''),
                'dari' => (string)($kontrak->dari ?? ''),
                'sampai' => (string)($kontrak->sampai ?? ''),
                'status_kontrak' => (string)($kontrak->status_kontrak ?? '0'),
                'tanggal' => (string)($kontrak->tanggal ?? ''),
                'nama_karyawan' => (string)($kontrak->nama_karyawan ?? ''),
                'nama_jabatan' => (string)($kontrak->nama_jabatan ?? '-'),
                'nama_cabang' => (string)($kontrak->nama_cabang ?? '-'),
                'nama_dept' => (string)($kontrak->nama_dept ?? '-'),
                'tempat_lahir' => (string)($kontrak->tempat_lahir ?? '-'),
                'tanggal_lahir' => $kontrak->tanggal_lahir ? Carbon::parse($kontrak->tanggal_lahir)->isoFormat('D MMMM Y') : '-',
                'jenis_kelamin' => $kontrak->jenis_kelamin == 'L' ? 'Laki-laki' : ($kontrak->jenis_kelamin == 'P' ? 'Perempuan' : '-'),
                'alamat_karyawan' => (string)($kontrak->alamat ?? '-'),
                'no_ktp' => (string)($kontrak->no_ktp ?? '-'),
                'no_hp' => (string)($kontrak->no_hp ?? '-'),
                'pendidikan_terakhir' => (string)($kontrak->pendidikan_terakhir ?? '-'),
                'nama_hrd' => (string)($pengaturan?->nama_hrd ?? 'Pihak Pertama'),
                'jabatan_hrd' => 'Owner ' . (string)($pengaturan?->nama_perusahaan ?? 'Perusahaan'),
                'nama_perusahaan' => (string)($pengaturan?->nama_perusahaan ?? '-'),
                'alamat_perusahaan' => (string)($pengaturan?->alamat ?? '-'),
                'gaji_pokok' => $kontrak->jumlah_gaji ? (double)$kontrak->jumlah_gaji : 0.0,
                'tunjangan' => $tunjanganBreakdown,
                'konten_html' => $konten,
            ]
        ]);
    }

    /**
     * Download contract PDF.
     */
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $kontrak = Kontrak::select(
            'kontrak.*',
            'karyawan.nama_karyawan',
            'karyawan.nik_show',
            'karyawan.tempat_lahir',
            'karyawan.tanggal_lahir',
            'karyawan.jenis_kelamin',
            'karyawan.alamat',
            'karyawan.no_ktp',
            'karyawan.pendidikan_terakhir',
            'karyawan.no_hp',
            'jabatan.nama_jabatan',
            'cabang.nama_cabang',
            'departemen.nama_dept',
            'gaji.jumlah as jumlah_gaji'
        )
            ->leftJoin('karyawan', 'kontrak.nik', '=', 'karyawan.nik')
            ->leftJoin('jabatan', 'kontrak.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('cabang', 'kontrak.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'kontrak.kode_dept', '=', 'departemen.kode_dept')
            ->leftJoin('karyawan_gaji_pokok as gaji', 'kontrak.kode_gaji', '=', 'gaji.kode_gaji')
            ->where('kontrak.id', $id)
            ->first();

        if (!$kontrak) {
            return response()->json([
                'success' => false,
                'message' => 'Kontrak tidak ditemukan'
            ], 404);
        }

        if ($kontrak->nik !== $userKaryawan->nik) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kontrak ini'
            ], 403);
        }

        $tunjanganItems = Detailtunjangan::select(
            'jenis_tunjangan.jenis_tunjangan as jenis',
            'karyawan_tunjangan_detail.jumlah'
        )
            ->join('jenis_tunjangan', 'karyawan_tunjangan_detail.kode_jenis_tunjangan', '=', 'jenis_tunjangan.kode_jenis_tunjangan')
            ->where('karyawan_tunjangan_detail.kode_tunjangan', $kontrak->kode_tunjangan)
            ->get();

        $setting = Pengaturanumum::first();
        $konten = $this->prepareContractContent($kontrak, $tunjanganItems, $setting);

        $pdf = Pdf::loadView('datamaster.kontrak.print_dynamic', [
            'konten' => $konten,
            'kontrak' => $kontrak,
            'setting' => $setting
        ])->setPaper('legal', 'portrait');

        $filename = 'kontrak-' . $kontrak->nik . '-' . $kontrak->no_kontrak . '.pdf';

        return $pdf->stream($filename);
    }

    protected function prepareContractContent($kontrak, $tunjanganItems, $setting)
    {
        // Get Template
        $kode_template = $kontrak->jenis_kontrak == 'T' ? 'PKWTT' : 'PKWT';
        $template = \App\Models\KonfigurasiDokumen::where('kode_dokumen', $kode_template)->first();
        if (!$template) {
             // Fallback to default if not configured
             $viewName = $kode_template == 'PKWTT' ? 'datamaster.kontrak.default_template_pkwtt' : 'datamaster.kontrak.default_template';
             $konten = view($viewName)->render();
        } else {
            $konten = $template->konten;
        }

        // Prepare Placeholders
        $totalTunjangan = $tunjanganItems->sum('jumlah');
        $totalGaji = ($kontrak->jumlah_gaji ?? 0) + $totalTunjangan;

        $placeholders = [
            '{{no_kontrak}}' => $kontrak->no_kontrak,
            '{{no_dokumen}}' => $kontrak->no_dokumen ?? '-',
            '{{hari_ini}}' => now()->isoFormat('dddd'),
            '{{tanggal_hari_ini}}' => now()->isoFormat('D MMMM Y'),
            '{{nama_hrd}}' => $setting->nama_hrd ?? 'Pihak Pertama',
            '{{jabatan_hrd}}' => 'Owner ' . ($setting->nama_perusahaan ?? 'Perusahaan'),
            '{{nama_perusahaan}}' => $setting->nama_perusahaan ?? 'Perusahaan',
            '{{alamat_perusahaan}}' => $setting->alamat ?? 'Lokasi Perusahaan',
            '{{nama_karyawan}}' => $kontrak->nama_karyawan,
            '{{tempat_lahir}}' => $kontrak->tempat_lahir ?? '-',
            '{{tanggal_lahir}}' => $kontrak->tanggal_lahir ? Carbon::parse($kontrak->tanggal_lahir)->isoFormat('D MMMM Y') : '-',
            '{{pendidikan_terakhir}}' => $kontrak->pendidikan_terakhir ?? '-',
            '{{jenis_kelamin}}' => $kontrak->jenis_kelamin == 'L' ? 'Laki-laki' : ($kontrak->jenis_kelamin == 'P' ? 'Perempuan' : ($kontrak->jenis_kelamin ?? '-')),
            '{{alamat_karyawan}}' => $kontrak->alamat ?? '-',
            '{{no_ktp}}' => $kontrak->no_ktp ?? '-',
            '{{no_hp}}' => $kontrak->no_hp ?? '-',
            '{{jabatan}}' => $kontrak->nama_jabatan,
            '{{cabang}}' => $kontrak->nama_cabang,
            '{{tanggal_mulai}}' => $kontrak->dari ? Carbon::parse($kontrak->dari)->isoFormat('D MMMM Y') : '-',
            '{{tanggal_selesai}}' => $kontrak->sampai ? Carbon::parse($kontrak->sampai)->isoFormat('D MMMM Y') : '-',
            '{{gaji_pokok}}' => 'Rp ' . number_format($kontrak->jumlah_gaji ?? 0, 0, ',', '.'),
            '{{total_gaji}}' => 'Rp ' . number_format($totalGaji, 0, ',', '.'),
        ];
        
        // Replace Tunjangan Loop
        $tunjanganHtml = '<table width="100%" style="border-collapse:collapse; margin:0; padding:0;">';
        if ($tunjanganItems->isNotEmpty()) {
            foreach ($tunjanganItems as $item) {
                if (($item->jumlah ?? 0) > 0) {
                    $tunjanganHtml .= '<tr><td class="label" style="width:55%; padding: 6px 10px; border:none;">'.$item->jenis.'</td><td class="value" style="text-align:right; padding: 6px 10px; border:none;">Rp '.number_format($item->jumlah ?? 0, 0, ',', '.').'</td></tr>';
                }
            }
        }
        $tunjanganHtml .= '</table>';
        $placeholders['{{tabel_tunjangan}}'] = $tunjanganHtml;

        // Perform Replacement
        foreach ($placeholders as $key => $value) {
            $konten = str_ireplace($key, $value, $konten);
        }

        return $konten;
    }
}
