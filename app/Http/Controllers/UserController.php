<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Userkaryawan;
use App\Models\Cabang;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $userType = $request->user_type ?? 'biasa';
        
        $users = User::with(['roles', 'cabangs', 'departemens'])
            ->when(!auth()->user()->hasRole('master admin'), function($query) {
                return $query->whereDoesntHave('roles', function($q) {
                    $q->where('name', 'master admin');
                });
            })
            ->when($request->name, function ($query, $name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->when($request->role_id, function ($query, $role_id) {
                return $query->whereHas('roles', function ($subQuery) use ($role_id) {
                    $subQuery->where('role_id', $role_id);
                });
            })
            ->leftjoin('users_karyawan', 'users.id', '=', 'users_karyawan.id_user')
            ->when($userType == 'karyawan', function ($query) {
                // Filter hanya users yang punya relasi dengan users_karyawan
                return $query->whereNotNull('users_karyawan.id_user');
            }, function ($query) {
                // Filter hanya users yang TIDAK punya relasi dengan users_karyawan
                return $query->whereNull('users_karyawan.id_user');
            })
            ->select('users.*', 'users_karyawan.nik')
            ->distinct()
            ->paginate(10);
        
        $users->appends($request->all());

        $roles = Role::orderBy('name')
            ->when(!auth()->user()->hasRole('master admin'), function($query) {
                return $query->where('name', '!=', 'master admin');
            })
            ->get();
        return view('settings.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')
            ->where('name', '!=', 'karyawan')
            ->when(!auth()->user()->hasRole('master admin'), function($query) {
                return $query->where('name', '!=', 'master admin');
            })
            ->get();
        $cabangs = Cabang::orderBy('kode_cabang')->get();
        $departemens = Departemen::orderBy('kode_dept')->get();
        return view('settings.users.create', compact('roles', 'cabangs', 'departemens'));
    }

    public function edit($id)
{
    $id = Crypt::decrypt($id);
    $user = User::with(['roles', 'cabangs', 'departemens'])->where('id', $id)->first();

    if ($user->hasRole('master admin') && !auth()->user()->hasRole('master admin')) {
        abort(403, 'Unauthorized action.');
    }

    $roles = Role::orderBy('name')
        ->where('name', '!=', 'karyawan')
        ->when(!auth()->user()->hasRole('master admin'), function($query) {
            return $query->where('name', '!=', 'master admin');
        })
        ->get();
    $cabangs = Cabang::orderBy('kode_cabang')->get();
    $departemens = Departemen::orderBy('kode_dept')->get();
    $userCabangs = $user->cabangs->pluck('kode_cabang')->toArray();
    $userDepartemens = $user->departemens->pluck('kode_dept')->toArray();
    
    // Approval delegation - get all non-karyawan users as potential admin
    $roleQuery = Role::where('name', '!=', 'karyawan')
        ->when(!auth()->user()->hasRole('master admin'), function($query) {
            return $query->where('name', '!=', 'master admin');
        });
    $adminUsers = User::role($roleQuery->pluck('name')->toArray())->orderBy('name')->get();
    $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
    
    return view('settings.users.edit', compact('user', 'roles', 'cabangs', 'departemens', 'userCabangs', 'userDepartemens', 'adminUsers', 'userkaryawan'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required'
        ]);

        // Validasi untuk role selain super admin
        $roleName = strtolower($request->role);
        if ($roleName !== 'super admin') {
            $request->validate([
                'cabangs' => 'required|array|min:1',
                'cabangs.*' => 'exists:cabang,kode_cabang',
                'departemens' => 'required|array|min:1',
                'departemens.*' => 'exists:departemen,kode_dept',
            ], [
                'cabangs.required' => 'Minimal 1 cabang harus dipilih',
                'cabangs.min' => 'Minimal 1 cabang harus dipilih',
                'departemens.required' => 'Minimal 1 departemen harus dipilih',
                'departemens.min' => 'Minimal 1 departemen harus dipilih',
            ]);
        }

        try {
            $roleName = strtolower($request->role);
            if ($roleName === 'master admin' && !auth()->user()->hasRole('master admin')) {
                return Redirect::back()->with(['eror' => 'Unauthorized role assignment.']);
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $user->assignRole($request->role);

            // Jika role adalah super admin, berikan akses ke semua cabang dan departemen
            if ($roleName === 'super admin') {
                $allCabangs = Cabang::pluck('kode_cabang')->toArray();
                $allDepartemens = Departemen::pluck('kode_dept')->toArray();
                $user->cabangs()->sync($allCabangs);
                $user->departemens()->sync($allDepartemens);
            } else {
                // Sync akses cabang
                if (isset($request->cabangs) && is_array($request->cabangs)) {
                    $user->cabangs()->sync($request->cabangs);
                }

                // Sync akses departemen
                if (isset($request->departemens) && is_array($request->departemens)) {
                    $user->departemens()->sync($request->departemens);
                }
            }

            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['eror' => 'Data Gagal Disimpan']);
        }
    }


    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $user = User::findorFail($id);

        if ($user->hasRole('master admin') && !auth()->user()->hasRole('master admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
        ]);

        try {

            if (isset($request->password)) {
                User::where('id', $id)->update([
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);
            } else {
                User::where('id', $id)->update([
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                ]);
            }

            if (isset($request->role)) {
                $roleName = strtolower($request->role);
                if ($roleName === 'master admin' && !auth()->user()->hasRole('master admin')) {
                    return Redirect::back()->with(['error' => 'Unauthorized role assignment.']);
                }
                $user->syncRoles([]);
                $user->assignRole($request->role);
            }

            // Jika role adalah super admin, berikan akses ke semua cabang dan departemen
            $roleName = isset($request->role) ? strtolower($request->role) : strtolower($user->roles->pluck('name')->first() ?? '');
            
            // Validasi untuk role selain super admin dan karyawan
            if ($roleName !== 'super admin' && $roleName !== 'karyawan') {
                $request->validate([
                    'cabangs' => 'required|array|min:1',
                    'cabangs.*' => 'exists:cabang,kode_cabang',
                    'departemens' => 'required|array|min:1',
                    'departemens.*' => 'exists:departemen,kode_dept',
                ], [
                    'cabangs.required' => 'Minimal 1 cabang harus dipilih',
                    'cabangs.min' => 'Minimal 1 cabang harus dipilih',
                    'departemens.required' => 'Minimal 1 departemen harus dipilih',
                    'departemens.min' => 'Minimal 1 departemen harus dipilih',
                ]);
            }
            
            if ($roleName === 'super admin') {
                $allCabangs = Cabang::pluck('kode_cabang')->toArray();
                $allDepartemens = Departemen::pluck('kode_dept')->toArray();
                $user->cabangs()->sync($allCabangs);
                $user->departemens()->sync($allDepartemens);
            } else {
                // Sync akses cabang
                if (isset($request->cabangs) && is_array($request->cabangs)) {
                    $user->cabangs()->sync($request->cabangs);
                } else {
                    // Jika tidak ada cabang yang dipilih, hapus semua akses
                    $user->cabangs()->sync([]);
                }

                // Sync akses departemen
                if (isset($request->departemens) && is_array($request->departemens)) {
                    $user->departemens()->sync($request->departemens);
                } else {
                    // Jika tidak ada departemen yang dipilih, hapus semua akses
                    $user->departemens()->sync([]);
                }
            }

            // Update approval_admin_id for karyawan
            $userkaryawan = Userkaryawan::where('id_user', $id)->first();
            if ($userkaryawan) {
                Userkaryawan::where('id_user', $id)->update([
                    'approval_admin_id' => $request->approval_admin_id ?: null,
                ]);
            }

            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }


    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        try {
            $user = User::findOrFail($id);
            if ($user->hasRole('master admin') && !auth()->user()->hasRole('master admin')) {
                abort(403, 'Unauthorized action.');
            }
            User::where('id', $id)->delete();
            $cek_user_karyawan = Userkaryawan::where('id_user', $id)->first();
            if ($cek_user_karyawan) {
                Userkaryawan::where('id_user', $id)->delete();
            }

            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }

    public function editpassword($id)
    {
        $id = Crypt::decrypt($id);
        $user = User::where('id', $id)->first();
        return view('settings.users.editpassword', compact('user'));
    }

    public function updatepassword(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'konfirmasipassword' => 'same:passwordbaru'
        ]);
        try {
            $data = [
                'username' => $request->username
            ];

            if (!empty($request->passwordbaru)) {
                $data['password'] = Hash::make($request->passwordbaru);
            }

            User::where('id', $id)->update($data);
            return Redirect::back()->with(['success' => 'Data Berhasil Diupdate']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }

    public function createuserpermission($id)
    {
        $id = Crypt::decrypt($id);
        $user = User::findOrFail($id);
        if ($user->hasRole('master admin') && !auth()->user()->hasRole('master admin')) {
            abort(403, 'Unauthorized action.');
        }
        $permissions = Permission::orderBy('id_permission_group')
            ->selectRaw('id_permission_group,permission_groups.name as group_name,GROUP_CONCAT(permissions.id,"-",permissions.name) as permissions')
            ->join('permission_groups', 'permissions.id_permission_group', '=', 'permission_groups.id')
            ->groupBy('id_permission_group')
            ->groupBy('permission_groups.name')
            ->get();

        $userpermissions = $user->getAllPermissions()->pluck('name')->toArray();
        $userRoles = $user->roles()->with('permissions')->get();
        $permissionRoleMap = [];
        foreach ($userRoles as $role) {
            foreach ($role->permissions as $perm) {
                $permissionRoleMap[$perm->name][] = ucwords($role->name);
            }
        }
        return view('settings.users.create_user_permission', compact('permissions', 'user', 'userpermissions', 'permissionRoleMap'));
    }

    public function storeuserpermission($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $user = User::findOrFail($id);
        if ($user->hasRole('master admin') && !auth()->user()->hasRole('master admin')) {
            abort(403, 'Unauthorized action.');
        }
        $permissions = $request->permission ?? [];

        try {
            $user->syncPermissions($permissions);
            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => $e->getMessage()]);
        }
    }

    public function impersonate($id)
    {
        $id = Crypt::decrypt($id);
        
        if (auth()->id() == $id) {
            return Redirect::back()->with(['error' => 'Anda tidak bisa impersonate diri sendiri.']);
        }

        $user = User::findOrFail($id);
        
        session(['impersonator_id' => auth()->id()]);
        
        auth()->login($user);
        
        return redirect()->route('dashboard.index')->with(['success' => 'Anda sekarang masuk sebagai ' . $user->name]);
    }

    public function stopImpersonate()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('dashboard.index');
        }

        $adminId = session('impersonator_id');
        session()->forget('impersonator_id');
        
        $admin = User::findOrFail($adminId);
        auth()->login($admin);
        
        return redirect()->route('users.index')->with(['success' => 'Kembali sebagai admin: ' . $admin->name]);
    }
}
