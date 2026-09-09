<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Detailtunjangan;
use App\Models\Jenistunjangan;
use App\Models\Karyawan;
use App\Models\Tunjangan;
use App\Imports\TunjanganImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class TunjanganController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $jenis_tunjangan = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        $select_tunjangan = [];
        foreach ($jenis_tunjangan as $d) {
            $select_tunjangan[] = DB::raw('SUM(IF(karyawan_tunjangan_detail.kode_jenis_tunjangan = "' . $d->kode_jenis_tunjangan . '", karyawan_tunjangan_detail.jumlah, 0)) as jumlah_' . $d->kode_jenis_tunjangan);
        }
        $query = Detailtunjangan::query();
        $query->join('karyawan_tunjangan', 'karyawan_tunjangan_detail.kode_tunjangan', '=', 'karyawan_tunjangan.kode_tunjangan');
        $query->join('karyawan', 'karyawan_tunjangan.nik', '=', 'karyawan.nik');
        $query->select(
            'karyawan_tunjangan_detail.kode_tunjangan',
            'karyawan_tunjangan.nik',
            'karyawan.nama_karyawan',
            'karyawan.kode_dept',
            'karyawan.kode_cabang',
            'karyawan.nik_show',
            'tanggal_berlaku',
            ...$select_tunjangan
        );
        if (!empty($request->nama_karyawan)) {
            $query->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }

        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!empty($request->kode_cabang)) {
                if (in_array($request->kode_cabang, $userCabangs)) {
                    $query->where('karyawan.kode_cabang', $request->kode_cabang);
                } else {
                    $query->where('karyawan.kode_cabang', 'INVALID');
                }
            } else {
                $query->whereIn('karyawan.kode_cabang', $userCabangs);
            }

            if (!empty($request->kode_dept)) {
                if (in_array($request->kode_dept, $userDepartemens)) {
                    $query->where('karyawan.kode_dept', $request->kode_dept);
                } else {
                    $query->where('karyawan.kode_dept', 'INVALID');
                }
            } else {
                $query->whereIn('karyawan.kode_dept', $userDepartemens);
            }
        } else {
            if (!empty($request->kode_cabang)) {
                $query->where('karyawan.kode_cabang', $request->kode_cabang);
            }
            if (!empty($request->kode_dept)) {
                $query->where('karyawan.kode_dept', $request->kode_dept);
            }
        }

        if (!empty($request->tanggal)) {
            $query->where('karyawan_tunjangan.tanggal_berlaku', $request->tanggal);
        }

        $query->groupBy(
            'karyawan_tunjangan_detail.kode_tunjangan',
            'karyawan_tunjangan.nik',
            'karyawan.nama_karyawan',
            'karyawan.kode_dept',
            'karyawan.kode_cabang',
            'karyawan.nik_show',
            'tanggal_berlaku'
        );
        $tunjangan = $query->paginate(20);
        $tunjangan->appends($request->all());
        $data['tunjangan'] = $tunjangan;
        $data['departemen'] = $user->getDepartemen();
        $data['cabang'] = $user->getCabang();
        $data['jenis_tunjangan'] = $jenis_tunjangan;
        return view('datamaster.tunjangan.index', $data);
    }


    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $karyawanQuery = Karyawan::query();
        if (!$user->isSuperAdmin()) {
            $karyawanQuery->whereIn('kode_cabang', $user->getCabangCodes());
            $karyawanQuery->whereIn('kode_dept', $user->getDepartemenCodes());
        }
        $data['karyawan'] = $karyawanQuery->orderby('nama_karyawan')->get();
        $data['jenis_tunjangan'] = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        return view('datamaster.tunjangan.create', $data);
    }


    public function edit($kode_tunjangan)
    {
        $kode_tunjangan = Crypt::decrypt($kode_tunjangan);
        $user = auth()->user();
        $tunjangan = Tunjangan::where('kode_tunjangan', $kode_tunjangan)
            ->join('karyawan', 'karyawan_tunjangan.nik', '=', 'karyawan.nik')
            ->select('karyawan_tunjangan.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->firstOrFail();

        if (!$user->isSuperAdmin()) {
            if (!in_array($tunjangan->kode_cabang, $user->getCabangCodes()) || !in_array($tunjangan->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        $karyawanQuery = Karyawan::query();
        if (!$user->isSuperAdmin()) {
            $karyawanQuery->whereIn('kode_cabang', $user->getCabangCodes());
            $karyawanQuery->whereIn('kode_dept', $user->getDepartemenCodes());
        }
        $data['karyawan'] = $karyawanQuery->orderby('nama_karyawan')->get();
        $data['tunjangan'] = $tunjangan;
        $detail_tunjangan = Jenistunjangan::where('kode_tunjangan', $kode_tunjangan)
            ->leftJoin('karyawan_tunjangan_detail', 'karyawan_tunjangan_detail.kode_jenis_tunjangan', '=', 'jenis_tunjangan.kode_jenis_tunjangan')
            ->orderBy('jenis_tunjangan.kode_jenis_tunjangan')
            ->get();
        $data['jenis_tunjangan'] = $detail_tunjangan;
        return view('datamaster.tunjangan.edit', $data);
    }


    public function store(Request $request)
    {
        // Validasi dengan pesan error yang jelas
        $request->validate([
            'nik' => [
                'required',
                'exists:karyawan,nik'
            ],
            'kode_jenis_tunjangan' => [
                'required',
                'array'
            ],
            'kode_jenis_tunjangan.*' => [
                'required',
                'exists:jenis_tunjangan,kode_jenis_tunjangan'
            ],
            'jumlah' => [
                'required',
                'array'
            ],
            'jumlah.*' => [
                'required'
            ],
            'tanggal_berlaku' => [
                'required',
                'date'
            ]
        ], [
            'nik.required' => 'Karyawan wajib dipilih',
            'nik.exists' => 'Karyawan yang dipilih tidak valid',
            'kode_jenis_tunjangan.required' => 'Jenis Tunjangan wajib dipilih',
            'kode_jenis_tunjangan.array' => 'Format Jenis Tunjangan tidak valid',
            'kode_jenis_tunjangan.*.required' => 'Semua Jenis Tunjangan wajib dipilih',
            'kode_jenis_tunjangan.*.exists' => 'Jenis Tunjangan yang dipilih tidak valid',
            'jumlah.required' => 'Jumlah Tunjangan wajib diisi',
            'jumlah.array' => 'Format Jumlah Tunjangan tidak valid',
            'jumlah.*.required' => 'Semua Jumlah Tunjangan wajib diisi',
            'tanggal_berlaku.required' => 'Tanggal Berlaku wajib diisi',
            'tanggal_berlaku.date' => 'Format Tanggal Berlaku tidak valid'
        ]);

        //Kode Tunjangan = T250001;
        $tahun_gaji = date('Y', strtotime($request->tanggal_berlaku));
        $last_tunjangan = Tunjangan::orderBy('kode_tunjangan', 'desc')
            ->whereRaw('YEAR(tanggal_berlaku) = ' . $tahun_gaji)
            ->first();
        $last_kode_tunjangan = $last_tunjangan != null ? $last_tunjangan->kode_tunjangan : '';
        $kode_tunjangan = buatkode($last_kode_tunjangan, "T" . substr($tahun_gaji, 2, 2), 4);
        
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $karyawan = Karyawan::where('nik', $request->nik)->firstOrFail();
            if (!in_array($karyawan->kode_cabang, $user->getCabangCodes()) || !in_array($karyawan->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke karyawan ini.');
            }
        }

        DB::beginTransaction();
        try {
            // Validasi jumlah setelah konversi
            foreach ($request->jumlah as $key => $jumlahValue) {
                $jumlah = toNumber($jumlahValue);
                if (!is_numeric($jumlah) || $jumlah < 0 || $jumlah > 999999999) {
                    DB::rollBack();
                    return Redirect::back()
                        ->withInput()
                        ->with(messageError('Jumlah Tunjangan harus berupa angka antara 0 sampai 999.999.999'));
                }
            }

            Tunjangan::create([
                'kode_tunjangan' => $kode_tunjangan,
                'nik' => $request->nik,
                'tanggal_berlaku' => $request->tanggal_berlaku
            ]);

            foreach ($request->kode_jenis_tunjangan as $key => $value) {
                Detailtunjangan::create([
                    'kode_tunjangan' => $kode_tunjangan,
                    'kode_jenis_tunjangan' => $value,
                    'jumlah' => toNumber($request->jumlah[$key]),
                ]);
            }
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Tangani error database khusus
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'Data too long')) {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('Data yang dimasukkan terlalu panjang'));
            } else {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('Terjadi kesalahan: ' . $errorMessage));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with(messageError('Data Gagal Disimpan: ' . $e->getMessage()));
        }
    }


    public function update($kode_tunjangan, Request $request)
    {
        $kode_tunjangan = Crypt::decrypt($kode_tunjangan);

        // Validasi dengan pesan error yang jelas
        $request->validate([
            'kode_jenis_tunjangan' => [
                'required',
                'array'
            ],
            'kode_jenis_tunjangan.*' => [
                'required',
                'exists:jenis_tunjangan,kode_jenis_tunjangan'
            ],
            'jumlah' => [
                'required',
                'array'
            ],
            'jumlah.*' => [
                'required'
            ],
            'tanggal_berlaku' => [
                'required',
                'date'
            ]
        ], [
            'kode_jenis_tunjangan.required' => 'Jenis Tunjangan wajib dipilih',
            'kode_jenis_tunjangan.array' => 'Format Jenis Tunjangan tidak valid',
            'kode_jenis_tunjangan.*.required' => 'Semua Jenis Tunjangan wajib dipilih',
            'kode_jenis_tunjangan.*.exists' => 'Jenis Tunjangan yang dipilih tidak valid',
            'jumlah.required' => 'Jumlah Tunjangan wajib diisi',
            'jumlah.array' => 'Format Jumlah Tunjangan tidak valid',
            'jumlah.*.required' => 'Semua Jumlah Tunjangan wajib diisi',
            'tanggal_berlaku.required' => 'Tanggal Berlaku wajib diisi',
            'tanggal_berlaku.date' => 'Format Tanggal Berlaku tidak valid'
        ]);

        $user = auth()->user();
        $tunjangan = Tunjangan::where('kode_tunjangan', $kode_tunjangan)
            ->join('karyawan', 'karyawan_tunjangan.nik', '=', 'karyawan.nik')
            ->select('karyawan_tunjangan.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->firstOrFail();

        if (!$user->isSuperAdmin()) {
            if (!in_array($tunjangan->kode_cabang, $user->getCabangCodes()) || !in_array($tunjangan->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        DB::beginTransaction();
        try {
            // Validasi jumlah setelah konversi
            foreach ($request->jumlah as $key => $jumlahValue) {
                $jumlah = toNumber($jumlahValue);
                if (!is_numeric($jumlah) || $jumlah < 0 || $jumlah > 999999999) {
                    DB::rollBack();
                    return Redirect::back()
                        ->withInput()
                        ->with(messageError('Jumlah Tunjangan harus berupa angka antara 0 sampai 999.999.999'));
                }
            }

            Tunjangan::where('kode_tunjangan', $kode_tunjangan)->update([
                'tanggal_berlaku' => $request->tanggal_berlaku
            ]);

            Detailtunjangan::where('kode_tunjangan', $kode_tunjangan)->delete();
            foreach ($request->kode_jenis_tunjangan as $key => $value) {
                Detailtunjangan::create([
                    'kode_tunjangan' => $kode_tunjangan,
                    'kode_jenis_tunjangan' => $value,
                    'jumlah' => toNumber($request->jumlah[$key]),
                ]);
            }
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Diupdate'));
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Tangani error database khusus
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'Data too long')) {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('Data yang dimasukkan terlalu panjang'));
            } else {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('Terjadi kesalahan: ' . $errorMessage));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with(messageError('Data Gagal Diupdate: ' . $e->getMessage()));
        }
    }


    public function destroy($kode_tunjangan)
    {
        $kode_tunjangan = Crypt::decrypt($kode_tunjangan);
        $user = auth()->user();
        $tunjangan = Tunjangan::where('kode_tunjangan', $kode_tunjangan)
            ->join('karyawan', 'karyawan_tunjangan.nik', '=', 'karyawan.nik')
            ->select('karyawan_tunjangan.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->firstOrFail();

        if (!$user->isSuperAdmin()) {
            if (!in_array($tunjangan->kode_cabang, $user->getCabangCodes()) || !in_array($tunjangan->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        try {
            Tunjangan::where('kode_tunjangan', $kode_tunjangan)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Dihapus ' . $e->getMessage()));
        }
    }

    public function import()
    {
        return view('datamaster.tunjangan.import');
    }

    public function import_proses(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new TunjanganImport, $request->file('file'));
            return Redirect::back()->with(messageSuccess('Data Berhasil Diimport'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Diimport: ' . $e->getMessage()));
        }
    }

    public function download_template()
    {
        $filename = 'template_tunjangan.xlsx';
        $jenis_tunjangan = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        
        $header = [
            'nik' => 'NIK',
            'nama_karyawan' => 'NAMA KARYAWAN',
            'tanggal_berlaku' => 'TANGGAL BERLAKU'
        ];

        foreach ($jenis_tunjangan as $jt) {
            $header[$jt->kode_jenis_tunjangan] = $jt->jenis_tunjangan;
        }

        $karyawan = Karyawan::limit(10)->get();
        $data = [];
        foreach ($karyawan as $k) {
            $row = [
                $k->nik,
                $k->nama_karyawan,
                date('Y-m-d')
            ];
            foreach ($jenis_tunjangan as $jt) {
                $row[] = '0';
            }
            $data[] = $row;
        }

        return Excel::download(new class($header, $data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $header;
            private $data;
            public function __construct($header, $data) {
                $this->header = $header;
                $this->data = $data;
            }
            public function collection() {
                return collect($this->data);
            }
            public function headings(): array {
                return array_values($this->header);
            }
        }, $filename);
    }

    public function delete_multiple(Request $request)
    {
        $kode_tunjangan = $request->kode_tunjangan;
        if (empty($kode_tunjangan)) {
            return Redirect::back()->with(messageError('Pilih data yang akan dihapus'));
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $unauthorized = Tunjangan::whereIn('kode_tunjangan', $kode_tunjangan)
                ->join('karyawan', 'karyawan_tunjangan.nik', '=', 'karyawan.nik')
                ->where(function($q) use ($user) {
                    $q->whereNotIn('karyawan.kode_cabang', $user->getCabangCodes())
                      ->orWhereNotIn('karyawan.kode_dept', $user->getDepartemenCodes());
                })
                ->exists();

            if ($unauthorized) {
                abort(403, 'Anda tidak memiliki akses untuk menghapus beberapa data ini.');
            }
        }

        try {
            Tunjangan::whereIn('kode_tunjangan', $kode_tunjangan)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Dihapus: ' . $e->getMessage()));
        }
    }
}
