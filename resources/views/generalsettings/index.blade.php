@extends('layouts.app')
@section('titlepage', 'General Settings')

@section('content')
@section('navigasi')
    <span>General Settings</span>
@endsection
@php
    use Illuminate\Support\Facades\Storage;
@endphp
<style>
    .checkbox-wrapper-55 input[type="checkbox"] {
        visibility: hidden;
        display: none;
    }

    .checkbox-wrapper-55 *,
    .checkbox-wrapper-55 ::after,
    .checkbox-wrapper-55 ::before {
        box-sizing: border-box;
    }

    .checkbox-wrapper-55 .rocker {
        display: inline-block;
        position: relative;
        /*
      SIZE OF SWITCH
      ==============
      All sizes are in em - therefore
      changing the font-size here
      will change the size of the switch.
      See .rocker-small below as example.
      */
        font-size: 2em;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
        color: #888;
        width: 7em;
        height: 4em;
        overflow: hidden;
        border-bottom: 0.5em solid #eee;
    }

    .checkbox-wrapper-55 .rocker-small {
        font-size: 0.75em;
    }

    .checkbox-wrapper-55 .rocker::before {
        content: "";
        position: absolute;
        top: 0.5em;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #999;
        border: 0.5em solid #eee;
        border-bottom: 0;
    }

    .checkbox-wrapper-55 .switch-left,
    .checkbox-wrapper-55 .switch-right {
        cursor: pointer;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 2.5em;
        width: 3em;
        transition: 0.2s;
        user-select: none;
    }

    .checkbox-wrapper-55 .switch-left {
        height: 2.4em;
        width: 2.75em;
        left: 0.85em;
        bottom: 0.4em;
        background-color: #ddd;
        transform: rotate(15deg) skewX(15deg);
    }

    .checkbox-wrapper-55 .switch-right {
        right: 0.5em;
        bottom: 0;
        background-color: #bd5757;
        color: #fff;
    }

    .checkbox-wrapper-55 .switch-left::before,
    .checkbox-wrapper-55 .switch-right::before {
        content: "";
        position: absolute;
        width: 0.4em;
        height: 2.45em;
        bottom: -0.45em;
        background-color: #ccc;
        transform: skewY(-65deg);
    }

    .checkbox-wrapper-55 .switch-left::before {
        left: -0.4em;
    }

    .checkbox-wrapper-55 .switch-right::before {
        right: -0.375em;
        background-color: transparent;
        transform: skewY(65deg);
    }

    .checkbox-wrapper-55 input:checked+.switch-left {
        background-color: #0084d0;
        color: #fff;
        bottom: 0px;
        left: 0.5em;
        height: 2.5em;
        width: 3em;
        transform: rotate(0deg) skewX(0deg);
    }

    .checkbox-wrapper-55 input:checked+.switch-left::before {
        background-color: transparent;
        width: 3.0833em;
    }

    .checkbox-wrapper-55 input:checked+.switch-left+.switch-right {
        background-color: #ddd;
        color: #888;
        bottom: 0.4em;
        right: 0.8em;
        height: 2.4em;
        width: 2.75em;
        transform: rotate(-15deg) skewX(-15deg);
    }

    .checkbox-wrapper-55 input:checked+.switch-left+.switch-right::before {
        background-color: #ccc;
    }

    /* Keyboard Users */
    .checkbox-wrapper-55 input:focus+.switch-left {
        color: #333;
    }

    .checkbox-wrapper-55 input:checked:focus+.switch-left {
        color: #fff;
    }

    .checkbox-wrapper-55 input:focus+.switch-left+.switch-right {
        color: #fff;
    }

    .checkbox-wrapper-55 input:checked:focus+.switch-left+.switch-right {
        color: #333;
    }
</style>
    <form action="{{ route('generalsetting.update', Crypt::encrypt($setting->id)) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- COLUMN 1 -->
            <div class="col-lg-4 col-md-6 col-sm-12">
                <!-- Perusahaan -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Informasi Perusahaan</h6>
                    </div>
                    <div class="card-body">
                        <x-input-with-icon-label label="Nama Aplikasi" name="nama_aplikasi" icon="ti ti-brand-appgallery" :value="$setting->nama_aplikasi ?? ''" />
                        <x-input-with-icon-label label="Nama Perusahaan" name="nama_perusahaan" icon="ti ti-home" :value="$setting->nama_perusahaan ?? ''" />
                        <x-textarea-label label="Alamat Perusahaan" name="alamat" icon="ti ti-map-pin" :value="$setting->alamat ?? ''" />
                        <x-input-with-icon-label label="Telepon" name="telepon" icon="ti ti-phone" :value="$setting->telepon ?? ''" />
                        <x-input-with-icon-label label="Nama HRD" name="nama_hrd" icon="ti ti-user" :value="$setting->nama_hrd ?? ''" />
                        <div class="form-group mb-3">
                            <label for="logo" style="font-weight: 600" class="form-label">Logo Perusahaan</label>
                            <input type="file" class="form-control" name="logo" id="logo">
                            <div class="mt-2 text-center">
                                @if ($setting->logo && Storage::exists('public/logo/' . $setting->logo))
                                    <img src="{{ asset('storage/logo/' . $setting->logo) }}" alt="Logo Perusahaan" style="max-width: 200px;">
                                @else
                                    <img src="https://placehold.co/200x200?text=Logo+Perusahaan&font=roboto" alt="Logo Default" style="max-width: 200px;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Laporan -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Pengaturan Laporan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <x-input-with-icon-label label="Periode Laporan Dari" icon="ti ti-calendar" name="periode_laporan_dari"
                                    :value="$setting->periode_laporan_dari ?? ''" />
                            </div>
                            <div class="col">
                                <x-input-with-icon-label label="Periode Laporan Sampai" icon="ti ti-calendar" name="periode_laporan_sampai"
                                    :value="$setting->periode_laporan_sampai ?? ''" />
                            </div>
                        </div>
                        <label for="periode_laporan_next_bulan" style="font-weight: 600" class="form-label">Metode Lintas Bulan</label>
                        <select name="periode_laporan_next_bulan" id="periode_laporan_next_bulan" class="form-select">
                            <option value="0" {{ ($setting->periode_laporan_next_bulan ?? 0) == 0 ? 'selected' : '' }}>Satu Bulan yang Sama (Default)</option>
                            <option value="1" {{ ($setting->periode_laporan_next_bulan ?? 0) == 1 ? 'selected' : '' }}>Bulan Sebelumnya ke Bulan Saat Ini (Contoh: 21 April - 20 Mei)</option>
                            <option value="2" {{ ($setting->periode_laporan_next_bulan ?? 0) == 2 ? 'selected' : '' }}>Bulan Saat Ini ke Bulan Berikutnya (Contoh: 2 Mei - 1 Juni)</option>
                        </select>
                    </div>
                </div>
                <!-- Email -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Pengaturan Email</h6>
                    </div>
                    <div class="card-body">
                        <x-input-with-icon-label label="Domain Email (contoh: adamadifa.site)" name="domain_email" icon="ti ti-mail" :value="$setting->domain_email ?? ''" />
                    </div>
                </div>
                <!-- Jadwal Kerja Global -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Jadwal Kerja Global</h6>
                    </div>
                    <div class="card-body">
                        <label for="" style="font-weight: 600" class="form-label">Aktifkan Jadwal Global</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="global_jamkerja_aktif" id="global_jamkerja_aktif" @checked($setting->global_jamkerja_aktif ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <div id="global_jamkerja_container" style="display: none;">
                            <small class="text-muted d-block mb-3">
                                Jadwal ini digunakan sebagai fallback jika karyawan tidak memiliki jadwal kerja dari level manapun (by date, grup, by day, departemen).
                            </small>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Hari</th>
                                        <th>Jam Kerja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                    @endphp
                                    @foreach($daftarHari as $hari)
                                        <tr>
                                            <td class="align-middle" style="font-weight: 600;">{{ $hari }}</td>
                                            <td>
                                                <select name="global_jamkerja[{{ $hari }}]" class="form-select form-select-sm">
                                                    <option value="">-- Libur --</option>
                                                    @foreach($jamkerja_list as $jk)
                                                        <option value="{{ $jk->kode_jam_kerja }}"
                                                            @selected(isset($global_jamkerja[$hari]) && $global_jamkerja[$hari]->kode_jam_kerja == $jk->kode_jam_kerja)>
                                                            {{ $jk->kode_jam_kerja }} - {{ $jk->nama_jam_kerja }} ({{ $jk->jam_masuk }} - {{ $jk->jam_pulang }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMN 2 -->
            <div class="col-lg-4 col-md-6 col-sm-12">
                <!-- Sistem -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Pengaturan Sistem</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="timezone" style="font-weight: 600" class="form-label">Zona Waktu Default Sistem <span class="text-danger">*</span></label>
                            <select class="form-select" name="timezone" id="timezone" required>
                                <option value="">Pilih Zona Waktu</option>
                                <option value="Asia/Jakarta" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'Asia/Jakarta')>Asia/Jakarta (WIB)</option>
                                <option value="Asia/Makassar" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'Asia/Makassar')>Asia/Makassar (WITA)</option>
                                <option value="Asia/Jayapura" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'Asia/Jayapura')>Asia/Jayapura (WIT)</option>
                                <option value="Asia/Singapore" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'Asia/Singapore')>Asia/Singapore</option>
                                <option value="Asia/Kuala_Lumpur" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'Asia/Kuala_Lumpur')>Asia/Kuala_Lumpur</option>
                                <option value="Asia/Bangkok" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'Asia/Bangkok')>Asia/Bangkok</option>
                                <option value="Asia/Manila" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'Asia/Manila')>Asia/Manila</option>
                                <option value="UTC" @selected(($setting->timezone ?? 'Asia/Jakarta') == 'UTC')>UTC</option>
                            </select>
                            <small class="text-muted">Zona waktu ini akan mengubah pengaturan timezone di file .env dan config/app.php</small>
                            <div class="mt-3">
                                <x-input-with-icon-label label="Durasi Sesi Login (Hari)" name="session_time"
                                    icon="ti ti-clock" :value="$setting->session_time ?? '120'" />
                                <small class="text-muted">Lama waktu user tetap login tanpa perlu login ulang (Default: 120 Hari)</small>
                            </div>
                            @if(auth()->user()->hasRole('master admin'))
                            <div class="mt-3">
                                <x-input-with-icon-label label="Tanggal Expired Aplikasi" name="expired"
                                    icon="ti ti-calendar-off" datepicker="flatpickr-date" :value="$setting->expired ?? ''" />
                                <small class="text-muted">Batas tanggal aktif aplikasi untuk klien/pengguna (Kosongkan jika tidak ada).</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Tema -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Pengaturan Tema Aplikasi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="theme_color_1" class="form-label" style="font-weight: 600">Warna Utama (Sidebar/Primary)</label>
                                    <input type="color" class="form-control form-control-color w-100" id="theme_color_1" name="theme_color_1" 
                                        value="{{ $setting->theme_color_1 ?? '#053b22' }}" title="Choose your color">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="theme_color_2" class="form-label" style="font-weight: 600">Warna Sekunder (Gradient/Hover)</label>
                                    <input type="color" class="form-control form-control-color w-100" id="theme_color_2" name="theme_color_2"
                                        value="{{ $setting->theme_color_2 ?? '#0b6a3a' }}" title="Choose your color">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label for="mobile_theme_scheme" class="form-label" style="font-weight: 600">Tema Aplikasi Mobile</label>
                                    <select name="mobile_theme_scheme" id="mobile_theme_scheme" class="form-select">
                                        <option value="green" @selected(($setting->mobile_theme_scheme ?? 'green') == 'green')>Green (Default)</option>
                                        <option value="blue" @selected(($setting->mobile_theme_scheme ?? 'green') == 'blue')>Blue (Ocean)</option>
                                        <option value="red" @selected(($setting->mobile_theme_scheme ?? 'green') == 'red')>Red (Passion)</option>
                                        <option value="orange" @selected(($setting->mobile_theme_scheme ?? 'green') == 'orange')>Orange (Sunset)</option>
                                        <option value="purple" @selected(($setting->mobile_theme_scheme ?? 'green') == 'purple')>Purple (Royal)</option>
                                        <option value="rose" @selected(($setting->mobile_theme_scheme ?? 'green') == 'rose')>Rose (Elegant)</option>
                                        <option value="dark" @selected(($setting->mobile_theme_scheme ?? 'green') == 'dark')>Dark (Night)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            Warna ini akan mengubah tampilan Sidebar, Tombol Primary, dan elemen utama lainnya.
                        </small>
                    </div>
                </div>

                <!-- Pengaturan Login Admin -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Pengaturan Login Admin</h6>
                    </div>
                    <div class="card-body">
                        <label for="block_admin_login" style="font-weight: 600" class="form-label">Nonaktifkan Login Admin di Halaman Utama</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="block_admin_login" id="block_admin_login" @checked($setting->block_admin_login ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <small class="text-muted d-block mb-3">Jika diaktifkan, user admin tidak bisa login melalui halaman utama (/).</small>

                        <div id="admin_login_url_container">
                            <x-input-with-icon-label label="Path Login Admin Khusus (contoh: panel)" name="admin_login_url" id="admin_login_url" icon="ti ti-link" :value="$setting->admin_login_url ?? 'panel'" />
                            <small class="text-muted">Alamat login admin akan menjadi: <strong>{{ url('/') }}/<span id="admin_path_preview">{{ $setting->admin_login_url ?? 'panel' }}</span></strong></small>
                        </div>
                    </div>
                </div>

                <!-- Presensi -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Pengaturan Presensi</h6>
                    </div>
                    <div class="card-body">
                        <x-input-with-icon-label label="Total Jam Kerja dalam 1 Bulan" name="total_jam_bulan" icon="ti ti-clock" :value="$setting->total_jam_bulan ?? ''" />
                        <div class="form-group mb-3">
                            <label for="sistem_hari_kerja" style="font-weight: 600" class="form-label">Sistem Hari Kerja <span class="text-danger">*</span></label>
                            <select class="form-select" name="sistem_hari_kerja" id="sistem_hari_kerja" required>
                                <option value="6" @selected(($setting->sistem_hari_kerja ?? '6') == '6')>6 Hari Kerja (Senin - Sabtu)</option>
                                <option value="5" @selected(($setting->sistem_hari_kerja ?? '6') == '5')>5 Hari Kerja (Senin - Jumat)</option>
                            </select>
                            <small class="text-muted">Gunakan 5 Hari Kerja jika Sabtu & Minggu adalah hari libur rutin.</small>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Potongan Jam Kerja</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="status_potongan_jam" @checked($setting->status_potongan_jam ?? true)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Tampilkan Rate di Slip Gaji</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="show_rate_slip" @checked($setting->show_rate_slip ?? true)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Denda</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="denda" @checked($setting->denda ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Face Recognition</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="face_recognition" @checked($setting->face_recognition ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Multi Lokasi</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="multi_lokasi" @checked($setting->multi_lokasi ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Batasi Jam Presensi</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="batasi_absen" @checked($setting->batasi_absen ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <x-input-with-icon-label label="Batas Jam Presensi Masuk (Dalam Jam) Sebelum Jam Masuk" name="batas_jam_absen"
                            icon="ti ti-clock" :value="$setting->batas_jam_absen ?? ''" />
                        <small class="text-muted">Wajib Diisi Jika Batasi Jam Presensi Diaktifkan</small>
                        <x-input-with-icon-label label="Batas Jam Presensi Pulang (Dalam Jam) Sebelum Jam Pulang" name="batas_jam_absen_pulang"
                            icon="ti ti-clock" :value="$setting->batas_jam_absen_pulang ?? ''" />
                        <div class="form-group">
                            <small class="text-muted">Wajib Diisi Jika Batasi Jam Presensi Diaktifkan</small>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Batasi Hari Izin</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="batasi_hari_izin" @checked($setting->batasi_hari_izin ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <x-input-with-icon-label label="Batas Hari Izin (Dalam Hari)" name="jml_hari_izin_max" icon="ti ti-clock" :value="$setting->jml_hari_izin_max ?? ''" />
                        <x-input-with-icon-label label="Batas Presensi Lintas Hari" name="batas_presensi_lintashari" icon="ti ti-clock"
                            :value="$setting->batas_presensi_lintashari ?? ''" />
                        <label for="" style="font-weight: 600" class="form-label">Absen Istirahat</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="absen_istirahat" @checked($setting->absen_istirahat ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Potongan Istirahat</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="potongan_istirahat" @checked($setting->potongan_istirahat ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMN 3 -->
            <div class="col-lg-4 col-md-6 col-sm-12">
                <!-- Pengaturan Menu Karyawan -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-between align-items-center">
                        <h6 class="mb-0 font-weight-bold">Menu Karyawan Aktif</h6>
                        <small class="text-muted">Centang untuk memunculkan menu</small>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <div class="row">
                            @foreach ($karyawan_menus as $menu)
                                <div class="col-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                            name="karyawan_menu[{{ $menu->kode_menu }}]" 
                                            id="menu_{{ $menu->kode_menu }}" 
                                            value="1" 
                                            @checked($menu->status == 1)>
                                        <label class="form-check-label" style="font-size: 12px; font-weight: 600;" for="menu_{{ $menu->kode_menu }}">
                                            {{ $menu->nama_menu }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Integrasi -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Pengaturan Integrasi Mesin Fingerprint</h6>
                    </div>
                    <div class="card-body">
                        <x-input-with-icon-label label="Cloud Id" name="cloud_id" icon="ti ti-cloud" :value="$setting->cloud_id ?? ''" />
                        <x-input-with-icon-label label="API Key" name="api_key" icon="ti ti-key" :value="$setting->api_key ?? ''" />
                    </div>
                </div>
                <!-- WA -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Whatsapp Gateway</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="provider_wa" style="font-weight: 600" class="form-label">Provider WA</label>
                            <select class="form-select" name="provider_wa" id="provider_wa">
                                <option value="ig" @selected(($setting->provider_wa ?? 'ig') == 'ig')>Internal Gateway</option>
                                <option value="fe" @selected(($setting->provider_wa ?? 'ig') == 'fe')>Fonnte</option>
                            </select>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Notifikasi WA</label>
                        <div class="checkbox-wrapper-55 mb-2">
                            <label class="rocker rocker-small">
                                <input type="checkbox" name="notifikasi_wa" @checked($setting->notifikasi_wa ?? false)>
                                <span class="switch-left">Yes</span>
                                <span class="switch-right">No</span>
                            </label>
                        </div>
                        <label for="" style="font-weight: 600" class="form-label">Tujuan Notifikasi WA</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tujuan_notifikasi_wa" id="tujuan_grup" value="1"
                                @checked(($setting->tujuan_notifikasi_wa ?? 0) == 1)>
                            <label class="form-check-label" for="tujuan_grup">
                                Kirim ke Grup
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tujuan_notifikasi_wa" id="tujuan_karyawan" value="0"
                                @checked(($setting->tujuan_notifikasi_wa ?? 0) == 0)>
                            <label class="form-check-label" for="tujuan_karyawan">
                                Kirim ke Karyawan
                            </label>
                        </div>
                        <div id="group_wa_input" style="display: none;">
                            <x-input-with-icon-label label="ID Group WA" name="id_group_wa" icon="ti ti-users" :value="$setting->id_group_wa ?? ''" />
                        </div>
                        <x-input-with-icon-label label="Domain WA Gateway (contoh: https://wa.adamadifa.site)" name="domain_wa_gateway"
                            icon="ti ti-message" :value="$setting->domain_wa_gateway ?? ''" />
                        <x-input-with-icon-label label="WA API Key" name="wa_api_key" icon="ti ti-brand-whatsapp" :value="$setting->wa_api_key ?? ''" />
                    </div>
                </div>

                <!-- PWA -->
                <div class="card mb-3">
                    <div class="card-header">
                         <h6 class="mb-0">PWA Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="pwa_icon" style="font-weight: 600" class="form-label">
                                Upload Icon Master (1080x1080px)
                            </label>
                            <input type="file" class="form-control" name="pwa_icon" id="pwa_icon" accept="image/*">
                            <small class="text-muted">
                                Upload gambar dengan ukuran 1080x1080px atau lebih besar.
                                Sistem akan otomatis generate berbagai ukuran untuk PWA.
                            </small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success w-100" id="btnGenerateIcons">
                                    <i class="ti ti-device-mobile me-1"></i> Generate
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-warning w-100" id="btnPreviewIcons">
                                    <i class="ti ti-eye me-1"></i> Preview
                                </button>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div id="progressContainer" style="display: none;">
                            <div class="progress mb-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                                </div>
                            </div>
                            <small class="text-muted" id="progressText">Menggenerate icons...</small>
                        </div>
                        
                        <!-- Generated Icons Preview -->
                        <div id="iconsPreview" class="mt-3" style="display: none;">
                            <h6>Generated:</h6>
                            <div class="row" id="iconsGrid"></div>
                        </div>
                          <!-- Current PWA Icons -->
                        <div class="mt-3">
                            <h6>Current Icons:</h6>
                            <div class="row" id="currentIconsGrid">
                                @php
                                    $iconDir = public_path('assets/img/icons/pwa');
                                    $currentIcons = [];
                                    if (file_exists($iconDir)) {
                                        $files = glob($iconDir . '/icon-*.png');
                                        foreach ($files as $file) {
                                            $filename = basename($file);
                                            $size = str_replace(['icon-', '.png'], '', $filename);
                                            $currentIcons[] = [
                                                'size' => $size,
                                                'path' => 'assets/img/icons/pwa/' . $filename,
                                            ];
                                        }
                                    }
                                @endphp

                                @if (count($currentIcons) > 0)
                                    @foreach ($currentIcons as $icon)
                                        <div class="col-3 mb-2">
                                            <div class="text-center">
                                                <img src="{{ asset($icon['path']) }}?v={{ time() }}" alt="{{ $icon['size'] }}" class="img-thumbnail"
                                                    style="width: 30px; height: 30px;">
                                                <small class="d-block" style="font-size: 9px">{{ $icon['size'] }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <small class="text-muted">Belum ada icon.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Perbaikan Permission Folder Storage -->
                <div class="card mb-3 border border-warning">
                    <div class="card-header bg-warning-subtle">
                        <h6 class="mb-0 text-warning-emphasis font-weight-bold">Perbaikan Folder Storage</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="font-size: 12px; line-height: 1.5;">
                            Jika Anda mengalami masalah file upload tidak bisa diakses (404/403) atau sistem gagal membuat folder/menyimpan file baru saat upload dokumen, klik tombol di bawah untuk menyetel ulang permission folder storage menjadi 775 secara rekursif.
                        </p>
                        <button type="button" class="btn btn-warning w-100" id="btnFixPermissions">
                            <i class="ti ti-folder-lock me-1"></i> Perbaiki Permission Storage
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mb-4" id="btnSimpan">
                    <i class="ti ti-refresh me-1"></i> Update Settings
                </button>
            </div>
        </div>
    </form>


@endsection
@push('myscript')
<script>
    $(document).ready(function() {
        $('#batas_presensi_lintashari').flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
        });

        $('.flatpickr-date').flatpickr({
            dateFormat: 'Y-m-d',
        });

        // Toggle Group WA Input
        function toggleGroupInput() {
            const tujuanGrup = $('#tujuan_grup').is(':checked');
            if (tujuanGrup) {
                $('#group_wa_input').show();
            } else {
                $('#group_wa_input').hide();
            }
        }

        // Initialize on page load
        toggleGroupInput();

        // Toggle on radio button change
        $('input[name="tujuan_notifikasi_wa"]').change(function() {
            toggleGroupInput();
        });

        // Toggle Global Jadwal Kerja Container
        function toggleGlobalJamkerja() {
            if ($('#global_jamkerja_aktif').is(':checked')) {
                $('#global_jamkerja_container').slideDown();
            } else {
                $('#global_jamkerja_container').slideUp();
            }
        }
        toggleGlobalJamkerja();
        $('#global_jamkerja_aktif').change(function() {
            toggleGlobalJamkerja();
        });

        // Toggle Admin Login URL input
        function toggleAdminLoginUrl() {
            if ($('#block_admin_login').is(':checked')) {
                $('#admin_login_url_container').slideDown();
            } else {
                $('#admin_login_url_container').slideUp();
            }
        }
        toggleAdminLoginUrl();
        $('#block_admin_login').change(function() {
            toggleAdminLoginUrl();
        });

        // Update preview dynamically
        $('#admin_login_url').on('input', function() {
            let val = $(this).val().replace(/[^a-zA-Z0-9-_]/g, '');
            $(this).val(val);
            $('#admin_path_preview').text(val || 'panel');
        });

        // PWA Icon Generator
        $('#btnGenerateIcons').click(function() {
            const fileInput = document.getElementById('pwa_icon');
            const file = fileInput.files[0];

            if (!file) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih file icon terlebih dahulu!'
                });
                return;
            }

            // Validate file size (max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ukuran file terlalu besar! Maksimal 10MB.'
                });
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'File harus berupa gambar!'
                });
                return;
            }

            generateIcons(file);
        });

        $('#btnPreviewIcons').click(function() {
            previewCurrentIcons();
        });

        function generateIcons(file) {
            const formData = new FormData();
            formData.append('icon', file);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            // Show progress
            $('#progressContainer').show();
            $('#btnGenerateIcons').prop('disabled', true);
            updateProgress(0, 'Memulai proses...');

            $.ajax({
                url: '{{ route('pwa.generate-icons') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            updateProgress(percentComplete, 'Uploading file...');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    updateProgress(100, 'Selesai!');

                    setTimeout(() => {
                        $('#progressContainer').hide();
                        $('#btnGenerateIcons').prop('disabled', false);

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: `Berhasil generate ${response.count} icon PWA!`
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    }, 1000);
                },
                error: function(xhr) {
                    $('#progressContainer').hide();
                    $('#btnGenerateIcons').prop('disabled', false);

                    let errorMessage = 'Terjadi kesalahan saat generate icons!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        }

        function updateProgress(percent, text) {
            $('.progress-bar').css('width', percent + '%');
            $('#progressText').text(text);
        }

        function previewCurrentIcons() {
            $.ajax({
                url: '{{ route('pwa.preview-icons') }}',
                type: 'GET',
                success: function(response) {
                    if (response.length > 0) {
                        let html = '';
                        response.forEach(function(icon) {
                            html += `
                                <div class="col-2 mb-2">
                                    <div class="text-center">
                                        <img src="${icon.url}?t=${new Date().getTime()}"
                                             alt="Icon ${icon.size}"
                                             class="img-thumbnail"
                                             style="width: 50px; height: 50px;">
                                        <small class="d-block">${icon.size}</small>
                                    </div>
                                </div>
                            `;
                        });

                        $('#iconsGrid').html(html);
                        $('#iconsPreview').show();

                        Swal.fire({
                            icon: 'info',
                            title: 'Preview Icons',
                            text: `Ditemukan ${response.length} icon PWA yang sudah di-generate.`
                        });
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Info',
                            text: 'Belum ada icon PWA yang di-generate.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat preview icons!'
                    });
                }
            });
        }

        // Handle Fix Permissions Button
        $('#btnFixPermissions').click(function() {
            Swal.fire({
                title: 'Perbaiki Permission?',
                text: "Sistem akan mengatur ulang permission folder storage menjadi 775 secara rekursif agar dapat diakses/ditulisi oleh aplikasi.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Perbaiki!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang memperbarui permission folder storage.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ route('generalsetting.fix-permissions') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan pada server. Pastikan Anda memiliki akses yang cukup.'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
