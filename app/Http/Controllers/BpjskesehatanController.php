<?php

namespace App\Http\Controllers;

use App\Models\Bpjskesehatan;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Imports\BpjskesehatanImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;

class BpjskesehatanController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $query = Bpjskesehatan::query();
        $query->join('karyawan', 'karyawan_bpjskesehatan.nik', '=', 'karyawan.nik');
        $query->select('karyawan_bpjskesehatan.*', 'karyawan.nama_karyawan', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.nik_show');
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
            $query->where('karyawan_bpjskesehatan.tanggal_berlaku', $request->tanggal);
        }

        $bpjskesehatan = $query->paginate(20);
        $bpjskesehatan->appends($request->all());
        $data['bpjskesehatan'] = $bpjskesehatan;
        $data['departemen'] = $user->getDepartemen();
        $data['cabang'] = $user->getCabang();
        return view('datamaster.bpjskesehatan.index', $data);
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
        return view('datamaster.bpjskesehatan.create', $data);
    }

    public function store(Request $request)
    {
        // Validasi dengan pesan error yang jelas
        $request->validate([
            'nik' => [
                'required',
                'exists:karyawan,nik'
            ],
            'jumlah' => [
                'required'
            ],
            'tanggal_berlaku' => [
                'required',
                'date'
            ]
        ], [
            'nik.required' => 'Karyawan wajib dipilih',
            'nik.exists' => 'Karyawan yang dipilih tidak valid',
            'jumlah.required' => 'BPJS Kesehatan wajib diisi',
            'tanggal_berlaku.required' => 'Tanggal Berlaku wajib diisi',
            'tanggal_berlaku.date' => 'Format Tanggal Berlaku tidak valid'
        ]);

        //Kode BPJS Kesehatan = K250001;
        $tahun = date('Y', strtotime($request->tanggal_berlaku));
        $last_bpjs_kesehatan = Bpjskesehatan::orderBy('kode_bpjs_kesehatan', 'desc')
            ->whereRaw('YEAR(tanggal_berlaku) = ' . $tahun)
            ->first();
        $last_kode_bpjs_kesehatan = $last_bpjs_kesehatan != null ? $last_bpjs_kesehatan->kode_bpjs_kesehatan : '';
        $kode_bpjs_kesehatan = buatkode($last_kode_bpjs_kesehatan, "K" . substr($tahun, 2, 2), 4);
        
        try {
            // Validasi jumlah setelah konversi
            $jumlah = toNumber($request->jumlah);
            if (!is_numeric($jumlah) || $jumlah < 0 || $jumlah > 999999999) {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('BPJS Kesehatan harus berupa angka antara 0 sampai 999.999.999'));
            }

            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $karyawan = Karyawan::where('nik', $request->nik)->firstOrFail();
                if (!in_array($karyawan->kode_cabang, $user->getCabangCodes()) || !in_array($karyawan->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Anda tidak memiliki akses ke karyawan ini.');
                }
            }

            Bpjskesehatan::create([
                'kode_bpjs_kesehatan' => $kode_bpjs_kesehatan,
                'nik' => $request->nik,
                'jumlah' => $jumlah,
                'tanggal_berlaku' => $request->tanggal_berlaku
            ]);

            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Illuminate\Database\QueryException $e) {
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
            return Redirect::back()
                ->withInput()
                ->with(messageError('Data Gagal Disimpan: ' . $e->getMessage()));
        }
    }


    public function edit($kode_bpjs_kesehatan)
    {
        $kode_bpjs_kesehatan = Crypt::decrypt($kode_bpjs_kesehatan);
        $user = auth()->user();
        $bpjskesehatan = Bpjskesehatan::where('kode_bpjs_kesehatan', $kode_bpjs_kesehatan)
            ->join('karyawan', 'karyawan_bpjskesehatan.nik', '=', 'karyawan.nik')
            ->select('karyawan_bpjskesehatan.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->firstOrFail();

        if (!$user->isSuperAdmin()) {
            if (!in_array($bpjskesehatan->kode_cabang, $user->getCabangCodes()) || !in_array($bpjskesehatan->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        $karyawanQuery = Karyawan::query();
        if (!$user->isSuperAdmin()) {
            $karyawanQuery->whereIn('kode_cabang', $user->getCabangCodes());
            $karyawanQuery->whereIn('kode_dept', $user->getDepartemenCodes());
        }
        $data['karyawan'] = $karyawanQuery->orderby('nama_karyawan')->get();
        $data['bpjskesehatan'] = $bpjskesehatan;
        return view('datamaster.bpjskesehatan.edit', $data);
    }

    public function update(Request $request, $kode_bpjs_kesehatan)
    {
        $kode_bpjs_kesehatan = Crypt::decrypt($kode_bpjs_kesehatan);
        
        // Validasi dengan pesan error yang jelas
        $request->validate([
            'jumlah' => [
                'required'
            ],
            'tanggal_berlaku' => [
                'required',
                'date'
            ]
        ], [
            'jumlah.required' => 'BPJS Kesehatan wajib diisi',
            'tanggal_berlaku.required' => 'Tanggal Berlaku wajib diisi',
            'tanggal_berlaku.date' => 'Format Tanggal Berlaku tidak valid'
        ]);
        
        try {
            $user = auth()->user();
            $bpjskesehatan = Bpjskesehatan::where('kode_bpjs_kesehatan', $kode_bpjs_kesehatan)
                ->join('karyawan', 'karyawan_bpjskesehatan.nik', '=', 'karyawan.nik')
                ->select('karyawan_bpjskesehatan.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
                ->firstOrFail();

            if (!$user->isSuperAdmin()) {
                if (!in_array($bpjskesehatan->kode_cabang, $user->getCabangCodes()) || !in_array($bpjskesehatan->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Anda tidak memiliki akses ke data ini.');
                }
            }

            // Validasi jumlah setelah konversi
            $jumlah = toNumber($request->jumlah);
            if (!is_numeric($jumlah) || $jumlah < 0 || $jumlah > 999999999) {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('BPJS Kesehatan harus berupa angka antara 0 sampai 999.999.999'));
            }

            Bpjskesehatan::where('kode_bpjs_kesehatan', $kode_bpjs_kesehatan)->update([
                'jumlah' => $jumlah,
                'tanggal_berlaku' => $request->tanggal_berlaku
            ]);
            return Redirect::back()->with(messageSuccess('Data Berhasil Diupdate'));
        } catch (\Illuminate\Database\QueryException $e) {
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
            return Redirect::back()
                ->withInput()
                ->with(messageError('Data Gagal Diupdate: ' . $e->getMessage()));
        }
    }

    public function destroy($kode_bpjs_kesehatan)
    {
        $kode_bpjs_kesehatan = Crypt::decrypt($kode_bpjs_kesehatan);
        $user = auth()->user();
        $bpjskesehatan = Bpjskesehatan::where('kode_bpjs_kesehatan', $kode_bpjs_kesehatan)
            ->join('karyawan', 'karyawan_bpjskesehatan.nik', '=', 'karyawan.nik')
            ->select('karyawan_bpjskesehatan.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->firstOrFail();

        if (!$user->isSuperAdmin()) {
            if (!in_array($bpjskesehatan->kode_cabang, $user->getCabangCodes()) || !in_array($bpjskesehatan->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        try {
            Bpjskesehatan::where('kode_bpjs_kesehatan', $kode_bpjs_kesehatan)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Dihapus ' . $e->getMessage()));
        }
    }

    public function import()
    {
        return view('datamaster.bpjskesehatan.import');
    }

    public function import_proses(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new BpjskesehatanImport, $request->file('file'));
            return Redirect::back()->with(messageSuccess('Data Berhasil Diimport'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Diimport: ' . $e->getMessage()));
        }
    }

    public function download_template()
    {
        $filename = 'template_bpjs_kesehatan.xlsx';
        $header = [
            'nik' => 'NIK',
            'nama_karyawan' => 'NAMA KARYAWAN',
            'jumlah' => 'JUMLAH',
            'tanggal_berlaku' => 'TANGGAL BERLAKU'
        ];

        $karyawan = Karyawan::limit(10)->get();
        $data = [];
        foreach ($karyawan as $k) {
            $data[] = [
                $k->nik,
                $k->nama_karyawan,
                '0',
                date('Y-m-d')
            ];
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
        $kode_bpjs_kesehatan = $request->kode_bpjs_kesehatan;
        if (empty($kode_bpjs_kesehatan)) {
            return Redirect::back()->with(messageError('Pilih data yang akan dihapus'));
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $unauthorized = Bpjskesehatan::whereIn('kode_bpjs_kesehatan', $kode_bpjs_kesehatan)
                ->join('karyawan', 'karyawan_bpjskesehatan.nik', '=', 'karyawan.nik')
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
            Bpjskesehatan::whereIn('kode_bpjs_kesehatan', $kode_bpjs_kesehatan)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Dihapus: ' . $e->getMessage()));
        }
    }
}
