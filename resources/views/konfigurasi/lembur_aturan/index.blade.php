@extends('layouts.app')
@section('titlepage', 'Aturan Lembur')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Aturan Lembur
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen faktor pengali upah lembur berjenjang dan nominal lembur khusus karyawan.
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
                        <i class="ti ti-settings ti-xs me-1"></i> Konfigurasi
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-clock-play ti-xs me-1"></i> Aturan Lembur
                </li>
            </ol>
        </nav>
    </div>
@endsection

{{-- Info Banner --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="rounded-3 px-4 py-3 d-flex align-items-start gap-3"
             style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                 style="width: 36px; height: 36px; background: var(--theme-color-1); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <i class="ti ti-bulb text-white" style="font-size: 18px;"></i>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.85rem; color: #1e293b; margin-bottom: 2px;">Tentang Aturan Lembur</div>
                <div style="font-size: 0.8rem; color: #475569; line-height: 1.5;">
                    Aturan ini menentukan faktor pengali untuk menghitung <strong style="color: var(--theme-color-1);">"Jam Netto"</strong> lembur.
                    Contoh: jika karyawan lembur 2 jam di hari kerja, maka jam netto = <strong style="color: var(--theme-color-1);">(1×1.5) + (1×2.0) = 3.5 jam</strong>.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-hari-kerja">
                        <i class="ti ti-briefcase me-1 ti-xs"></i> Hari Kerja
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-hari-libur">
                        <i class="ti ti-calendar-off me-1 ti-xs"></i> Hari Libur
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-lembur-khusus">
                        <i class="ti ti-users me-1 ti-xs"></i> Lembur Khusus Karyawan
                        @if($lembur_khusus->count() > 0)
                            <span class="badge rounded-pill bg-label-primary ms-1">{{ $lembur_khusus->count() }}</span>
                        @endif
                    </button>
                </li>
            </ul>
            <div class="tab-content p-0 shadow-none border-0 mt-3" style="background: transparent;">
                {{-- Tab Hari Kerja --}}
                <div class="tab-pane fade show active" id="navs-hari-kerja" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        {{-- Card Header --}}
                        <div class="px-4 py-3 d-flex justify-content-between align-items-center"
                            style="background: var(--theme-color-1); min-height: 60px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                    style="width: 34px; height: 34px; background: rgba(255,255,255,0.2);">
                                    <i class="ti ti-briefcase text-white" style="font-size: 18px;"></i>
                                </div>
                                <h6 class="mb-0 text-white" style="font-weight: 600; font-size: 0.95rem;">Hari Kerja (Senin - Sabtu)</h6>
                            </div>
                            <a href="#" class="btn btn-sm d-flex align-items-center gap-1 btnCreate" data-tipe="1"
                            style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #fff; border-radius: 8px;">
                                <i class="ti ti-plus" style="font-size: 14px;"></i> Tambah Aturan
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: var(--theme-color-1);">
                                            <th class="py-2 px-4" style="color: white;">Rentang Jam</th>
                                            <th class="py-2 px-4 text-center" style="color: white;">Faktor</th>
                                            <th class="py-2 px-4 text-center" style="color: white;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($aturan_kerja as $d)
                                            <tr>
                                                <td class="py-2 px-4">
                                                    <span style="font-weight: 600;">{{ number_format($d->jam_dari, 1) }} - {{ $d->jam_sampai < 99 && $d->jam_sampai > 0 ? number_format($d->jam_sampai, 1) : 'Seterusnya' }} jam</span>
                                                </td>
                                                <td class="py-2 px-4 text-center">
                                                    <span class="badge bg-primary text-white" style="font-weight: 700;">{{ $d->faktor }}×</span>
                                                </td>
                                                <td class="py-2 px-4 text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="#" class="btnEdit btn btn-xs btn-label-secondary" data-id="{{ $d->id }}"><i class="ti ti-pencil ti-xs"></i></a>
                                                        <form method="POST" class="deleteform m-0" action="{{ route('lemburaturan.delete', $d->id) }}">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="delete-confirm btn btn-xs btn-label-danger"><i class="ti ti-trash ti-xs"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data aturan hari kerja.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab Hari Libur --}}
                <div class="tab-pane fade" id="navs-hari-libur" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="px-4 py-3 d-flex justify-content-between align-items-center"
                            style="background: #ea580c; min-height: 60px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                    style="width: 34px; height: 34px; background: rgba(255,255,255,0.2);">
                                    <i class="ti ti-calendar-off text-white" style="font-size: 18px;"></i>
                                </div>
                                <h6 class="mb-0 text-white" style="font-weight: 600; font-size: 0.95rem;">Hari Libur (Minggu & Nasional)</h6>
                            </div>
                            <a href="#" class="btn btn-sm d-flex align-items-center gap-1 btnCreate" data-tipe="2"
                            style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #fff; border-radius: 8px;">
                                <i class="ti ti-plus" style="font-size: 14px;"></i> Tambah Aturan
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: #ea580c;">
                                            <th class="py-2 px-4" style="color: white;">Rentang Jam</th>
                                            <th class="py-2 px-4 text-center" style="color: white;">Faktor</th>
                                            <th class="py-2 px-4 text-center" style="color: white;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($aturan_libur as $d)
                                            <tr>
                                                <td class="py-2 px-4">
                                                    <span style="font-weight: 600;">{{ number_format($d->jam_dari, 1) }} - {{ $d->jam_sampai < 99 && $d->jam_sampai > 0 ? number_format($d->jam_sampai, 1) : 'Seterusnya' }} jam</span>
                                                </td>
                                                <td class="py-2 px-4 text-center">
                                                    <span class="badge bg-warning text-white" style="font-weight: 700;">{{ $d->faktor }}×</span>
                                                </td>
                                                <td class="py-2 px-4 text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="#" class="btnEdit btn btn-xs btn-label-secondary" data-id="{{ $d->id }}"><i class="ti ti-pencil ti-xs"></i></a>
                                                        <form method="POST" class="deleteform m-0" action="{{ route('lemburaturan.delete', $d->id) }}">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="delete-confirm btn btn-xs btn-label-danger"><i class="ti ti-trash ti-xs"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data aturan hari libur.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab Lembur Khusus --}}
                <div class="tab-pane fade" id="navs-lembur-khusus" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="px-4 py-3 d-flex justify-content-between align-items-center"
                            style="background: #1e293b; min-height: 60px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                    style="width: 34px; height: 34px; background: rgba(255,255,255,0.2);">
                                    <i class="ti ti-users text-white" style="font-size: 18px;"></i>
                                </div>
                                <h6 class="mb-0 text-white" style="font-weight: 600; font-size: 0.95rem;">Lembur Khusus Karyawan</h6>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('lemburaturan.index') }}" method="GET" class="d-none d-md-flex">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="nama_karyawan_search" class="form-control" placeholder="Cari nama..." value="{{ request('nama_karyawan_search') }}">
                                        <button class="btn btn-outline-light" type="submit"><i class="ti ti-search"></i></button>
                                    </div>
                                </form>
                                <a href="#" class="btn btn-sm d-flex align-items-center gap-1 btnCreateKhusus"
                                style="background: var(--theme-color-1); border: none; color: #fff; border-radius: 8px; padding: 6px 14px;">
                                    <i class="ti ti-user-plus" style="font-size: 14px;"></i> Tambah
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: #334155;">
                                            <th class="py-2 px-4" style="color: white;">Karyawan</th>
                                            <th class="py-2 px-4" style="color: white;">Jabatan</th>
                                            <th class="py-2 px-4 text-center" style="color: white;">Upah/Jam</th>
                                            <th class="py-2 px-4 text-center" style="color: white;">Status</th>
                                            <th class="py-2 px-4 text-center" style="color: white;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($lembur_khusus as $d)
                                            <tr>
                                                <td class="py-2 px-4">
                                                    <div class="d-flex flex-column">
                                                        <span style="font-weight: 600;">{{ $d->nama_karyawan }}</span>
                                                        <small class="text-muted">{{ $d->nik }}</small>
                                                    </div>
                                                </td>
                                                <td class="py-2 px-4">{{ $d->nama_jabatan }}</td>
                                                <td class="py-2 px-4 text-center">
                                                    <span class="text-success font-weight-bold" style="font-weight: 700;">Rp {{ number_format($d->upah_perjam, 0, ',', '.') }}</span>
                                                </td>
                                                <td class="py-2 px-4 text-center">
                                                    @if($d->status == 1)
                                                        <span class="badge bg-label-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-label-danger">Non-Aktif</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-4 text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="#" class="btnEditKhusus btn btn-xs btn-label-secondary" data-id="{{ $d->id }}" title="Edit"><i class="ti ti-pencil ti-xs"></i></a>
                                                        <form method="POST" class="deleteform m-0" action="{{ route('lemburaturan.deletekhusus', $d->id) }}">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="delete-confirm btn btn-xs btn-label-danger" title="Hapus"><i class="ti ti-trash ti-xs"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data lembur khusus.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Simulasi Perhitungan --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="px-4 py-3 d-flex align-items-center gap-2" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <div class="rounded-2 d-flex align-items-center justify-content-center"
                     style="width: 30px; height: 30px; background: #f1f5f9;">
                    <i class="ti ti-calculator" style="font-size: 16px; color: #64748b;"></i>
                </div>
                <h6 class="mb-0" style="font-weight: 600; font-size: 0.88rem; color: #1e293b;">Contoh Simulasi Perhitungan</h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-4">
                    {{-- Contoh Hari Kerja --}}
                    <div class="col-md-6">
                        <div class="rounded-3 p-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ti ti-briefcase" style="color: var(--theme-color-1); font-size: 16px;"></i>
                                <span style="font-weight: 600; font-size: 0.82rem; color: #1e293b;">Lembur 3 Jam — Hari Kerja</span>
                            </div>
                            <div style="font-size: 0.78rem; color: #475569; line-height: 1.7;">
                                @if($aturan_kerja->count() > 0)
                                    @php
                                        $contoh_jam = 3;
                                        $total_netto = 0;
                                        $detail_parts = [];
                                        foreach($aturan_kerja as $rule) {
                                            $start = $rule->jam_dari; 
                                            $end = $rule->jam_sampai ?: 99;
                                            
                                            $jam_di_tier_ini = max(0, min($contoh_jam, $end) - $start);
                                            
                                            if ($jam_di_tier_ini > 0) {
                                                $netto = $jam_di_tier_ini * $rule->faktor;
                                                $total_netto += $netto;
                                                $detail_parts[] = 'Jam ' . number_format($start, 1) . '-' . number_format($end, 1) . ' (' . number_format($jam_di_tier_ini, 1) . ' jam) × ' . $rule->faktor . ' = ' . number_format($netto, 1);
                                            }
                                        }
                                    @endphp
                                    @foreach($detail_parts as $part)
                                        <div>• {{ $part }}</div>
                                    @endforeach
                                    <div class="mt-2 pt-2" style="border-top: 1px dashed #e2e8f0;">
                                        <strong style="color: var(--theme-color-1);">Jam Netto = {{ number_format($total_netto, 1) }} jam</strong>
                                    </div>
                                @else
                                    <span class="text-muted">Tambahkan aturan untuk melihat simulasi.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- Contoh Hari Libur --}}
                    <div class="col-md-6">
                        <div class="rounded-3 p-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ti ti-calendar-off" style="color: #ea580c; font-size: 16px;"></i>
                                <span style="font-weight: 600; font-size: 0.82rem; color: #1e293b;">Lembur 9 Jam — Hari Libur</span>
                            </div>
                            <div style="font-size: 0.78rem; color: #475569; line-height: 1.7;">
                                @if($aturan_libur->count() > 0)
                                    @php
                                        $contoh_jam = 9;
                                        $total_netto = 0;
                                        $detail_parts = [];
                                        foreach($aturan_libur as $rule) {
                                            $start = $rule->jam_dari; 
                                            $end = $rule->jam_sampai ?: 99;
                                            
                                            $jam_di_tier_ini = max(0, min($contoh_jam, $end) - $start);
                                            
                                            if ($jam_di_tier_ini > 0) {
                                                $netto = $jam_di_tier_ini * $rule->faktor;
                                                $total_netto += $netto;
                                                $detail_parts[] = 'Jam ' . number_format($start, 1) . '-' . number_format($end, 1) . ' (' . number_format($jam_di_tier_ini, 1) . ' jam) × ' . $rule->faktor . ' = ' . number_format($netto, 1);
                                            }
                                        }
                                    @endphp
                                    @foreach($detail_parts as $part)
                                        <div>• {{ $part }}</div>
                                    @endforeach
                                    <div class="mt-2 pt-2" style="border-top: 1px dashed #e2e8f0;">
                                        <strong style="color: #ea580c;">Jam Netto = {{ number_format($total_netto, 1) }} jam</strong>
                                    </div>
                                @else
                                    <span class="text-muted">Tambahkan aturan untuk melihat simulasi.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal-form id="modal" show="loadmodal" />
@endsection

@push('myscript')
<script>
    $(function() {
        function loading() {
            $("#loadmodal").html(`<div class="sk-wave sk-primary" style="margin:auto">
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                </div>`);
        };

        // Auto-activate Lembur Khusus tab if searching
        @if(request('nama_karyawan_search'))
            const tabKhusus = new bootstrap.Tab(document.querySelector('button[data-bs-target="#navs-lembur-khusus"]'));
            tabKhusus.show();
        @endif

        $(".btnCreate").click(function(e) {
            e.preventDefault();
            loading();
            const tipe = $(this).data("tipe");
            $('#modal').modal("show");
            $(".modal-title").text("Tambah Aturan Lembur");
            $("#loadmodal").load('/lemburaturan/create?tipe_hari=' + tipe);
        });

        $(".btnEdit").click(function(e) {
            e.preventDefault();
            loading();
            var id = $(this).data("id");
            $('#modal').modal("show");
            $(".modal-title").text("Edit Aturan Lembur");
            $("#loadmodal").load('/lemburaturan/edit?id=' + id);
        });

        $(".btnCreateKhusus").click(function(e) {
            e.preventDefault();
            loading();
            $('#modal').modal("show");
            $(".modal-title").text("Tambah Lembur Khusus Karyawan");
            $("#loadmodal").load('/lemburaturan/createkhusus');
        });

        $(".btnEditKhusus").click(function(e) {
            e.preventDefault();
            loading();
            var id = $(this).data("id");
            $('#modal').modal("show");
            $(".modal-title").text("Edit Lembur Khusus Karyawan");
            $("#loadmodal").load('/lemburaturan/editkhusus?id=' + id);
        });
    });
</script>
@endpush
