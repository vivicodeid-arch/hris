@extends('layouts.app')
@section('titlepage', 'Push Subscriptions')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Push Subscriptions
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Daftar perangkat pengguna yang terdaftar untuk menerima Push Notification.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="ti ti-home-2 ti-xs"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);">
                        <i class="ti ti-adjustments-alt ti-xs me-1"></i> Utilities
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-bell-ringing ti-xs me-1"></i> Push Subscriptions
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <form action="{{ route('push-subscription.index') }}" method="GET">
            <div class="row g-2 mb-3 align-items-end">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <x-input-with-icon label="Cari Pengguna" value="{{ request('cari') }}" name="cari"
                        icon="ti ti-user" hideLabel placeholder="Cari Nama atau Username / NIK..." />
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="ti ti-search me-1"></i> Cari
                        </button>
                        <a href="{{ route('push-subscription.index') }}" class="btn btn-label-secondary p-2" title="Reset">
                            <i class="ti ti-refresh"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-bell-ringing me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Data Push Subscriptions</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3" width="50">NO.</th>
                                <th class="text-white py-3">PENGGUNA</th>
                                <th class="text-white py-3 border-start">INFO AKUN</th>
                                <th class="text-white py-3 border-start">ENDPOINT BROWSER</th>
                                <th class="text-white py-3 border-start">TERAKHIR DIPERBARUI</th>
                                <th class="text-white py-3 border-start text-center" width="100">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subscriptions as $sub)
                                <tr>
                                    <td class="py-2">{{ $subscriptions->firstItem() + $loop->index }}</td>
                                    <td class="py-2 fw-bold">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary shadow-sm" style="font-size: 0.75rem;">
                                                    {{ strtoupper(substr($sub->nama_karyawan ?? $sub->user_name ?? '?', 0, 1)) }}
                                                </span>
                                            </div>
                                            {{ $sub->nama_karyawan ?? $sub->user_name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="py-2 border-start">
                                        <span class="badge bg-label-secondary border-0" style="font-size: 0.65rem;">
                                            <i class="ti ti-id-badge me-1" style="font-size: 0.75rem;"></i>{{ $sub->nik_show ?? $sub->username ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-2 border-start" style="max-width: 350px;">
                                        @php
                                            // Deteksi nama browser dari endpoint
                                            $browser = 'Unknown Browser';
                                            if (str_contains($sub->endpoint, 'android.googleapis.com') || str_contains($sub->endpoint, 'fcm.googleapis.com')) {
                                                $browser = 'Google Chrome / Android';
                                            } elseif (str_contains($sub->endpoint, 'updates.push.services.mozilla.com')) {
                                                $browser = 'Mozilla Firefox';
                                            } elseif (str_contains($sub->endpoint, 'web.push.apple.com')) {
                                                $browser = 'Apple Safari';
                                            }
                                        @endphp
                                        <span class="badge bg-label-info border shadow-xs d-inline-block mb-1" style="font-size: 0.75rem;">
                                            {{ $browser }}
                                        </span>
                                        <div class="text-wrap text-truncate" style="max-width: 330px;" data-bs-toggle="tooltip" title="{{ $sub->endpoint }}">
                                            <small class="text-muted opacity-75" style="font-size: 0.7rem; line-height: 1;">{{ $sub->endpoint }}</small>
                                        </div>
                                    </td>
                                    <td class="py-2 border-start fw-bold">
                                        <div class="d-flex flex-column">
                                            <span>{{ \Carbon\Carbon::parse($sub->updated_at)->format('d/m/Y') }}</span>
                                            <small class="text-primary" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($sub->updated_at)->format('H:i:s') }} WIB</small>
                                        </div>
                                    </td>
                                    <td class="py-2 border-start text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <form action="{{ route('push-subscription.test', $sub->id) }}" method="POST" class="test-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-primary btn-test-sub" data-bs-toggle="tooltip" title="Kirim Notifikasi Tes">
                                                    <i class="ti ti-bell-ringing"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('push-subscription.deleteAdmin', $sub->id) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger btn-delete-sub" data-bs-toggle="tooltip" title="Hapus Perangkat">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center text-muted">
                                            <i class="ti ti-info-circle fs-1 mb-2"></i>
                                            <p class="mb-0">Tidak ada data push subscription perangkat yang terdaftar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">
            {{ $subscriptions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
    $(function() {
        $(".btn-delete-sub").click(function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: "Apakah Anda Yakin?",
                text: "Langganan push notification perangkat ini akan dihapus dari sistem!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        $(".btn-test-sub").click(function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: "Kirim Notifikasi Uji Coba?",
                text: "Notifikasi push tes akan dikirim ke perangkat ini!",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#7367f0",
                cancelButtonColor: "#8592a3",
                confirmButtonText: "Ya, Kirim!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
