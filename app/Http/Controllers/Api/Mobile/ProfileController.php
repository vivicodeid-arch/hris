<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get employee profile data.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $userKaryawan = $user->userkaryawan;
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $karyawan = $userKaryawan->karyawan;
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Profil data karyawan tidak ditemukan'
            ], 404);
        }

        $karyawan->load(['jabatan', 'departemen', 'cabang']);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $karyawan->nama_karyawan ?? $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'nik' => $karyawan->nik,
                'no_hp' => $karyawan->no_hp,
                'no_ktp' => $karyawan->no_ktp,
                'alamat' => $karyawan->alamat,
                'jabatan' => $karyawan->jabatan->nama_jabatan ?? null,
                'departemen' => $karyawan->departemen->nama_dept ?? null,
                'cabang' => $karyawan->cabang->nama_cabang ?? null,
                'foto' => $karyawan->foto ? asset('storage/karyawan/' . $karyawan->foto . '?t=' . strtotime($karyawan->updated_at)) : null,
            ]
        ]);
    }

    /**
     * Change user password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->input('old_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai'
            ], 400);
        }

        User::where('id', $user->id)->update([
            'password' => Hash::make($request->input('password'))
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui'
        ]);
    }

    /**
     * Update employee photo.
     */
    public function updateFoto(Request $request)
    {
        $user = $request->user();
        
        $userKaryawan = $user->userkaryawan;
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $karyawan = Karyawan::where('nik', $userKaryawan->nik)->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Profil data karyawan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = $karyawan->nik . "." . $file->getClientOriginalExtension();
                $destinationPath = "/public/karyawan";

                // Ensure directory exists
                if (!Storage::exists($destinationPath)) {
                    Storage::makeDirectory($destinationPath, 0775, true);
                }

                // Delete old photo if exists
                if ($karyawan->foto) {
                    Storage::delete($destinationPath . "/" . $karyawan->foto);
                }

                // Store new file
                $file->storeAs($destinationPath, $filename);

                // Update database
                Karyawan::where('nik', $karyawan->nik)->update([
                    'foto' => $filename
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Foto profil berhasil diperbarui',
                    'foto' => asset('storage/karyawan/' . $filename . '?t=' . time())
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah foto: ' . $e->getMessage()
            ], 500);
        }
    }
}
