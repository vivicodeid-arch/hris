<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Facerecognition;
use App\Models\Karyawan;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FacerecognitionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $wajahList = Facerecognition::where('nik', $userkaryawan->nik)->get();

        $nama_folder = $karyawan->nik . "-" . getNamaDepan(strtolower($karyawan->nama_karyawan));
        
        $formattedWajahList = $wajahList->map(function ($wajah) use ($nama_folder) {
            $filePath = 'public/uploads/facerecognition/' . $nama_folder . '/' . $wajah->wajah;
            $exists = Storage::exists($filePath);
            
            return [
                'id' => $wajah->id,
                'nik' => $wajah->nik,
                'wajah' => $wajah->wajah,
                'url' => $exists ? url('/storage/uploads/facerecognition/' . $nama_folder . '/' . $wajah->wajah) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'registered' => $wajahList->count() > 0,
            'wajah_list' => $formattedWajahList
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = auth()->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $nama_folder = $karyawan->nik . "-" . getNamaDepan(strtolower($karyawan->nama_karyawan));
        $folderPath = "public/uploads/facerecognition/" . $nama_folder . "/";

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath, 0775, true);
            Storage::setVisibility($folderPath, 'public');
        }

        try {
            $saved = [];
            $cekWajah = Facerecognition::where('nik', $userkaryawan->nik)->count();
            $urutan = $cekWajah + 1;

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $index => $file) {
                    $direction = $request->input("directions.$index", 'front');
                    $fileName = $urutan . "_" . $direction . ".png";
                    
                    $file->storeAs($folderPath, $fileName);

                    Facerecognition::create([
                        'nik' => $userkaryawan->nik,
                        'wajah' => $fileName
                    ]);

                    $saved[] = $fileName;
                    $urutan++;
                }

                return response()->json([
                    'success' => true,
                    'message' => count($saved) . ' gambar berhasil disimpan',
                    'files' => $saved
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada gambar yang dikirim'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan wajah: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy()
    {
        $user = auth()->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $nama_folder = $karyawan->nik . "-" . getNamaDepan(strtolower($karyawan->nama_karyawan));
        $folderPath = 'public/uploads/facerecognition/' . $nama_folder;

        try {
            if (Storage::exists($folderPath)) {
                Storage::deleteDirectory($folderPath);
            }
            Facerecognition::where('nik', $userkaryawan->nik)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data wajah berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data wajah: ' . $e->getMessage()
            ], 500);
        }
    }
}
