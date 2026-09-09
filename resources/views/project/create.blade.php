@extends('layouts.app')
@section('titlepage', 'Buat Project Baru')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Buat Project
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Daftarkan project baru dan koordinasikan tim pengerjaan.
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
                    <a href="{{ route('project.index') }}">Projects</a>
                </li>
                <li class="breadcrumb-item active">Buat Project</li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row justify-content-center">
    <div class="col-lg-10 col-md-12 col-sm-12">
        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (Session::get('error'))
            <div class="alert alert-danger mb-3">
                {{ Session::get('error') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <i class="ti ti-plus me-2 fs-5"></i>
                <h6 class="card-title mb-0 text-white">Form Project Baru</h6>
            </div>
            
            <div class="card-body py-4">
                @include('project.form')
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
    $(function() {
        // Auto format rupiah input for budget
        $('#budget').on('input', function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            if(val) {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            } else {
                $(this).val('');
            }
        });
    });
</script>
@endpush
