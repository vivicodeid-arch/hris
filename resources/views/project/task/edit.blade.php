@extends('layouts.app')
@section('titlepage', 'Edit Task')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Edit Task - {{ $task->kode_task }}
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Perbarui penugasan atau status pada task project: <strong>{{ $project->nama_project }}</strong>.
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
                <li class="breadcrumb-item">
                    <a href="{{ route('project.show', Crypt::encrypt($project->id)) }}">{{ $project->nama_project }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('project.task.show', Crypt::encrypt($task->id)) }}">{{ $task->kode_task }}</a>
                </li>
                <li class="breadcrumb-item active">Edit</li>
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
                <i class="ti ti-edit me-2 fs-5"></i>
                <h6 class="card-title mb-0 text-white">Form Edit Task</h6>
            </div>
            
            <div class="card-body py-4">
                @include('project.task.edit_form')
            </div>
        </div>
    </div>
</div>
@endsection
