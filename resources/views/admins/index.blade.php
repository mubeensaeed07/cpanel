@extends('layouts.app')

@section('title', 'Admins')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Admins</h1>
            <div class="text-muted fs-13">Manage module-wise admin accounts.</div>
        </div>
        <div class="ms-md-1 ms-0">
            <a href="{{ route('admins.create') }}" class="btn btn-primary">Create Admin</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card custom-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table text-nowrap table-bordered">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Module Permissions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                {{ collect($admin->module_permissions)->map(fn ($key) => $modules[$key] ?? $key)->implode(', ') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admins.show', $admin) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted">No admins created yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $admins->links() }}
            </div>
        </div>
    </div>
@endsection
