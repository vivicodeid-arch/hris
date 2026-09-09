<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Gajipokok;
use App\Models\Karyawan;
use App\Imports\GajipokokImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class GajipokokController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $query = Gajipokok::query();
        $query->join('karyawan', 'karyawan_gaji_pokok.nik', '=', 'karyawan.nik');
        $query->select('karyawan_gaji_pokok.*', 'karyawan.nama_karyawan', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.nik_show');
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
            $query->where('karyawan_gaji_pokok.tanggal_berlaku', $request->tanggal);
        }

        $gajipokok = $query->paginate(20);
        $gajipokok->appends($request->all());
        $data['gajipokok'] = $gajipokok;
        $data['departemen'] = $user->getDepartemen();
        $data['cabang'] = $user->getCabang();
        return view('datamaster.gajipokok.index', $data);
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
        return view('datamaster.gajipokok.create', $data);
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
            ],
            'jenis_upah' => [
                'required'
            ]
        ], [
            'nik.required' => 'Karyawan wajib dipilih',
            'nik.exists' => 'Karyawan yang dipilih tidak valid',
            'jumlah.required' => 'Gaji Pokok wajib diisi',
            'tanggal_berlaku.required' => 'Tanggal Berlaku wajib diisi',
            'tanggal_berlaku.date' => 'Format Tanggal Berlaku tidak valid',
            'jenis_upah.required' => 'Jenis Upah wajib dipilih'
        ]);

        try {
            // Validasi jumlah setelah konversi
            $jumlah = toNumber($request->jumlah);
            if (!is_numeric($jumlah) || $jumlah < 1 || $jumlah > 999999999) {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('Gaji Pokok harus berupa angka antara 1 sampai 999.999.999'));
            }

            //Kode Gaji = G250001;
            $tahun_gaji = date('Y', strtotime($request->tanggal_berlaku));
            $last_gaji = Gajipokok::orderBy('kode_gaji', 'desc')
                ->whereRaw('YEAR(tanggal_berlaku) = ' . $tahun_gaji)
                ->first();
            $last_kode_gaji = $last_gaji != null ? $last_gaji->kode_gaji : '';
            $kode_gaji = buatkode($last_kode_gaji, "G" . substr($tahun_gaji, 2, 2), 4);
            
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                $karyawan = Karyawan::where('nik', $request->nik)->firstOrFail();
                if (!in_array($karyawan->kode_cabang, $user->getCabangCodes()) || !in_array($karyawan->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Anda tidak memiliki akses ke karyawan ini.');
                }
            }

            Gajipokok::create([
                'kode_gaji' => $kode_gaji,
                'nik' => $request->nik,
                'jumlah' => $jumlah,
                'jenis_upah' => $request->jenis_upah,
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

    public function edit($kode_gaji)
    {
        $kode_gaji = Crypt::decrypt($kode_gaji);
        $user = auth()->user();
        $gajipokok = Gajipokok::where('kode_gaji', $kode_gaji)
            ->join('karyawan', 'karyawan_gaji_pokok.nik', '=', 'karyawan.nik')
            ->select('karyawan_gaji_pokok.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->firstOrFail();

        if (!$user->isSuperAdmin()) {
            if (!in_array($gajipokok->kode_cabang, $user->getCabangCodes()) || !in_array($gajipokok->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        $karyawanQuery = Karyawan::query();
        if (!$user->isSuperAdmin()) {
            $karyawanQuery->whereIn('kode_cabang', $user->getCabangCodes());
            $karyawanQuery->whereIn('kode_dept', $user->getDepartemenCodes());
        }
        $data['karyawan'] = $karyawanQuery->orderby('nama_karyawan')->get();
        $data['gajipokok'] = $gajipokok;
        return view('datamaster.gajipokok.edit', $data);
    }

    public function update(Request $request, $kode_gaji)
    {
        $kode_gaji = Crypt::decrypt($kode_gaji);

        // Validasi dengan pesan error yang jelas
        $request->validate([
            'jumlah' => [
                'required'
            ],
            'tanggal_berlaku' => [
                'required',
                'date'
            ],
            'jenis_upah' => [
                'required'
            ]
        ], [
            'jumlah.required' => 'Gaji Pokok wajib diisi',
            'tanggal_berlaku.required' => 'Tanggal Berlaku wajib diisi',
            'tanggal_berlaku.date' => 'Format Tanggal Berlaku tidak valid',
            'jenis_upah.required' => 'Jenis Upah wajib dipilih'
        ]);

        try {
            $user = auth()->user();
            $gajipokok = Gajipokok::where('kode_gaji', $kode_gaji)
                ->join('karyawan', 'karyawan_gaji_pokok.nik', '=', 'karyawan.nik')
                ->select('karyawan_gaji_pokok.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
                ->firstOrFail();

            if (!$user->isSuperAdmin()) {
                if (!in_array($gajipokok->kode_cabang, $user->getCabangCodes()) || !in_array($gajipokok->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Anda tidak memiliki akses ke data ini.');
                }
            }

            // Validasi jumlah setelah konversi
            $jumlah = toNumber($request->jumlah);
            if (!is_numeric($jumlah) || $jumlah < 1 || $jumlah > 999999999) {
                return Redirect::back()
                    ->withInput()
                    ->with(messageError('Gaji Pokok harus berupa angka antara 1 sampai 999.999.999'));
            }

            Gajipokok::where('kode_gaji', $kode_gaji)->update([
                'jumlah' => $jumlah,
                'jenis_upah' => $request->jenis_upah,
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

    public function destroy($kode_gaji)
    {
        $kode_gaji = Crypt::decrypt($kode_gaji);
        $user = auth()->user();
        $gajipokok = Gajipokok::where('kode_gaji', $kode_gaji)
            ->join('karyawan', 'karyawan_gaji_pokok.nik', '=', 'karyawan.nik')
            ->select('karyawan_gaji_pokok.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->firstOrFail();

        if (!$user->isSuperAdmin()) {
            if (!in_array($gajipokok->kode_cabang, $user->getCabangCodes()) || !in_array($gajipokok->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        try {
            Gajipokok::where('kode_gaji', $kode_gaji)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Dihapus ' . $e->getMessage()));
        }
    }

    public function import()
    {
        return view('datamaster.gajipokok.import');
    }

    public function import_proses(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new GajipokokImport, $request->file('file'));
            return Redirect::back()->with(messageSuccess('Data Berhasil Diimport'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Diimport: ' . $e->getMessage()));
        }
    }

    public function download_template()
    {
        $filename = 'template_gaji_pokok.xlsx';
        $header = [
            'nik' => 'NIK',
            'nama_karyawan' => 'NAMA KARYAWAN',
            'jumlah' => 'JUMLAH',
            'jenis_upah' => 'JENIS UPAH',
            'tanggal_berlaku' => 'TANGGAL BERLAKU'
        ];

        // Fetch some sample data
        $karyawan = Karyawan::limit(10)->get();
        $data = [];
        foreach ($karyawan as $k) {
            $data[] = [
                $k->nik,
                $k->nama_karyawan,
                '0',
                'Bulanan',
                date('Y-m-d')
            ];
        }

        // Use a simple CSV style or Excel facade to create template
        // For simplicity, we can use the same approach as Karyawan download_template if available
        // or just use Excel::download with an anonymous class.

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
        $kode_gaji = $request->kode_gaji;
        if (empty($kode_gaji)) {
            return Redirect::back()->with(messageError('Pilih data yang akan dihapus'));
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $unauthorized = Gajipokok::whereIn('kode_gaji', $kode_gaji)
                ->join('karyawan', 'karyawan_gaji_pokok.nik', '=', 'karyawan.nik')
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
            Gajipokok::whereIn('kode_gaji', $kode_gaji)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data Gagal Dihapus: ' . $e->getMessage()));
        }
    }
}
