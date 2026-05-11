@extends('layouts.app')

@section('title', 'Admin: '.$admin->name)

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Admin details</h1>
            <div class="text-muted fs-13">Update profile, password, and module access.</div>
        </div>
        <div class="ms-md-1 ms-0">
            <a href="{{ route('admins.index') }}" class="btn btn-secondary">Back to list</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card custom-card">
        <div class="card-body">
            <form action="{{ route('admins.update', $admin) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        @php($viewable = $admin->superAdminViewablePassword())
                        <label class="form-label">Password (visible to Super Admin)</label>
                        @if($viewable !== null)
                            <input type="text" class="form-control font-monospace" readonly value="{{ $viewable }}" autocomplete="off">
                            <div class="form-text">Last password set from this form. Login still uses a secure hash; this copy is encrypted in the database (APP_KEY).</div>
                        @else
                            <p class="text-warning border border-warning border-opacity-25 rounded-2 py-2 px-3 small mb-3">No saved viewable copy yet. The database only has a one-way hash, so the old password cannot be “fetched.” If you still know what this admin logs in with, enter it below and save — we verify it against the hash and store an encrypted Super Admin copy without changing their password.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Their current login password</label>
                                    <input type="password" name="reveal_current_password" class="form-control @error('reveal_current_password') is-invalid @enderror" autocomplete="off">
                                    @error('reveal_current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm current password</label>
                                    <input type="password" name="reveal_current_password_confirmation" class="form-control" autocomplete="off">
                                </div>
                            </div>
                            <div class="form-text mb-0">Or set a brand-new password in the fields below (that replaces login and the viewable copy).</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Set new password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current" autocomplete="new-password">
                        <div class="form-text">If you type a new password, login is updated and the visible copy above is replaced.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm new password</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>

                <div class="card custom-card mt-4 mb-0">
                    <div class="card-header">
                        <div class="card-title">Module permissions</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($modules as $key => $name)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="module_permissions[]" value="{{ $key }}" id="mod_{{ $key }}"
                                            {{ in_array($key, old('module_permissions', $admin->module_permissions ?? []), true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="mod_{{ $key }}">{{ $name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
