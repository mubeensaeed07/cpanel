@extends('layouts.app')

@section('title', 'Create Admin')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Create Admin</h1>
            <div class="text-muted fs-13">Assign access module-wise (all or selected modules).</div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card custom-card">
        <div class="card-body">
        <form action="{{ route('admins.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-text">Super Admin can open this admin later and see this password (stored encrypted with APP_KEY).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="card custom-card mt-4 mb-0">
                <div class="card-header">
                    <div class="card-title">Module Permissions</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                    @foreach($modules as $key => $name)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="module_permissions[]" value="{{ $key }}" id="module_{{ $key }}"
                                    {{ in_array($key, old('module_permissions', []), true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="module_{{ $key }}">{{ $name }}</label>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Admin</button>
                <a href="{{ route('admins.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        </div>
    </div>
@endsection
