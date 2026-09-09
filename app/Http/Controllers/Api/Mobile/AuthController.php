<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle mobile login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $username = $request->input('username');
        $password = $request->input('password');

        // Check user by username or email
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        // Check if user is linked to Karyawan
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

        // Create Sanctum Token
        $token = $user->createToken('mobile-token')->plainTextToken;

        // Fetch associated relationships
        $karyawan->load(['jabatan', 'departemen', 'cabang']);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $karyawan->nama_karyawan ?? $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'nik' => $karyawan->nik,
                'jabatan' => $karyawan->jabatan->nama_jabatan ?? null,
                'departemen' => $karyawan->departemen->nama_dept ?? null,
                'cabang' => $karyawan->cabang->nama_cabang ?? null,
                'foto' => $karyawan->foto ? asset('storage/karyawan/' . $karyawan->foto . '?t=' . strtotime($karyawan->updated_at)) : null,
            ]
        ]);
    }

    /**
     * Handle mobile logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get mobile user profile.
     */
    public function profile(Request $request)
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
                'jabatan' => $karyawan->jabatan->nama_jabatan ?? null,
                'departemen' => $karyawan->departemen->nama_dept ?? null,
                'cabang' => $karyawan->cabang->nama_cabang ?? null,
                'foto' => $karyawan->foto ? asset('storage/karyawan/' . $karyawan->foto . '?t=' . strtotime($karyawan->updated_at)) : null,
            ]
        ]);
    }
}
