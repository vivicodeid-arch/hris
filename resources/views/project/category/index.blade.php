@extends('layouts.app')
@section('titlepage', 'Kategori Project')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100" style="text-transform: none !important; letter-spacing: normal !important;">
        <div class="text-dark fw-bold" style="font-size: 1.125rem; font-weight: 700 !important; text-transform: none !important;">
            Kategori Project
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: 400 !important; text-transform: none !important; letter-spacing: normal !important; line-height: 1.4;">
                Manajemen kategori pengelompokan project organisasi.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem; text-transform: none !important; font-weight: 400 !important; letter-spacing: normal !important;">
            <ol class="breadcrumb breadcrumb-style1 mb-0" style="text-transform: none !important; font-weight: 400 !important; letter-spacing: normal !important;">
                <li class="breadcrumb-item" style="text-transform: none !important; font-weight: 400 !important;">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="ti ti-home-2 ti-xs"></i>
                    </a>
                </li>
                <li class="breadcrumb-item" style="text-transform: none !important; font-weight: 400 !important;">
                    <a href="javascript:void(0);" style="text-transform: none !important; font-weight: 400 !important;">
                        <i class="ti ti-briefcase ti-xs me-1"></i> Project Management
                    </a>
                </li>
                <li class="breadcrumb-item active" style="text-transform: none !important; font-weight: 400 !important; color: #64748b !important;">
                    Kategori Project
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            @can('projectcategory.create')
                <a href="#" class="btn btn-primary" id="btnCreate">
                    <i class="ti ti-plus me-1"></i> Tambah Kategori
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-tags me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Daftar Kategori</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3" style="width: 60px;">NO.</th>
                                <th class="text-white py-3" style="width: 120px;">WARNA</th>
                                <th class="text-white py-3">NAMA KATEGORI</th>
                                <th class="text-white py-3">DESKRIPSI</th>
                                <th class="text-white py-3 text-center" style="width: 120px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $cat)
                                <tr>
                                    <td class="py-2">{{ $loop->iteration }}</td>
                                    <td class="py-2">
                                        <span class="badge" style="background-color: {{ $cat->warna }}; color: #fff; font-size: 0.75rem;">
                                            {{ $cat->warna }}
                                        </span>
                                    </td>
                                    <td class="fw-bold py-2">{{ $cat->nama_kategori }}</td>
                                    <td class="py-2">{{ $cat->deskripsi ?? '-' }}</td>
                                    <td class="py-2 text-center">
                                        <div class="d-inline-flex border rounded overflow-hidden shadow-xs">
                                            @can('projectcategory.edit')
                                                <a href="#" class="btn btn-sm btnEdit px-2 py-1 border-0 rounded-0"
                                                    kode_cat="{{ Crypt::encrypt($cat->id) }}" title="Edit"
                                                    style="background: #f8f9fa;">
                                                    <i class="ti ti-edit fs-6 text-primary"></i>
                                                </a>
                                            @endcan

                                            @can('projectcategory.delete')
                                                <form method="POST" name="deleteform" class="deleteform m-0"
                                                    action="{{ route('projectcategory.delete', Crypt::encrypt($cat->id)) }}">
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
                            @if($categories->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Data kategori kosong.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
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
        }

        $("#btnCreate").click(function() {
            loading();
            $("#modal").modal("show");
            $(".modal-title").text("Tambah Kategori Project");
            $("#loadmodal").load("{{ route('projectcategory.create') }}");
        });

        $(".btnEdit").click(function() {
            loading();
            const id = $(this).attr("kode_cat");
            $("#modal").modal("show");
            $(".modal-title").text("Edit Kategori Project");
            $("#loadmodal").load(`/projectcategory/${id}/edit`);
        });

        // Setup generic delete confirmation if present in layout or using simple confirm
        $('.delete-confirm').click(function(e) {
            var form = $(this).closest("form");
            e.preventDefault();
            if (confirm("Apakah Anda yakin ingin menghapus kategori ini?")) {
                form.submit();
            }
        });
    });
</script>
@endpush
