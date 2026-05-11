@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Dashboard</h1>
            <div class="text-muted fs-13">Super Admin can create admins and assign module-level permissions.</div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Total Admins</p>
                            <h3 class="fw-semibold mb-0">{{ $stats['admins'] }}</h3>
                        </div>
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="bx bx-user fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted">Available Modules</p>
                            <h3 class="fw-semibold mb-0">{{ $stats['modules'] }}</h3>
                        </div>
                        <span class="avatar avatar-md bg-success-transparent">
                            <i class="bx bx-grid-alt fs-18"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header justify-content-between">
            <div class="card-title">Module Shortcuts</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
            @foreach($modules as $moduleKey => $moduleName)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('modules.show', $moduleKey) }}" class="btn btn-outline-primary w-100">{{ $moduleName }}</a>
                </div>
            @endforeach
            </div>
        </div>
    </div>
@endsection
