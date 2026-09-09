@extends('layouts.app')
@section('titlepage', 'Gaji Pokok')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Gaji Pokok
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen data gaji pokok karyawan.
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
                        <i class="ti ti-database ti-xs me-1"></i> Data Master
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-cash ti-xs me-1"></i> Gaji Pokok
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            @can('gajipokok.create')
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-primary" id="btnCreate">
                        <i class="ti ti-plus me-1"></i> Tambah Gaji Pokok
                    </a>
                    <a href="#" class="btn btn-success" id="btnImport">
                        <i class="ti ti-file-import me-1"></i> Import Data
                    </a>
                    <button type="button" class="btn btn-danger d-none" id="btnDeleteMultiple">
                        <i class="ti ti-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>
            @endcan
        </div>
        <form action="{{ route('gajipokok.index') }}">
            <div class="row g-2 mb-3">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <x-input-with-icon label="Cari Nama Karyawan" value="{{ Request('nama_karyawan') }}" name="nama_karyawan"
                        icon="ti ti-search" hideLabel />
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12">
                    <x-select label="Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang"
                        selected="{{ Request('kode_cabang') }}" hideLabel />
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12">
                    <x-select label="Departemen" name="kode_dept" :data="$departemen" key="kode_dept" textShow="nama_dept"
                        selected="{{ Request('kode_dept') }}" upperCase="true" hideLabel />
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <x-input-with-icon label="Tanggal Berlaku" value="{{ Request('tanggal') }}" name="tanggal"
                        icon="ti ti-calendar" hideLabel datepicker="flatpickr-date" />
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12">
                    <button class="btn btn-primary w-100"><i class="ti ti-search me-1"></i> Cari</button>
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
                    <i class="ti ti-layout-grid me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Data Gaji Pokok</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3 text-center" style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th class="text-white py-3">KODE GAJI</th>
                                <th class="text-white py-3">NIK</th>
                                <th class="text-white py-3">NAMA KARYAWAN</th>
                                <th class="text-white py-3">DEPT</th>
                                <th class="text-white py-3">CABANG</th>
                                <th class="text-white py-3">JENIS UPAH</th>
                                <th class="text-white py-3 text-end">JUMLAH</th>
                                <th class="text-white py-3">BERLAKU</th>
                                <th class="text-white py-3 text-center" style="width: 100px;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gajipokok as $d)
                                <tr>
                                    <td class="py-2 text-center">
                                        <input type="checkbox" class="form-check-input checkItem" name="kode_gaji[]" value="{{ $d->kode_gaji }}">
                                    </td>
                                    <td class="py-2">{{ $d->kode_gaji }}</td>
                                    <td class="py-2">{{ $d->nik_show ?? $d->nik }}</td>
                                    <td class="py-2 fw-bold text-primary">{{ $d->nama_karyawan }}</td>
                                    <td class="py-2">{{ $d->kode_dept }}</td>
                                    <td class="py-2">{{ $d->kode_cabang }}</td>
                                    <td class="py-2">
                                        @if ($d->jenis_upah == 'Harian')
                                            <span class="badge bg-label-info">Harian</span>
                                        @else
                                            <span class="badge bg-label-success">Bulanan</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-end fw-bold">{{ formatAngka($d->jumlah) }}</td>
                                    <td class="py-2">{{ date('d-m-Y', strtotime($d->tanggal_berlaku)) }}</td>
                                    <td class="py-2 text-center">
                                        <div class="d-inline-flex border rounded overflow-hidden shadow-xs">
                                            @can('gajipokok.edit')
                                                <a href="#" class="btn btn-sm btnEdit px-2 py-1 border-0 rounded-0"
                                                    kode_gaji="{{ Crypt::encrypt($d->kode_gaji) }}" title="Edit"
                                                    style="background: #f8f9fa;">
                                                    <i class="ti ti-edit fs-6 text-primary"></i>
                                                </a>
                                            @endcan

                                            @can('gajipokok.delete')
                                                <form method="POST" name="deleteform" class="deleteform m-0"
                                                    action="{{ route('gajipokok.delete', Crypt::encrypt($d->kode_gaji)) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm delete-confirm px-2 py-1 border-0 rounded-0 border-start"
                                                        title="Hapus" style="background: #f8f9fa;">
                                                        <i class="ti ti-trash fs-6 text-danger"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($gajipokok->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-end">
            {{ $gajipokok->links() }}
        </div>
    </div>
</div>
<x-modal-form id="modal" show="loadmodal" />
@endsection

@push('myscript')
<script>
    $(function() {
        $(".flatpickr-date").flatpickr({
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d",
        });

        function loading() {
            $("#loadmodal").html(`<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            </div>`);
        };
        loading();

        $("#btnCreate").click(function() {
            $("#modal").modal("show");
            $(".modal-title").text("Tambah Data Gaji Pokok");
            $("#loadmodal").load("{{ route('gajipokok.create') }}");
        });

        $("#btnImport").click(function() {
            $("#modal").modal("show");
            $(".modal-title").text("Import Data Gaji Pokok");
            $("#loadmodal").load("{{ route('gajipokok.import') }}");
        });

        $(".btnEdit").click(function() {
            loading();
            const kode_gaji = $(this).attr("kode_gaji");
            $("#modal").modal("show");
            $(".modal-title").text("Edit Gaji Pokok");
            $("#loadmodal").load(`/gajipokok/${kode_gaji}/edit`);
        });

        // Multiple Delete Logic
        $("#checkAll").click(function() {
            $(".checkItem").prop('checked', $(this).prop('checked'));
            showDeleteButton();
        });

        $(".checkItem").click(function() {
            showDeleteButton();
        });

        function showDeleteButton() {
            const checkedCount = $(".checkItem:checked").length;
            if (checkedCount > 0) {
                $("#btnDeleteMultiple").removeClass("d-none");
            } else {
                $("#btnDeleteMultiple").addClass("d-none");
            }
        }

        $("#btnDeleteMultiple").click(function() {
            const checkedCount = $(".checkItem:checked").length;
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Anda akan menghapus " + checkedCount + " data gaji pokok sekaligus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const selectedIds = [];
                    $(".checkItem:checked").each(function() {
                        selectedIds.push($(this).val());
                    });

                    // Create dynamic form for delete multiple
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': "{{ route('gajipokok.delete_multiple') }}"
                    });

                    const token = $('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': "{{ csrf_token() }}"
                    });

                    const method = $('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': "DELETE"
                    });

                    form.append(token, method);

                    selectedIds.forEach(id => {
                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': 'kode_gaji[]',
                            'value': id
                        }));
                    });

                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
